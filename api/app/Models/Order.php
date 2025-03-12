<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['final_value'];

    public function orderParts()
    {
        return $this->hasMany(OrderPart::class);
    }
}