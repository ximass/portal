<template>
  <v-row align="center" justify="space-between" class="mb-4 pa-4 mt-4">
    <h3 class="text-h6">Processos</h3>
    <v-btn small color="primary" @click="addProcess">Adicionar</v-btn>
  </v-row>
  <div class="process-list">
    <div
      class="mb-2"
      v-for="(proc, index) in internalProcesses"
      :key="index"
    >
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
            @update:model-value="onSelectChange(index)"
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
            step="1"
            min="0"
            :rules="[v => Number.isInteger(Number(v)) || 'Apenas números inteiros']"
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
            :rules="[v => /^\d+(\.\d{1,2})?$/.test(String(v)) || 'Máximo 2 casas decimais']"
            @blur="onFinalValueBlur(index)"
          />
        </v-col>
        <v-col cols="1">
          <v-btn variant="text" small icon color="error" @click="removeProcess(index)">
            <v-icon small>mdi-delete</v-icon>
          </v-btn>
        </v-col>
      </v-row>
    </div>
  </div>
</template>

<style scoped>

.process-list {
  min-height: 100px;
  overflow-y: auto;
  max-height: 150px;
  overflow-x: hidden;
}

</style>

<script lang="ts">
import { defineComponent, ref, onMounted, watch, type PropType } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import type { Process, ProcessPivot } from '../types/types';

export default defineComponent({
  name: 'ProcessMultiField',
  props: {
    modelValue: {
      type: Array as PropType<Array<{ id: Process['id'] | null; pivot: ProcessPivot }>>,
      default: () => []
    }
  },
  emits: ['update:modelValue', 'process-updated'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const { roundValue } = useMisc();
    
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
        showToast('Erro ao buscar processos: ' + error, 'error');
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

      emit('update:modelValue', internalProcesses.value);
    };
    
    const removeProcess = (index: number) => {
      internalProcesses.value.splice(index, 1);

      emit('update:modelValue', internalProcesses.value);
      emit('process-updated');
    };    
    
    const onFinalValueBlur = (index: number) => {
      const proc = internalProcesses.value[index];

      if (proc && typeof proc.pivot.final_value === 'number') {
        proc.pivot.final_value = roundValue(proc.pivot.final_value, 2);
        emit('update:modelValue', internalProcesses.value);
      }
    };

    const getValuePerMinute = (id: Process['id'] | null): number | string => {
      if (!id) return '';
      const found = processOptions.value.find(p => p.id === id);

      return found?.value_per_minute !== undefined
        ? roundValue(found.value_per_minute, 2)
        : '';
    };

    const calculateProcess = async (index: number) => {
      const proc = internalProcesses.value[index];

      if (!proc.id || !proc.pivot.time) {
        proc.pivot.final_value = 0;
        
        emit('update:modelValue', internalProcesses.value);
        emit('process-updated');
        return;
      }

      try {
        const { data } = await axios.post<{ value: number }>('/api/set-parts/calculateProcessValue', {
          process_id: proc.id,
          time: proc.pivot.time
        });
        proc.pivot.final_value = data.value;
      } catch (error) {
        showToast('Erro ao calcular valor do processo: ' + error, 'error');
        proc.pivot.final_value = 0;
      }

      emit('update:modelValue', internalProcesses.value);
      emit('process-updated');
    };    
    
    const onSelectChange = (index: number) => {
      emit('update:modelValue', internalProcesses.value);
      calculateProcess(index);
      emit('process-updated');
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

    // Watcher para atualizar internalProcesses quando modelValue mudar (navegação entre partes)
    watch(
      () => props.modelValue,
      (newVal) => {
        if (JSON.stringify(newVal) !== JSON.stringify(internalProcesses.value)) {
          internalProcesses.value = flattenProcesses(newVal);
        }
      },
      { deep: true, immediate: true }
    );

    onMounted(() => {
      fetchProcessOptions();
    });

    return {
      internalProcesses,
      processOptions,
      addProcess,
      removeProcess,
      onFinalValueBlur,
      getValuePerMinute,
      calculateProcess,
      onSelectChange
    };
  }
});
</script>