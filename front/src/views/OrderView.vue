<template>
  <v-container fluid>
    <v-card class="mb-4">
      <v-card-text class="pa-6">
        <v-form ref="formRef">
          <!-- Seção: Informações Gerais -->
          <div class="mb-6">
            <h3 class="text-subtitle-1 mb-3 font-weight-bold">
              <v-icon class="me-2" size="small">mdi-information</v-icon>
              Informações gerais
            </h3>
            <v-row>
              <v-col cols="12" md="4" sm="6">
                <v-select
                  label="Tipo"
                  v-model="form.type"
                  :items="orderTypeOptions"
                  item-value="value"
                  item-title="title"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-format-list-bulleted-type"
                  :rules="[v => !!v || 'Tipo é obrigatório']"
                  :disabled="isTypeDisabled"
                  :hint="isTypeDisabled ? 'Não é possível alterar um pedido para orçamento' : ''"
                  persistent-hint
                  hide-details="auto"
                />
              </v-col>
              <v-col cols="12" md="4" sm="6">
                <v-select
                  label="Cliente"
                  v-model="form.customer_id"
                  :items="customersWithDocument"
                  item-value="id"
                  item-title="displayName"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-account"
                  clearable
                  hide-details="auto"
                  persistent-hint
                />
              </v-col>
            </v-row>
          </div>

          <!-- Seção: Valores e Custos -->
          <div class="mb-6">
            <h3 class="text-subtitle-1 mb-3 font-weight-bold">
              <v-icon class="me-2" size="small">mdi-currency-usd</v-icon>
              Valores e custos
            </h3>
            <v-row>
              <v-col cols="12" md="4" sm="6">
                <v-text-field
                  label="Markup"
                  v-model="form.markup"
                  type="number"
                  step="0.001"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-percent"
                  suffix="%"
                  hide-details="auto"
                  persistent-hint
                  @change="onMarkupChange"
                />
              </v-col>
              <v-col cols="12" md="4" sm="6">
                <v-text-field
                  label="Valor do frete"
                  v-model="form.delivery_value"
                  type="number"
                  step="0.01"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-truck-delivery"
                  prefix="R$"
                  hide-details="auto"
                />
              </v-col>
              <v-col cols="12" md="4" sm="6">
                <v-text-field
                  label="Valor de serviço"
                  v-model="form.service_value"
                  type="number"
                  step="0.01"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-currency-usd"
                  prefix="R$"
                  hide-details="auto"
                />
              </v-col>
              <v-col cols="12" md="4" sm="6">
                <v-text-field
                  label="Desconto"
                  v-model="form.discount"
                  type="number"
                  step="0.01"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-currency-usd"
                  prefix="R$"
                  hide-details="auto"
                />
              </v-col>
            </v-row>
          </div>

          <!-- Seção: Informações adicionais -->
          <div class="mb-6">
            <h3 class="text-subtitle-1 mb-3 font-weight-bold">
              <v-icon class="me-2" size="small">mdi-truck</v-icon>
              Informações adicionais
            </h3>
            <v-row>
              <v-col cols="12" md="4" sm="6">
                <v-select
                  label="Tipo de entrega"
                  v-model="form.delivery_type"
                  :items="deliveryTypeOptions"
                  item-value="value"
                  item-title="title"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-package-variant"
                  clearable
                  hide-details="auto"
                />
              </v-col>
              <v-col cols="12" md="4" sm="6">
                <v-text-field
                  label="Data estimada de entrega"
                  v-model="form.estimated_delivery_date"
                  type="text"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-calendar-clock"
                  hide-details="auto"
                />
              </v-col>
              <v-col cols="12" md="4" sm="6">
                <v-text-field
                  label="Data de entrega"
                  v-model="form.delivery_date"
                  type="text"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-calendar-check"
                  hide-details="auto"
                  placeholder="DD/MM/AAAA"
                  maxlength="10"
                  @input="applyDateMask"
                />
              </v-col>
            </v-row>
            <v-row class="mt-4">
              <v-col cols="12">
                <v-textarea
                  label="Descrição"
                  placeholder="Digite a descrição do pedido..."
                  v-model="form.obs"
                  variant="outlined"
                  density="comfortable"
                  rows="3"
                  auto-grow
                  prepend-inner-icon="mdi-text-box"
                  hide-details="auto"
                />
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="12">
                <v-textarea
                  label="Observação de pagamento"
                  placeholder="Digite informações adicionais sobre o pagamento..."
                  v-model="form.payment_obs"
                  variant="outlined"
                  density="comfortable"
                  rows="3"
                  auto-grow
                  prepend-inner-icon="mdi-text"
                  hide-details="auto"
                />
              </v-col>
            </v-row>
            <!-- Seção de Ordem de serviço -->
            <v-row class="mt-4">
              <v-col cols="12">
                <v-card variant="outlined" class="os-card">
                  <v-card-title class="text-subtitle-2 py-3 d-flex align-center">
                    <v-icon class="me-2" size="small">mdi-file-document</v-icon>
                    Ordem de serviço
                  </v-card-title>
                  <v-divider></v-divider>
                  <v-card-text class="pa-4">
                    <div v-if="!currentOsFile && !osFileInput" class="os-empty-state">
                      <v-row align="center">
                        <v-col cols="12" sm="auto" class="text-center text-sm-left">
                          <div class="d-flex align-center justify-center justify-sm-start">
                            <v-icon size="40" color="grey-lighten-1" class="me-3">mdi-file-upload-outline</v-icon>
                            <p class="text-body-2 text-medium-emphasis mb-0">
                              Nenhuma ordem de serviço anexada
                            </p>
                          </div>
                        </v-col>
                        <v-col cols="12" sm>
                          <v-file-input
                            v-model="osFileInput"
                            variant="outlined"
                            density="comfortable"
                            prepend-icon="mdi-paperclip"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"
                            hint="Formatos aceitos: PDF, DOC, DOCX, JPG, PNG (máx. 10MB)"
                            persistent-hint
                            show-size
                            :disabled="isNew"
                            @update:model-value="handleOsFileChange"
                            hide-details="auto"
                          >
                            <template v-slot:label>
                              <span>{{ isNew ? 'Salve o pedido para anexar a OS' : 'Selecionar arquivo' }}</span>
                            </template>
                          </v-file-input>
                        </v-col>
                      </v-row>
                    </div>

                    <div v-else-if="currentOsFile" class="os-file-attached">
                      <v-row align="center">
                        <v-col cols="12" sm="auto">
                          <v-avatar size="48" color="success" class="os-file-icon">
                            <v-icon size="28">mdi-file-check</v-icon>
                          </v-avatar>
                        </v-col>
                        <v-col cols="12" sm>
                          <div class="os-file-info">
                            <p class="text-body-1 font-weight-medium mb-1">
                              {{ getOsFileName(currentOsFile) }}
                            </p>
                            <p class="text-caption text-medium-emphasis">
                              Arquivo anexado com sucesso
                            </p>
                          </div>
                        </v-col>
                        <v-col cols="12" sm="auto">
                          <div class="d-flex gap-2 flex-wrap">
                            <v-btn
                              variant="tonal"
                              color="primary"
                              prepend-icon="mdi-download"
                              @click="downloadOsFile"
                            >
                              Baixar
                            </v-btn>
                            <v-btn
                              variant="tonal"
                              color="error"
                              prepend-icon="mdi-delete"
                              @click="confirmRemoveOsFile"
                            >
                              Remover
                            </v-btn>
                          </div>
                        </v-col>
                      </v-row>
                      
                      <v-divider class="my-4"></v-divider>
                      <v-file-input
                        v-model="osFileInput"
                        variant="outlined"
                        density="compact"
                        prepend-icon="mdi-refresh"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"
                        hint="Selecione um novo arquivo para substituir o atual"
                        persistent-hint
                        show-size
                        @update:model-value="handleOsFileChange"
                        hide-details="auto"
                      >
                        <template v-slot:label>
                          <span class="text-body-2">Substituir arquivo</span>
                        </template>
                      </v-file-input>
                    </div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </div>
        </v-form>
      </v-card-text>
    </v-card>

    <!-- Seção de Status do Pedido -->
    <OrderStatusFlow
      v-if="!isNew && currentOrder"
      :order-id="currentOrder.id"
      :order-type="currentOrder.type"
      :current-status="currentOrder.status"
      @status-updated="handleStatusUpdate"
    />

    <!-- Seção de Conjuntos -->
    <div>
      <v-row class="align-center mb-2">
        <v-col>
          <h2 class="text-h6">
            <v-icon class="me-2" size="small">mdi-package-variant-closed</v-icon>
            Conjuntos
          </h2>
        </v-col>
        <v-col class="text-right">
          <v-btn 
            color="primary" 
            @click="createSet"
            prepend-icon="mdi-plus"
          >
            Adicionar Conjunto
          </v-btn>
        </v-col>
      </v-row>
    </div>

    <!-- Listagem de conjuntos -->
    <v-card v-for="(setItem, setIndex) in sets" :key="setItem.id" class="mb-4">
      <v-progress-linear
        v-if="uploadingIndex === setIndex"
        indeterminate
        color="primary"
        height="4"
      />
      <v-card-title>
        <v-row class="d-flex flex-row align-center">
          <v-col cols="auto">
            <v-icon size="large">mdi-package-variant</v-icon>
          </v-col>
          <v-col>
            <span class="text-h6">{{ setItem.name }}</span>
            <div v-if="setItem.setParts.length" class="text-caption text-medium-emphasis">
              {{ setItem.setParts.length }} {{ setItem.setParts.length === 1 ? 'peça' : 'peças' }}
            </div>
          </v-col>
          <v-col cols="auto" class="d-flex justify-end align-center">
            <v-menu offset-y>
              <template #activator="{ props }">
                <v-btn
                  variant="text"
                  :ripple="false"
                  v-bind="props"
                  class="menu-actions"
                  size="large"
                  title="Ações do conjunto"
                  icon
                >
                  <v-icon>mdi-dots-vertical</v-icon>
                </v-btn>
              </template>
              <v-list density="compact">
                <v-list-item @click.stop="editSet(setIndex)" prepend-icon="mdi-pencil">
                  <v-list-item-title>Editar</v-list-item-title>
                </v-list-item>
                <v-list-item 
                  @click.stop="confirmDeleteSet(setIndex)" 
                  prepend-icon="mdi-delete"
                  class="text-error"
                >
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
                    class="add-part-icon"
                  >
                    <path
                      d="M12 2V22M2 12H22"
                      stroke="currentColor"
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
                        /* Lines 305-307 omitted */
                      "
                      class="image-error-placeholder"
                    >
                      <v-icon large color="grey lighten-1">mdi-file</v-icon>
                    </div>
                  </template>
                </v-img>
                <div 
                  class="counter"
                  :class="{
                    'counter-in-production': part.status === 'in_production',
                    'counter-finished': part.status === 'finished'
                  }"
                >
                  {{ partIndex + 1 }}
                </div>
                <div class="overlay">
                  <span class="overlay-text">{{ part.title }}</span>
                  <v-menu offset-y>
                    <template #activator="{ props }">
                      <v-btn
                        variant="text"
                        :ripple="false"
                        v-bind="props"
                        class="menu-actions"
                        title="Ações da peça"
                        icon
                        size="small"
                      >
                        <v-icon color="white">mdi-dots-vertical</v-icon>
                      </v-btn>
                    </template>
                    <v-list density="compact">
                      <v-list-item @click.stop="openPartModal(part)" prepend-icon="mdi-eye">
                        <v-list-item-title>Visualizar</v-list-item-title>
                      </v-list-item>
                      <v-list-item @click.stop="printPart(part)" prepend-icon="mdi-printer">
                        <v-list-item-title>Imprimir</v-list-item-title>
                      </v-list-item>
                      <v-divider></v-divider>
                      <v-list-item
                        v-if="part.status === 'in_production'"
                        @click.stop="updatePartStatus(part, 'finished')"
                        prepend-icon="mdi-check-circle"
                      >
                        <v-list-item-title>Marcar como finalizado</v-list-item-title>
                      </v-list-item>
                      <v-list-item
                        v-if="part.status === 'finished'"
                        @click.stop="updatePartStatus(part, 'in_production')"
                        prepend-icon="mdi-refresh"
                      >
                        <v-list-item-title>Voltar para produção</v-list-item-title>
                      </v-list-item>
                      <v-divider></v-divider>
                      <v-list-item
                        @click.stop="confirmDeletePart(setIndex, setItem.setParts.length - 1 - partIndex)"
                        prepend-icon="mdi-delete"
                        class="text-error"
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
      v-if="!isNew && canViewValues"
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

    <SetForm
      :show="showSetModal"
      :setData="selectedSet"
      @close="closeSetModal"
      @saved="updateSetInList"
    />

    <!-- Modal de Confirmação de Exclusão -->
    <v-dialog v-model="showDeleteConfirmDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h5">Confirmar exclusão</v-card-title>
        <v-card-text>
          <span v-if="pendingDeleteSetIndex !== null">
            Tem certeza de que deseja excluir este conjunto? Esta ação não pode ser desfeita.
          </span>
          <span v-else>
            Tem certeza de que deseja excluir esta peça? Esta ação não pode ser desfeita.
          </span>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" variant="flat" @click="showDeleteConfirmDialog = false">
            Cancelar
          </v-btn>
          <v-btn color="error" variant="outlined" @click="confirmDeleteAction">
            Excluir
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Rodapé com ações -->
    <div class="mt-4 d-flex justify-end align-center flex-wrap gap-2">
      <v-menu
        v-if="!isNew && availableDocuments.length > 0"
        v-model="showDocumentsMenu"
        location="bottom"
        transition="scale-transition"
      >
        <template v-slot:activator="{ props }">
          <v-btn
            color="success"
            v-bind="props"
            :disabled="loadingDocument"
            prepend-icon="mdi-file-document"
          >
            Documentos
          </v-btn>
        </template>
        <v-card min-width="250">
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
        @click="printAllParts"
        prepend-icon="mdi-printer"
      >
        Imprimir peças
      </v-btn>
      <v-btn 
        color="primary" 
        @click="saveOrder"
        prepend-icon="mdi-content-save"
      >
        Salvar
      </v-btn>
    </div>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, watch, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import { useAuth } from '../composables/useAuth';
