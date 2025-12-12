<template>
  <v-card>
    <v-card-title class="d-flex justify-space-between align-center">
      <span>Valores</span>
    </v-card-title>
    <v-card-text>
      <v-expansion-panels variant="accordion" multiple>
        <v-expansion-panel v-for="setGroup in groupedSets" :key="setGroup.setName">
          <v-expansion-panel-title>
            <div class="d-flex justify-space-between align-center w-100 pe-4">
              <span class="text-subtitle-1 font-weight-medium">{{ setGroup.setName }}</span>
              <div class="d-flex gap-4 align-center">
                <v-chip size="small" color="secondary" variant="tonal">
                  {{ setGroup.parts.length }} {{ setGroup.parts.length === 1 ? 'peça' : 'peças' }}
                </v-chip>
                <v-chip v-if="setGroup.setQuantity && setGroup.setQuantity > 1" size="small" color="info" variant="tonal">
                  Qtd: {{ setGroup.setQuantity }}
                </v-chip>
                <v-chip v-if="canViewValues" size="small" color="primary" variant="flat">
                  {{ formatCurrency(setGroup.total) }}
                </v-chip>
              </div>
            </div>
          </v-expansion-panel-title>
          <v-expansion-panel-text>
            <v-table density="compact" class="set-table">
              <thead>
                <tr>
                  <th>Título</th>
                  <th class="text-center">Qtd</th>
                  <th v-if="canViewValues" class="text-end">Valor unit.</th>
                  <th v-if="canViewValues" class="text-end">Valor total</th>
                  <th class="text-end">Peso bruto</th>
                  <th class="text-end">Peso líquido</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="part in setGroup.parts" :key="part.id">
                  <td>
                    <div class="d-flex flex-column">
                      <span class="text-body-2">{{ part.title }}</span>
                      <span v-if="part.reference" class="text-caption text-medium-emphasis">
                        Ref: {{ part.reference }}
                      </span>
                    </div>
                  </td>
                  <td class="text-center">{{ part.quantity }}</td>
                  <td v-if="canViewValues" class="text-end">{{ formatCurrency(part.calculated_unit_value ?? 0) }}</td>
                  <td v-if="canViewValues" class="text-end font-weight-medium">
                    {{ formatCurrency(part.calculated_total_value ?? 0) }}
                  </td>
                  <td class="text-end">{{ formatWeight(part.unit_gross_weight * part.quantity) }}</td>
                  <td class="text-end">{{ formatWeight(part.unit_net_weight * part.quantity) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="set-total-row">
                  <td colspan="2" class="text-end font-weight-bold">Subtotal do conjunto:</td>
                  <td v-if="canViewValues" colspan="2" class="text-end font-weight-bold text-primary">
                    {{ formatCurrency(setGroup.total) }}
                  </td>
                  <td class="text-end font-weight-bold">{{ formatWeight(setGroup.totalGrossWeight) }}</td>
                  <td class="text-end font-weight-bold">{{ formatWeight(setGroup.totalNetWeight) }}</td>
                </tr>
              </tfoot>
            </v-table>
          </v-expansion-panel-text>
        </v-expansion-panel>
      </v-expansion-panels>

      <!-- Total Geral -->
      <v-card variant="tonal" color="primary" class="mt-4">
        <v-card-text>
          <v-row dense>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Total de conjuntos</div>
              <div class="text-h6 font-weight-bold">{{ groupedSets.length }}</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Total de peças</div>
              <div class="text-h6 font-weight-bold">{{ totalParts }}</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Peso total bruto</div>
              <div class="text-h6 font-weight-bold">{{ formatWeight(totalGrossWeight) }}</div>
            </v-col>
            <v-col cols="12" sm="6" md="3">
              <div class="text-caption text-medium-emphasis">Peso total líquido</div>
              <div class="text-h6 font-weight-bold">{{ formatWeight(totalNetWeight) }}</div>
            </v-col>
            <v-col v-if="canViewValues" cols="12">
              <v-divider class="my-2"></v-divider>
              
              <!-- Subtotal de peças -->
              <div class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-1">Subtotal das peças:</span>
                <span class="text-body-1 font-weight-medium">{{ formatCurrency(subtotalParts) }}</span>
              </div>
              
              <!-- Frete -->
              <div v-if="deliveryValue && deliveryValue > 0" class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2">Frete:</span>
                <span class="text-body-2">{{ formatCurrency(deliveryValue) }}</span>
              </div>
              
              <!-- Serviço -->
              <div v-if="serviceValue && serviceValue > 0" class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2">Serviço:</span>
                <span class="text-body-2">{{ formatCurrency(serviceValue) }}</span>
              </div>
              
              <!-- Desconto -->
              <div v-if="discount && discount > 0" class="d-flex justify-space-between align-center mb-2">
                <span class="text-body-2">Desconto:</span>
                <span class="text-body-2 error--text">- {{ formatCurrency(discount) }}</span>
              </div>
              
              <v-divider class="my-2"></v-divider>
              
              <!-- Total Geral -->
              <div class="d-flex justify-space-between align-center">
                <span class="text-h6">Valor total do pedido:</span>
                <span class="text-h5 font-weight-bold text-primary">{{ formatCurrency(grandTotal) }}</span>
              </div>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-card-text>
  </v-card>
</template>

<script lang="ts">
import { defineComponent, computed, onMounted } from 'vue';
import type { Part } from '../types/types';
import { useAuth } from '../composables/useAuth';
import { useOrderCalculations } from '../composables/useOrderCalculations';

interface SetGroup {
  setName: string;
  parts: Part[];
  total: number;
  totalGrossWeight: number;
  totalNetWeight: number;
  setQuantity?: number;
}

export default defineComponent({
  name: 'OrderValuesTable',
  props: {
    items: {
      type: Array as () => Part[],
      required: true,
    },
    deliveryValue: {
      type: Number,
      default: 0,
    },
    serviceValue: {
      type: Number,
      default: 0,
    },
    discount: {
      type: Number,
      default: 0,
    },
  },
  setup(props) {
    const { canViewMonetaryValues, loadCurrentUser, isLoaded } = useAuth();
    const { groupPartsBySet, calculateSubtotal, calculateGrandTotal, calculateTotalWeights } = useOrderCalculations();
    
    // Carrega o usuário ao montar o componente
    onMounted(() => {
      if (!isLoaded.value) {
        loadCurrentUser();
      }
    });
    
    const canViewValues = computed(() => canViewMonetaryValues());

    const groupedSets = computed<SetGroup[]>(() => {
      return groupPartsBySet(props.items);
    });

    const subtotalParts = computed(() => {
      return calculateSubtotal(groupedSets.value);
    });

    // Total geral do pedido (com frete, serviço e desconto)
    const grandTotal = computed(() => {
      return calculateGrandTotal(
        subtotalParts.value,
        props.deliveryValue,
        props.serviceValue,
        props.discount
      );
    });

    const totalParts = computed(() => {
      return props.items.length;
    });

    const weights = computed(() => {
      return calculateTotalWeights(groupedSets.value);
    });

    const totalGrossWeight = computed(() => weights.value.totalGrossWeight);
    const totalNetWeight = computed(() => weights.value.totalNetWeight);

    const formatCurrency = (value: number): string => {
      return value.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    };

    const formatWeight = (value: number): string => {
      return `${value.toFixed(2)} kg`;
    };

    return {
      canViewValues,
      groupedSets,
      subtotalParts,
      grandTotal,
      totalParts,
      totalGrossWeight,
      totalNetWeight,
      formatCurrency,
      formatWeight,
      deliveryValue: computed(() => props.deliveryValue),
      serviceValue: computed(() => props.serviceValue),
      discount: computed(() => props.discount),
    };
  },
});
</script>

<style scoped>
.set-table {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 4px;
}

.set-total-row {
  background-color: rgba(var(--v-theme-surface-variant), 0.5);
  border-top: 2px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.set-total-row td {
  padding: 12px 16px !important;
}

.gap-4 {
  gap: 16px;
}

:deep(.v-expansion-panel-text__wrapper) {
  padding: 16px;
}

:deep(.v-expansion-panel-title) {
  padding: 16px 20px;
}
</style>
