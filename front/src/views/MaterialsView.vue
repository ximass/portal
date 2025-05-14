<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
      <h2>Materiais</h2>
      <v-btn color="primary" @click="openForm">Adicionar</v-btn>
    </v-row>

    <v-data-table :items="materials" :headers="headers" class="elevation-1">
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editMaterial(item)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item @click="deleteMaterial(item.id)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <MaterialForm
      :dialog="dialog"
      :materialData="selectedMaterial"
      :isEdit="isEdit"
      @close="dialog = false"
      @saved="handleSaved"
    />
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import MaterialForm from '../components/MaterialForm.vue';

export default defineComponent({
  name: 'MaterialsView',
  components: { MaterialForm },
  setup() {
    const materials = ref<any[]>([]);
    const dialog = ref(false);
    const isEdit = ref(false);
    const selectedMaterial = ref<any>({});
    const { showToast } = useToast();

    const headers = [
      { title: 'Código', value: 'id', sortable: true },
      { title: 'Nome', value: 'name', sortable: true },
      { title: 'Espessura (mm)', value: 'thickness', sortable: true },
      { title: 'Peso específico (g/cm³)', value: 'specific_weight', sortable: true },
      { title: 'Preço (R$)', value: 'price_kg', sortable: true },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchMaterials = async () => {
      try {
        const { data } = await axios.get('/api/materials');
        materials.value = data;
      } catch (error) {
        showToast('Erro ao buscar materiais', 'error');
      }
    };

    const openForm = () => {
      selectedMaterial.value = {};
      isEdit.value = false;
      dialog.value = true;
    };

    const editMaterial = (material: any) => {
      selectedMaterial.value = { ...material };
      isEdit.value = true;
      dialog.value = true;
    };

    const deleteMaterial = async (id: number) => {
      if (!confirm('Deseja realmente excluir este material?')) return;
      
      try {
        await axios.delete(`/api/materials/${id}`);
        await fetchMaterials();
        showToast('Material excluído com sucesso.', 'success');
      } catch (error) {
        showToast('Erro ao excluir material', 'error');
      }
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchMaterials();
    };

    onMounted(() => {
      fetchMaterials();
    });

    return { materials, headers, dialog, isEdit, selectedMaterial, openForm, editMaterial, deleteMaterial, handleSaved };
  },
});
</script>