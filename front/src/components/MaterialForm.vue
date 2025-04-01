<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{ isEdit ? 'Editar material' : 'Novo material' }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field label="Nome do material" v-model="formData.name" :rules="[v => !!v || 'Nome é obrigatório']"
            required />
          <v-text-field label="Espessura" v-model="formData.thickness" :rules="[v => !!v || 'Espessura é obrigatória']"
            type="number" required hint="Em mm" />
          <v-text-field label="Peso específico" v-model="formData.specific_weight"
            :rules="[v => !!v || 'Peso específico é obrigatório']" type="number" required hint="Em g/mm³" />
          <v-text-field label="Preço por kilo" v-model="formData.price_kg" :rules="[v => !!v || 'Preço é obrigatório']"
            type="number" required hint="Em BRL/kg" />
        </v-form>
      </v-card-text>
      <v-card-actions class="justify-end">
        <v-btn variant="flat" @click="closeDialog">Cancelar</v-btn>
        <v-btn variant="flat" color="primary" @click="submitForm">
          Salvar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, ref, watch, type PropType } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { Material } from '@/types/types';

export default defineComponent({
  name: 'MaterialForm',
  props: {
    dialog: { type: Boolean, required: true },
    materialData: { type: Object as PropType<Material>, default: () => ({}) },
    isEdit: { type: Boolean, default: false },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const internalDialog = ref(props.dialog);
    const { showToast } = useToast();

    const form = ref();
    const formData = ref<Material>({
      id: null,
      name: '',
      thickness: null,
      specific_weight: null,
      price_kg: null,
    });

    watch(() => props.materialData, (newVal) => {
      formData.value = { ...newVal };
    }, { immediate: true });

    watch(() => props.dialog, (newVal) => {
      internalDialog.value = newVal;
    });

    const closeDialog = () => {
      emit('close');
    };

    const submitForm = async () => {
      try {
        const validation = await form.value.validate();

        if (!validation.valid) return;

        if (props.isEdit) {
          await axios.put(`/api/materials/${props.materialData.id}`, formData.value);
        } else {
          await axios.post('/api/materials', formData.value);
        }

        showToast('Material salvo com sucesso!', 'success');
        emit('saved');
        closeDialog();
      } catch (err: any) {
        showToast('Erro ao salvar material: ' + err.response.data.message);
      }
    };

    return { internalDialog, form, formData, closeDialog, submitForm };
  },
});
</script>