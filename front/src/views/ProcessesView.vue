<template>
  <v-container style="padding: 50px">
    <v-row
      justify="space-between"
      align="center"
      class="mb-4"
      style="margin: 0"
    >
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
            <v-list-item @click="item.id !== null && deleteProcess(item.id)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <ConfirmDialog
      :show="isConfirmDialogOpen"
      :title="confirmTitle"
      :message="confirmMessage"
      @confirm="handleConfirm"
      @cancel="closeConfirm"
    />
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import router from '../router';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import type { Process } from '../types/types';
import ConfirmDialog from '../components/ConfirmDialog.vue';

export default defineComponent({
  name: 'ProcessesView',
  components: { ConfirmDialog },
  setup() {
    const processes = ref<Process[]>([]);
    const headers = [
      { title: 'Código', value: 'id' },
      { title: 'Nome', value: 'title' },
      { title: 'Observações', value: 'content' },
      { title: 'Valor por minuto', value: 'value_per_minute' },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const { showToast } = useToast();
    const {
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      openConfirm,
      closeConfirm,
      handleConfirm,
    } = useConfirm();

    const fetchProcesses = async () => {
      const { data } = await axios.get('/api/processes');
      processes.value = data;
    };

    const openForm = () => {
      router.push({ name: 'ProcessView', params: { id: 'new' } });
    };

    const editProcess = (process: Process) => {
      router.push({ name: 'ProcessView', params: { id: process.id } });
    };

    const deleteProcess = async (id: number) => {
      openConfirm(
        'Tem certeza que deseja excluir este processo?',
        async () => {
          try {
            await axios.delete(`/api/processes/${id}`);
            await fetchProcesses();
            showToast('Processo excluído com sucesso.', 'success');
          } catch (error) {
            showToast('Erro ao excluir processo', 'error');
          }
        },
        'Excluir processo'
      );
    };

    onMounted(() => fetchProcesses());

    return {
      processes,
      headers,
      openForm,
      editProcess,
      deleteProcess,
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      closeConfirm,
      handleConfirm,
    };
  },
});
</script>
