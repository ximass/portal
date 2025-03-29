<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sheet extends Model
{
    protected $table = 'sheets';

    protected $primaryKey = 'material_id';
    public $incrementing = false;

    // thickness, width and length are in mm
    // specific_weight is in kg/m3
    // price_kg is in BRL/kg

    protected $fillable = [
        'material_id',
        'thickness',
        'width',
        'length',
        'specific_weight',
        'price_kg',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}