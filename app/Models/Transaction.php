<?php

namespace App\Models;

use App\Enums\Bank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'total_price' => 'float',
            'after_tax_price' => 'float',
        ];
    }
}
