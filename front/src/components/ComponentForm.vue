<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{ isEdit ? 'Editar componente' : 'Novo componente' }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field
            label="Nome"
            v-model="formData.name"
            :rules="[v => !!v || 'Nome é obrigatório']"
            required
          />
          <v-textarea
            label="Especificações"
            v-model="formData.specification"
          />
          <v-text-field
            label="Valor unitário (BRL)"
            v-model="formData.unit_value"
            type="number"
            :rules="[
              v => !!v || 'Valor unitário é obrigatório',
              v => /^\d+(\.\d{1,2})?$/.test(String(v)) || 'Máximo 2 casas decimais'
            ]"
            required
            hint="Em BRL"
            @blur="formData.unit_value = roundValue(formData.unit_value, 2)"
          />
          <v-text-field
            label="Fornecedor"
            v-model="formData.supplier"
            type="text"
          />
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="closeDialog">Cancelar</v-btn>
        <v-btn color="primary" @click="submitForm">
          {{ isEdit ? 'Atualizar' : 'Salvar' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, ref, watch, type PropType } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import { useMisc } from '@/composables/misc';
import { Component as ComponentType } from '@/types/types';

export default defineComponent({
  name: 'ComponentForm',
  props: {
    dialog: { type: Boolean, required: true },
    componentData: { type: Object as PropType<ComponentType>, default: () => ({} as ComponentType) },
    isEdit: { type: Boolean, default: false },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const internalDialog = ref(props.dialog);
    const form = ref();
    const formData = ref<ComponentType>({
      id: null,
      name: '',
      specification: '',
      unit_value: 0,
      supplier: '',
    });
    const { showToast } = useToast();
    const { roundValue } = useMisc();

    watch(() => props.componentData, (newVal) => {
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
        const valid = await form.value?.validate();
        if (!valid) return;
        if (props.isEdit) {
          await axios.put(`/api/components/${props.componentData.id}`, formData.value);
        } else {
          await axios.post('/api/components', formData.value);
        }
        emit('saved');
      } catch (error: any) {
        showToast(error.response?.data?.message || 'Erro ao salvar componente', 'error');
      }
    };

    return { internalDialog, form, formData, closeDialog, submitForm, roundValue };
  },
});
</script>