<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers\ItemsRelationManager;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Vendor;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $vendor = Vendor::find($state);

                                if (!$vendor) {
                                    return;
                                }
                                $sets = [
                                    'vendor_email' => $vendor->email,
                                    'vendor_bank_name' => $vendor->bank_name,
                                    'vendor_bank_account' => $vendor->bank_account,
                                ];

                                foreach ($sets as $key => $value) {
                                    $set($key, $value);
                                }
                            }),
                        TextInput::make('vendor_email')
                            ->required()
                            ->email(),
                        TextInput::make('vendor_bank_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('vendor_bank_account')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('total_price')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->maxValue(5000000)
                            ->live()
                            ->helperText(function (Get $get) {
                                $totalPrice = $get('total_price');
                                if ($totalPrice > 5000000) {
                                    return 'The total price must not exceed Rp 5,000,000.';
                                }
                            })
                            ->afterStateUpdated(fn () => $this->validateOnly('data.total_price'))
                            ->placeholder(function (Get $get, Set $set) {
                                $sum = 0;
                                foreach ($get('items') as $item) {
                                    $sum = $sum + $item['subtotal'];
                                }
                                $set('total_price', $sum);
                                return $sum;
                            }),
                        TextInput::make('after_tax_price')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->placeholder(function (Get $get, Set $set) {
                                $totalPrice = round($get('total_price') * 1.11, 2);
                                $set('after_tax_price', $totalPrice);
                                return $totalPrice;
                            })
                    ]),
                Section::make()
                    ->columns(1)
                    ->schema([static::getItemsRepeater()]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Transaction date')
                    ->dateTime()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_price')
                    ->prefix('Rp')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('after_tax_price')
                    ->prefix('Rp')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $selectedProducts = collect($get('items'));

        $total = $selectedProducts->reduce(function ($total, $product) {
            return $total + ($product['price'] * $product['quantity']);
        }, 0);

        $set('total_price', $total);
        $set('after_tax_price', $total * 1.11);
    }

    public static function updateSubtotal(Get $get, Set $set): void
    {
        $price = $get('price');
        $quantity = $get('quantity');

        $set('subtotal', $price * $quantity);
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->schema([
                Select::make('item_id')
                    ->relationship('item', 'name', function (Builder $query) {
                        $today = today();
                        $query->whereDoesntHave('transactionItems', function (Builder $query) use ($today) {
                            $query->whereDate('updated_at', $today)
                                ->where('quantity', '>=', 2);
                        });
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        $item = Item::find($state);

                        if (!$item) {
                            return;
                        }
                        $set('price', $item->price);
                        self::updateSubtotal($get, $set);
                        self::updateTotals($get, $set);
                    })
                    ->afterStateHydrated(function ($state, Set $set, Get $get) {
                        $item = Item::find($state);

                        if (!$item) {
                            return;
                        }
                        $set('price', $item->price);
                        self::updateSubtotal($get, $set);
                        self::updateTotals($get, $set);
                    }),
                TextInput::make('price')
                    ->numeric()
                    ->readOnly()
                    ->prefix('Rp')
                    ->live(),
                TextInput::make('quantity')
                    ->default(1)
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(2)
                    ->rules([
                        function (Get $get) {
                            return function ($attribute, $value, Closure $fail) use ($get) {
                                $itemId = $get('item_id');
                                $quantityOrderedToday = TransactionItem::where('item_id', $itemId)
                                    ->whereDate('updated_at', today())
                                    ->sum('quantity');
                                if ($quantityOrderedToday + $value > 2) {
                                    $fail('The quantity of this item has reached the limit of 2 for today.');
                                }
                            };
                        }
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        self::updateSubtotal($get, $set);
                        self::updateTotals($get, $set);
                    })
                    ->afterStateHydrated(function (Get $get, Set $set) {
                        self::updateSubtotal($get, $set);
                        self::updateTotals($get, $set);
                    }),
                TextInput::make('subtotal')
                    ->disabled()
                    ->numeric()
                    ->prefix('Rp')
                    ->live()
            ])
            ->live()
            ->afterStateUpdated(function (Get $get, Set $set) {
                self::updateSubtotal($get, $set);
                self::updateTotals($get, $set);
            })
            ->afterStateHydrated(function (Get $get, Set $set) {
                self::updateSubtotal($get, $set);
                self::updateTotals($get, $set);
            })
            ->deleteAction(
                fn (Action $action) => $action->after(fn (Get $get, Set $set) => self::updateTotals($get, $set)),
            )
            ->reorderable(false)
            ->columns(4)
            ->minItems(1);
    }
}
