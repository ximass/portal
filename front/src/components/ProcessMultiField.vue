<template>
  <v-row align="center" justify="space-between" class="mb-4 pa-4">
    <h3 class="text-h6">Processos</h3>
    <v-btn small color="primary" @click="addProcess">Adicionar</v-btn>
  </v-row>
  <div v-for="(proc, index) in internalProcesses" :key="index" class="mb-2">
    <v-row dense>
      <v-col cols="4">
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
      <v-col cols="4">
        <v-text-field
          label="Tempo"
          v-model="proc.time"
          type="number"
          required
          density="compact"
        />
      </v-col>
      <v-col cols="3">
        <v-text-field
          label="Quantidade"
          v-model="proc.quantity"
          type="number"
          required
          density="compact"
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
    const processOptions = ref<Array<{ id: number; title: string }>>([]);

    // Updated flattenProcesses to preserve time/quantity if they already exist,
    // otherwise fall back to pivot values.
    const flattenProcesses = (processes: any[]): ProcessSelection[] => {
      return processes.map(proc => ({
        id: proc.id || null,
        time: proc.hasOwnProperty('time') ? proc.time : (proc.pivot && proc.pivot.time ? proc.pivot.time : 0),
        quantity: proc.hasOwnProperty('quantity') ? proc.quantity : (proc.pivot && proc.pivot.quantity ? proc.pivot.quantity : 0),
      }));
    };

    const internalProcesses = ref<ProcessSelection[]>(flattenProcesses(props.modelValue));

    const fetchProcessOptions = async () => {
      try {
        const { data } = await axios.get('/api/processes');
        processOptions.value = data;
      } catch (error) {
        showToast({ message: 'Erro ao buscar processos: ' + error, type: 'error' });
      }
    };

    const addProcess = () => {
      internalProcesses.value.push({
        id: null,
        time: 0,
        quantity: 0,
      });
    };

    const removeProcess = (index: number) => {
      internalProcesses.value.splice(index, 1);
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
    };
  }
});
</script>