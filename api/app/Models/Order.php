<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'type',
        'markup',
        'final_value',
        'delivery_type',
        'delivery_value',
        'delivery_date',
        'estimated_delivery_date',
        'payment_obs',
    ];

    protected $casts = [
        'final_value' => 'float',
        'delivery_value' => 'float',
        'markup' => 'float',
        'delivery_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sets()
    {
        return $this->hasMany(Set::class);
    }
}