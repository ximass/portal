<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'material_id',
        'transaction_date',
        'operation',
        'quantity',
        'obs'
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}