import axios from 'axios';
import PartForm from '../components/PartForm.vue';
import SetForm from '../components/SetForm.vue';
import OrderValuesTable from '../components/OrderValuesTable.vue';
import OrderStatusFlow from '../components/OrderStatusFlow.vue';
import type { OrderForm, OrderSet, Part, OrderType, Set, Customer, Order } from '../types/types';

export default defineComponent({
  name: 'Orders',
  components: {
    PartForm,
    SetForm,
    OrderValuesTable,
    OrderStatusFlow,
  },
  setup() {
    const { showToast } = useToast();
    const { getPartImageUrl, formatDateToInput, formatDateFromInput } = useMisc();
    const { canViewMonetaryValues, canGeneratePartsPdf, canGenerateSetsPdf } = useAuth();

    const canViewValues = computed(() => canViewMonetaryValues());

    const route = useRoute();
    const router = useRouter();
    const isNew = ref(route.params.id === 'new');
    const customers = ref<Customer[]>([]);
    const currentOrder = ref<Order | null>(null);

    const form = ref<OrderForm>({
      type: 'pre_order',
      customer_id: '',
      delivery_type: '',
      delivery_value: '',
      service_value: '',
      discount: '',
      markup: '',
      delivery_date: '',
      estimated_delivery_date: '',
      payment_obs: '',
      obs: 'Prezados Senhores, atendendo sua solicitação, apresentamos proposta para fornecimento de material do equipamento: ',
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
    const showSetModal = ref(false);
    const selectedSet = ref<Set | null>(null);
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
    const availableDocuments = computed(() => {
      const docs = [];
      
      if (canGeneratePartsPdf()) {
        docs.push({
          title: 'Orçamento por peça',
          value: 'quote-set-parts',
          icon: 'mdi-file-document-multiple-outline'
        });
      }
      
      if (canGenerateSetsPdf()) {
        docs.push({
          title: 'Orçamento por conjunto',
          value: 'quote-sets',
          icon: 'mdi-file-document-outline',
        });
      }
      
      docs.push({
        title: 'Impressão do pedido',
        value: 'print-order',
        icon: 'mdi-printer-outline'
      });
      
      return docs;
    });

    // Delete confirmation
    const showDeleteConfirmDialog = ref(false);
    const pendingDeleteSetIndex = ref<number | null>(null);
    const pendingDeletePartIndex = ref<number | null>(null);

    // OS File handling
    const osFileInput = ref<File | null>(null);
    const currentOsFile = ref<string | null>(null);

    const customersWithDocument = computed(() => {
      return customers.value.map(customer => ({
        ...customer,
        displayName: customer.cnpj 
          ? `${customer.name} - CNPJ: ${customer.cnpj}`
          : customer.cpf 
            ? `${customer.name} - CPF: ${customer.cpf}`
            : customer.name
      }));
    });

    const isTypeDisabled = computed(() => {
      return !isNew.value && currentOrder.value?.type === 'order';
    });

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

    const editSet = (setIndex: number) => {
      const setItem = sets.value[setIndex];
      // @ts-ignore
      selectedSet.value = setItem as Set;
      showSetModal.value = true;
    };

    const closeSetModal = () => {
      selectedSet.value = null;
      showSetModal.value = false;
    };

    const updateSetInList = (updatedSet: Set) => {
      const setIndex = sets.value.findIndex(s => s.id === updatedSet.id);
      if (setIndex !== -1) {
        sets.value[setIndex] = { ...sets.value[setIndex], ...updatedSet };
      }
      showToast('Conjunto atualizado com sucesso!', 'success');
    };

    const handlePartNavigation = (part: Part) => {
      selectedPart.value = part;
    };

    onMounted(async () => {
      try {
        const { data } = await axios.get('/api/customers');
        customers.value = data;
      } catch (error: any) {
        const message = error.response?.data?.message || error.message || 'Erro ao carregar clientes';
        showToast(message, 'error');
      }

      if (!isNew.value && route.params.id) {
        try {
          const { data } = await axios.get(`/api/orders/${route.params.id}`);

          currentOrder.value = data;

          form.value.type = data.type;
          form.value.customer_id = data.customer_id;
          form.value.delivery_type = data.delivery_type;
          form.value.delivery_value = data.delivery_value;
          form.value.service_value = data.service_value;
          form.value.discount = data.discount;
          form.value.markup = data.markup;
          form.value.delivery_date = formatDateToInput(data.delivery_date);
          form.value.estimated_delivery_date = data.estimated_delivery_date;
          form.value.payment_obs = data.payment_obs;
          form.value.obs = data.obs || 'Prezados Senhores, atendendo sua solicitação, apresentamos proposta para fornecimento de material do equipamento: ';
          currentOsFile.value = data.os_file || null;

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
        } catch (error: any) {
          const message = error.response?.data?.message || error.message || 'Erro ao carregar pedido';
          showToast(message, 'error');
        }
      }
    });

    const saveOrder = async () => {
      try {
        const formData = new FormData();
        formData.append('type', form.value.type);
        if (form.value.customer_id) formData.append('customer_id', form.value.customer_id.toString());
        if (form.value.delivery_type) formData.append('delivery_type', form.value.delivery_type);
        if (form.value.delivery_value) formData.append('delivery_value', form.value.delivery_value);
        if (form.value.service_value) formData.append('service_value', form.value.service_value);
        if (form.value.discount) formData.append('discount', form.value.discount);
        if (form.value.markup) formData.append('markup', form.value.markup);
        
        // Convert delivery_date from dd/mm/yyyy to yyyy-mm-dd before sending
        const deliveryDateFormatted = form.value.delivery_date 
          ? formatDateFromInput(form.value.delivery_date)
          : '';
        formData.append('delivery_date', deliveryDateFormatted);
        formData.append('estimated_delivery_date', form.value.estimated_delivery_date || '');
        formData.append('payment_obs', form.value.payment_obs || '');
        formData.append('obs', form.value.obs || '');
        
        // Add OS file if selected
        if (osFileInput.value) {
          formData.append('os_file', osFileInput.value);
        }

        if (isNew.value) {
          const { data } = await axios.post('/api/orders', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          });
          isNew.value = false;
          currentOrder.value = data;

          router.push({ name: 'OrderView', params: { id: data.id } });
        } else {
          const { data } = await axios.post(`/api/orders/${route.params.id}`, formData, {
            headers: { 
              'Content-Type': 'multipart/form-data',
              'X-HTTP-Method-Override': 'PUT'
            }
          });

          currentOrder.value = data;
        }
        showToast('Pedido salvo com sucesso.', 'success');
      } catch (error: any) {
        console.error('Erro ao salvar pedido:', error);
        const message = error.response?.data?.message || error.message || 'Erro ao salvar pedido';
        showToast(message, 'error');
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
          status: data.status || 'in_production',
          fileList: null,
          setParts: [] as Part[],
        });
      } catch (error: any) {
        const message = error.response?.data?.message || error.message || 'Erro ao criar conjunto';
        showToast(message, 'error');
      }
    };

    const confirmDeleteSet = (setIndex: number) => {
      pendingDeleteSetIndex.value = setIndex;
      pendingDeletePartIndex.value = null;
      showDeleteConfirmDialog.value = true;
    };

    const deleteSet = async (setIndex: number) => {
      try {
        const set = sets.value[setIndex];
        if (set.id) {
          await axios.delete(`/api/sets/${set.id}`);
          sets.value.splice(setIndex, 1);
        }
        showToast('Conjunto excluído com sucesso.', 'success');
      } catch (error: any) {
        const message = error.response?.data?.message || error.message || 'Erro ao excluir conjunto';
        showToast(message, 'error');
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
          } catch (error: any) {
            const message = error.response?.data?.message || error.message || 'Erro ao fazer upload de arquivo';
            showToast(message, 'error');
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

    const deletePart = async (setIndex: number, partIndex: number) => {
      try {
        const part = sets.value[setIndex].setParts[partIndex];

        if (part.id) {
          await axios.delete(`/api/set-parts/${part.id}`);

          sets.value[setIndex].setParts.splice(partIndex, 1);

          showToast('Peça excluída com sucesso.', 'success');
        }
      } catch (error: any) {
        const message = error.response?.data?.message || error.message || 'Erro ao excluir peça';
        showToast(message, 'error');
      }
    };

    const confirmDeletePart = (setIndex: number, partIndex: number) => {
      pendingDeleteSetIndex.value = setIndex;
      pendingDeletePartIndex.value = partIndex;
      showDeleteConfirmDialog.value = true;
    };

    const confirmDeleteAction = async () => {
      if (pendingDeleteSetIndex.value !== null && pendingDeletePartIndex.value === null) {
        await deleteSet(pendingDeleteSetIndex.value);
      } else if (pendingDeleteSetIndex.value !== null && pendingDeletePartIndex.value !== null) {
        await deletePart(pendingDeleteSetIndex.value, pendingDeletePartIndex.value);
      }
      showDeleteConfirmDialog.value = false;
      pendingDeleteSetIndex.value = null;
      pendingDeletePartIndex.value = null;
    };

    const updatePartStatus = async (part: Part, newStatus: 'in_production' | 'finished') => {
      try {
        const { data } = await axios.put(`/api/set-parts/${part.id}/status`, {
          status: newStatus,
        });
        // Atualiza o status na lista local
        sets.value.forEach(set => {
          const index = set.setParts.findIndex(p => p.id === part.id);
          if (index !== -1) {
            set.setParts[index].status = data.status;
          }
        });
        const statusText = newStatus === 'finished' ? 'finalizado' : 'em produção';
        showToast(`Peça marcada como ${statusText}.`, 'success');
      } catch (error: any) {
        const message = error.response?.data?.message || error.message || 'Erro ao atualizar status da peça';
        showToast(message, 'error');
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
      } catch (error: any) {
        const message = error.response?.data?.message || error.message || 'Erro ao adicionar nova peça';
        showToast(message, 'error');
      }
    };

    // Propriedade computada para "achatar" as set_parts incluindo o nome do set para agrupamento
    const allSetParts = computed(() => {
      return sets.value.flatMap(set => {
        return set.setParts.map(part => ({ ...part, setName: set.name, setQuantity: set.quantity || 1 }));
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
        } catch (error: any) {
          const message = error.response?.data?.message || error.message || 'Erro ao recalcular valores das peças';
          showToast(message, 'error');
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
          case 'quote-set-parts':
            endpoint = `/api/orders/${route.params.id}/pdf`;
            filename = `orcamento-${String(route.params.id).padStart(6, '0')}.pdf`;
            break;
          case 'quote-sets':
            endpoint = `/api/orders/${route.params.id}/pdf-sets`;
            filename = `orcamento-conjuntos-${String(route.params.id).padStart(6, '0')}.pdf`;
            break;
          case 'print-order':
            endpoint = `/api/orders/${route.params.id}/pdf-print`;
            filename = `impressao-pedido-${String(route.params.id).padStart(6, '0')}.pdf`;
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
      } catch (error: any) {
        console.error('Erro ao gerar documento:', error);
        const message = error.response?.data?.message || error.message || 'Erro ao gerar documento';
        showToast(message, 'error');
      } finally {
        loadingDocument.value = false;
      }
    };

    const applyDateMask = (event: Event) => {
      const input = event.target as HTMLInputElement;
      let value = input.value.replace(/\D/g, '');
      
      // Limita a 8 dígitos (ddmmyyyy)
      if (value.length > 8) {
        value = value.substring(0, 8);
      }
      
      let maskedValue = '';
      
      if (value.length >= 1) {
        maskedValue = value.substring(0, 2);
      }
      if (value.length >= 3) {
        maskedValue += '/' + value.substring(2, 4);
      }
      if (value.length >= 5) {
        maskedValue += '/' + value.substring(4, 8);
      }
      
      form.value.delivery_date = maskedValue;
    };

    // OS File methods
    const handleOsFileChange = async () => {
      if (!osFileInput.value) return;
      
      // Auto-save OS file if order already exists
      if (!isNew.value && route.params.id) {
        try {
          const formData = new FormData();
          formData.append('os_file', osFileInput.value);
          
          await axios.post(`/api/orders/${route.params.id}`, formData, {
            headers: { 
              'Content-Type': 'multipart/form-data',
              'X-HTTP-Method-Override': 'PUT'
            }
          });
          
          // Update current file reference
          const { data } = await axios.get(`/api/orders/${route.params.id}`);
          currentOsFile.value = data.os_file || null;
          
          // Clear the file input after successful upload
          osFileInput.value = null;
          
          showToast('Arquivo da OS salvo com sucesso!', 'success');
        } catch (error: any) {
          console.error('Erro ao salvar arquivo da OS:', error);
          const message = error.response?.data?.message || error.message || 'Erro ao salvar arquivo da OS';
          showToast(message, 'error');
          // Clear file input on error as well
          osFileInput.value = null;
        }
      } else {
        showToast('Salve o pedido antes de fazer upload da OS', 'warning');
        osFileInput.value = null;
      }
    };

    const confirmRemoveOsFile = () => {
      if (confirm('Tem certeza que deseja remover o arquivo da OS?')) {
        removeOsFile();
      }
    };

    const removeOsFile = async () => {
      if (!isNew.value && route.params.id && currentOsFile.value) {
        try {
          await axios.delete(`/api/orders/${route.params.id}/remove-os`);
          
          currentOsFile.value = null;
          osFileInput.value = null;

          showToast('Arquivo da OS removido com sucesso!', 'success');
        } catch (error: any) {
          console.error('Erro ao remover arquivo da OS:', error);
          const message = error.response?.data?.message || error.message || 'Erro ao remover arquivo da OS';
          showToast(message, 'error');
        }
      }
    };

    const getOsFileName = (filePath: string) => {
      if (!filePath) return '';
      const parts = filePath.split('/');
      return parts[parts.length - 1].replace(/^\d+_/, '');
    };

    const downloadOsFile = async () => {
      if (!route.params.id || route.params.id === 'new') return;
      
      try {
        const response = await axios.get(`/api/orders/${route.params.id}/download-os`, {
          responseType: 'blob',
        });

        const blob = new Blob([response.data]);
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = currentOsFile.value ? getOsFileName(currentOsFile.value) : 'ordem_servico';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        showToast('Arquivo baixado com sucesso!', 'success');
      } catch (error: any) {
        console.error('Erro ao baixar arquivo:', error);
        const message = error.response?.data?.message || error.message || 'Erro ao baixar arquivo da OS';
        showToast(message, 'error');
      }
    };

    const handleStatusUpdate = (updatedData: { status: string; type: OrderType }) => {
      if (currentOrder.value) {
        currentOrder.value.status = updatedData.status as any;
        currentOrder.value.type = updatedData.type;
      }
      form.value.type = updatedData.type;
      
      showToast('Status atualizado com sucesso!', 'success');
    };

    return {
      isNew,
      form,
      customers,
      customersWithDocument,
      isTypeDisabled,
      currentOrder,
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
      canViewValues,
      saveOrder,
      createSet,
      deleteSet,
      confirmDeleteSet,
      deletePart,
      addNewPart,
      confirmDeletePart,
      confirmDeleteAction,
      updatePartStatus,
      updatePartInList,
      getPartImageUrl,
      openPartModal,
      closePartModal,
      editSet,
      closeSetModal,
      updateSetInList,
      showSetModal,
      selectedSet,
      printPart,
      printAllParts,
      onMarkupChange,
      handleFileUpload,
      handlePartNavigation,
      handleStatusUpdate,
      // Delete confirmation
      showDeleteConfirmDialog,
      pendingDeleteSetIndex,
      pendingDeletePartIndex,
      // Documents
      showDocumentsMenu,
      loadingDocument,
      availableDocuments,
      generateDocument,
      // OS File
      osFileInput,
      currentOsFile,
      handleOsFileChange,
      removeOsFile,
      confirmRemoveOsFile,
      getOsFileName,
      downloadOsFile,
      applyDateMask,
    };
  },
});
</script>

<style scoped>
/* Cards e layout */
.v-card {
  transition: all 0.3s ease;
}

/* Preview de imagens das peças */
.image-preview {
  position: relative;
  width: 150px;
  height: 150px;
  border: 2px solid rgb(var(--v-border-color));
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
  background-color: rgb(var(--v-theme-surface));
}

.image-preview:hover {
  transform: translateY(-2px);
  border-color: rgb(var(--v-theme-primary));
}

/* Contador de peças */
.counter {
  position: absolute;
  top: 8px;
  right: 8px;
  background: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
  padding: 4px 8px;
  border-radius: 16px;
  font-size: 12px;
  font-weight: 600;
  z-index: 2;
  box-shadow: 0 2px 4px rgba(var(--v-theme-on-surface), 0.2);
}

.counter-in-production {
  background: #FFC107 !important;
  color: #000000 !important;
}
.counter-finished {
  background: #4CAF50 !important;
  color: #FFFFFF !important;
}


/* Overlay das peças */
.overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 100%);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px;
  min-height: 44px;
  transition: all 0.3s ease;
}

.image-preview:hover .overlay {
  background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0.5) 100%);
}

