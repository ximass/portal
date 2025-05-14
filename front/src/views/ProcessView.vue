<template>
  <v-container>
    <v-card>
      <v-card-title>{{ isNew ? 'Novo processo' : 'Editar processo' }}</v-card-title>
      <v-card-text>
        <v-form ref="processForm" v-model="isFormValid" @submit.prevent="saveProcess">
          <v-row>
            <v-col cols="12">
              <v-text-field
                label="Nome"
                v-model="form.title"
                required
                :rules="[v => !!v || 'Campo obrigatório']"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <v-textarea
                label="Observações"
                v-model="form.content"
                required
                :rules="[v => !!v || 'Campo obrigatório']"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field
                type="number"
                label="Valor por minuto"
                v-model="form.value_per_minute"
                :rules="[
                  v => !v || /^\d+(\.\d{1,2})?$/.test(v) || 'Máximo de 2 casas decimais'
                ]"
                @blur="form.value_per_minute = roundValue(form.value_per_minute, 2)"
              />
            </v-col>
          </v-row>
          <v-card-actions class="d-flex justify-end">
            <v-btn @click="goBack">Cancelar</v-btn>
            <v-btn color="primary" type="submit">Salvar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import type { ProcessForm } from '../types/types';

export default defineComponent({
  name: 'ProcessView',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const isNew = ref(route.params.id === 'new');
    const processForm = ref();
    const isFormValid = ref(false);
    const form = ref<ProcessForm>({ title: '', content: '', value_per_minute: 0 });

    const { showToast } = useToast();
    const { roundValue } = useMisc();

    onMounted(async () => {
      if (!isNew.value) {
        const { data } = await axios.get(`/api/processes/${route.params.id}`);
        form.value = data;
      }
    });

    const saveProcess = async () => {
      try {
        const validation = await processForm.value.validate();

        if (!validation.valid) return;

        if (isNew.value) {
          await axios.post('/api/processes', form.value);
        } else {
          await axios.put(`/api/processes/${route.params.id}`, form.value);
        }

        router.push({ name: 'ProcessesView' });
      } catch (error) {
        showToast('Erro ao salvar processo', 'error');
      }
    };

    const goBack = () => router.push({ name: 'ProcessesView' });

    return { isNew, form, saveProcess, goBack, processForm, isFormValid, roundValue };
  },
});
</script>