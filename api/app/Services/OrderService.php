<?php

namespace App\Services;

use App\Models\Order;

class OrderService
{
    /**
     * Calculate all values for an order (parts and sets)
     * 
     * @param Order $order
     * @return array
     */
    public function calculateOrderValues(Order $order): array
    {
        $subtotalParts = 0;
        
        // Calculate parts values
        foreach ($order->sets as $set) {
            foreach ($set->setParts as $part) {
                $calculations = $this->calculatePartValues($part);
                
                $part->calculated_unit_value = $calculations['unit_value'];
                $part->calculated_total_value = $calculations['total_value'];
                
                $subtotalParts += $calculations['total_value'];
            }
        }
        
        // Calculate grand total
        $grandTotal = $subtotalParts;
        $grandTotal += $order->delivery_value ?? 0;
        $grandTotal += $order->service_value ?? 0;
        $grandTotal -= $order->discount ?? 0;
        
        return [
            'subtotal_parts' => $subtotalParts,
            'delivery_value' => $order->delivery_value ?? 0,
            'service_value' => $order->service_value ?? 0,
            'discount' => $order->discount ?? 0,
            'grand_total' => $grandTotal,
        ];
    }
    
    /**
     * Calculate values for a single part
     * 
     * @param \App\Models\SetPart $part
     * @return array
     */
    public function calculatePartValues($part): array
    {
        $ipi = ($part->ncm->ipi ?? 0) / 100;
        
        $unitValueWithIPI = $part->unit_value ?? 0;
        $baseUnitValue = $ipi !== 0 ? ($unitValueWithIPI / (1 + $ipi)) : $unitValueWithIPI;
        
        $totalValue = $part->final_value ?? 0;
        
        return [
            'unit_value' => $baseUnitValue,
            'total_value' => $totalValue,
        ];
    }
    
    /**
     * Calculate values for sets (used in order-sets PDF)
     * 
     * @param Order $order
     * @return array
     */
    public function calculateSetValues(Order $order): array
    {
        $totalGeral = 0;
        
        foreach ($order->sets as $set) {
            $setUnitWithIpi = 0;
            $setUnitBase = 0;
            $setTotalValue = 0;
            
            foreach ($set->setParts as $part) {
                $partFinalWithIpi = $part->final_value ?? 0;
                $ipi = ($part->ncm->ipi ?? 0) / 100;
                
                $partBaseFinal = $ipi !== 0 ? ($partFinalWithIpi / (1 + $ipi)) : $partFinalWithIpi;
                
                $setUnitWithIpi += $partFinalWithIpi;
                $setUnitBase += $partBaseFinal;
            }
            
            $setTotalValue = $setUnitWithIpi * ($set->quantity ?? 1);
            
            $set->calculated_unit_value = $setUnitBase;
            $set->calculated_total_value = $setTotalValue;
            
            $totalGeral += $setTotalValue;
        }
        
        $totalGeral += $order->delivery_value ?? 0;
        $totalGeral += $order->service_value ?? 0;
        $totalGeral -= $order->discount ?? 0;
        
        return [
            'grand_total' => $totalGeral,
        ];
    }
    
    /**
     * Group parts by set name with calculated values
     * 
     * @param array $parts
     * @return array
     */
    public function groupPartsBySet(array $parts): array
    {
        $setMap = [];
        
        foreach ($parts as $part) {
            $setName = $part['setName'] ?? 'Sem conjunto';
            
            if (!isset($setMap[$setName])) {
                $setMap[$setName] = [
                    'setName' => $setName,
                    'parts' => [],
                    'total' => 0,
                    'totalGrossWeight' => 0,
                    'totalNetWeight' => 0,
                ];
            }
            
            // Calculate IPI and values
            $ipi = ($part['ncm']['ipi'] ?? 0) / 100;
            $unitValueWithIPI = $part['unit_value'] ?? 0;
            $baseUnitValue = $ipi !== 0 ? ($unitValueWithIPI / (1 + $ipi)) : $unitValueWithIPI;
            $totalValue = $part['final_value'] ?? 0;
            
            // Add calculated values to part
            $part['calculated_unit_value'] = $baseUnitValue;
            $part['calculated_total_value'] = $totalValue;
            
            $setMap[$setName]['parts'][] = $part;
            
            // Calculate weights
            $grossWeight = ($part['unit_gross_weight'] ?? 0) * ($part['quantity'] ?? 0);
            $netWeight = ($part['unit_net_weight'] ?? 0) * ($part['quantity'] ?? 0);
            
            $setMap[$setName]['total'] += $totalValue;
            $setMap[$setName]['totalGrossWeight'] += $grossWeight;
            $setMap[$setName]['totalNetWeight'] += $netWeight;
        }
        
        return array_values($setMap);
    }
}
