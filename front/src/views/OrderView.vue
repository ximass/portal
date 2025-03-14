<template>
  <v-container>
    <v-card class="mb-4">
      <v-card-title>Pedido</v-card-title>
      <v-card-text>
        <v-form ref="formRef">
          <v-row>
            <v-col cols="4" md="4" sm="12">
              <v-text-field
                label="Valor final"
                placeholder="Digite o valor final"
                v-model="form.final_value"
              />
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>

    <!-- Botão para criar novos sets -->
    <v-row class="justify-end pa-4 mb-2">
      <v-btn color="secondary" @click="createSet">Adicionar Conjunto</v-btn>
    </v-row>

    <!-- Listagem de conjuntos -->
    <v-card v-for="(setItem, setIndex) in sets" :key="setItem.id" class="mb-4">
      <v-card-title>
        {{ setItem.name || 'Conjunto ' + (setIndex + 1) }}
      </v-card-title>
      <v-card-text>
        <v-file-input
          v-model="setItem.fileList"
          multiple
          clearable
          label="Selecione arquivos"
          show-size
          counter
        />
        <div class="setParts-container">
          <v-row class="d-flex flex-row" dense>
            <v-col
              v-for="(part, partIndex) in setItem.setParts.slice().reverse()"
              :key="partIndex"
              cols="auto"
              class="pa-2"
            >
              <div class="image-preview">
                <v-img
                  :src="getPartImageUrl(part.content)"
                  width="150"
                  height="150"
                  contain
                >
                  <template #error>
                    <div
                      style="width:150px;height:150px;display:flex;align-items:center;justify-content:center;background-color:#f0f0f0"
                    >
                      <v-icon large color="grey lighten-1">mdi-file</v-icon>
                    </div>
                  </template>
                </v-img>
                <div class="counter">{{ partIndex + 1 }}</div>
                <div class="overlay">
                  <span class="overlay-text">{{ part.title }}</span>
                  <v-icon
                    color="white"
                    class="delete-icon"
                    @click="deletePart(setIndex, setItem.setParts.length - 1 - partIndex)"
                  >
                    mdi-delete
                  </v-icon>
                </div>
              </div>
            </v-col>
          </v-row>
        </div>
      </v-card-text>
    </v-card>

    <v-row class="justify-end pa-4">
      <v-btn color="primary" @click="saveOrder">Salvar</v-btn>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from '@/composables/useToast';
import axios from 'axios';

export default defineComponent({
  name: 'Orders',
  setup() {
    const route = useRoute();
    const router = useRouter();
    const isNew = ref(route.params.id === 'new');
    const { showToast } = useToast();

    const form = ref({ final_value: '' });
    const sets = ref<Array<{
      id?: number;
      name?: string;
      setParts: { title: string; content: string }[];
      fileList: File[] | null;
    }>>([]);

    onMounted(async () => {
      if (!isNew.value && route.params.id) {
        try {
          const { data } = await axios.get(`/api/orders/${route.params.id}`);

          form.value.final_value = data.final_value;

          if (data.sets && data.sets.length) {
            sets.value = data.sets.map((s: any) => ({
              ...s,
              fileList: null,
              setParts: [],
            }));

            for (const set of sets.value) {
              const { data } = await axios.get(`/api/sets/${set.id}/parts`);
              console.log(data);
              set.setParts = data;
            }
          }
        } catch (error) {
          console.error(error);
        }
      }
    });

    const saveOrder = async () => {
      try {
        if (isNew.value) {
          const { data } = await axios.post('/api/orders', {
            final_value: form.value.final_value,
          });

          router.push({ name: 'Orders', params: { id: data.id } });
        } else {
          await axios.put(`/api/orders/${route.params.id}`, {
            final_value: form.value.final_value,
          });
        }

        showToast('Pedido salvo com sucesso.', 'success');
      } catch (error) {
        showToast('Erro ao salvar pedido: ' + error, 'error');
      }
    };

    const createSet = async () => {
      if (isNew.value) {
        showToast('Crie o pedido antes de adicionar conjuntos.', 'warning');
        return;
      }
      try {
        const { data } = await axios.post('/api/sets', {
          name: 'Novo conjunto',
          order_id: route.params.id,
        });
        sets.value.push({
          ...data,
          fileList: null,
          setParts: [],
        });
      } catch (error) {
        showToast('Erro ao criar conjunto: ' + error, 'error');
      }
    };

    watch(
      () => sets.value.map((s) => s.fileList),
      (newValues, oldValues) => {
        newValues.forEach((files, index) => {
          if (files && files.length) handleFileUpload(index);
        });
      },
      { deep: true }
    );

    const handleFileUpload = async (setIndex: number) => {
      const currentSet = sets.value[setIndex];
      const files = currentSet.fileList;

      if (files && files.length && currentSet.id) {
        for (const file of files) {
          const formData = new FormData();

          formData.append('file', file);
          formData.append('set_id', currentSet.id!.toString());

          try {
            const response = await axios.post('/api/upload-set-part', formData, {
              headers: { 'Content-Type': 'multipart/form-data' },
            });

            currentSet.setParts.push({
              title: response.data.title,
              content: response.data.content,
            });
          } catch (error) {
            console.error('File upload error:', error);
          }
        }
      }
      currentSet.fileList = null;
    };

    const deletePart = (setIndex: number, partIndex: number) => {
      sets.value[setIndex].setParts.splice(partIndex, 1);
    };

    const getPartImageUrl = (content: string) => {
      const baseUrl = import.meta.env.VITE_API_URL
      return `${baseUrl}${content}`
    }

    return {
      isNew,
      form,
      sets,
      saveOrder,
      createSet,
      deletePart,
      getPartImageUrl
    };
  },
});
</script>

<style scoped>
.v-card {
  margin-bottom: 16px;
}
.image-preview {
  position: relative;
  width: 150px;
  height: 150px;
  border: 2px solid #f0f0f0;
  border-radius: 4px;
}
.counter {
  position: absolute;
  top: 4px;
  right: 4px;
  background: #1976d2;
  color: white;
  padding: 2px 6px;
  border-radius: 12px;
  font-size: 12px;
  z-index: 2;
}
.overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2px 4px;
  min-height: 40px;
}
.overlay-text {
  font-size: 12px;
  color: white;
}
.delete-icon {
  cursor: pointer;
}

.setParts-container {
  max-height: calc(150px * 3 + 16px);
  overflow-y: auto;
  padding: 8px;
}

.setParts-container::-webkit-scrollbar {
  width: 8px;
}
.setParts-container::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.4);
  border-radius: 4px;
}
</style>