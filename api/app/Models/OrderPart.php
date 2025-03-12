<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'order_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}