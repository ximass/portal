<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{
          isEdit ? 'Editar componente' : 'Novo componente'
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
          <v-textarea label="Especificações" v-model="formData.specification" />
          <v-text-field
            label="Valor unitário (BRL)"
            v-model="formData.unit_value"
            type="number"
            :rules="[
              v => !!v || 'Valor unitário é obrigatório',
              v =>
                /^\d+(\.\d{1,2})?$/.test(String(v)) ||
                'Máximo 2 casas decimais',
            ]"
            required
            hint="Em BRL"
            @blur="formData.unit_value = roundValue(formData.unit_value, 2)"
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
import { defineComponent, ref, watch, type PropType, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import type {
  Component as ComponentType,
  MercosurCommonNomenclature,
} from '../types/types';

export default defineComponent({
  name: 'ComponentForm',
  props: {
    dialog: { type: Boolean, required: true },
    componentData: {
      type: Object as PropType<ComponentType>,
      default: () => ({}) as ComponentType,
    },
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
      () => props.componentData,
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
        const valid = await form.value?.validate();
        if (!valid) return;
        if (props.isEdit) {
          await axios.put(
            `/api/components/${props.componentData.id}`,
            formData.value
          );
        } else {
          await axios.post('/api/components', formData.value);
        }
        showToast('Componente salvo com sucesso!', 'success');
        emit('saved');
      } catch (error: any) {
        showToast(
          error.response?.data?.message || 'Erro ao salvar componente',
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