.overlay-text {
  font-size: 13px;
  color: white;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 500;
  flex: 1;
  margin-right: 8px;
}

/* Menu de ações */
.menu-actions {
  cursor: pointer;
}

/* Container de peças */
.setParts-container {
  max-height: calc(150px * 3 + 32px);
  overflow-y: auto;
  padding: 12px;
  border-radius: 8px;
}

.setParts-container::-webkit-scrollbar {
  width: 10px;
}

.setParts-container::-webkit-scrollbar-track {
  border-radius: 10px;
}

.setParts-container::-webkit-scrollbar-thumb {
  background: rgb(var(--v-theme-primary));
  border-radius: 10px;
}

.setParts-container::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--v-theme-primary), 0.8);
}

/* Botão de adicionar peça */
.add-part-button {
  cursor: pointer;
  border: 2px dashed rgb(var(--v-border-color)) !important;
  background: rgb(var(--v-theme-surface));
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.add-part-button:hover {
  border-color: rgb(var(--v-theme-primary)) !important;
  background: rgba(var(--v-theme-primary), 0.08);
  transform: translateY(-2px);
}

.add-part-icon {
  color: rgb(var(--v-theme-secondary));
  transition: color 0.3s ease;
}

.add-part-button:hover .add-part-icon {
  color: rgb(var(--v-theme-primary));
}

.add-part-button:hover .add-part-text {
  color: rgb(var(--v-theme-primary));
}

.add-part-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 16px;
}

