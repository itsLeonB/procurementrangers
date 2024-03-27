<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    protected function casts(): array
    {
        return [
            'available_date' => 'date:Y-m-d',
            'price' => 'float',
            'weight_kg' => 'float',
        ];
    }
}
