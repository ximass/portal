<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{
          isEdit ? 'Editar material' : 'Novo material'
        }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field
            label="Nome do material"
            v-model="formData.name"
            :rules="[v => !!v || 'Nome é obrigatório']"
            required
          />
          <v-text-field
            label="Peso específico"
            v-model="formData.specific_weight"
            :rules="[
              v => !!v || 'Peso específico é obrigatório',
              v =>
                /^\d+(\.\d{1,6})?$/.test(String(v)) ||
                'Máximo 6 casas decimais',
            ]"
            type="number"
            required
            hint="Em g/mm³"
            @blur="
              formData.specific_weight = roundValue(formData.specific_weight, 6)
            "
          />
          <v-text-field
            label="Preço por kilo"
            v-model="formData.price_kg"
            :rules="[
              v => !!v || 'Preço é obrigatório',
              v =>
                /^\d+(\.\d{1,4})?$/.test(String(v)) ||
                'Máximo 4 casas decimais',
            ]"
            type="number"
            required
            hint="Em BRL/kg"
            @blur="formData.price_kg = roundValue(formData.price_kg, 4)"
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
      <v-card-actions class="justify-end">
        <v-btn variant="outlined" @click="closeDialog">Cancelar</v-btn>
        <v-btn variant="flat" color="primary" @click="submitForm">
          Salvar
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
import type { Material, MercosurCommonNomenclature } from '../types/types';

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
    const { roundValue } = useMisc();

    const form = ref();
    const formData = ref<Material>({
      id: null,
      name: '',
      specific_weight: 0,
      price_kg: 0,
      ncm_id: null,
    });

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
      () => props.materialData,
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
            `/api/materials/${props.materialData.id}`,
            formData.value
          );
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
