<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{ isEdit ? 'Editar barra' : 'Nova barra' }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-select
            label="Material"
            :items="materials"
            item-title="name"
            item-value="id"
            v-model="formData.material_id"
            :rules="[v => !!v || 'Material é obrigatório']"
            required
          />
          <v-text-field label="Nome" v-model="formData.name" :rules="[v => !!v || 'Nome é obrigatório']"
            required />
          <v-text-field label="Diâmetro (mm)" v-model="formData.diameter" type="number"
            :rules="[v => !!v || 'Diâmetro é obrigatório']" required />
          <v-text-field label="Comprimento (mm)" v-model="formData.length" type="number"
            :rules="[v => !!v || 'Comprimento é obrigatório']" required />
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
import { defineComponent, ref, watch, onMounted, PropType } from 'vue';
import axios from 'axios';
import { useToast } from '@/composables/useToast';
import type { Bar, Material } from '@/types/types';

export default defineComponent({
  name: 'BarForm',
  props: {
    dialog: { type: Boolean, required: true },
    barData: {
      type: Object as PropType<Bar>,
      default: () => ({ id: null, name: '', material_id: null, diameter: null, length: null }),
    },
    isEdit: { type: Boolean, default: false },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const internalDialog = ref(props.dialog);
    const form = ref();
    const formData = ref<Bar>({
      id: null,
      material_id: null,
      name: '',
      diameter: null,
      length: null,
    });
    const materials = ref<Material[]>([]);
    const { showToast } = useToast();

    const fetchMaterials = async () => {
      try {
        const { data } = await axios.get('/api/materials');
        materials.value = data;
      } catch (error: any) {
        showToast(error.response?.data?.message || 'Erro ao buscar materiais', 'error');
      }
    };

    onMounted(() => {
      fetchMaterials();
    });

    watch(() => props.barData, (newVal) => {
      formData.value = { ...newVal };
    }, { immediate: true });

    watch(() => props.dialog, (newVal) => {
      internalDialog.value = newVal;
    });

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
        emit('saved');
      } catch (error: any) {
        showToast('Erro ao salvar barra: ' + error.response?.data?.message, 'error');
      }
    };

    return { internalDialog, form, formData, materials, closeDialog, submitForm };
  },
});
</script>