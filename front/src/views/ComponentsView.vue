<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
      <h2>Componentes</h2>
      <v-btn color="primary" @click="openForm">Adicionar</v-btn>
    </v-row>

    <v-data-table :items="components" :headers="headers">
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
            <v-list-item @click="deleteComponent(item.material_id)">
              <v-list-item-title>Excluir</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <ComponentForm :dialog="dialog" :componentData="selectedComponent" :isEdit="isEdit" @close="dialog = false"
      @saved="handleSaved" />
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import ComponentForm from '../components/ComponentForm.vue';
import { useToast } from '../composables/useToast';

export default defineComponent({
  name: 'ComponentsView',
  components: { ComponentForm },
  setup() {
    const components = ref<any[]>([]);
    const dialog = ref(false);
    const isEdit = ref(false);
    const selectedComponent = ref<any>({});

    const headers = [
      { title: 'Código', value: 'id' },
      { title: 'Nome', value: 'name' },
      { title: 'Valor unitário', value: 'unit_value' },
      { title: 'Especificações', value: 'specification' },
      { title: 'Fornecedor', value: 'supplier' },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const { showToast } = useToast();

    const fetchComponents = async () => {
      try {
        const { data } = await axios.get('/api/components');
        components.value = data;
      } catch (error) {
        showToast('Erro ao carregar componentes', 'error');
      }
    };

    const openForm = () => {
      selectedComponent.value = {};
      isEdit.value = false;
      dialog.value = true;
    };

    const editComponent = (component: any) => {
      selectedComponent.value = { ...component };
      isEdit.value = true;
      dialog.value = true;
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchComponents();
    };

    const deleteComponent = async (materialId: number) => {
      if (!confirm('Deseja excluir este componente?')) return;
      try {
        await axios.delete(`/api/components/${materialId}`);
        fetchComponents();
      } catch (error) {
        showToast('Erro ao excluir componente', 'error');
      }
    };

    onMounted(() => {
      fetchComponents();
    });

    return { components, headers, dialog, isEdit, selectedComponent, openForm, editComponent, deleteComponent, handleSaved };
  },
});
</script>