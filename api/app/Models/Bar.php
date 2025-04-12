<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
    protected $table = 'bars';

    // length is in mm
    // weight is in kg
    // price_kg is in BRL/kg

    protected $fillable = [
        'name',
        'weight',
        'length',
        'price_kg'
    ];
}