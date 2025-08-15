<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Set extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'order_id', 
        'content',
        'quantity',
        'ncm_id',
        'reference',
        'obs'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function setParts()
    {
        return $this->hasMany(SetPart::class);
    }

    public function ncm()
    {
        return $this->belongsTo(MercosurCommonNomenclature::class, 'ncm_id');
    }
}