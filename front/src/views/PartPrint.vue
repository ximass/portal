<template>
  <v-container class="pa-0" fluid>
    <template v-for="(part, idx) in parts" :key="part.id">
      <div class="print-page">
        <v-row no-gutters>
          <v-col cols="12">
            <div class="part-print-header">
              <span class="text-h5"
                >#{{ idx + 1 }} {{ part?.obs ?? part.title }}</span
              >
            </div>
            <v-divider />
          </v-col>
          <v-col cols="7" class="pa-8">
            <img
              v-if="part?.content"
              :src="getPartImageUrl(part.content)"
              style="
                object-fit: contain;
                max-width: 100%;
                max-height: 70vh;
                display: block;
              "
              alt="Imagem da peça"
            />
            <div v-else>Sem imagem para exibir</div>
          </v-col>
          <!-- informações -->
          <v-col cols="5" class="align-self-start">
            <v-list density="compact">
              <v-row dense>
                <v-col cols="12">
                  <v-list-item>
                    <v-list-item-title>Tipo</v-list-item-title>
                    <v-list-item-subtitle>{{
                      part?.type ? (partTypes[part.type] ?? part.type) : '-'
                    }}</v-list-item-subtitle>
                  </v-list-item>
                </v-col>
              </v-row>
              <v-row
                dense
                v-if="part?.type === 'material' || part?.type === 'sheet'"
              >
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Material</v-list-item-title>
                    <v-list-item-subtitle>{{
                      part?.material?.name || '-'
                    }}</v-list-item-subtitle>
                  </v-list-item>
                </v-col>
                <v-col cols="6" v-if="part?.type === 'sheet'">
                  <v-list-item>
                    <v-list-item-title>Chapa</v-list-item-title>
                    <v-list-item-subtitle>{{
                      part?.sheet?.name || '-'
                    }}</v-list-item-subtitle>
                  </v-list-item>
                </v-col>
                <v-col cols="6" v-else-if="part?.type === 'material'"></v-col>
              </v-row>
              <v-row dense v-if="part?.type === 'bar'">
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Barra</v-list-item-title>
                    <v-list-item-subtitle>{{
                      part?.bar?.name || '-'
                    }}</v-list-item-subtitle>
                  </v-list-item>
                </v-col>
                <v-col cols="6"></v-col>
              </v-row>
              <v-row dense v-if="part?.type === 'component'">
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Componente</v-list-item-title>
                    <v-list-item-subtitle>{{
                      part?.component?.name || '-'
                    }}</v-list-item-subtitle>
                  </v-list-item>
                </v-col>
                <v-col cols="6"></v-col>
              </v-row>
              <v-row
                dense
                v-if="part?.type === 'material' || part?.type === 'sheet'"
              >
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Largura</v-list-item-title>
                    <v-list-item-subtitle
                      >{{ part?.width ?? '-' }} mm</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Comprimento</v-list-item-title>
                    <v-list-item-subtitle
                      >{{ part?.length ?? '-' }} mm</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
              </v-row>
              <v-row dense v-if="part?.type === 'bar'">
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Comprimento</v-list-item-title>
                    <v-list-item-subtitle
                      >{{ part?.length ?? '-' }} mm</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Perda</v-list-item-title>
                    <v-list-item-subtitle
                      >{{ part?.loss ?? '-' }} %</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
              </v-row>
              <v-row
                dense
                v-if="part?.type === 'material' || part?.type === 'sheet'"
              >
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Perda</v-list-item-title>
                    <v-list-item-subtitle
                      >{{ part?.loss ?? '-' }} %</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
                <v-col cols="6"></v-col>
              </v-row>
              <v-row dense v-if="part?.type && part?.type !== 'process' && part?.markup">
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Markup</v-list-item-title>
                    <v-list-item-subtitle>{{
                      part?.markup ?? '-'
                    }}</v-list-item-subtitle>
                  </v-list-item>
                </v-col>
                <v-col cols="6"></v-col>
              </v-row>
              <v-row dense>
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Quantidade</v-list-item-title>
                    <v-list-item-subtitle>{{
                      part?.quantity
                    }}</v-list-item-subtitle>
                  </v-list-item>
                </v-col>
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Peso líquido unitário</v-list-item-title>
                    <v-list-item-subtitle
                      >{{
                        formatNumber(part?.unit_net_weight)
                      }}
                      KG</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
              </v-row>
              <v-row dense>
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Peso bruto unitário</v-list-item-title>
                    <v-list-item-subtitle
                      >{{
                        formatNumber(part?.unit_gross_weight)
                      }}
                      KG</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Peso líquido</v-list-item-title>
                    <v-list-item-subtitle
                      >{{
                        formatNumber(part?.net_weight)
                      }}
                      KG</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
              </v-row>
              <v-row dense>
                <v-col cols="6">
                  <v-list-item>
                    <v-list-item-title>Peso bruto</v-list-item-title>
                    <v-list-item-subtitle
                      >{{
                        formatNumber(part?.gross_weight)
                      }}
                      KG</v-list-item-subtitle
                    >
                  </v-list-item>
                </v-col>
                <v-col cols="6"></v-col>
              </v-row>
            </v-list>
            <v-divider class="my-2" />
            <div v-if="part?.processes?.length">
              <strong>Processos:</strong>
              <ul>
                <li v-for="proc in part.processes" :key="proc.id">
                  {{ proc.title }} ({{ proc.pivot?.time ?? '-' }} min)
                </li>
              </ul>
            </div>
          </v-col>
        </v-row>
      </div>
    </template>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import type { Part } from '../types/types';

