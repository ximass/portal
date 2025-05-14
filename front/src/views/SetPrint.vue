<template>
  <v-container class="pa-0" fluid>
    <v-row>
      <v-col>
        <template v-for="set in setsArray" :key="set.id">
          <v-card flat class="mb-4">
            <v-card-title class="set-title text-h6 pa-2">
              {{ set.name }}
            </v-card-title>
          </v-card>
          <v-table 
            density="compact"
          >
            <thead>
              <tr>
                <th class="font-weight-bold" style="width:30%;">Peça</th>
                <th class="font-weight-bold" style="width:40px;">Imagem</th>
                <th class="font-weight-bold">Tipo</th>
                <th class="font-weight-bold">Qtd.</th>
                <th class="font-weight-bold">Peso líq. unit.</th>
                <th class="font-weight-bold">Peso líq.</th>
                <th class="font-weight-bold">Peso bruto unit.</th>
                <th class="font-weight-bold">Peso bruto</th>
                <th class="font-weight-bold">Valor unitário</th>
                <th class="font-weight-bold">Valor final</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="part in set.parts" :key="part.id">
                <tr>
                  <td style="width:30%;">{{ part.obs ? part.obs : part.title }}</td>
                  <td>
                    <span v-if="part.secondary_content">
                      <img
                        :src="getPartImageUrl(part.secondary_content)"
                        alt="Secundária"
                        style="width:100%;height:100%;object-fit:contain;display:block;"
                      />
                    </span>
                    <span v-else style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
                      <v-icon size="32" color="grey-lighten-1">mdi-image-off</v-icon>
                    </span>
                  </td>
                  <td>{{ part.type ? (partTypes[part.type] ?? part.type) : '' }}</td>
                  <td>{{ part.quantity }}</td>
                  <td>{{ formatNumber(part.unit_net_weight) }} KG</td>
                  <td>{{ formatNumber(part.net_weight) }} KG</td>
                  <td>{{ formatNumber(part.unit_gross_weight) }} KG</td>
                  <td>{{ formatNumber(part.gross_weight) }} KG</td>
                  <td>{{ formatCurrency(part.unit_value) }}</td>
                  <td>{{ formatCurrency(part.final_value) }}</td>
                </tr>
                <tr v-if="part.processes && part.processes.length" class="process-row">
                  <td :colspan="2" class="arrow-cell">
                    <div class="d-flex align-center gap-1">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M8 4v6c0 2.21 1.79 4 4 4h3.17l-2.59-2.59L14 10l5 5-5 5-1.41-1.41L15.17 16H12c-3.31 0-6-2.69-6-6V4h2z" fill="#888"/>
                      </svg>
                      <span>Processos</span>
                    </div>
                  </td>
                  <td :colspan="8" class="process-info-cell">
                    <span>
                      <template v-for="(proc, idx) in part.processes" :key="proc.id">
                        {{ proc.title }} ({{ proc.pivot?.time ?? '-' }} min, {{ formatCurrency(proc.pivot?.final_value ?? 0) }})<span v-if="idx < part.processes.length - 1">;&nbsp;</span>
                      </template>
                    </span>
                  </td>
                </tr>
              </template>
              <tr v-if="set.parts?.length" style="font-weight: bold;">
                <td colspan="2" style="width:30%;">Totais</td>
                <td></td>
                <td>{{ totalQuantity(set) }}</td>
                <td>{{ total(set, 'unit_net_weight', false) }}</td>
                <td>{{ total(set, 'net_weight', false) }}</td>
                <td>{{ total(set, 'unit_gross_weight', false) }}</td>
                <td>{{ total(set, 'gross_weight', false) }}</td>
                <td>{{ total(set, 'unit_value', true) }}</td>
                <td>{{ total(set, 'final_value', true) }}</td>
              </tr>
            </tbody>
          </v-table>
          <v-divider class="my-6" />
        </template>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import axios from 'axios';
import { defineComponent, ref, onMounted, nextTick, computed } from 'vue';
import { useRoute } from 'vue-router';
import type { Set, Part } from '../types/types';
import { useMisc } from '../composables/misc';

