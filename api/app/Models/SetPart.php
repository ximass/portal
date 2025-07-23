<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetPart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'secondary_content',
        'obs',
        'set_id',
        'type',
        'locked_values',
        'material_id',
        'sheet_id',
        'bar_id',
        'component_id',
        'quantity',
        'unit_net_weight',
        'net_weight',
        'unit_gross_weight',
        'gross_weight',
        'unit_value',
        'final_value',

        // bar and sheet
        'thickness',
        'width',
        'length',
        'loss',

        // component
        'markup'
    ];

    protected $casts = [
        'locked_values' => 'array',
    ];

    public function getLockedValuesAttribute($value)
    {
        $value = json_decode($value, true);

        return (array) $value;
    }

    public function set()
    {
        return $this->belongsTo(Set::class);
    }

    public function material()
    {
        return $this->hasOne(Material::class);
    }

    public function sheet()
    {
        return $this->hasOne(Sheet::class);
    }

    public function bar()
    {
        return $this->hasOne(Bar::class);
    }

    public function component()
    {
        return $this->hasOne(Component::class);
    }

    public function processes()
    {
        return $this->belongsToMany(Process::class, 'part_process')
                    ->withPivot('time', 'final_value')
                    ->withTimestamps();
    }
}