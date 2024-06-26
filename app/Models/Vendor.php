<?php

namespace App\Models;

use App\Enums\Bank;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
