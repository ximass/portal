<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'set_id',
    ];

    public function set()
    {
        return $this->belongsTo(Set::class);
    }
}