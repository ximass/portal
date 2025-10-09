<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bar extends Model
{
    use SoftDeletes;
    protected $table = 'bars';

    // length is in mm
    // weight is in kg
    // price_kg is in BRL/kg

    protected $fillable = [
        'name',
        'weight',
        'length',
        'price_kg',
        'ncm_id'
    ];

    public function ncm()
    {
        return $this->belongsTo(MercosurCommonNomenclature::class, 'ncm_id');
    }
}