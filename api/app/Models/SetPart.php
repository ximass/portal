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
        'material_id',
        'quantity',
        'unit_net_weight',
        'net_weight',
        'unit_gross_weight',
        'gross_weight',
        'unit_value',
        'final_value',

        // bar and sheet
        'width',
        'length',
        'loss',

        // component
        'markup'
    ];

    public function set()
    {
        return $this->belongsTo(Set::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function processes()
    {
        return $this->belongsToMany(Process::class, 'part_process')
                    ->withPivot('time', 'quantity')
                    ->withTimestamps();
    }
}