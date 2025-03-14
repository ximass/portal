<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $fillable = [
        'title', 'content', 'value_per_minute', 'fixed_value'
    ];
}