<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">Editar conjunto</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field
            label="Nome do conjunto"
            v-model="formData.name"
            :rules="[v => !!v || 'Nome é obrigatório']"
            required
          />
          
          <v-file-input
            label="Imagem ou PDF do conjunto"
            v-model="imageFile"
            accept="image/*,.pdf"
            clearable
            show-size
            prepend-icon="mdi-camera"
          />

          <!-- Preview da imagem atual -->
          <div v-if="currentImageUrl" class="mt-4">
            <v-card class="mb-3">
              <v-card-subtitle>Imagem atual:</v-card-subtitle>
              <v-card-text>
                <v-img
                  :src="currentImageUrl"
                  max-height="200"
                  contain
                  class="rounded"
                />
              </v-card-text>
            </v-card>
          </div>

          <!-- Preview da nova imagem -->
          <div v-if="imagePreview" class="mt-4">
            <v-card class="mb-3">
              <v-card-subtitle>Nova imagem:</v-card-subtitle>
              <v-card-text>
                <v-img
                  v-if="imagePreview !== 'PDF_FILE'"
                  :src="imagePreview"
                  max-height="200"
                  contain
                  class="rounded"
                />
                <div v-else class="d-flex align-center justify-center" style="height: 200px;">
                  <div class="text-center">
                    <v-icon size="64" color="primary">mdi-file-pdf-box</v-icon>
                    <div class="mt-2">Arquivo PDF selecionado</div>
                    <div class="text-caption">O PDF será convertido para imagem automaticamente</div>
                  </div>
                </div>
              </v-card-text>
            </v-card>
          </div>
        </v-form>
      </v-card-text>
      <v-card-actions class="justify-end">
        <v-btn variant="flat" @click="closeDialog">Cancelar</v-btn>
        <v-btn
          color="primary"
          :loading="loading"
          @click="submitForm"
        >
          Salvar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, ref, watch, computed } from 'vue';
import { useToast } from '../composables/useToast';
import axios from 'axios';
import type { Set, SetForm } from '../types/types';

export default defineComponent({
  name: 'SetForm',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    setData: {
      type: Object as () => Set | null,
      default: null,
    },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const form = ref<any>(null);
    const loading = ref(false);
    const imageFile = ref<File | null>(null);

    const formData = ref<SetForm>({
      name: '',
      content: null,
    });

    const internalDialog = computed({
      get: () => props.show,
      set: (value: boolean) => {
        if (!value) {
          emit('close');
        }
      },
    });

    const imagePreview = ref<string | null>(null);

    const currentImageUrl = computed(() => {
      let url = null;
      if (props.setData?.content) {
        const baseUrl = import.meta.env.VITE_API_URL;
        url = `${baseUrl}${props.setData.content}`;
      }
      return url;
    });

    watch(
      () => props.setData,
      (newSet) => {
        if (newSet) {
          formData.value = {
            name: newSet.name,
            content: newSet.content || null,
          };
        }
      },
      { immediate: true }
    );

    watch(
      () => props.show,
      (newShow) => {
        if (!newShow) {
          imageFile.value = null;
          imagePreview.value = null;
        }
      }
    );

    watch(
      () => imageFile.value,
      (newFile) => {
        if (newFile) {
          const file = newFile;

          if (file.type === 'application/pdf') {
            imagePreview.value = 'PDF_FILE';
          } else {
            const reader = new FileReader();
            reader.onload = (e) => {
              imagePreview.value = e.target?.result as string;
            };
            reader.readAsDataURL(file);
          }
        } else {
          imagePreview.value = null;
        }
      }
    );

    const closeDialog = () => {
      emit('close');
    };

    const submitForm = async () => {
      const validation = await form.value.validate();
      if (!validation.valid) {
        return;
      }

      if (!props.setData?.id) {
        showToast('Erro: ID do conjunto não encontrado', 'error');
        return;
      }

      loading.value = true;

      try {
        const formDataToSend = new FormData();
        formDataToSend.append('name', formData.value.name);

        if (imageFile.value) {
          const file = imageFile.value;
          formDataToSend.append('image', file);
        } else {
          console.log('Nenhum arquivo selecionado para upload');
        }

        const response = await axios.post(
          `/api/sets/${props.setData.id}/update`,
          formDataToSend,
          {
            headers: { 'Content-Type': 'multipart/form-data' },
          }
        );

        emit('saved', response.data);
        showToast('Conjunto atualizado com sucesso!', 'success');
        closeDialog();
      } catch (error: any) {
        console.error('Erro ao salvar conjunto:', error);
        const message = error.response?.data?.message || 'Erro ao salvar conjunto';
        showToast(message, 'error');
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      formData,
      loading,
      internalDialog,
      imageFile,
      imagePreview,
      currentImageUrl,
      closeDialog,
      submitForm,
    };
  },
});
</script>

<style scoped>
.rounded {
  border-radius: 8px;
}
</style>
