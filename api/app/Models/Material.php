<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materials';

    protected $fillable = [
        'name',
        'thickness',
        'specific_weight',
        'price_kg'
    ];

    public function sheets()
    {
        return $this->hasMany(Sheet::class, 'material_id');
    }

    public function setParts()
    {
        return $this->hasMany(SetPart::class, 'material_id');
    }
}