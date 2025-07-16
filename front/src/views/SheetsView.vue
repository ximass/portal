<template>
  <v-container style="padding: 50px">
    <v-row
      justify="space-between"
      align="center"
      class="mb-4"
      style="margin: 0"
    >
      <h2>Chapas</h2>
      <v-btn color="primary" @click="openForm">Adicionar</v-btn>
    </v-row>

    <v-data-table :items="sheets" :headers="headers">
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editSheet(item)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item @click="item.id !== null && deleteSheet(item.id)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <SheetForm
      :dialog="dialog"
      :sheetData="selectedSheet"
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
import SheetForm from '../components/SheetForm.vue';
import type { Sheet, SheetForm as SheetFormType } from '../types/types';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

export default defineComponent({
  name: 'SheetsView',
  components: { SheetForm, ConfirmDialog },
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

    const sheets = ref<Sheet[]>([]);
    const selectedSheet = ref<SheetFormType>();

    const headers = [
      { title: 'Código', value: 'id' },
      { title: 'Material', value: 'material.name' },
      { title: 'Largura', value: 'width' },
      { title: 'Comprimento', value: 'length' },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchSheets = async () => {
      try {
        const { data } = await axios.get('/api/sheets');
        sheets.value = data;
      } catch (error) {
        showToast('Erro ao carregar chapas', 'error');
      }
    };

    const openForm = () => {
      selectedSheet.value = {
        id: null,
        name: '',
        material_id: null,
        width: 0,
        length: 0,
      };
      isEdit.value = false;
      dialog.value = true;
    };

    const editSheet = (sheet: Sheet) => {
      selectedSheet.value = { ...sheet };
      isEdit.value = true;
      dialog.value = true;
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchSheets();
    };

    const deleteSheet = async (id: number) => {
      openConfirm(
        'Tem certeza que deseja excluir esta chapa?',
        async () => {
          try {
            await axios.delete(`/api/sheets/${id}`);
            await fetchSheets();
            showToast('Chapa excluída com sucesso.', 'success');
          } catch (error) {
            showToast('Erro ao excluir chapa', 'error');
          }
        },
        'Excluir chapa'
      );
    };

    onMounted(() => fetchSheets());

    return {
      sheets,
      headers,
      dialog,
      isEdit,
      selectedSheet,
      openForm,
      editSheet,
      deleteSheet,
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
