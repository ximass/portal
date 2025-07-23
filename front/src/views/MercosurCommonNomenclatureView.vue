<template>
  <v-container style="padding: 50px">
    <v-row
      justify="space-between"
      align="center"
      class="mb-4"
      style="margin: 0"
    >
      <h2>NCM</h2>
      <v-btn color="primary" @click="openForm">Adicionar</v-btn>
    </v-row>

    <v-data-table :items="nomenclatures" :headers="headers" class="elevation-1">
      <template #item.ipi="{ item }"> {{ item.ipi }}% </template>
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editNomenclature(item)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item
              @click="item.id !== null && deleteNomenclature(item.id)"
            >
              <v-list-item-title>
                <v-icon class="me-2">mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <MercosurCommonNomenclatureForm
      :dialog="dialog"
      :nomenclatureData="selectedNomenclature"
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
import MercosurCommonNomenclatureForm from '../components/MercosurCommonNomenclatureForm.vue';
import type { MercosurCommonNomenclature } from '../types/types';
import ConfirmDialog from '../components/ConfirmDialog.vue';

export default defineComponent({
  name: 'MercosurCommonNomenclatureView',
  components: { MercosurCommonNomenclatureForm, ConfirmDialog },
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

    const nomenclatures = ref<MercosurCommonNomenclature[]>([]);
    const selectedNomenclature = ref<MercosurCommonNomenclature>({
      id: null,
      code: '',
      ipi: 0,
    });

    const headers = [
      { title: 'Código', value: 'id', sortable: true },
      { title: 'Código NCM', value: 'code', sortable: true },
      { title: 'IPI (%)', value: 'ipi', sortable: true },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchNomenclatures = async () => {
      try {
        const { data } = await axios.get('/api/mercosur-common-nomenclatures');
        nomenclatures.value = data;
      } catch (error) {
        showToast('Erro ao buscar NCMs', 'error');
      }
    };

    const openForm = () => {
      selectedNomenclature.value = {
        id: null,
        code: '',
        ipi: 0,
      };
      isEdit.value = false;
      dialog.value = true;
    };

    const editNomenclature = (nomenclature: MercosurCommonNomenclature) => {
      selectedNomenclature.value = { ...nomenclature };
      isEdit.value = true;
      dialog.value = true;
    };

    const deleteNomenclature = async (id: number) => {
      openConfirm(
        'Tem certeza que deseja excluir esta NCM?',
        async () => {
          try {
            await axios.delete(`/api/mercosur-common-nomenclatures/${id}`);
            await fetchNomenclatures();
            showToast('NCM excluída com sucesso.', 'success');
          } catch (error) {
            showToast('Erro ao excluir NCM', 'error');
          }
        },
        'Excluir NCM'
      );
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchNomenclatures();
    };

    onMounted(() => {
      fetchNomenclatures();
    });

    return {
      nomenclatures,
      headers,
      dialog,
      isEdit,
      selectedNomenclature,
      openForm,
      editNomenclature,
      deleteNomenclature,
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
