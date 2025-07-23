<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{
          isEdit ? 'Editar chapa' : 'Nova chapa'
        }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-select
            label="Material"
            v-model="formData.material_id"
            :items="materials"
            item-title="name"
            item-value="id"
            :rules="[v => !!v || 'Material é obrigatório']"
            required
          />
          <v-text-field
            label="Nome da chapa"
            v-model="formData.name"
            :rules="[v => !!v || 'Nome é obrigatório']"
            required
          />
          <v-text-field
            label="Espessura (mm)"
            v-model="formData.thickness"
            type="number"
            :rules="[
              v => !!v || 'Espessura é obrigatória',
              v =>
                /^\d+(\.\d{1,2})?$/.test(String(v)) ||
                'Máximo 2 casas decimais',
            ]"
            required
            @blur="formData.thickness = roundValue(formData.thickness, 2)"
          />
          <v-text-field
            label="Largura (mm)"
            v-model="formData.width"
            type="number"
            :rules="[
              v => !!v || 'Largura é obrigatória',
              v =>
                /^\d+(\.\d{1,2})?$/.test(String(v)) ||
                'Máximo 2 casas decimais',
            ]"
            required
            @blur="formData.width = roundValue(formData.width, 2)"
          />
          <v-text-field
            label="Comprimento (mm)"
            v-model="formData.length"
            type="number"
            :rules="[
              v => !!v || 'Comprimento é obrigatório',
              v =>
                /^\d+(\.\d{1,2})?$/.test(String(v)) ||
                'Máximo 2 casas decimais',
            ]"
            required
            @blur="formData.length = roundValue(formData.length, 2)"
          />
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn @click="closeDialog">Cancelar</v-btn>
        <v-btn color="primary" @click="submitForm">{{
          isEdit ? 'Atualizar' : 'Salvar'
        }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, ref, watch, onMounted, type PropType } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import type { SheetForm as SheetFormType, Material } from '../types/types';

export default defineComponent({
  name: 'SheetForm',
  props: {
    dialog: { type: Boolean, required: true },
    sheetData: {
      type: Object as PropType<SheetFormType>,
      default: () => ({
        id: null,
        material_id: 0,
        name: '',
        thickness: 0,
        width: 0,
        length: 0,
      }),
    },
    isEdit: { type: Boolean, default: false },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const internalDialog = ref(props.dialog);
    const form = ref();
    const { showToast } = useToast();
    const { roundValue } = useMisc();

    const formData = ref<SheetFormType>({
      id: 0,
      name: '',
      material_id: 0,
      thickness: 0,
      width: 0,
      length: 0,
    });

    const materials = ref<Material[]>([]);

    const fetchMaterials = async () => {
      try {
        const { data } = await axios.get('/api/materials');

        materials.value = data;
      } catch (error: any) {
        showToast(
          error.response?.data?.message || 'Erro ao buscar materiais',
          'error'
        );
      }
    };

    watch(
      () => props.sheetData,
      newVal => {
        formData.value = { ...newVal };
      },
      { immediate: true }
    );

    watch(
      () => props.dialog,
      newVal => {
        internalDialog.value = newVal;
      }
    );

    const closeDialog = () => {
      emit('close');
    };

    const submitForm = async () => {
      try {
        const validation = await form.value.validate();

        if (!validation.valid) return;

        if (props.isEdit) {
          await axios.put(`/api/sheets/${formData.value.id}`, formData.value);
        } else {
          await axios.post('/api/sheets', formData.value);
        }

        showToast('Chapa salva com sucesso!', 'success');
        emit('saved');
      } catch (error: any) {
        showToast(
          'Erro ao salvar chapa: ' + error.response.data.message,
          'error'
        );
      }
    };

    onMounted(() => {
      fetchMaterials();
    });

    return {
      internalDialog,
      form,
      formData,
      materials,
      closeDialog,
      submitForm,
      roundValue,
    };
  },
});
</script>
