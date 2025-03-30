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
                        'quantity' => intval($process['pivot']['quantity'] ?? $process['quantity'] ?? 0),
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
                        'quantity' => intval($process['pivot']['quantity'] ?? $process['quantity'] ?? 0),
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

    public function calculateProperties(Request $request)
    {
        $part = (object) $request->input('part');
        $material = (object) $request->input('material');

        if ($material->type === 'sheet') {
            return self::calculateSheetProperties($part, (object) $material->sheet);
        } elseif ($material->type === 'bar') {
            return self::calculateBarProperties($part, (object) $material->bar);
        } elseif ($material->type === 'component') {
            return self::calculateComponentProperties($part, (object) $material->component);
        }

        return response()->json(['error' => 'Invalid material type'], 400);
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
        // Converte medidas de mm para m
        $widthInMeters     = $sheet->width / 1000;
        $lengthInMeters    = $sheet->length / 1000;
        $thicknessInMeters = $sheet->thickness / 1000;

        // Peso específico de g/mm³ em kg/m³
        $specificWeight = $sheet->specific_weight * 1000 * 1000;
        
        // Fator de perda (ex.: se perda for 5%, fator = 0.95)
        $loss = isset($part->loss) ? $part->loss : 0;
        $factor = (100 - $loss) / 100;

        // Cálculo dos pesos unitários:
        // volume (m³) * peso específico (kg/m³)
        $unitNetWeight = $widthInMeters * $lengthInMeters * $thicknessInMeters * $specificWeight * $factor;
        $unitGrossWeight = $widthInMeters * $lengthInMeters * $thicknessInMeters * $specificWeight;
        
        $quantity = isset($part->quantity) ? $part->quantity : 0;
        
        // Pesos totais
        $netWeight = $unitNetWeight * $quantity;
        $grossWeight = $unitGrossWeight * $quantity;
        
        // Valor unitário com base no peso unitário e preço por kg
        $unitValue = $unitNetWeight * $sheet->price_kg;
        
        // Valor final considerando a quantidade
        $finalValue = $quantity * $unitValue;
        
        return [
            'unit_net_weight'   => $unitNetWeight,
            'net_weight'        => $netWeight,
            'unit_gross_weight' => $unitGrossWeight,
            'gross_weight'      => $grossWeight,
            'unit_value'        => $unitValue,
            'final_value'       => $finalValue,
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
        $specificWeight = $sheet->specific_weight * 1000 * 1000;
        
        // Cálculo da área da seção transversal: A = π * (raio)²
        $radius = $diameterInMeters / 2;
        $area = pi() * ($radius ** 2);
        
        // Peso unitário líquido = volume (área * comprimento) * peso específico
        $unitNetWeight = $area * $lengthInMeters * $specificWeight;
        
        $loss = isset($part->loss) ? $part->loss : 0;
        $factor = (100 - $loss) / 100;
        $quantity = isset($part->quantity) ? $part->quantity : 0;
        
        $netWeight = $quantity * $unitNetWeight * $factor;
        $unitGrossWeight = $unitNetWeight;
        $grossWeight = $netWeight;
        $unitValue = $unitNetWeight * $bar->price_kg;
        $finalValue = $quantity * $unitValue;
        
        return [
            'unit_net_weight'   => $unitNetWeight,
            'net_weight'        => $netWeight,
            'unit_gross_weight' => $unitGrossWeight,
            'gross_weight'      => $grossWeight,
            'unit_value'        => $unitValue,
            'final_value'       => $finalValue,
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
        $unitNetWeight = 0;
        $netWeight = 0;
        $unitGrossWeight = 0;
        $grossWeight = 0;
        
        // O valor unitário já está definido no componente
        $unitValue = $component->unit_value;
        $quantity = isset($part->quantity) ? $part->quantity : 0;
        $finalValue = $quantity * $unitValue;
        
        return [
            'unit_net_weight'   => $unitNetWeight,
            'net_weight'        => $netWeight,
            'unit_gross_weight' => $unitGrossWeight,
            'gross_weight'      => $grossWeight,
            'unit_value'        => $unitValue,
            'final_value'       => $finalValue,
        ];
    }
}