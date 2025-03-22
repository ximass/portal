<template>
  <v-container style="padding: 50px;">
    <v-form ref="materialForm" @submit.prevent="submitForm">
      <!-- Basic Fields Card -->
      <v-card class="mb-4">
        <v-card-title>
          <span class="text-h5">{{ isEdit ? 'Editar material' : 'Novo material' }}</span>
        </v-card-title>
        <v-row class="pa-4">
          <v-col cols="6" md="6">
            <v-text-field
              label="Nome do material"
              v-model="form.name"
              :rules="[v => !!v || 'Nome é obrigatório']"
              required
            />
          </v-col>
          <v-col cols="6" md="6">
            <v-select
              label="Tipo"
              :items="materialTypes"
              item-value="id"
              item-title="name"
              v-model="form.type"
              :rules="[v => !!v || 'Tipo é obrigatório']"
              required
            />
          </v-col>
        </v-row>
      </v-card>

      <!-- Specific Fields Card -->
      <v-card v-if="form.type" class="mb-4">
        <v-card-title>
          <span class="text-h6">Propriedades</span>
        </v-card-title>
        <v-card-text>
          <template v-if="form.type === 'sheet'">
            <v-row>
              <v-col cols="3">
                <v-text-field label="Espessura" v-model="form.sheet.thickness" type="number" required />
              </v-col>
              <v-col cols="3">
                <v-text-field label="Largura" v-model="form.sheet.width" type="number" required />
              </v-col>
              <v-col cols="3">
                <v-text-field label="Comprimento" v-model="form.sheet.length" type="number" required />
              </v-col>
              <v-col cols="3">
                <v-text-field label="Peso específico" v-model="form.sheet.specific_weight" type="number" required />
              </v-col>
              <v-col cols="3">
                <v-text-field label="Preço por grama" v-model="form.sheet.price_per_gram" type="number" required />
              </v-col>
            </v-row>
          </template>
          <template v-else-if="form.type === 'bar'">
            <v-row>
              <v-col cols="3">
                <v-text-field label="Diâmetro" v-model="form.bar.diameter" type="number" required />
              </v-col>
              <v-col cols="3">
                <v-text-field label="Comprimento" v-model="form.bar.length" type="number" required />
              </v-col>
              <v-col cols="3">
                <v-text-field label="Peso específico" v-model="form.bar.specific_weight" type="number" required />
              </v-col>
              <v-col cols="3">
                <v-text-field label="Preço por grama" v-model="form.bar.price_per_gram" type="number" required />
              </v-col>
            </v-row>
          </template>
          <template v-else-if="form.type === 'component'">
            <v-row>
              <v-col cols="12">
                <v-textarea label="Especificações" v-model="form.component.specification" />
              </v-col>
              <v-col cols="4">
                <v-text-field label="Valor unitário" v-model="form.component.unit_value" type="number" required />
              </v-col>
            </v-row>
          </template>
        </v-card-text>
      </v-card>

      <!-- Form Actions -->
      <v-card-actions class="justify-end">
        <v-btn variant="flat" @click="goBack">Cancelar</v-btn>
        <v-btn variant="flat" type="submit" color="primary">Salvar</v-btn>
      </v-card-actions>
    </v-form>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { useToast } from '@/composables/useToast';

export default defineComponent({
  name: 'MaterialView',
  setup() {
    const router = useRouter();
    const route = useRoute();
    const { showToast } = useToast();
    const isEdit = ref(route.params.id !== 'new');

    const form = reactive({
      id: null as number | null,
      name: '',
      type: '' as '' | 'sheet' | 'bar' | 'component',
      sheet: {
        thickness: null as number | null,
        width: null as number | null,
        length: null as number | null,
        specific_weight: null as number | null,
        price_per_gram: null as number | null,
      },
      bar: {
        diameter: null as number | null,
        length: null as number | null,
        specific_weight: null as number | null,
        price_per_gram: null as number | null,
      },
      component: {
        specification: '',
        unit_value: null as number | null,
      }
    });

    const materialTypes = [
      { id: 'sheet', name: 'Chapa' },
      { id: 'bar', name: 'Barra' },
      { id: 'component', name: 'Componente' },
    ];

    const loadMaterial = async () => {
      try {
        const { data } = await axios.get(`/api/materials/${route.params.id}`);

        form.id = data.id;
        form.name = data.name;
        form.type = data.type;

        // Load type-specific fields if available
        if(form.type === 'sheet' && data.sheet) {
          Object.assign(form.sheet, data.sheet);
        } else if(form.type === 'bar' && data.bar) {
          Object.assign(form.bar, data.bar);
        } else if(form.type === 'component' && data.component) {
          Object.assign(form.component, data.component);
        }
      } catch (error) {
        showToast('Erro ao carregar material', 'error');
      }
    };

    onMounted(() => {
      if (isEdit.value) loadMaterial();
    });

    const submitForm = async () => {
      try {
        let materialResponse;
        if (isEdit.value) {
          await axios.put(`/api/materials/${form.id}`, {
            name: form.name,
            type: form.type,
          });
          materialResponse = { id: form.id };
        } else {
          const res = await axios.post('/api/materials', {
            name: form.name,
            type: form.type,
          });
          materialResponse = res.data;
        }
        const materialId = materialResponse.id;
        if (form.type === 'sheet') {
          await axios.post('/api/sheets', {
            material_id: materialId,
            thickness: form.sheet.thickness,
            width: form.sheet.width,
            length: form.sheet.length,
            specific_weight: form.sheet.specific_weight,
            price_per_gram: form.sheet.price_per_gram,
          });
        } else if (form.type === 'bar') {
          await axios.post('/api/bars', {
            material_id: materialId,
            diameter: form.bar.diameter,
            length: form.bar.length,
            specific_weight: form.bar.specific_weight,
            price_per_gram: form.bar.price_per_gram,
          });
        } else if (form.type === 'component') {
          await axios.post('/api/components', {
            material_id: materialId,
            specification: form.component.specification,
            unit_value: form.component.unit_value,
          });
        }

        showToast('Material salvo com sucesso.', 'success');

        router.push({ name: 'MaterialsView' });
      } catch (error) {
        showToast('Erro ao salvar material', 'error');
      }
    };

    const goBack = () => {
      router.push({ name: 'MaterialsView' });
    };

    return { form, materialTypes, isEdit, submitForm, goBack };
  },
});
</script>