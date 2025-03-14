<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Set extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'order_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function setParts()
    {
        return $this->hasMany(SetPart::class);
    }
}