<template>
  <v-container>
    <v-card class="mb-4">
      <v-card-text>
        <v-form ref="formRef">
          <v-row>
            <v-col cols="12" md="3" sm="12">
              <v-select
                label="Tipo"
                v-model="form.type"
                :items="orderTypeOptions"
                item-value="value"
                item-text="title"
              />
            </v-col>
            <v-col cols="12" md="3" sm="12">
              <v-select
                label="Cliente"
                v-model="form.customer_id"
                :items="customers"
                item-value="id"
                item-title="name"
                clearable
              />
            </v-col>
            <v-col cols="12" md="3" sm="12">
              <v-text-field
                label="Markup"
                placeholder="Digite o markup"
                v-model="form.markup"
                type="number"
                step="0.001"
                @change="onMarkupChange"
              />
            </v-col>
            <v-col cols="12" md="3" sm="12">
              <v-select
                label="Tipo de entrega"
                v-model="form.delivery_type"
                :items="deliveryTypeOptions"
                item-value="value"
                item-text="title"
                clearable
              />
            </v-col>
            <v-col cols="12" md="3" sm="12">
              <v-text-field
                label="Valor do frete"
                placeholder="Digite o valor do frete"
                v-model="form.delivery_value"
                type="number"
                step="1"
                prefix="R$"
              />
            </v-col>
            <v-col cols="12" md="3" sm="12">
              <v-text-field
                label="Data estimada de entrega"
                v-model="form.estimated_delivery_date"
                type="text"
              />
            </v-col>
            <v-col cols="12" md="3" sm="12">
              <v-text-field
                label="Data de entrega"
                v-model="form.delivery_date"
                type="datetime-local"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <v-textarea
                label="Observação de pagamento"
                placeholder="Digite a observação de pagamento"
                v-model="form.payment_obs"
                rows="3"
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
      <v-progress-linear
        v-if="uploadingIndex === setIndex"
        indeterminate
        color="primary"
        height="4"
      />
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
            <v-menu offset-y>
              <template #activator="{ props }">
                <v-btn
                  variant="plain"
                  :ripple="false"
                  v-bind="props"
                  class="menu-actions"
                  size="large"
                  title="Ações do conjunto"
                >
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list>
                <v-list-item @click.stop="deleteSet(setIndex)">
                  <v-list-item-title>Excluir</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </v-col>
        </v-row>
      </v-card-title>
      <v-card-text>
        <v-file-input
          v-model="setItem.fileList"
          multiple
          clearable
          :disabled="uploadingIndex === setIndex"
          label="Adicionar peças ao conjunto"
          show-size
          counter
        />
        <div class="setParts-container">
          <v-row class="d-flex flex-row" dense>
            <v-col cols="auto" class="pa-2">
              <div
                class="image-preview add-part-button"
                @click="addNewPart(setIndex)"
              >
                <div class="add-part-content">
                  <svg
                    width="40"
                    height="40"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M12 2V22M2 12H22"
                      stroke="#666"
                      stroke-width="2"
                      stroke-linecap="round"
                    />
                  </svg>
                  <span class="add-part-text">Adicionar peça</span>
                </div>
              </div>
            </v-col>
            <v-col
              v-for="(part, partIndex) in setItem.setParts.slice().reverse()"
              :key="part.id"
              cols="auto"
              class="pa-2"
            >
              <div setPart class="image-preview">
                <v-img
                  :src="getPartImageUrl(part.content!)"
                  width="150"
                  height="150"
                  contain
                >
                  <template #error>
                    <div
                      style="
                        width: 150px;
                        height: 150px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background-color: #f0f0f0;
                      "
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
                      <v-btn
                        variant="plain"
                        :ripple="false"
                        v-bind="props"
                        class="menu-actions"
                        title="Ações da peça"
                      >
                        <v-icon color="white">mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list>
                      <v-list-item @click.stop="openPartModal(part)">
                        <v-list-item-title>Visualizar</v-list-item-title>
                      </v-list-item>
                      <v-list-item @click.stop="printPart(part)">
                        <v-list-item-title>Imprimir</v-list-item-title>
                      </v-list-item>
                      <v-list-item
                        @click.stop="
                          deletePart(
                            setIndex,
                            setItem.setParts.length - 1 - partIndex
                          )
                        "
                      >
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

    <!-- Tabela de valores -->
    <OrderValuesTable
      v-if="!isNew"
      :headers="setPartsHeaders"
      :items="allSetParts"
      :groupBy="groupByValuesTable"
    />

    <PartForm
      v-if="selectedPart"
      :part="selectedPart"
      :show="showPartModal"
      :getPartImageUrl="getPartImageUrl"
      :allParts="allPartsInDisplayOrder"
      :currentPartIndex="currentPartIndex"
      :showNavigation="allPartsInDisplayOrder.length > 1"
      @part-saved="updatePartInList"
      @close="closePartModal"
      @navigate-to-part="handlePartNavigation"
    />

    <v-row class="justify-end pa-4">
      <v-menu
        v-if="!isNew"
        v-model="showDocumentsMenu"
        location="bottom"
        transition="scale-transition"
      >
        <template v-slot:activator="{ props }">
          <v-btn
            color="success"
            class="me-2"
            v-bind="props"
            :disabled="loadingDocument"
          >
            <v-icon class="me-2">mdi-file-document</v-icon>
            Documentos
          </v-btn>
        </template>
        <v-card min-width="200">
          <v-card-title class="text-subtitle-1">Documentos</v-card-title>
          <v-divider></v-divider>
          <v-list density="compact">
            <v-list-item
              v-for="document in availableDocuments"
              :key="document.value"
              @click="generateDocument(document.value)"
              :disabled="loadingDocument"
            >
              <v-list-item-title>{{ document.title }}</v-list-item-title>
              <template v-slot:prepend>
                <v-icon>{{ document.icon }}</v-icon>
              </template>
            </v-list-item>
          </v-list>
        </v-card>
      </v-menu>
      <v-btn
        v-if="!isNew"
        color="secondary"
        class="me-2"
        @click="printAllParts"
      >
        <v-icon class="me-2">mdi-printer</v-icon>
        Peças
      </v-btn>
      <v-btn color="primary" @click="saveOrder">Salvar</v-btn>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, watch, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import axios from 'axios';
