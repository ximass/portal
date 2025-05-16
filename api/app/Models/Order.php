<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'final_value',
        'delivery_type',
        'markup',
        'delivery_date',
        'estimated_delivery_date',
        'payment_obs',
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