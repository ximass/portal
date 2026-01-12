<template>
  <v-container style="padding: 50px">
    <v-row justify="space-between" align="center" style="margin: 0">
      <h2>Orçamentos</h2>
      <div>
        <v-btn color="secondary" @click="clearFilters" class="mr-2">
          Limpar filtros
        </v-btn>
        <v-btn color="primary" @click="openForm">Novo</v-btn>
      </div>
    </v-row>

    <!-- Filtros -->
    <v-row>
      <v-col cols="12" md="4">
        <v-text-field
          v-model="filters.search"
          label="Pesquisar por código, cliente, número do pedido ou NF"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          clearable
          @input="applyFilters"
          density="compact"
        />
      </v-col>
      <v-col cols="12" md="2">
        <v-select
          v-model="filters.type"
          :items="orderTypes"
          label="Tipo"
          variant="outlined"
          clearable
          density="compact"
          @update:model-value="applyFilters"
        />
      </v-col>
      <v-col cols="12" md="2">
        <v-select
          v-model="filters.status"
          :items="allStatuses"
          label="Status"
          variant="outlined"
          clearable
          density="compact"
          @update:model-value="applyFilters"
        />
      </v-col>
      <v-col cols="12" md="2">
        <v-text-field
          v-model="filters.dateFrom"
          label="Data de entrega (de)"
          type="datetime-local"
          variant="outlined"
          clearable
          density="compact"
          @input="applyFilters"
        />
      </v-col>
      <v-col cols="12" md="2">
        <v-text-field
          v-model="filters.dateTo"
          label="Data de entrega (até)"
          type="datetime-local"
          variant="outlined"
          clearable
          density="compact"
          @input="applyFilters"
        />
      </v-col>
    </v-row>

    <v-data-table
      :items="filteredOrders"
      :headers="headers"
      class="elevation-1"
    >
      <template #item.status="{ item }">
        <v-chip
          :color="getStatusColor(item.status)"
          size="small"
          variant="flat"
        >
          {{ getStatusLabel(item.status) }}
        </v-chip>
      </template>
      <template #item.customer_document="{ item }">
        {{ formatCustomerDocument(item.customer) }}
      </template>
      <template #item.customer_state="{ item }">
        {{ formatCustomerState(item.customer) }}
      </template>
      <template #item.customer.address="{ item }">
        {{ formatAddress(item.customer?.address) }}
      </template>
      <template #item.delivery_value="{ item }">
        {{
          item.delivery_value
            ? `R$ ${item.delivery_value.toFixed(2).replace('.', ',')}`
            : '-'
        }}
      </template>
      <template #item.delivery_date="{ item }">
        {{ formatDateBR(item.delivery_date ?? '') }}
      </template>
      <template #item.estimated_delivery_date="{ item }">
        <div class="d-flex align-center gap-2">
          <span>{{item.estimated_delivery_date }}</span>
        </div>
      </template>
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editOrder(item)">
              <v-list-item-title>
                <v-icon>mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item @click="duplicateOrder(item)">
              <template #title>
                <v-icon class="mr-2">mdi-content-copy</v-icon>
                Duplicar
              </template>
            </v-list-item>
            <v-list-item 
              v-if="canDeleteOrder()"
              @click="deleteOrder(item.id)"
            >
              <v-list-item-title>
                <v-icon>mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <ConfirmDialog
      :show="isConfirmDialogOpen"
      :title="confirmTitle"
      :message="confirmMessage"
      @confirm="handleConfirm"
      @cancel="closeConfirm"
    />

    <!-- Duplicate Dialog -->
    <v-dialog v-model="showDuplicateDialog" max-width="500">
      <v-card>
        <v-card-title>Duplicar {{ orderToDuplicate?.type === 'order' ? 'pedido' : 'orçamento' }}</v-card-title>
        <v-card-text>
          <p class="mb-4">Tem certeza que deseja duplicar este {{ orderToDuplicate?.type === 'order' ? 'pedido' : 'orçamento' }}?</p>
          
          <v-checkbox
            v-if="orderToDuplicate?.type === 'order'"
            v-model="convertToPreOrder"
            label="Converter para orçamento"
            color="primary"
            hide-details
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn color="grey" variant="text" @click="closeDuplicateDialog">
            Cancelar
          </v-btn>
          <v-btn color="primary" variant="flat" @click="confirmDuplicate">
            Duplicar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
<script lang="ts">
import { defineComponent, ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useRouter } from 'vue-router';
import { useMisc } from '../composables/misc';
import { useConfirm } from '../composables/useConfirm';
import { useAuth } from '../composables/useAuth';
import { useOrderStatus } from '../composables/useOrderStatus';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import type { Order, OrderFilters, Customer } from '../types/types';

