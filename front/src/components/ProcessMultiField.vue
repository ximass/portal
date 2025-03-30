<template>
  <v-row align="center" justify="space-between" class="mb-4 pa-4">
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
        />
      </v-col>
      <v-col cols="3">
        <v-text-field
          label="Valor por minuto"
          :value="getValuePerMinute(proc.id)"
          readonly
          density="compact"
          prefix="R$"
        />
      </v-col>
      <v-col cols="2">
        <v-text-field
          label="Tempo"
          v-model="proc.time"
          type="number"
          required
          density="compact"
          hint="Em minutos"
        />
      </v-col>
      <v-col cols="3">
        <v-text-field
          label="Valor final"
          v-model="proc.final_value"
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
import { defineComponent, ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import type { ProcessSelection } from '@/types/types';

export default defineComponent({
  name: 'ProcessMultiField',
  props: {
    modelValue: {
      type: Array as () => any[],
      default: () => []
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const processOptions = ref<Array<{ id: number; title: string; value_per_minute?: number }>>([]);

    // Include final_value on each process object
    const flattenProcesses = (processes: any[]): ProcessSelection[] => {
      return processes.map(proc => ({
        id: proc.id || null,
        time: proc.hasOwnProperty('time')
          ? proc.time
          : (proc.pivot && proc.pivot.time ? proc.pivot.time : 0),
        final_value: proc.hasOwnProperty('final_value')
          ? proc.final_value
          : (proc.pivot && proc.pivot.final_value ? proc.pivot.final_value : 0)
      }));
    };

    const internalProcesses = ref<ProcessSelection[]>(flattenProcesses(props.modelValue));

    console.log('internalProcesses', internalProcesses.value);

    const fetchProcessOptions = async () => {
      try {
        const { data } = await axios.get('/api/processes');
        processOptions.value = data;
        console.log('processOptions', processOptions.value);
      } catch (error) {
        showToast({ message: 'Erro ao buscar processos: ' + error, type: 'error' });
      }
    };

    const addProcess = () => {
      internalProcesses.value.push({
        id: null,
        time: 0,
        final_value: 0
      });
    };

    const removeProcess = (index: number) => {
      internalProcesses.value.splice(index, 1);
    };

    // Returns the value_per_minute of the selected process
    const getValuePerMinute = (id: number | null): number | string => {
      if (!id) return '';

      const found = processOptions.value.find(p => p.id === id);
      
      return found?.value_per_minute ?? '';
    };

    // Watch internalProcesses and emit changes only if there's a difference compared to the prop value.
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
      getValuePerMinute
    };
  }
});
</script>