import PartForm from '../components/PartForm.vue';
import OrderValuesTable from '../components/OrderValuesTable.vue';
import type { OrderForm, OrderSet, Part, Set, OrderType } from '../types/types';

export default defineComponent({
  name: 'Orders',
  components: {
    PartForm,
    OrderValuesTable,
  },
  setup() {
    const { showToast } = useToast();
    const { getPartImageUrl } = useMisc();

    const route = useRoute();
    const router = useRouter();
    const isNew = ref(route.params.id === 'new');
    const customers = ref<any[]>([]);

    const form = ref<OrderForm>({
      type: 'pre_order',
      customer_id: '',
      delivery_type: '',
      delivery_value: '',
      markup: '',
      delivery_date: '',
      estimated_delivery_date: '',
      payment_obs: '',
    });

    const orderTypeOptions = ref<{ title: string; value: OrderType }[]>([
      { title: 'Orçamento', value: 'pre_order' },
      { title: 'Pedido', value: 'order' },
    ]);

    const deliveryTypeOptions = ref([
      { title: 'CIF', value: 'CIF' },
      { title: 'FOB', value: 'FOB' },
    ]);

    const sets = ref<OrderSet[]>([]);
    const showPartModal = ref(false);
    const selectedPart = ref<Part | null>(null);
    const setParts = ref<Part[]>([]);
    const setPartsHeaders = [
      { title: 'Peça', value: 'title', sortable: true },
      { title: 'Valor unitário', value: 'unit_value', sortable: true },
      { title: 'Qtd.', value: 'quantity', sortable: true },
      { title: 'Valor total', value: 'final_value', sortable: true },
      { title: 'Peso bruto', value: 'gross_weight', sortable: true },
      { title: 'Peso líquido', value: 'net_weight', sortable: true },
    ];

    const uploadingIndex = ref<number | null>(null);

    const showDocumentsMenu = ref(false);
    const loadingDocument = ref(false);
    const availableDocuments = ref([
      {
        title: 'Orçamento',
        value: 'quote',
        icon: 'mdi-file-document-outline',
      },
    ]);

    const updatePartInList = (updatedPart: Part) => {
      sets.value.forEach(set => {
        const index = set.setParts.findIndex(
          part => part.id === updatedPart.id
        );
        if (index !== -1) {
          set.setParts[index] = updatedPart;
        }
      });

      showToast('Peça atualizada com sucesso.', 'success');
    };

    const openPartModal = (part: Part) => {
      selectedPart.value = part;
      showPartModal.value = true;
    };

    const closePartModal = () => {
      selectedPart.value = null;
      showPartModal.value = false;
    };

    const handlePartNavigation = (part: Part) => {
      selectedPart.value = part;
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
          form.value.type = data.type;
          form.value.customer_id = data.customer_id;
          form.value.delivery_type = data.delivery_type;
          form.value.delivery_value = data.delivery_value;
          form.value.markup = data.markup;
          form.value.delivery_date = data.delivery_date;
          form.value.estimated_delivery_date = data.estimated_delivery_date;
          form.value.payment_obs = data.payment_obs;

          if (data.sets && data.sets.length) {
            sets.value = data.sets.map((s: any) => ({
              ...s,
              fileList: null,
              setParts: [] as Part[],
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
        const payload = {
          type: form.value.type,
          customer_id: form.value.customer_id,
          delivery_type: form.value.delivery_type,
          delivery_value: form.value.delivery_value,
          markup: form.value.markup,
          delivery_date: form.value.delivery_date,
          estimated_delivery_date: form.value.estimated_delivery_date,
          payment_obs: form.value.payment_obs,
        };

        if (isNew.value) {
          const { data } = await axios.post('/api/orders', payload);
          isNew.value = false;
          router.push({ name: 'OrderView', params: { id: data.id } });
        } else {
          await axios.put(`/api/orders/${route.params.id}`, payload);
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
          setParts: [] as Part[],
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

    const handleFileUpload = async (setIndex: number) => {
      uploadingIndex.value = setIndex;

      const currentSet = sets.value[setIndex];
      const files = currentSet.fileList;

      if (files && files.length && currentSet.id) {
        for (const file of files) {
          const formData = new FormData();
          formData.append('file', file);
          formData.append('set_id', currentSet.id.toString());

          try {
            const response = await axios.post(
              '/api/upload-set-part',
              formData,
              {
                headers: { 'Content-Type': 'multipart/form-data' },
              }
            );

            const data = response.data;

            if (Array.isArray(data)) {
              data.forEach(part => currentSet.setParts.push(part));
            } else {
              currentSet.setParts.push(data);
            }
          } catch (error) {
            showToast('Erro ao fazer upload de arquivo: ' + error, 'error');
          }
        }
      }
      currentSet.fileList = null;
      uploadingIndex.value = null;
    };

    watch(
      () => sets.value.map(s => s.fileList),
      newValues => {
        newValues.forEach((files, index) => {
          if (files && files.length) handleFileUpload(index);
        });
      },
      { deep: true }
    );

    const deletePart = (setIndex: number, partIndex: number) => {
      try {
        const part = sets.value[setIndex].setParts[partIndex];

        if (part.id) {
          axios.delete(`/api/set-parts/${part.id}`);

          sets.value[setIndex].setParts.splice(partIndex, 1);

          showToast('Peça excluída com sucesso.', 'success');
        }
      } catch (error) {
        showToast('Erro ao excluir peça: ' + error, 'error');
      }
    };

    const addNewPart = async (setIndex: number) => {
      try {
        const setId = sets.value[setIndex].id;

        if (!setId) {
          showToast('Erro: conjunto não encontrado.', 'error');
          return;
        }

        const newPartData = {
          set_id: setId,
          type: null,
          title: 'Nova peça',
          content: null,
          quantity: 0,
          unit_net_weight: 0,
          unit_gross_weight: 0,
          net_weight: 0,
          gross_weight: 0,
          unit_value: 0,
          final_value: 0,
          width: 0,
          length: 0,
          loss: null,
          markup: null,
          locked_values: [],
        };

        const { data } = await axios.post(
          `/api/sets/${setId}/parts`,
          newPartData
        );

        sets.value[setIndex].setParts.push(data);

        showToast('Nova peça adicionada com sucesso.', 'success');
      } catch (error) {
        showToast('Erro ao adicionar nova peça: ' + error, 'error');
      }
    };

    // Propriedade computada para "achatar" as set_parts incluindo o nome do set para agrupamento
    const allSetParts = computed(() => {
      return sets.value.flatMap(set => {
        return set.setParts.map(part => ({ ...part, setName: set.name }));
      });
    });

    const allPartsInDisplayOrder = computed(() => {
      const parts: Part[] = [];
      sets.value.forEach(set => {
        const reversedParts = set.setParts.slice().reverse();
        parts.push(...reversedParts);
      });
      return parts;
    });

    const currentPartIndex = computed(() => {
      if (!selectedPart.value) return -1;

      return allPartsInDisplayOrder.value.findIndex(
        part => part.id === selectedPart.value?.id
      );
    });

    const groupByValuesTable = [{ key: 'setName', order: 'asc' }];

    function printPart(part: Part) {
      const url = router.resolve({
        name: 'PartPrint',
        params: { id: part.id },
      }).href;

      window.open(url, '_blank');
    }

    function printAllParts() {
      const url = router.resolve({
        path: '/order/parts/print',
        query: { order_id: route.params.id as string },
      }).href;
      window.open(url, '_blank');
    }

    const onMarkupChange = async () => {
      if (!isNew.value && route.params.id && form.value.markup) {
        try {
          await axios.put(`/api/orders/${route.params.id}/on-markup-change`, {
            markup: form.value.markup,
          });

          for (const set of sets.value) {
            if (set.id) {
              const { data } = await axios.get(`/api/sets/${set.id}/parts`);
              set.setParts = data;
            }
          }

          showToast('Valores das peças recalculados com sucesso.', 'success');
        } catch (error) {
          showToast('Erro ao recalcular valores das peças: ' + error, 'error');
        }
      }
    };

    // Documents methods
    const generateDocument = async (documentType: string) => {
      if (!route.params.id || route.params.id === 'new') return;

      loadingDocument.value = true;
      showDocumentsMenu.value = false;

      try {
        let endpoint = '';
        let filename = '';

        switch (documentType) {
          case 'quote':
            endpoint = `/api/orders/${route.params.id}/pdf`;
            filename = `orcamento-${String(route.params.id).padStart(6, '0')}.pdf`;
            break;
          default:
            showToast('Tipo de documento não reconhecido', 'error');
            return;
        }

        const response = await axios.get(endpoint, {
          responseType: 'blob',
        });

        const blob = new Blob([response.data], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        showToast('Documento gerado com sucesso!', 'success');
      } catch (error) {
        console.error('Erro ao gerar documento:', error);
        showToast('Erro ao gerar documento', 'error');
      } finally {
        loadingDocument.value = false;
      }
    };

    return {
      isNew,
      form,
      customers,
      orderTypeOptions,
      deliveryTypeOptions,
      sets,
      uploadingIndex,
      showPartModal,
      selectedPart,
      setParts,
      setPartsHeaders,
      allSetParts,
      groupByValuesTable,
      allPartsInDisplayOrder,
      currentPartIndex,
      saveOrder,
      createSet,
      deleteSet,
      updateSetName,
      deletePart,
      addNewPart,
      updatePartInList,
      getPartImageUrl,
      openPartModal,
      closePartModal,
      printPart,
      printAllParts,
      onMarkupChange,
      handleFileUpload,
      handlePartNavigation,
      // Documents
      showDocumentsMenu,
      loadingDocument,
      availableDocuments,
      generateDocument,
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
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.menu-actions {
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

.add-part-button {
  cursor: pointer;
  border: 2px dashed #ccc !important;
  background-color: #fafafa;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.add-part-button:hover {
  border-color: #1976d2;
  background-color: #f5f5f5;
}

.add-part-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.add-part-text {
  font-size: 12px;
  color: #666;
  text-align: center;
  font-weight: 500;
}
</style>