export default defineComponent({
  name: 'OrdersView',
  components: {
    ConfirmDialog,
  },
  setup() {
    const router = useRouter();
    const orders = ref<Array<Order>>([]);
    const { showToast } = useToast();
    const { formatDateBR } = useMisc();
    const { canDeleteOrder, loadCurrentUser } = useAuth();
    const { getStatusLabel, getStatusColor, getAllStatuses } = useOrderStatus();
    const {
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      openConfirm,
      closeConfirm,
      handleConfirm,
    } = useConfirm();

    const filters = ref<OrderFilters>({
      search: '',
      type: null,
      status: null,
      dateFrom: '',
      dateTo: '',
    });

    const orderTypes = ref([
      { title: 'Orçamento', value: 'pre_order' },
      { title: 'Pedido', value: 'order' },
    ]);

    const allStatuses = getAllStatuses();

    const showDuplicateDialog = ref(false);
    const orderToDuplicate = ref<Order | null>(null);
    const convertToPreOrder = ref(false);

    const filteredOrders = computed(() => {
      let filtered = orders.value;

      if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        filtered = filtered.filter(
          order =>
            order.id.toString().includes(searchTerm) ||
            (order.customer?.name &&
              order.customer.name.toLowerCase().includes(searchTerm)) ||
            (order.order_number &&
              order.order_number.toLowerCase().includes(searchTerm)) ||
            (order.nf_number &&
              order.nf_number.toLowerCase().includes(searchTerm))
        );
      }

      if (filters.value.type) {
        filtered = filtered.filter(order => order.type === filters.value.type);
      }

      if (filters.value.status) {
        filtered = filtered.filter(order => order.status === filters.value.status);
      }

      if (filters.value.dateFrom) {
        filtered = filtered.filter(
          order =>
            order.delivery_date && order.delivery_date >= filters.value.dateFrom
        );
      }

      if (filters.value.dateTo) {
        filtered = filtered.filter(
          order =>
            order.delivery_date && order.delivery_date <= filters.value.dateTo
        );
      }

      return filtered;
    });

    const headers = [
      { title: 'Código', value: 'id', sortable: true },
      { title: 'Cliente', value: 'customer.name', sortable: true },
      { title: 'CNPJ/CPF', value: 'customer_document', sortable: true },
      { title: 'Estado', value: 'customer_state', sortable: true },
      { title: 'Endereço', value: 'customer.address', sortable: true },
      { title: 'Status', value: 'status', sortable: true },
      { title: 'Valor do frete', value: 'delivery_value', sortable: true },
      { title: 'Data de entrega', value: 'delivery_date', sortable: true },
      { title: 'Data estimada', value: 'estimated_delivery_date', sortable: true },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchOrders = async () => {
      try {
        const response = await axios.get('/api/orders');
        orders.value = response.data;
      } catch (error) {
        showToast('Erro ao buscar pedidos');
      }
    };

    const openForm = () => {
      router.push({ name: 'OrderView', params: { id: 'new' } });
    };

    const editOrder = (order: Order) => {
      router.push({ name: 'OrderView', params: { id: order.id } });
    };

    const duplicateOrder = (order: Order) => {
      orderToDuplicate.value = order;
      convertToPreOrder.value = false;
      showDuplicateDialog.value = true;
    };

    const closeDuplicateDialog = () => {
      showDuplicateDialog.value = false;
      orderToDuplicate.value = null;
      convertToPreOrder.value = false;
    };

    const confirmDuplicate = async () => {
      if (!orderToDuplicate.value) return;

      try {
        const payload: any = {};

        if (convertToPreOrder.value) {
          payload.convert_to_pre_order = true;
        }

        const wasConvertedToPreOrder = convertToPreOrder.value;
        const originalType = orderToDuplicate.value.type;

        const response = await axios.post(
          `/api/orders/${orderToDuplicate.value.id}/duplicate`,
          payload
        );
        
        fetchOrders();
        closeDuplicateDialog();
        
        const itemType = wasConvertedToPreOrder ? 'orçamento' : 
          (originalType === 'order' ? 'pedido' : 'orçamento');

        showToast(`${itemType.charAt(0).toUpperCase() + itemType.slice(1)} duplicado com sucesso!`, 'success');
        
        router.push({ name: 'OrderView', params: { id: response.data.id } });
      } catch (error) {
        showToast('Erro ao duplicar');
      }
    };

    const deleteOrder = async (orderId: number) => {
      openConfirm(
        'Tem certeza que deseja excluir este orçamento?',
        async () => {
          try {
            await axios.delete(`/api/orders/${orderId}`);
            fetchOrders();
            showToast('Orçamento excluído com sucesso!', 'success');
          } catch (error) {
            showToast('Erro ao deletar pedido');
          }
        },
        'Excluir orçamento'
      );
    };

    const applyFilters = () => {};

    const formatCustomerDocument = (customer?: Customer) => {
      if (!customer) return '-';
      if (customer.cnpj) return customer.cnpj;
      if (customer.cpf) return customer.cpf;
      return '-';
    };

    const formatAddress = (address?: string | null) => {
      if (!address) return '-';
      return address.length > 75 ? address.substring(0, 75) + '...' : address;
    };

    const formatCustomerState = (customer?: Customer) => {
      if (!customer || !customer.state) return '-';
      return customer.state.abbreviation || customer.state.name || '-';
    };

    const formatEstimatedDate = (dateStr: string | null): string => {
      if (!dateStr) return '-';
      
      const datePart = dateStr.split(' ')[0];
      const [day, month, year] = datePart.split('/');
      
      if (!year || !month || !day) return '-';
      
      return `${day}/${month}/${year}`;
    };

    const clearFilters = () => {
      filters.value = {
        search: '',
        type: null,
        status: null,
        dateFrom: '',
        dateTo: '',
      };
    };

    onMounted(() => {
      fetchOrders();
      loadCurrentUser();
    });

    return {
      orders,
      headers,
      fetchOrders,
      openForm,
      editOrder,
      duplicateOrder,
      deleteOrder,
      formatDateBR,
      formatCustomerDocument,
      formatAddress,
      formatCustomerState,
      formatEstimatedDate,
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      closeConfirm,
      handleConfirm,
      filters,
      filteredOrders,
      orderTypes,
      allStatuses,
      applyFilters,
      clearFilters,
      canDeleteOrder,
      getStatusLabel,
      getStatusColor,
      showDuplicateDialog,
      orderToDuplicate,
      convertToPreOrder,
      closeDuplicateDialog,
      confirmDuplicate,
    };
  },
});
</script>

<style scoped>
.v-container {
  padding-top: 50px;
}
</style>
