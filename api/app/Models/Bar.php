<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bar extends Model
{
    protected $table = 'bars';

    protected $primaryKey = 'material_id';
    public $incrementing = false;

    protected $fillable = [
        'material_id',
        'diameter',
        'length',
        'price_per_gram',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}