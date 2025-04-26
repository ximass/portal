<?php

namespace App\Http\Controllers;

use App\Models\SetPart;
use App\Models\Material;
use App\Models\Sheet;
use App\Models\Bar;
use App\Models\Component;
use App\Models\Process;

use App\Http\Controllers\ProcessController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetPartController extends Controller
{
    const SET_PART_TYPES = [
        'material' => 'Material',
        'sheet'    => 'Chapa',
        'bar'      => 'Barra',
        'component'=> 'Componente',
        'process'  => 'Processo',
    ];

    public function index()
    {
        $setParts = SetPart::with('processes')->get();

        return response()->json($setParts);
    }

    public function getSetParts($setId)
    {
        $setParts = SetPart::where('set_id', $setId)
            ->with('processes')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($setParts);
    }

    public function store(Request $request, $setId)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'secondary_content'=> 'sometimes|nullable|string',
            'obs'              => 'sometimes|nullable|string',
            'type'             => 'sometimes|in:material,sheet,bar,component,process',
            'material_id'      => 'sometimes|nullable|integer|exists:materials,id',
            'sheet_id'         => 'sometimes|nullable|integer|exists:sheets,id',
            'bar_id'           => 'sometimes|nullable|integer|exists:bars,id',
            'component_id'     => 'sometimes|nullable|integer|exists:components,id',
            'quantity'         => 'sometimes|nullable|integer',
            'loss'             => 'sometimes|nullable|numeric',
            'unit_net_weight'  => 'sometimes|nullable|numeric',
            'net_weight'       => 'sometimes|nullable|numeric',
            'unit_gross_weight'=> 'sometimes|nullable|numeric',
            'gross_weight'     => 'sometimes|nullable|numeric',
            'unit_value'       => 'sometimes|nullable|numeric',
            'final_value'      => 'sometimes|nullable|numeric',
            'markup'           => 'sometimes|nullable|numeric',
            'width'            => 'sometimes|nullable|numeric',
            'length'           => 'sometimes|nullable|numeric',
            'locked_values'    => 'sometimes|nullable|array',
        ]);

        $setPart = SetPart::create([
            'title'            => $request->title,
            'content'          => $request->content,
            'secondary_content'=> $request->input('secondary_content'),
            'obs'              => $request->input('obs'),
            'set_id'           => $setId,
            'type'             => $request->input('type'),
            'material_id'      => $request->input('material_id'),
            'sheet_id'         => $request->input('sheet_id'),
            'bar_id'           => $request->input('bar_id'),
            'component_id'     => $request->input('component_id'),
            'quantity'         => $request->input('quantity'),
            'loss'             => $request->input('loss'),
            'unit_net_weight'  => $request->input('unit_net_weight'),
            'net_weight'       => $request->input('net_weight'),
            'unit_gross_weight'=> $request->input('unit_gross_weight'),
            'gross_weight'     => $request->input('gross_weight'),
            'unit_value'       => $request->input('unit_value'),
            'final_value'      => $request->input('final_value'),
            'markup'           => $request->input('markup'),
            'width'            => $request->input('width'),
            'length'           => $request->input('length'),
            'locked_values'    => $request->input('locked_values', []),
        ]);

        if ($request->has('processes')) {
            $processes = $request->input('processes');
            $syncData = [];

            foreach ($processes as $process) {
                if (isset($process['id'])) {
                    $syncData[$process['id']] = [
                        'time' => $process['pivot']['time'] ?? 0,
                        'final_value' => $process['pivot']['final_value'] ?? 0,
                    ];
                }
            }

            $setPart->processes()->sync($syncData);
        }

        return response()->json($setPart, 201);
    }

    public function show($id)
    {
        $setPart = SetPart::with('processes')->findOrFail($id);

        return response()->json($setPart->toArray());
    }

    public function update(Request $request, $setId, $id)
    {
        $request->validate([
            'title'            => 'sometimes|required|string|max:255',
            'content'          => 'sometimes|required|string',
            'secondary_content'=> 'sometimes|nullable|string',
            'obs'              => 'sometimes|nullable|string',
            'type'             => 'sometimes|in:material,sheet,bar,component,process',
            'material_id'      => 'sometimes|nullable|integer|exists:materials,id',
            'sheet_id'         => 'sometimes|nullable|integer|exists:sheets,id',
            'bar_id'           => 'sometimes|nullable|integer|exists:bars,id',
            'component_id'     => 'sometimes|nullable|integer|exists:components,id',
            'quantity'         => 'sometimes|nullable|integer',
            'loss'             => 'sometimes|nullable|numeric',
            'unit_net_weight'  => 'sometimes|nullable|numeric',
            'net_weight'       => 'sometimes|nullable|numeric',
            'unit_gross_weight'=> 'sometimes|nullable|numeric',
            'gross_weight'     => 'sometimes|nullable|numeric',
            'unit_value'       => 'sometimes|nullable|numeric',
            'final_value'      => 'sometimes|nullable|numeric',
            'markup'           => 'sometimes|nullable|numeric',
            'width'            => 'sometimes|nullable|numeric',
            'length'           => 'sometimes|nullable|numeric',
            'locked_values'    => 'sometimes|nullable|array',
        ]);

        $setPart = SetPart::where('set_id', $setId)->findOrFail($id);
        $setPart->update($request->only(
            'title',
            'content',
            'secondary_content',
            'obs',
            'type',
            'material_id',
            'sheet_id',
            'bar_id',
            'component_id',
            'quantity',
            'loss',
            'unit_net_weight',
            'net_weight',
            'unit_gross_weight',
            'gross_weight',
            'unit_value',
            'final_value',
            'markup',
            'width',
            'length',
            'locked_values',
        ));

        if ($request->has('processes')) {
            $processes = $request->input('processes');
            $syncData = [];

            foreach ($processes as $process) {
                if (isset($process['id'])) {
                    $syncData[$process['id']] = [
                        'time' => $process['pivot']['time'] ??  0,
                        'final_value' => $process['pivot']['final_value'] ?? 0,
                    ];
                }
            }

            $setPart->processes()->sync($syncData);
        }

        return response()->json($setPart);
    }

    public function destroy($id)
    {
        $setPart = SetPart::findOrFail($id);
        $setPart->delete();

        return response()->json(['message' => 'Order part deleted successfully']);
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        $file = $request->file('file');

        $request->validate([
            'file'   => 'required|file|mimes:jpg,jpeg,png,pdf,webp',
            'set_id' => 'required|integer',
            'secondary' => 'sometimes|boolean'
        ]);

        $path = $file->store('uploads/order-parts', 'public');

        if ($request->boolean('secondary')) {
            $partId = $request->input('part_id');

            $setPart = SetPart::findOrFail($partId);
            $setPart->secondary_content = Storage::url($path);
            $setPart->save();

            return response()->json($setPart->toArray(), 200);
        } else {
            $setPart = SetPart::create([
                'title'   => $file->getClientOriginalName(),
                'content' => Storage::url($path),
                'set_id'  => $request->input('set_id'),
            ]);

            return response()->json($setPart, 201);
        }
    }

    public static function getPartTypes()
    {
        return response()->json(self::SET_PART_TYPES);
    }

    public function calculatePartProperties(Request $request)
    {
        $partData = $request->input('part');

        $part = new SetPart($partData);
        $part->setAttribute('processes', $request->input('part.processes', []));

        try {
            $calculatedPart = self::calculateProperties($part);
            return response()->json($calculatedPart);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Calcula as propriedades de uma part com base no tipo de material.
     *
     * @param object $part
     * @param object $material Dados do material, incluindo o tipo e suas propriedades específicas.
     * @return object
     */
    public static function calculateProperties(object $part): object
    {
        // Force zero weights and values when quantity is missing or zero
        if (!isset($part->quantity) || $part->quantity == 0) {
            $part->unit_net_weight   = 0;
            $part->net_weight        = 0;
            $part->unit_gross_weight = 0;
            $part->gross_weight      = 0;
            $part->unit_value        = 0;
            $part->final_value       = 0;
            
            return $part;
        }

        if ($part->type === 'material') {
            $part = self::calculateMaterialProperties($part);
        } elseif ($part->type === 'sheet') {
            $part = self::calculateSheetProperties($part);
        } elseif ($part->type === 'bar') {
            $part = self::calculateBarProperties($part);
        } elseif ($part->type === 'component') {
            $part = self::calculateComponentProperties($part);
        } else if ($part->type === 'process') {
            $part->unit_value = 0;
            $part->final_value = 0;
        } else {
            throw new \InvalidArgumentException('Invalid part type');
        }

        $part = self::calculatePartProcesses($part);

        return $part;
    }

    public static function calculateMaterialProperties(object $part)
    {
        return self::calculateSheetProperties($part, true);
    }

    /**
     * Calcula as propriedades de uma part para material do tipo "sheet".
     *
     * @param object $part
     * @param object $sheet Objeto com os dados do sheet: width, length, thickness, specific_weight, price_kg
     * @return object 
     */
    public static function calculateSheetProperties(object $part, $onlyMaterial = false): object
    {
        if ($onlyMaterial)
        {
            $material = Material::findOrFail($part->material_id);
        }
        else
        {
            $sheet = Sheet::findOrFail($part->sheet_id);
            $material = $sheet->material;
        }

        // Converte medidas de mm para m e aplica arredondamento para width e length se necessário
        $widthInMeters     = round($part->width / 1000, 2);
        $lengthInMeters    = round($part->length / 1000, 2);
        $thicknessInMeters = $material->thickness / 1000;
        
        // Peso específico de g/mm³ em kg/m³
        $specificWeight = $material->specific_weight * 1000 * 1000;
        
        // Fator de perda (ex.: se perda for 5%, fator = 0.95) e arredonda loss para 2 casas
        $loss     = isset($part->loss) ? round($part->loss, 2) : 0;
        $factor   = (100 - $loss) / 100;
        $quantity = isset($part->quantity) ? $part->quantity : 0;
        $markup   = !empty($part->set->order->markup) ? $part->set->order->markup : 1;
        
        // Cálculo dos pesos unitários:
        $unitNetWeight   = $widthInMeters * $lengthInMeters * $thicknessInMeters * $specificWeight * $factor;
        $unitGrossWeight = $widthInMeters * $lengthInMeters * $thicknessInMeters * $specificWeight;

        // Pesos totais
        $netWeight   = $unitNetWeight * $quantity;
        $grossWeight = $unitGrossWeight * $quantity;

        // Travamento dos campos unit_net_weight e unit_gross_weight
        if (in_array('unit_net_weight', $part->locked_values) && isset($part->unit_net_weight)) {
            $unitNetWeight = $part->unit_net_weight;
            $netWeight     = $unitNetWeight * $quantity;
        }

        if (in_array('unit_gross_weight', $part->locked_values) && isset($part->unit_gross_weight)) {
            $unitGrossWeight = $part->unit_gross_weight;
            $grossWeight     = $unitGrossWeight * $quantity;
        }

        // Travamento dos campos net_weight e gross_weight (ajusta os unitários)
        if (in_array('net_weight', $part->locked_values) && isset($part->net_weight) && $quantity > 0) {
            $netWeight = $part->net_weight;
            $unitNetWeight = $netWeight / $quantity;
        }
        
        if (in_array('gross_weight', $part->locked_values) && isset($part->gross_weight) && $quantity > 0) {
            $grossWeight = $part->gross_weight;
            $unitGrossWeight = $grossWeight / $quantity;
        }

        // Valor unitário com base no peso unitário e preço por kg
        $unitValue  = $unitNetWeight * $material->price_kg * $markup;

        if (in_array('unit_value', $part->locked_values) && isset($part->unit_value)) {
            $unitValue = $part->unit_value;
        }

        $finalValue = $quantity * $unitValue;

        if (in_array('final_value', $part->locked_values) && isset($part->final_value)) {
            $finalValue = $part->final_value;
        }

        $part->unit_net_weight   = round($unitNetWeight, 2);
        $part->net_weight        = round($netWeight, 2);
        $part->unit_gross_weight = round($unitGrossWeight, 2);
        $part->gross_weight      = round($grossWeight, 2);
        $part->unit_value        = round($unitValue, 2);
        $part->final_value       = round($finalValue, 2);

        return $part;
    }

    /**
     * Calcula as propriedades de uma part para material do tipo "bar".
     *
     * @param object $part
     * @return object
     */
    public static function calculateBarProperties(object $part): object
    {
        $bar = Bar::findOrFail($part->bar_id);

        // Proporção do comprimento utilizado
        $proportion = $bar->length > 0 ? $part->length / $bar->length : 0;
        
        // Peso parcial da barra conforme a proporção
        $partialWeight = $bar->weight * $proportion;
        
        $loss     = isset($part->loss) ? round($part->loss, 2) : 0;
        $factor   = (100 - $loss) / 100;
        $quantity = isset($part->quantity) ? $part->quantity : 0;
        $markup   = !empty($part->set->order->markup) ? $part->set->order->markup : 1;
        
        $unitNetWeight   = $partialWeight * $factor;
        $netWeight       = $unitNetWeight * $quantity;
        $unitGrossWeight = $partialWeight;
        $grossWeight     = $partialWeight * $quantity;

        if (in_array('unit_net_weight', $part->locked_values) && isset($part->unit_net_weight)) {
            $unitNetWeight = $part->unit_net_weight;
            $netWeight     = $unitNetWeight * $quantity;
        }

        if (in_array('unit_gross_weight', $part->locked_values) && isset($part->unit_gross_weight)) {
            $unitGrossWeight = $part->unit_gross_weight;
            $grossWeight     = $unitGrossWeight * $quantity;
        }


        if (in_array('net_weight', $part->locked_values) && isset($part->net_weight) && $quantity > 0) {
            $netWeight = $part->net_weight;
            $unitNetWeight = $netWeight / $quantity;
        }

        if (in_array('gross_weight', $part->locked_values) && isset($part->gross_weight) && $quantity > 0) {
            $grossWeight = $part->gross_weight;
            $unitGrossWeight = $grossWeight / $quantity;
        }

        $unitValue  = $unitNetWeight * $bar->price_kg * $markup;

        if (in_array('unit_value', $part->locked_values) && isset($part->unit_value)) {
            $unitValue = $part->unit_value;
        }

        $finalValue = $unitValue * $quantity;

        if (in_array('final_value', $part->locked_values) && isset($part->final_value)) {
            $finalValue = $part->final_value;
        }

        $part->unit_net_weight   = round($unitNetWeight, 2);
        $part->net_weight        = round($netWeight, 2);
        $part->unit_gross_weight = round($unitGrossWeight, 2);
        $part->gross_weight      = round($grossWeight, 2);
        $part->unit_value        = round($unitValue, 2);
        $part->final_value       = round($finalValue, 2);

        return $part;
    }

    /**
     * Calcula as propriedades de uma part para material do tipo "component".
     *
     * @param object $part
     * @param object $component Objeto com os dados do component: unit_value
     * @return object
     */
    public static function calculateComponentProperties(object $part): object
    {
        $component = Component::findOrFail($part->component_id);

        // Para component, os cálculos de peso não se aplicam; valores assumidos como zero
        $unitNetWeight   = 0;
        $netWeight       = 0;
        $unitGrossWeight = 0;
        $grossWeight     = 0;

        if (in_array('unit_net_weight', $part->locked_values) && isset($part->unit_net_weight)) {
            $unitNetWeight = $part->unit_net_weight;
            $netWeight = $unitNetWeight * (isset($part->quantity) ? $part->quantity : 0);
        }

        if (in_array('unit_gross_weight', $part->locked_values) && isset($part->unit_gross_weight)) {
            $unitGrossWeight = $part->unit_gross_weight;
            $grossWeight = $unitGrossWeight * (isset($part->quantity) ? $part->quantity : 0);
        }

        // Travamento dos campos net_weight e gross_weight (ajusta os unitários)
        $quantity = isset($part->quantity) ? $part->quantity : 0;
        if (in_array('net_weight', $part->locked_values) && isset($part->net_weight) && $quantity > 0) {
            $netWeight = $part->net_weight;
            $unitNetWeight = $netWeight / $quantity;
        }

        if (in_array('gross_weight', $part->locked_values) && isset($part->gross_weight) && $quantity > 0) {
            $grossWeight = $part->gross_weight;
            $unitGrossWeight = $grossWeight / $quantity;
        }

        $unitValue = $component->unit_value;

        if (in_array('unit_value', $part->locked_values) && isset($part->unit_value)) {
            $unitValue = $part->unit_value;
        }

        $markup    = !empty($part->markup) ? $part->markup : (!empty($part->set->order->markup) ? $part->set->order->markup : 1);

        $finalValue = $quantity * $unitValue * $markup;

        if (in_array('final_value', $part->locked_values) && isset($part->final_value)) {
            $finalValue = $part->final_value;
        }

        $part->unit_net_weight   = round($unitNetWeight, 2);
        $part->net_weight        = round($netWeight, 2);
        $part->unit_gross_weight = round($unitGrossWeight, 2);
        $part->gross_weight      = round($grossWeight, 2);
        $part->unit_value        = round($unitValue, 2);
        $part->final_value       = round($finalValue, 2);

        return $part;
    }

    public static function calculatePartProcesses(object $part)
    {
        $processes = $part->processes ?? [];

        $baseUnitValue  = $part->unit_value ?? 0;
        $baseFinalValue = $part->final_value ?? 0;
        $processUnit  = 0;
        $processFinal = 0;

        foreach ($processes as $partProcess) {
            if (!isset($partProcess['id'])) {
                continue;
            }
            
            $process = Process::findOrFail($partProcess['id']);
            $value = ProcessController::calculateValue($process, [
                'time' => $partProcess['pivot']['time'] ?? 0
            ]);

            $processUnit  += round($value, 2);
            $processFinal += round($value * $part->quantity, 2);
        }
        
        $part->unit_value  = round($baseUnitValue + $processUnit, 2);
        $part->final_value = round($baseFinalValue + $processFinal, 2);
        
        return $part;
    }
}