.add-part-text {
  font-size: 13px;
  color: rgb(var(--v-theme-secondary));
  text-align: center;
  font-weight: 600;
  transition: color 0.3s ease;
}

/* Placeholder de erro de imagem */
.image-error-placeholder {
  align-items: center;
  justify-content: center;
  background-color: rgb(var(--v-theme-surface));
}

/* Seções do formulário */
h3.text-subtitle-1 {
  border-left: 4px solid;
  padding-left: 12px;
}

/* Melhorias de responsividade */
@media (max-width: 960px) {
  .setParts-container {
    max-height: calc(150px * 2 + 32px);
  }
}

@media (max-width: 600px) {
  .image-preview {
    width: 120px;
    height: 120px;
  }
  
  .setParts-container {
    max-height: calc(120px * 2 + 32px);
  }
}

/* Espaçamento entre gaps */
.gap-2 {
  gap: 8px;
}

/* Estilos para a seção de OS */
.os-card {
  border: 2px solid rgb(var(--v-border-color));
  transition: border-color 0.3s ease;
}

.os-empty-state {
  padding: 8px 0;
}

.os-file-attached {
  padding: 8px 0;
}

.os-file-icon {
  flex-shrink: 0;
}

.os-file-info {
  min-width: 0;
}

.os-file-info p {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Responsividade dos botões da OS */
@media (max-width: 600px) {
  .os-file-attached .d-flex.gap-2 {
    width: 100%;
  }
  
  .os-file-attached .d-flex.gap-2 .v-btn {
    flex: 1;
  }
}

</style>
