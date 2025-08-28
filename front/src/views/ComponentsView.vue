<template>
  <v-container style="padding: 50px">
    <v-row
      justify="space-between"
      align="center"
      class="mb-4"
      style="margin: 0"
    >
      <h2>Componentes</h2>
      <v-btn color="primary" @click="openForm">Adicionar</v-btn>
    </v-row>

    <v-text-field
      v-model="search"
      label="Buscar componentes..."
      prepend-inner-icon="mdi-magnify"
      variant="outlined"
      hide-details
      single-line
      density="compact"
      class="mb-4"
    ></v-text-field>

    <v-data-table 
      :items="components" 
      :headers="headers"
      :sort-by="[{ key: 'name', order: 'asc' }]"
      :search="search"
    >
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editComponent(item)">
              <v-list-item-title>Editar</v-list-item-title>
            </v-list-item>
            <v-list-item @click="item.id !== null && deleteComponent(item.id)">
              <v-list-item-title>Excluir</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <ComponentForm
      :dialog="dialog"
      :componentData="selectedComponent"
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
import ComponentForm from '../components/ComponentForm.vue';
import type { Component } from '../types/types';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import ConfirmDialog from '../components/ConfirmDialog.vue';

export default defineComponent({
  name: 'ComponentsView',
  components: { ComponentForm, ConfirmDialog },
  setup() {
    const dialog = ref(false);
    const isEdit = ref(false);
    const search = ref('');
    const { showToast } = useToast();
    const {
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      openConfirm,
      closeConfirm,
      handleConfirm,
    } = useConfirm();

    const components = ref<Component[]>([]);
    const selectedComponent = ref<Component>({
      id: null,
      name: '',
      unit_value: 0,
      specification: '',
      supplier: '',
    });

    const headers = [
      { title: 'Código', value: 'id', sortable: true },
      { title: 'Nome', value: 'name', sortable: true },
      { title: 'Valor unitário', value: 'unit_value', sortable: true },
      { title: 'Especificações', value: 'specification', sortable: true },
      { title: 'Fornecedor', value: 'supplier', sortable: true },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchComponents = async () => {
      try {
        const { data } = await axios.get('/api/components');
        components.value = data;
      } catch (error) {
        showToast('Erro ao carregar componentes', 'error');
      }
    };

    const openForm = () => {
      selectedComponent.value = {
        id: null,
        name: '',
        unit_value: 0,
        specification: '',
        supplier: '',
      };
      isEdit.value = false;
      dialog.value = true;
    };

    const editComponent = (component: Component) => {
      selectedComponent.value = { ...component };
      isEdit.value = true;
      dialog.value = true;
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchComponents();
    };

    const deleteComponent = async (id: number) => {
      openConfirm(
        'Tem certeza que deseja excluir este componente?',
        async () => {
          try {
            await axios.delete(`/api/components/${id}`);
            fetchComponents();
            showToast('Componente excluído com sucesso!', 'success');
          } catch (error) {
            showToast('Erro ao excluir componente', 'error');
          }
        },
        'Excluir componente'
      );
    };

    onMounted(() => {
      fetchComponents();
    });

    return {
      components,
      headers,
      dialog,
      isEdit,
      search,
      selectedComponent,
      openForm,
      editComponent,
      deleteComponent,
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
