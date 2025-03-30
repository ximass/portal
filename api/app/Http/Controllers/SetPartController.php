<?php

namespace App\Http\Controllers;

use App\Models\SetPart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetPartController extends Controller
{
    public function index($setId)
    {
        return response()->json(
            SetPart::where('set_id', $setId)
            ->with('processes')
            ->get()
        );
    }

    public function store(Request $request, $setId)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'material_id'      => 'sometimes|nullable|integer|exists:materials,id',
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
        ]);

        $setPart = SetPart::create([
            'title'            => $request->title,
            'content'          => $request->content,
            'set_id'           => $setId,
            'material_id'      => $request->input('material_id'),
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
        ]);

        if ($request->has('processes')) {
            $processes = $request->input('processes');
            $syncData = [];

            foreach ($processes as $process) {
                if (isset($process['id'])) {
                    $syncData[$process['id']] = [
                        'time' => $process['pivot']['time'] ?? $process['time'] ?? 0,
                        'final_value' => $process['pivot']['final_value'] ?? $process['final_value'] ?? 0,
                    ];
                }
            }

            $setPart->processes()->sync($syncData);
        }

        return response()->json($setPart, 201);
    }

    public function show($setId, $id)
    {
        $setPart = SetPart::where('set_id', $setId)->findOrFail($id);

        return response()->json($setPart);
    }

    public function update(Request $request, $setId, $id)
    {
        $request->validate([
            'title'            => 'sometimes|required|string|max:255',
            'content'          => 'sometimes|required|string',
            'material_id'      => 'sometimes|nullable|integer|exists:materials,id',
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
        ]);

        $setPart = SetPart::where('set_id', $setId)->findOrFail($id);
        $setPart->update($request->only(
            'title',
            'content',
            'material_id',
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
        ));

        if ($request->has('processes')) {
            $processes = $request->input('processes');
            $syncData = [];

            foreach ($processes as $process) {
                if (isset($process['id'])) {
                    $syncData[$process['id']] = [
                        'time' => $process['pivot']['time'] ?? $process['time'] ?? 0,
                        'final_value' => $process['pivot']['final_value'] ?? $process['final_value'] ?? 0,
                    ];
                }
            }

            $setPart->processes()->sync($syncData);
        }

        return response()->json($setPart);
    }

    public function destroy($setId, $id)
    {
        $setPart = SetPart::where('set_id', $setId)->findOrFail($id);
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
            'set_id' => 'required|integer'
        ]);

        $path = $file->store('uploads/order-parts', 'public');

        $setPart = SetPart::create([
            'title'   => $file->getClientOriginalName(),
            'content' => Storage::url($path),
            'set_id'  => $request->input('set_id'),
        ]);

        return response()->json($setPart, 201);
    }

    public function calculatePartProperties(Request $request)
    {
        $part = (object) $request->input('part');
        $material = (object) $request->input('material');

        try
        {
            return $this->calculateProperties($part, $material);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Calcula as propriedades de uma part com base no tipo de material.
     *
     * @param object $part Dados da part.
     * @param object $material Dados do material, incluindo o tipo e suas propriedades específicas.
     * @return array Array associativo com os campos calculados.
     */
    public static function calculateProperties(object $part, object $material): array
    {
        if ($material->type === 'sheet') {
            return self::calculateSheetProperties($part, (object) $material->sheet);
        } elseif ($material->type === 'bar') {
            return self::calculateBarProperties((array) $part, (object) $material->bar);
        } elseif ($material->type === 'component') {
            return self::calculateComponentProperties((array) $part, (object) $material->component);
        }

        throw new \InvalidArgumentException('Invalid material type');
    }

    /**
     * Calcula as propriedades de uma part para material do tipo "sheet".
     *
     * @param array $part Dados da part. Deve conter 'quantity' e 'loss' (percentual de perda)
     * @param object $sheet Objeto com os dados do sheet: width, length, thickness, specific_weight, price_kg
     * @return array Array associativo com os campos calculados:
     *               - unit_net_weight
     *               - net_weight
     *               - unit_gross_weight
     *               - gross_weight
     *               - unit_value
     *               - final_value
     */
    public static function calculateSheetProperties(object $part, object $sheet): array
    {
        // Converte medidas de mm para m e aplica arredondamento para width e length se necessário
        $widthInMeters     = round($sheet->width / 1000, 2);
        $lengthInMeters    = round($sheet->length / 1000, 2);
        $thicknessInMeters = $sheet->thickness / 1000;
        
        // Peso específico de g/mm³ em kg/m³
        $specificWeight = $sheet->specific_weight * 1000 * 1000;
        
        // Fator de perda (ex.: se perda for 5%, fator = 0.95) e arredonda loss para 2 casas
        $loss   = isset($part->loss) ? round($part->loss, 2) : 0;
        $factor = (100 - $loss) / 100;
        
        // Cálculo dos pesos unitários:
        $unitNetWeight   = $widthInMeters * $lengthInMeters * $thicknessInMeters * $specificWeight * $factor;
        $unitGrossWeight = $widthInMeters * $lengthInMeters * $thicknessInMeters * $specificWeight;
        
        $quantity = isset($part->quantity) ? $part->quantity : 0;
        
        // Pesos totais
        $netWeight   = $unitNetWeight * $quantity;
        $grossWeight = $unitGrossWeight * $quantity;
        
        // Valor unitário com base no peso unitário e preço por kg
        $unitValue  = $unitNetWeight * $sheet->price_kg;
        
        // Valor final considerando a quantidade
        $finalValue = $quantity * $unitValue;
        
        return [
            'unit_net_weight'   => round($unitNetWeight, 2),
            'net_weight'        => round($netWeight, 2),
            'unit_gross_weight' => round($unitGrossWeight, 2),
            'gross_weight'      => round($grossWeight, 2),
            'unit_value'        => round($unitValue, 2),
            'final_value'       => round($finalValue, 2),
        ];
    }

    /**
     * Calcula as propriedades de uma part para material do tipo "bar".
     *
     * @param array $part Dados da part. Deve conter 'quantity' e 'loss' (percentual de perda)
     * @param object $bar Objeto com os dados do bar: diameter, length, specific_weight, price_kg
     * @return array Array associativo com os campos calculados:
     *               - unit_net_weight
     *               - net_weight
     *               - unit_gross_weight
     *               - gross_weight
     *               - unit_value
     *               - final_value
     */
    public static function calculateBarProperties(array $part, object $bar): array
    {
        // Converter medidas: diâmetro e comprimento de mm para m
        $diameterInMeters = $bar->diameter / 1000;
        $lengthInMeters   = $bar->length / 1000;
        
        // Peso específico de g/mm³ em kg/m³
        $specificWeight = $bar->specific_weight * 1000 * 1000;
        
        // Área da seção transversal: A = π * (raio)²
        $radius = $diameterInMeters / 2;
        $area = pi() * ($radius ** 2);
        
        // Peso unitário líquido = volume (área * comprimento) * peso específico
        $unitNetWeight = $area * $lengthInMeters * $specificWeight;
        
        $loss   = isset($part['loss']) ? round($part['loss'], 2) : 0;
        $factor = (100 - $loss) / 100;
        $quantity = isset($part['quantity']) ? $part['quantity'] : 0;
        
        $netWeight       = $quantity * $unitNetWeight * $factor;
        $unitGrossWeight = $unitNetWeight;
        $grossWeight     = $netWeight;
        
        $unitValue  = $unitNetWeight * $bar->price_kg;
        $finalValue = $quantity * $unitValue;
        
        return [
            'unit_net_weight'   => round($unitNetWeight, 2),
            'net_weight'        => round($netWeight, 2),
            'unit_gross_weight' => round($unitGrossWeight, 2),
            'gross_weight'      => round($grossWeight, 2),
            'unit_value'        => round($unitValue, 2),
            'final_value'       => round($finalValue, 2),
        ];
    }

    /**
     * Calcula as propriedades de uma part para material do tipo "component".
     *
     * @param array $part Dados da part. Deve conter 'quantity'
     * @param object $component Objeto com os dados do component: unit_value
     * @return array Array associativo com os campos calculados:
     *               - unit_net_weight
     *               - net_weight
     *               - unit_gross_weight
     *               - gross_weight
     *               - unit_value
     *               - final_value
     */
    public static function calculateComponentProperties(array $part, object $component): array
    {
        // Para component, os cálculos de peso não se aplicam; valores assumidos como zero
        $unitNetWeight   = 0;
        $netWeight       = 0;
        $unitGrossWeight = 0;
        $grossWeight     = 0;
        
        // O valor unitário já está definido no componente
        $unitValue  = $component->unit_value;
        $quantity   = isset($part['quantity']) ? $part['quantity'] : 0;
        $finalValue = $quantity * $unitValue;
        
        return [
            'unit_net_weight'   => round($unitNetWeight, 2),
            'net_weight'        => round($netWeight, 2),
            'unit_gross_weight' => round($unitGrossWeight, 2),
            'gross_weight'      => round($grossWeight, 2),
            'unit_value'        => round($unitValue, 2),
            'final_value'       => round($finalValue, 2),
        ];
    }

    public function calculatePartProcesses(Request $request)
    {
        try
        {
            $setPart = SetPart::findOrFail($request->input('set_id'));

            return self::calculateProcesses($setPart);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Calcula os valores dos processos associados a uma SetPart.
     *
     * @param \App\Models\SetPart $setPart
     * @return array Array de arrays, cada qual contendo 'process_id' e o 'final_value' calculado.
     */
    public static function calculateProcesses(\App\Models\SetPart $setPart): array
    {
        $result = [];

        foreach ($setPart->processes as $process) {
            $response = ProcessController::calculateValue($process, [
                'time'     => $process->pivot->time ?? 0,
                'quantity' => $setPart->quantity ?? 1
            ]);

            $finalValue = $response['value'];

            $result[] = [
                'process_id' => $process->id,
                'final_value' => round($finalValue, 2),
            ];
        }

        return $result;
    }
}