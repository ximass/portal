<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
      <h2>Barras</h2>
      <v-btn color="primary" @click="openForm">Adicionar</v-btn>
    </v-row>

    <v-data-table :items="bars" :headers="headers">
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editBar(item)">
              <v-list-item-title>Editar</v-list-item-title>
            </v-list-item>
            <v-list-item @click="item.id !== null && deleteBar(item.id)">
              <v-list-item-title>Excluir</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>    
    
    <BarForm :dialog="dialog" :barData="selectedBar" :isEdit="isEdit" @close="dialog = false" @saved="handleSaved" />
    
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
import BarForm from '../components/BarForm.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import type { Bar } from '../types/types';

export default defineComponent({
  name: 'BarsView',
  components: { BarForm, ConfirmDialog },  
  setup() {
    const { showToast } = useToast();
    const { isConfirmDialogOpen, confirmTitle, confirmMessage, openConfirm, closeConfirm, handleConfirm } = useConfirm();
    const bars = ref<Bar[]>([]);
    const dialog = ref(false);
    const isEdit = ref(false);
    const selectedBar = ref<Bar>({
      id: null,
      name: '',
      length: 0,
      weight: 0,
      price_kg: 0,
    });

    const headers = [
      { title: 'Código', value: 'id' },
      { title: 'Nome', value: 'name' },
      { title: 'Comprimento (mm)', value: 'length' },
      { title: 'Peso (kg)', value: 'weight' },
      { title: 'Preço por kg', value: 'price_kg' },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchBars = async () => {
      try {
        const { data } = await axios.get<Bar[]>('/api/bars');
        bars.value = data;
      } catch (error: any) {
        showToast('Erro ao carregar barras: ' + error.response?.data?.message || 'Erro desconhecido', 'error');
      }
    };

    const openForm = () => {
      selectedBar.value = {
        id: null,
        name: '',
        length: 0,
        weight: 0,
        price_kg: 0,
      };
      isEdit.value = false;
      dialog.value = true;
    };

    const editBar = (bar: Bar) => {
      selectedBar.value = { ...bar };
      isEdit.value = true;
      dialog.value = true;
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchBars();
    };    
    
    const deleteBar = async (id: number) => {
      openConfirm(
        'Tem certeza que deseja excluir esta barra?',
        async () => {
          try {
            await axios.delete(`/api/bars/${id}`);
            fetchBars();
            showToast('Barra excluída com sucesso!', 'success');
          } catch (error: any) {
            showToast('Erro ao excluir barra: ' + error.response?.data?.message || 'Erro desconhecido', 'error');
          }
        },
        'Excluir barra'
      );
    };

    onMounted(() => {
      fetchBars();
    });

    return { 
      bars, 
      headers, 
      dialog, 
      isEdit, 
      selectedBar, 
      openForm, 
      editBar, 
      deleteBar, 
      handleSaved,
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      closeConfirm,
      handleConfirm
    };
  },
});
</script>