export default defineComponent({
  props: {
    order_id: { type: [String, Number], default: null },
  },
  setup(props) {
    const route = useRoute();
    const parts = ref<Part[]>([]);
    const partTypes = ref<Record<string, string>>({});

    const getPartImageUrl = (content: string) => {
      const baseUrl = import.meta.env.VITE_API_URL;
      return `${baseUrl}${content}`;
    };

    onMounted(async () => {
      const { data: typesData } = await axios.get('/api/set-parts/types');
      partTypes.value = typesData;

      if (props.order_id) {
        const { data: orderData } = await axios.get(
          `/api/orders/${props.order_id}`
        );
        const sets = orderData.sets || [];

        let allParts: Part[] = [];

        for (const set of sets) {
          const { data: setParts } = await axios.get(
            `/api/sets/${set.id}/parts`
          );

          allParts = allParts.concat(setParts);
        }

        parts.value = allParts;
      } else {
        const { data } = await axios.get(`/api/set-parts/${route.params.id}`);

        parts.value = [data];
      }

      for (const part of parts.value) {
        if (
          part.material_id &&
          (part.type === 'material' || part.type === 'sheet')
        ) {
          try {
            const { data } = await axios.get(
              `/api/materials/${part.material_id}`
            );
            part.material = data;
          } catch {}
        }
        if (part.sheet_id && part.type === 'sheet') {
          try {
            const { data } = await axios.get(`/api/sheets/${part.sheet_id}`);
            part.sheet = data;
          } catch {}
        }
        if (part.bar_id && part.type === 'bar') {
          try {
            const { data } = await axios.get(`/api/bars/${part.bar_id}`);
            part.bar = data;
          } catch {}
        }
        if (part.component_id && part.type === 'component') {
          try {
            const { data } = await axios.get(
              `/api/components/${part.component_id}`
            );
            part.component = data;
          } catch {}
        }
      }

      window.dispatchEvent(new Event('print-ready'));
      setTimeout(() => window.print(), 500);
    });

    function formatNumber(value: number) {
      return Number(value || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
    }

    function formatCurrency(value: number) {
      return Number(value || 0).toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
      });
    }

    return { parts, partTypes, getPartImageUrl, formatNumber, formatCurrency };
  },
});
</script>

<style scoped>
.v-list-item-title {
  font-weight: 500;
}

.v-list-item-subtitle {
  font-size: 15px;
}

.part-print-header {
  padding: 32px 32px 0 32px;
}

/* Todas as fontes pretas */
.print-page,
.print-page * {
  color: #000 !important;
}

/* Forçar fundo branco em tudo */
html,
body,
#app,
.v-application,
.v-main,
.v-container,
.print-page,
.v-row,
.v-col,
.v-list,
.v-list-item,
.v-list-item-title,
.v-list-item-subtitle {
  background-color: #fff !important;
  background: #fff !important;
}

/* Remover fill-height e scroll vertical */
html,
body,
#app,
.v-application {
  height: 100% !important;
  min-height: 100vh !important;
  overflow-x: hidden !important;
  overflow-y: visible !important;
}

.v-container {
  padding: 0 !important;
  margin: 0 !important;
  width: 100vw !important;
  max-width: 100vw !important;
  overflow-x: hidden !important;
  overflow-y: visible !important;
}

body,
.v-list {
  overflow-x: hidden !important;
}

@page {
  margin: 10mm;
}

.print-page {
  break-after: page;
  min-height: 100vh;
  height: 100vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

@media print {
  * {
    background-color: #fff !important;
    background: #fff !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  html,
  body,
  #app,
  .v-application,
  .v-main,
  .v-container,
  .print-page {
    background-color: #fff !important;
    background: #fff !important;
  }
}
</style>
