<template>
  <v-row align="center" justify="space-between" class="mb-4 pa-4 mt-4">
    <h3 class="text-h6">Processos</h3>
    <v-btn small color="primary" @click="addProcess">Adicionar</v-btn>
  </v-row>
  <div v-for="(proc, index) in internalProcesses" :key="index" class="mb-2">
    <v-row dense>
      <v-col cols="3">
        <v-select
          label="Processo"
          :items="processOptions"
          item-title="title"
          item-value="id"
          v-model="proc.id"
          required
          density="compact"
          @change="calculateProcess(index)"
        />
      </v-col>
      <v-col cols="3">
        <v-text-field
          label="Valor por minuto"
          :model-value="getValuePerMinute(proc.id)"
          readonly
          density="compact"
          prefix="R$"
        />
      </v-col>
      <v-col cols="2">
        <v-text-field
          label="Tempo"
          v-model="proc.pivot.time"
          type="number"
          required
          density="compact"
          hint="Em minutos"
          @blur="calculateProcess(index)"
        />
      </v-col>
      <v-col cols="3">
        <v-text-field
          label="Valor final"
          v-model="proc.pivot.final_value"
          type="number"
          density="compact"
          prefix="R$"
        />
      </v-col>
      <v-col cols="1">
        <v-btn variant="text" small icon color="error" @click="removeProcess(index)">
          <v-icon small>mdi-delete</v-icon>
        </v-btn>
      </v-col>
    </v-row>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, watch, PropType } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import type { Process, ProcessPivot } from '@/types/types';

export default defineComponent({
  name: 'ProcessMultiField',
  props: {
    modelValue: {
      type: Array as PropType<Array<{ id: Process['id'] | null; pivot: ProcessPivot }>>,
      default: () => []
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const processOptions = ref<Process[]>([]);

    const flattenProcesses = (processes: Array<{ id: Process['id'] | null; pivot: ProcessPivot }>) => {
      return processes.map(proc => ({
        id: proc.id || null,
        pivot: {
          time: proc.pivot?.time ?? 0,
          final_value: proc.pivot?.final_value ?? 0
        }
      }));
    };

    const internalProcesses = ref<Array<{ id: Process['id'] | null; pivot: ProcessPivot }>>(flattenProcesses(props.modelValue));

    const fetchProcessOptions = async () => {
      try {
        const { data } = await axios.get<Process[]>('/api/processes');
        processOptions.value = data;
      } catch (error) {
        showToast({ message: 'Erro ao buscar processos: ' + error, type: 'error' });
      }
    };

    const addProcess = () => {
      internalProcesses.value.push({
        id: null,
        pivot: {
          time: 0,
          final_value: 0
        }
      });
    };

    const removeProcess = (index: number) => {
      internalProcesses.value.splice(index, 1);
    };

    const getValuePerMinute = (id: Process['id'] | null): number | string => {
      if (!id) return '';
      const found = processOptions.value.find(p => p.id === id);
      return found?.value_per_minute ?? '';
    };

    const calculateProcess = async (index: number) => {
      const proc = internalProcesses.value[index];

      if (!proc.id || !proc.pivot.time) {
        proc.pivot.final_value = 0;
        return;
      }

      try {
        const { data } = await axios.post<{ value: number }>('/api/set-parts/calculateProcessValue', {
          process_id: proc.id,
          time: proc.pivot.time
        });
        proc.pivot.final_value = data.value;
      } catch (error) {
        showToast({ message: 'Erro ao calcular valor do processo: ' + error, type: 'error' });
        proc.pivot.final_value = 0;
      }
    };

    watch(
      () => internalProcesses.value,
      (newVal) => {
        if (JSON.stringify(newVal) !== JSON.stringify(props.modelValue)) {
          emit('update:modelValue', newVal);
        }
      },
      { deep: true }
    );

    onMounted(() => {
      fetchProcessOptions();
    });

    return {
      internalProcesses,
      processOptions,
      addProcess,
      removeProcess,
      getValuePerMinute,
      calculateProcess
    };
  }
});
</script>