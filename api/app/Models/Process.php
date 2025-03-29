<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Process extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title', 
        'content', 
        'value_per_minute'
    ];
}