<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materials';

    protected $fillable = [
        'name',
        'type',
    ];

    public function sheet()
    {
        return $this->hasOne(Sheet::class, 'material_id');
    }

    public function bar()
    {
        return $this->hasOne(Bar::class, 'material_id');
    }

    public function component()
    {
        return $this->hasOne(Component::class, 'material_id');
    }
}