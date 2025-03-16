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

            <v-col cols="4" md="4" sm="12">
              <v-select
                label="Cliente"
                v-model="form.customer_id"
                :items="customers"
                item-value="id"
                item-title="name"
                clearable
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
        <v-row class="d-flex flex-row">
          <v-col cols="4">
            <v-text-field
              v-model="setItem.name"
              variant="underlined"
              placeholder="Digite o nome do conjunto"
              @change="updateSetName(setIndex)"
            />
          </v-col>
          <v-col cols="8" class="d-flex justify-end align-center">
              <v-btn color="error" @click="deleteSet(setIndex)">
                Excluir
              </v-btn>
          </v-col>
        </v-row>
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
            <div
              setPart
              class="image-preview"
            >
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
                <v-menu offset-y>
                  <template #activator="{ props }">
                    <v-btn variant="plain" :ripple="false" v-bind="props">
                      <v-icon color="white" class="delete-icon">mdi-dots-vertical</v-icon>
                    </v-btn>
                  </template>
                  <v-list>
                    <v-list-item @click.stop="openPartModal(part)">
                      <v-list-item-title>Visualizar</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click.stop="deletePart(setIndex, setItem.setParts.length - 1 - partIndex)">
                      <v-list-item-title>Excluir</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </div>
            </div>
            </v-col>
          </v-row>
        </div>
      </v-card-text>
    </v-card>

    <PartForm
      v-if="selectedPart"
      :part="selectedPart"
      :show="showPartModal"
      :getPartImageUrl="getPartImageUrl"
      @close="closePartModal"
    />

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
import PartForm from '@/components/PartForm.vue';

export default defineComponent({
  name: 'Orders',
  components: {
    PartForm
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const isNew = ref(route.params.id === 'new');
    const customers = ref<any[]>([]);

    const { showToast } = useToast();

    const form = ref({ customer_id: '', final_value: '' });
    const sets = ref<Array<{
      id?: number;
      name?: string;
      setParts: { id: string, set_id: string, title: string; content: string }[];
      fileList: File[] | null;
    }>>([]);

    const showPartModal = ref(false);
    const selectedPart = ref(null);

    const openPartModal = (part: any) => {
      selectedPart.value = part;
      showPartModal.value = true;
    };

    const closePartModal = () => {
      selectedPart.value = null;
      showPartModal.value = false;
    };

    onMounted(async () => {
      try {
        const { data } = await axios.get('/api/customers');

        customers.value = data;
      } catch (error) {
        showToast('Erro ao carregar clientes', 'error');
      }

      if (!isNew.value && route.params.id) {
        try {
          const { data } = await axios.get(`/api/orders/${route.params.id}`);

          form.value.customer_id = data.customer_id;
          form.value.final_value = data.final_value;

          if (data.sets && data.sets.length) {
            sets.value = data.sets.map((s: any) => ({
              ...s,
              fileList: null,
              setParts: [],
            }));

            for (const set of sets.value) {
              const { data } = await axios.get(`/api/sets/${set.id}/parts`);

              set.setParts = data;
            }
          }
        } catch (error) {
          showToast('Erro ao carregar pedido: ' + error, 'error');
        }
      }
    });

    const saveOrder = async () => {
      try {
        if (isNew.value) {
          const { data } = await axios.post('/api/orders', {
            customer_id: form.value.customer_id,
            final_value: form.value.final_value
          });

          router.push({ name: 'Orders', params: { id: data.id } });
        } else {
          await axios.put(`/api/orders/${route.params.id}`, {
            customer_id: form.value.customer_id,
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

    const deleteSet = async (setIndex: number) => {
      try {
        const set = sets.value[setIndex];

        if (set.id) {
          await axios.delete(`/api/sets/${set.id}`);
          
          sets.value.splice(setIndex, 1);
        }

        showToast('Conjunto excluído com sucesso.', 'success');
      } catch (error) {
        showToast('Erro ao excluir conjunto: ' + error, 'error');
      }
    };

    const updateSetName = async (setIndex: number) => {
      try {
        const set = sets.value[setIndex];

        if (set.id) {
          await axios.put(`/api/sets/${set.id}`, {
            name: set.name,
          });
        }

        showToast('Nome do conjunto atualizado com sucesso.', 'success');
      } catch (error) {
        showToast('Erro ao atualizar nome do conjunto: ' + error, 'error');
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
              id: response.data.id,
              set_id: response.data.set_id,
              title: response.data.title,
              content: response.data.content,
            });
          } catch (error) {
            showToast('Erro ao fazer upload de arquivo: ' + error, 'error');
          }
        }
      }
      currentSet.fileList = null;
    };

    const deletePart = (setIndex: number, partIndex: number) => {
      try
      {
        const part = sets.value[setIndex].setParts[partIndex];
  
        if (part.id) {
          axios.delete(`/api/sets/${part.set_id}/parts/${part.id}`);
          sets.value[setIndex].setParts.splice(partIndex, 1);
        }
      }
      catch (error) {
        showToast('Erro ao excluir peça: ' + error, 'error');
      }
    };

    const getPartImageUrl = (content: string) => {
      const baseUrl = import.meta.env.VITE_API_URL
      return `${baseUrl}${content}`
    }

    return {
      isNew,
      form,
      customers,
      sets,
      saveOrder,
      createSet,
      deleteSet,
      updateSetName,
      deletePart,
      getPartImageUrl,
      openPartModal,
      closePartModal,
      showPartModal,
      selectedPart,
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