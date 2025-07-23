<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{
          isEdit ? 'Editar estado' : 'Novo estado'
        }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field
            label="Nome do estado"
            v-model="formData.name"
            :rules="[v => !!v || 'Nome é obrigatório']"
            required
          />
          <v-text-field
            label="Abreviação (UF)"
            v-model="formData.abbreviation"
            :rules="[
              v => !!v || 'Abreviação é obrigatória',
              v => v.length === 2 || 'Deve ter exatamente 2 caracteres',
            ]"
            maxlength="2"
            required
            hint="Ex: SP, RJ, MG"
            style="text-transform: uppercase"
            @input="formData.abbreviation = formData.abbreviation.toUpperCase()"
          />
          <v-text-field
            label="ICMS"
            v-model="formData.icms"
            :rules="[
              v => !!v || 'ICMS é obrigatório',
              v => v >= 0 || 'ICMS deve ser maior ou igual a 0',
              v => v <= 100 || 'ICMS deve ser menor ou igual a 100',
              v =>
                /^\d+(\.\d{1,2})?$/.test(String(v)) ||
                'Máximo 2 casas decimais',
            ]"
            type="number"
            required
            hint="Percentual do ICMS (0-100)"
            @blur="formData.icms = roundValue(formData.icms, 2)"
          />
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
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import type { State } from '../types/types';

export default defineComponent({
  name: 'StateForm',
  props: {
    dialog: { type: Boolean, required: true },
    stateData: { type: Object as PropType<State>, default: () => ({}) },
    isEdit: { type: Boolean, default: false },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const internalDialog = ref(props.dialog);
    const { showToast } = useToast();
    const { roundValue } = useMisc();

    const form = ref();
    const formData = ref<State>({
      id: null,
      name: '',
      abbreviation: '',
      icms: 0,
    });

    watch(
      () => props.stateData,
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
          await axios.put(`/api/states/${props.stateData.id}`, formData.value);
        } else {
          await axios.post('/api/states', formData.value);
        }

        showToast('Estado salvo com sucesso!', 'success');
        emit('saved');
        closeDialog();
      } catch (err: any) {
        showToast('Erro ao salvar estado: ' + err.response.data.message);
      }
    };

    return {
      internalDialog,
      form,
      formData,
      closeDialog,
      submitForm,
      roundValue,
    };
  },
});
</script>
