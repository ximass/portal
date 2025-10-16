<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;
    protected $table = 'materials';

    protected $fillable = [
        'name',
        'specific_weight',
        'price_kg',
        'ncm_id'
    ];

    public function ncm()
    {
        return $this->belongsTo(MercosurCommonNomenclature::class, 'ncm_id');
    }

    public function sheets()
    {
        return $this->hasMany(Sheet::class, 'material_id');
    }

    public function setParts()
    {
        return $this->hasMany(SetPart::class, 'material_id');
    }
}