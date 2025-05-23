<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
      <h2>Orçamentos</h2>
      <v-btn color="primary" @click="openForm">Novo</v-btn>
    </v-row>
    <v-data-table
      :items="orders"
      :headers="headers"
      class="elevation-1"
    >
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
        </v-menu>        </template>
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
  import { defineComponent, ref, onMounted } from 'vue';
  import axios from 'axios';
  import { useToast } from '../composables/useToast';
  import { useRouter } from 'vue-router';
  import { useMisc } from '../composables/misc';
  import { useConfirm } from '../composables/useConfirm';
  import ConfirmDialog from '../components/ConfirmDialog.vue';
  import type { Order } from '../types/types';

  export default defineComponent({
    name: 'OrdersView',
    components: {
      ConfirmDialog,
    },
    setup() {      const router = useRouter();
      const orders = ref<Array<Order>>([]);
      const { showToast } = useToast();
      const { formatDateBR } = useMisc();
      const { isConfirmDialogOpen, confirmTitle, confirmMessage, openConfirm, closeConfirm, handleConfirm } = useConfirm();
  
      const headers = [
        { title: 'Código', value: 'id', sortable: true },
        { title: 'Cliente', value: 'customer.name', sortable: true },
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
      };
    },
  });
</script>

<style scoped>
.v-container {
  padding-top: 50px;
}
</style>