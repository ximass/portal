<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetPart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', // Nome do arquivo
        'content', // Foto primária
        'secondary_content', // Foto secundária
        'reference', // Referência
        'obs', // Observações
        'set_id',
        'type',
        'locked_values',
        'material_id',
        'sheet_id',
        'bar_id',
        'component_id',
        'ncm_id',
        'quantity',
        'unit', // Unidade (piece ou kg)
        'unit_net_weight',
        'net_weight',
        'unit_gross_weight',
        'gross_weight',
        'unit_value',
        'final_value',
        'unit_ipi_value',
        'total_ipi_value',
        'unit_icms_value',
        'total_icms_value',

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
        return $this->belongsTo(Material::class);
    }

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }

    public function bar()
    {
        return $this->belongsTo(Bar::class);
    }

    public function component()
    {
        return $this->belongsTo(Component::class);
    }

    public function ncm()
    {
        return $this->belongsTo(MercosurCommonNomenclature::class, 'ncm_id');
    }

    public function processes()
    {
        return $this->belongsToMany(Process::class, 'part_process')
                    ->withPivot('time', 'final_value')
                    ->withTimestamps();
    }
}