export default defineComponent({
  setup() {
    const { getPartImageUrl } = useMisc();

    const route = useRoute();
    const sets = ref<Set[] | Set | null>(null);
    const partTypes = ref<Record<string, string>>({});

    async function fetchPartsForSet(set: Set) {
      const { data } = await axios.get(`/api/sets/${set.id}/parts`);
      const partsWithProcesses = await Promise.all(
        data.map(async (part: Part) => {
          if (!part.processes) {
            try {
              const response = await axios.get(`/api/sets/${set.id}/parts/${part.id}/processes`);
              part.processes = response.data;
            } catch {
              part.processes = [];
            }
          }
          return part;
        })
      );
      set.parts = partsWithProcesses;
      return set;
    }

    onMounted(async () => {
      const { data: typesData } = await axios.get('/api/set-parts/types');
      partTypes.value = typesData;

      const orderId = route.query.order_id;
      const setId = route.query.set_id || route.params.id;

      if (orderId) {
        const { data: setsData } = await axios.get(`/api/orders/${orderId}`);
        
        if (setsData.sets && Array.isArray(setsData.sets)) {
          const setsWithParts = await Promise.all(
            setsData.sets.map(async (set: Set) => await fetchPartsForSet(set))
          );
          sets.value = setsWithParts;
        } else {
          sets.value = [];
        }
      } else if (setId) {
        const response = await fetch(`/api/sets/${setId}`);
        const set = await response.json();

        await fetchPartsForSet(set);

        sets.value = [set];
      }

      await nextTick();

      window.dispatchEvent(new Event('print-ready'));
      setTimeout(() => window.print(), 500);
    });

    function formatNumber(value: number) {
      return Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatCurrency(value: number) {
      return Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function total(set: Set, field: string, isCurrency = false) {
      if (!set?.parts) return isCurrency ? formatCurrency(0) : formatNumber(0);
      const sum = set.parts.reduce((sum: number, part: any) => {
        const value = Number(part[field]) || 0;
        return sum + value;
      }, 0);
      return isCurrency ? formatCurrency(sum) : formatNumber(sum);
    }

    function totalQuantity(set: Set) {
      if (!set?.parts) return 0;
      return set.parts.reduce((sum: number, part: any) => {
        const value = Number(part.quantity) || 0;
        return sum + value;
      }, 0);
    }

    const setsArray = computed(() => {
      if (!sets.value) return [];
      return Array.isArray(sets.value) ? sets.value : [sets.value];
    });

    return { 
      setsArray, 
      partTypes,
      total, 
      totalQuantity, 
      formatNumber, 
      formatCurrency,
      getPartImageUrl,
    };
  }
});
</script>

<style scoped>
.v-table th, .v-table td {
  font-size: 13px;
  padding: 4px 8px;
}

body, html {
  margin: 0 !important;
  padding: 0 !important;
  background: #fff !important;
}
.v-container, .v-row, .v-col {
  padding: 0 !important;
  margin: 0 !important;
}
.v-table {
  width: 100% !important;
  border-collapse: collapse !important;
  font-size: 12px !important;
}
.v-table th, .v-table td {
  border: 1px solid #333 !important;
  padding: 4px 8px !important;
  color: #222 !important;
  background: #fff !important;
}
@page {
  margin: 10mm;
}

.arrow-cell {
  width: 24px !important;
  min-width: 24px !important;
  max-width: 24px !important;
  text-align: center;
  background: transparent !important;
  border: none !important;
  padding: 0 !important;
}

.process-row td {
  padding-top: 0 !important;
  padding-right: 0 !important;
  padding-bottom: 0 !important;
  padding-left: 12px !important;
  margin: 0 !important;
  border-top: none !important;
  border-bottom: none !important;
  background: #f9f9f9 !important;
  vertical-align: middle;
  height: 18px !important;
  line-height: 1.1 !important;
  font-size: 11px !important;
}

.process-info-cell {
  background: #f9f9f9 !important;
  text-align: left;
}

.set-title {
  font-weight: bold !important;
  color: #000 !important;
  text-transform: uppercase !important;
  letter-spacing: 1px;
}

.v-table tr, .process-row {
  break-inside: avoid !important;
  page-break-inside: avoid !important;
}
</style>
