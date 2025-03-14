<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
      <h2>Processos</h2>
      <v-btn color="primary" @click="openForm">Novo</v-btn>
    </v-row>
    <v-data-table :items="processes" :headers="headers">
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editProcess(item)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item @click="deleteProcess(item.id)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import router from '@/router';
import { useToast } from '@/composables/useToast';

export default defineComponent({
  name: 'ProcessesView',
  setup() {
    const processes = ref([]);
    const headers = [
      { title: 'ID', value: 'id' },
      { title: 'Título', value: 'title' },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const { showToast } = useToast();

    const fetchProcesses = async () => {
      const { data } = await axios.get('/api/processes');
      processes.value = data;
    };

    const openForm = () => {
      router.push({ name: 'ProcessView', params: { id: 'new' } });
    };

    const editProcess = (process: any) => {
      router.push({ name: 'ProcessView', params: { id: process.id } });
    };

    const deleteProcess = async (id: number) => {
      try {
        const confirmed = window.confirm('Deseja realmente excluir este processo?');

        if (!confirmed) {
          return;
        }
        
        await axios.delete(`/api/processes/${id}`);
        fetchProcesses();
      }
      catch (error) {
        showToast('Erro ao excluir processo', 'error');
      }
    };

    onMounted(() => fetchProcesses());
    return { processes, headers, openForm, editProcess, deleteProcess };
  },
});
</script>