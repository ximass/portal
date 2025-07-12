<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sheet extends Model
{
    use SoftDeletes;
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