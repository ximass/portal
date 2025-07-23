<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MercosurCommonNomenclature extends Model
{
    use HasFactory;

    protected $table = 'mercosur_common_nomenclatures';

    protected $fillable = [
        'code',
        'ipi',
    ];

    protected $casts = [
        'ipi' => 'decimal:2',
    ];

    public function materials()
    {
        return $this->hasMany(Material::class, 'ncm_id');
    }
}
