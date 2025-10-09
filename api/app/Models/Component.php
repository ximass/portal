<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Component extends Model
{
    use SoftDeletes;
    protected $table = 'components';

    protected $fillable = [
        'name',
        'specification',
        'unit_value',
        'supplier',
        'ncm_id'
    ];

    public function ncm()
    {
        return $this->belongsTo(MercosurCommonNomenclature::class, 'ncm_id');
    }
}