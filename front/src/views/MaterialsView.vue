<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
      <h2>Materiais</h2>
      <v-btn color="primary" @click="createMaterial">Novo</v-btn>
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
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useRouter } from 'vue-router';

export default defineComponent({
  name: 'MaterialsView',
  setup() {
    const materials = ref<Array<any>>([]);
    const headers = [
      { title: 'Código', value: 'id', sortable: true },
      { title: 'Nome', value: 'name', sortable: true },
      { title: 'Tipo', value: 'type', sortable: true },
      { title: 'Ações', value: 'actions', sortable: false }
    ];
    const router = useRouter();
    const { showToast } = useToast();

    const fetchMaterials = async () => {
      try {
        const { data } = await axios.get('/api/materials');

        materials.value = data;
      } catch (error) {
        showToast('Erro ao buscar materiais', 'error');
      }
    };

    const createMaterial = () => {
      router.push({ name: 'MaterialView', params: { id: 'new' } });
    };

    const editMaterial = (material: any) => {
      router.push({ name: 'MaterialView', params: { id: material.id } });
    };

    const deleteMaterial = async (id: number) => {
      if (!confirm('Deseja realmente excluir este material?')) return;
      try {
        await axios.delete(`/api/materials/${id}`);

        fetchMaterials();

        showToast('Material excluído com sucesso.', 'success');
      } catch (error) {
        showToast('Erro ao excluir material', 'error');
      }
    };

    onMounted(() => fetchMaterials());

    return { materials, headers, createMaterial, editMaterial, deleteMaterial };
  },
});
</script>