<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $casts =["objects" =>"json"];
}
