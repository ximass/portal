import { ref } from 'vue';
import axios from 'axios';
import type { Part, Set, OrderSet } from '../types/types';

interface OrderCalculations {
  subtotal_parts: number;
  delivery_value: number;
  service_value: number;
  discount: number;
  grand_total: number;
}

interface SetGroup {
  setName: string;
  parts: Part[];
  total: number;
  totalUnitValues: number;
  totalGrossWeight: number;
  totalNetWeight: number;
  setQuantity?: number;
}

export function useOrderCalculations() {
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  /**
   * Fetch calculations from the backend
   */
  const fetchCalculations = async (orderId: number): Promise<OrderCalculations | null> => {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await axios.get(`/api/orders/${orderId}/calculate-values`);
      return response.data;
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Erro ao calcular valores do pedido';
      console.error('Error calculating order values:', err);
      return null;
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Calculate part values locally (for display purposes)
   */
  const calculatePartValues = (part: Part) => {
    const ipi = (part.ncm?.ipi ?? 0) / 100;
    const unitValueWithIPI = Number(part.unit_value) || 0;
    const baseUnitValue = ipi !== 0 ? (unitValueWithIPI / (1 + ipi)) : unitValueWithIPI;
    const totalValue = Number(part.final_value) || 0;

    return {
      calculated_unit_value: baseUnitValue,
      calculated_total_value: totalValue,
    };
  };

  /**
   * Group parts by set with calculated values
   */
  const groupPartsBySet = (sets: (Set | OrderSet)[]): SetGroup[] => {
    return sets.map(set => {
      const setQuantity = Number(set.quantity) || 1;
      
      let total = 0;
      let totalUnitValues = 0;
      let totalGrossWeight = 0;
      let totalNetWeight = 0;
      
      const partsWithCalculations = set.parts.map(part => {
        // Calculate values
        const calculations = calculatePartValues(part);
        
        // Calculate weights
        const grossWeight = (Number(part.unit_gross_weight) || 0) * (Number(part.quantity) || 0);
        const netWeight = (Number(part.unit_net_weight) || 0) * (Number(part.quantity) || 0);
        
        total += calculations.calculated_total_value;
        // totalUnitValues is the sum of unit values (without IPI) times quantity
        totalUnitValues += calculations.calculated_unit_value * (Number(part.quantity) || 0);
        totalGrossWeight += grossWeight;
        totalNetWeight += netWeight;
        
        return {
          ...part,
          ...calculations,
        };
      });
      
      return {
        setName: set.name || 'Sem nome',
        parts: partsWithCalculations,
        total: total * setQuantity,
        totalUnitValues: totalUnitValues * setQuantity,
        totalGrossWeight: totalGrossWeight * setQuantity,
        totalNetWeight: totalNetWeight * setQuantity,
        setQuantity,
      };
    });
  };

  /**
   * Calculate subtotal of parts
   */
  const calculateSubtotal = (groupedSets: SetGroup[]): number => {
    return groupedSets.reduce((acc, group) => acc + group.total, 0);
  };

  /**
   * Calculate grand total with delivery, service and discount
   */
  const calculateGrandTotal = (
    subtotal: number,
    deliveryValue: number = 0,
    serviceValue: number = 0,
    discount: number = 0
  ): number => {
    let total = subtotal;
    total += deliveryValue || 0;
    total += serviceValue || 0;
    total -= discount || 0;
    return total;
  };

  /**
   * Calculate total weights
   */
  const calculateTotalWeights = (groupedSets: SetGroup[]) => {
    return {
      totalGrossWeight: groupedSets.reduce((acc, group) => acc + group.totalGrossWeight, 0),
      totalNetWeight: groupedSets.reduce((acc, group) => acc + group.totalNetWeight, 0),
    };
  };

  return {
    isLoading,
    error,
    fetchCalculations,
    calculatePartValues,
    groupPartsBySet,
    calculateSubtotal,
    calculateGrandTotal,
    calculateTotalWeights,
  };
}
