<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    protected $table = 'components';

    protected $primaryKey = 'material_id';
    public $incrementing = false;

    protected $fillable = [
        'material_id',
        'name',
        'specification',
        'unit_value',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}