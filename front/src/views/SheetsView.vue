<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
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
              <v-list-item-title>Editar</v-list-item-title>
            </v-list-item>
            <v-list-item @click="deleteSheet(item.id)">
              <v-list-item-title>Excluir</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <SheetForm :dialog="dialog" :sheetData="selectedSheet" :isEdit="isEdit" @close="dialog = false"
      @saved="handleSaved" />
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import SheetForm from '../components/SheetForm.vue';

export default defineComponent({
  name: 'SheetsView',
  components: { SheetForm },
  setup() {
    const sheets = ref<any[]>([]);
    const dialog = ref(false);
    const isEdit = ref(false);
    const selectedSheet = ref<any>({});

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
        console.error('Erro ao carregar chapas');
      }
    };

    const openForm = () => {
      selectedSheet.value = {};
      isEdit.value = false;
      dialog.value = true;
    };

    const editSheet = (sheet: any) => {
      selectedSheet.value = { ...sheet };
      isEdit.value = true;
      dialog.value = true;
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchSheets();
    };

    const deleteSheet = async (id: number) => {
      if (!confirm('Deseja excluir esta chapa?')) return;
      try {
        await axios.delete(`/api/sheets/${id}`);
        fetchSheets();
      } catch (error) {
        console.error('Erro ao excluir chapa');
      }
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
    };
  },
});
</script>