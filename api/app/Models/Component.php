<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $table = 'components';

    protected $fillable = [
        'name',
        'specification',
        'unit_value',
        'supplier',
    ];
}