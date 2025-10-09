<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{
          isEdit ? 'Editar barra' : 'Nova barra'
        }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field
            label="Nome"
            v-model="formData.name"
            :rules="[v => !!v || 'Nome é obrigatório']"
            required
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
            suffix="mm"
            @blur="formData.length = roundValue(formData.length, 2)"
          />
          <v-text-field
            label="Peso (kg)"
            v-model="formData.weight"
            type="number"
            :rules="[
              v => !!v || 'Peso é obrigatório',
              v =>
                /^\d+(\.\d{1,2})?$/.test(String(v)) ||
                'Máximo 2 casas decimais',
            ]"
            required
            suffix="kg"
            @blur="formData.weight = roundValue(formData.weight, 2)"
          />
          <v-text-field
            label="Preço (R$/kg)"
            v-model="formData.price_kg"
            type="number"
            :rules="[
              v => !!v || 'Preço é obrigatório',
              v =>
                /^\d+(\.\d{1,4})?$/.test(String(v)) ||
                'Máximo 2 casas decimais',
            ]"
            required
            suffix="R$/kg"
            @blur="formData.price_kg = roundValue(formData.price_kg, 2)"
          />
          <v-autocomplete
            label="NCM (opcional)"
            v-model="formData.ncm_id"
            :items="ncms"
            item-title="code"
            item-value="id"
            clearable
            no-data-text="Nenhum NCM encontrado"
          >
            <template #item="{ item, props }">
              <v-list-item v-bind="props">
                <v-list-item-subtitle
                  >IPI: {{ item.raw.ipi }}%</v-list-item-subtitle
                >
              </v-list-item>
            </template>
          </v-autocomplete>
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
import { defineComponent, ref, watch, type PropType, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import type { Bar, MercosurCommonNomenclature } from '../types/types';

export default defineComponent({
  name: 'BarForm',
  props: {
    dialog: { type: Boolean, required: true },
    barData: {
      type: Object as PropType<Bar>,
      default: () => ({
        id: null,
        name: '',
        length: null,
        weight: null,
        price_kg: null,
        ncm_id: null,
      }),
    },
    isEdit: { type: Boolean, default: false },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const internalDialog = ref(props.dialog);
    const form = ref();
    const formData = ref<Bar>({
      id: null,
      name: '',
      length: 0,
      weight: 0,
      price_kg: 0,
      ncm_id: null,
    });
    const { showToast } = useToast();
    const { roundValue } = useMisc();

    const ncms = ref<MercosurCommonNomenclature[]>([]);

    const fetchNCMs = async () => {
      try {
        const { data } = await axios.get('/api/mercosur-common-nomenclatures');
        ncms.value = data;
      } catch (error) {
        showToast('Erro ao buscar NCMs', 'error');
      }
    };

    onMounted(() => {
      fetchNCMs();
    });

    watch(
      () => props.barData,
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

    const closeDialog = () => emit('close');

    const submitForm = async () => {
      try {
        const validation = await form.value.validate();

        if (!validation.valid) return;

        if (props.isEdit) {
          await axios.put(`/api/bars/${formData.value.id}`, formData.value);
        } else {
          await axios.post('/api/bars', formData.value);
        }

        showToast('Barra salva com sucesso!', 'success');
        emit('saved');
      } catch (error: any) {
        showToast(
          'Erro ao salvar barra: ' + error.response?.data?.message,
          'error'
        );
      }
    };

    return {
      internalDialog,
      form,
      formData,
      ncms,
      closeDialog,
      submitForm,
      roundValue,
    };
  },
});
</script>
