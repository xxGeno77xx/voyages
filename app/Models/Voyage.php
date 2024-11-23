<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voyage extends Model
{
    Public function bills(): HasMany{
        return $this->hasMany(Bill::class);
    }

    Public function expenses(): HasMany{
        return $this->hasMany(Expense::class);
    }
}
