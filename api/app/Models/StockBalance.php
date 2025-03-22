<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    protected $primaryKey = 'material_id';
    public $incrementing = false;

    protected $fillable = [
        'material_id',
        'balance'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}