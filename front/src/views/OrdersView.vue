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
      <v-col cols="12" md="3">
        <v-text-field
          v-model="filters.search"
          label="Pesquisar por código ou cliente"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          clearable
          @input="applyFilters"
          density="compact"
        />
      </v-col>
      <v-col cols="12" md="3">
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
      <v-col cols="12" md="3">
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
      <v-col cols="12" md="3">
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
            <v-list-item @click="deleteOrder(item.id)">
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
  </v-container>
</template>
<script lang="ts">
import { defineComponent, ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useRouter } from 'vue-router';
import { useMisc } from '../composables/misc';
import { useConfirm } from '../composables/useConfirm';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import type { Order, OrderFilters } from '../types/types';

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
      dateFrom: '',
      dateTo: '',
    });

    const orderTypes = ref([
      { title: 'Orçamento', value: 'pre_order' },
      { title: 'Pedido', value: 'order' },
    ]);

    const filteredOrders = computed(() => {
      let filtered = orders.value;

      if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase();
        filtered = filtered.filter(
          order =>
            order.id.toString().includes(searchTerm) ||
            (order.customer?.name &&
              order.customer.name.toLowerCase().includes(searchTerm))
        );
      }

      if (filters.value.type) {
        filtered = filtered.filter(order => order.type === filters.value.type);
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
      { title: 'Valor do frete', value: 'delivery_value', sortable: true },
      { title: 'Data de entrega', value: 'delivery_date', sortable: true },
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

    const clearFilters = () => {
      filters.value = {
        search: '',
        type: null,
        dateFrom: '',
        dateTo: '',
      };
    };

    onMounted(() => {
      fetchOrders();
    });

    return {
      orders,
      headers,
      fetchOrders,
      openForm,
      editOrder,
      deleteOrder,
      formatDateBR,
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      closeConfirm,
      handleConfirm,
      filters,
      filteredOrders,
      orderTypes,
      applyFilters,
      clearFilters,
    };
  },
});
</script>

<style scoped>
.v-container {
  padding-top: 50px;
}
</style>
