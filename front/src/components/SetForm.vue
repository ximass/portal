<template>
  <v-dialog 
    v-model="internalDialog" 
    width="70vw"
    height="90vh"
  >
    <v-card>
      <v-card-title class="d-flex align-center justify-space-between">
        <v-text-field
          variant="underlined"
          v-model="formData.name"
          label="Nome do conjunto"
          :rules="[v => !!v || 'Nome é obrigatório']"
          required
        />
        <button
          class="close-btn"
          @click="closeDialog"
          aria-label="Fechar"
          title="Fechar"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            height="24"
            width="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </v-card-title>
      <v-card-text>
        <v-row>
          <!-- Left: Image panel -->
          <v-col cols="6">
            <v-responsive
              max-height="60vh"
              max-width="40vw"
              min-height="50vh"
            >
              <!-- Prioriza a imagem selecionada para upload -->
              <template v-if="imagePreview && imagePreview !== 'PDF_FILE'">
                <v-img
                  :src="imagePreview"
                  contain
                  max-width="100%"
                />
              </template>
              <template v-else-if="imagePreview === 'PDF_FILE'">
                <div class="d-flex align-center justify-center" style="height: 100%; min-height: 300px;">
                  <div class="text-center">
                    <v-icon size="120" color="primary">mdi-file-pdf-box</v-icon>
                    <div class="mt-4 text-body-1">Arquivo PDF selecionado</div>
                    <div class="text-caption text-grey-lighten-1">O PDF será convertido para imagem automaticamente</div>
                  </div>
                </div>
              </template>
              <template v-else-if="currentImageUrl">
                <template v-if="isPdf(currentImageUrl)">
                  <iframe
                    :src="currentImageUrl"
                    width="100%"
                    height="100%"
                  />
                </template>
                <template v-else>
                  <v-img
                    :src="currentImageUrl"
                    contain
                    max-width="100%"
                  />
                </template>
              </template>
              <div v-else class="no-image-placeholder">
                <v-icon size="120" color="grey-lighten-2">mdi-image-off-outline</v-icon>
                <div class="text-grey-lighten-1 text-body-2 mt-4">
                  Nenhuma imagem disponível
                </div>
              </div>
            </v-responsive>
            
            <!-- Upload de imagem -->
            <div class="mt-4">
              <v-file-input
                v-model="imageFile"
                accept="image/*,.pdf"
                density="compact"
                show-size
                prepend-icon="mdi-upload"
                label="Imagem ou PDF do conjunto"
                clearable
              />
            </div>
          </v-col>

          <!-- Right: Form panel -->
          <v-col cols="6">
            <v-form ref="form" @submit.prevent="submitForm">
              <v-row dense style="margin-top: -40px">
                <v-col cols="12">
                  <v-text-field
                    label="Referência"
                    v-model="formData.reference"
                    variant="underlined"
                    clearable
                    hide-details="auto"
                    density="compact"
                  />
                </v-col>
              </v-row>
              
              <v-row dense>
                <v-col cols="12">
                  <v-textarea
                    label="Observações"
                    v-model="formData.obs"
                    variant="underlined"
                    clearable
                    hide-details="auto"
                    density="compact"
                    rows="3"
                    auto-grow
                  />
                </v-col>
              </v-row>

              <v-row dense>
                <v-col cols="12" md="6">
                  <v-select
                    label="NCM"
                    :items="ncms"
                    item-title="code"
                    item-value="id"
                    v-model="formData.ncm_id"
                    variant="underlined"
                    density="compact"
                    hide-details="auto"
                    clearable
                  >
                    <template #item="{ props, item }">
                      <v-list-item v-bind="props">
                        <v-list-item-title>{{ item.raw.code }}</v-list-item-title>
                        <v-list-item-subtitle>IPI: {{ item.raw.ipi }}%</v-list-item-subtitle>
                      </v-list-item>
                    </template>
                    <template #selection="{ item }">
                      {{ item.raw.code }} (IPI: {{ item.raw.ipi }}%)
                    </template>
                  </v-select>
                </v-col>
                <v-col cols="12" md="6">
                  <v-select
                    label="Unidade"
                    :items="unitOptions"
                    item-title="title"
                    item-value="value"
                    v-model="formData.unit"
                    variant="underlined"
                    density="compact"
                    hide-details="auto"
                    clearable
                  />
                </v-col>
              </v-row>

              <v-row dense>
                <v-col cols="12" md="6">
                  <v-text-field
                    label="Quantidade"
                    v-model="formData.quantity"
                    type="number"
                    variant="underlined"
                    density="compact"
                    hide-details="auto"
                    min="0"
                  />
                </v-col>
              </v-row>
            </v-form>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn variant="outlined" @click="closeDialog">Cancelar</v-btn>
        <v-btn
          color="primary"
          variant="flat"
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
import { defineComponent, ref, watch, computed, onMounted } from 'vue';
import { useToast } from '../composables/useToast';
import axios from 'axios';
import type { Set, SetForm, MercosurCommonNomenclature } from '../types/types';

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
    const ncms = ref<MercosurCommonNomenclature[]>([]);

    const formData = ref<SetForm>({
      name: '',
      content: null,
      quantity: null,
      unit: null,
      ncm_id: null,
      reference: null,
      obs: null,
    });

    const unitOptions = ref([
      { title: 'Peça', value: 'piece' },
      { title: 'Kg', value: 'kg' },
    ]);

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

    const selectedNcm = computed(() => {
      if (formData.value.ncm_id) {
        return ncms.value.find(ncm => ncm.id === formData.value.ncm_id);
      }
      return null;
    });

    const isPdf = (filePath: string): boolean => {
      if (!filePath) return false;
      return filePath.toLowerCase().includes('.pdf') || filePath.toLowerCase().endsWith('.pdf');
    };

    const fetchNCMs = async () => {
      try {
        const response = await axios.get('/api/mercosur-common-nomenclatures');
        ncms.value = response.data;
      } catch (error) {
        console.error('Erro ao buscar NCMs:', error);
        showToast('Erro ao carregar NCMs', 'error');
      }
    };

    watch(
      () => props.setData,
      (newSet) => {
        if (newSet) {
          formData.value = {
            name: newSet.name,
            content: newSet.content || null,
            quantity: newSet.quantity || null,
            unit: newSet.unit || null,
            ncm_id: newSet.ncm_id || null,
            reference: newSet.reference || null,
            obs: newSet.obs || null,
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
        
        if (formData.value.quantity !== null && formData.value.quantity !== undefined) {
          formDataToSend.append('quantity', formData.value.quantity.toString());
        }
        
        if (formData.value.unit) {
          formDataToSend.append('unit', formData.value.unit);
        }
        
        if (formData.value.ncm_id !== null && formData.value.ncm_id !== undefined) {
          formDataToSend.append('ncm_id', formData.value.ncm_id.toString());
        }
        
        if (formData.value.reference) {
          formDataToSend.append('reference', formData.value.reference);
        }
        
        if (formData.value.obs) {
          formDataToSend.append('obs', formData.value.obs);
        }

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

        // Limpa a imagem preview após upload bem-sucedido
        imageFile.value = null;
        imagePreview.value = null;

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

    onMounted(() => {
      fetchNCMs();
    });

    return {
      form,
      formData,
      loading,
      internalDialog,
      imageFile,
      imagePreview,
      currentImageUrl,
      selectedNcm,
      ncms,
      unitOptions,
      isPdf,
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

.close-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  margin-left: 12px;
  display: flex;
  align-items: center;
}

.close-btn svg {
  width: 24px;
  height: 24px;
  color: rgb(var(--v-theme-secondary));
  transition: color 0.2s;
}

.close-btn:hover svg {
  color: rgb(var(--v-theme-primary));
}

.no-image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 300px;
}
</style>
