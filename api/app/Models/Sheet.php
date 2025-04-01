<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sheet extends Model
{
    protected $table = 'sheets';

    // thickness, width and length are in mm
    // specific_weight is in kg/m3
    // price_kg is in BRL/kg

    protected $fillable = [
        'material_id',
        'name',
        'width',
        'length',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}