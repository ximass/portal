<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sheet extends Model
{
    protected $table = 'sheets';

    protected $primaryKey = 'material_id';
    public $incrementing = false;

    protected $fillable = [
        'material_id',
        'thickness',
        'width',
        'length',
        'price_per_gram',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}