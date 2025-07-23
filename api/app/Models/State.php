<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $table = 'states';

    protected $fillable = [
        'name',
        'abbreviation',
        'icms',
    ];

    protected $casts = [
        'icms' => 'decimal:2',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
