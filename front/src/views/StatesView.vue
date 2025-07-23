<template>
  <v-container style="padding: 50px">
    <v-row
      justify="space-between"
      align="center"
      class="mb-4"
      style="margin: 0"
    >
      <h2>Estados</h2>
      <v-btn color="primary" @click="openForm">Adicionar</v-btn>
    </v-row>

    <v-data-table :items="states" :headers="headers" class="elevation-1">
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editState(item)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item @click="item.id !== null && deleteState(item.id)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <StateForm
      :dialog="dialog"
      :stateData="selectedState"
      :isEdit="isEdit"
      @close="dialog = false"
      @saved="handleSaved"
    />

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
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import StateForm from '../components/StateForm.vue';
import type { State } from '../types/types';
import ConfirmDialog from '../components/ConfirmDialog.vue';

export default defineComponent({
  name: 'StatesView',
  components: { StateForm, ConfirmDialog },
  setup() {
    const dialog = ref(false);
    const isEdit = ref(false);
    const { showToast } = useToast();
    const {
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      openConfirm,
      closeConfirm,
      handleConfirm,
    } = useConfirm();

    const states = ref<State[]>([]);
    const selectedState = ref<State>({
      id: null,
      name: '',
      abbreviation: '',
      icms: 0,
    });

    const headers = [
      { title: 'Código', value: 'id', sortable: true },
      { title: 'Nome', value: 'name', sortable: true },
      { title: 'UF', value: 'abbreviation', sortable: true },
      { title: 'ICMS (%)', value: 'icms', sortable: true },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchStates = async () => {
      try {
        const { data } = await axios.get('/api/states');
        states.value = data;
      } catch (error) {
        showToast('Erro ao buscar estados', 'error');
      }
    };

    const openForm = () => {
      selectedState.value = {
        id: null,
        name: '',
        abbreviation: '',
        icms: 0,
      };
      isEdit.value = false;
      dialog.value = true;
    };

    const editState = (state: State) => {
      selectedState.value = { ...state };
      isEdit.value = true;
      dialog.value = true;
    };

    const deleteState = async (id: number) => {
      openConfirm(
        'Tem certeza que deseja excluir este estado?',
        async () => {
          try {
            await axios.delete(`/api/states/${id}`);
            await fetchStates();
            showToast('Estado excluído com sucesso.', 'success');
          } catch (error) {
            showToast('Erro ao excluir estado', 'error');
          }
        },
        'Excluir estado'
      );
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchStates();
    };

    onMounted(() => {
      fetchStates();
    });

    return {
      states,
      headers,
      dialog,
      isEdit,
      selectedState,
      openForm,
      editState,
      deleteState,
      handleSaved,
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      closeConfirm,
      handleConfirm,
    };
  },
});
</script>
