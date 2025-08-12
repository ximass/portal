<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{ isEdit ? 'Editar NCM' : 'Nova NCM' }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field
            label="Código"
            v-model="formData.code"
            :rules="[v => !!v || 'Código é obrigatório']"
            required
            hint="Código de identificação da NCM"
          />
          <v-text-field
            label="IPI (%)"
            v-model="formData.ipi"
            :rules="[
              v => (v !== null && v !== undefined && v !== '') || 'IPI é obrigatório',
              v => v >= 0 || 'IPI deve ser maior ou igual a 0',
              v => v <= 100 || 'IPI deve ser menor ou igual a 100',
              v =>
                /^\d+(\.\d{1,2})?$/.test(String(v)) ||
                'Máximo 2 casas decimais',
            ]"
            type="number"
            required
            hint="Percentual de IPI de 0 a 100"
            @blur="formData.ipi = roundValue(formData.ipi, 2)"
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
import type { MercosurCommonNomenclature } from '../types/types';

export default defineComponent({
  name: 'MercosurCommonNomenclatureForm',
  props: {
    dialog: { type: Boolean, required: true },
    nomenclatureData: {
      type: Object as PropType<MercosurCommonNomenclature>,
      default: () => ({}),
    },
    isEdit: { type: Boolean, default: false },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const internalDialog = ref(props.dialog);
    const { showToast } = useToast();
    const { roundValue } = useMisc();

    const form = ref();
    const formData = ref<MercosurCommonNomenclature>({
      id: null,
      code: '',
      ipi: 0,
    });

    watch(
      () => props.nomenclatureData,
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
          await axios.put(
            `/api/mercosur-common-nomenclatures/${props.nomenclatureData.id}`,
            formData.value
          );
        } else {
          await axios.post(
            '/api/mercosur-common-nomenclatures',
            formData.value
          );
        }

        showToast('NCM salva com sucesso!', 'success');
        emit('saved');
        closeDialog();
      } catch (err: any) {
        showToast('Erro ao salvar NCM: ' + err.response.data.message);
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
