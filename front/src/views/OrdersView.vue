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
    </v-container>
  </template>
  
  <script lang="ts">
  import { defineComponent, ref, onMounted } from 'vue';
  import axios from 'axios';
  import { useToast } from '@/composables/useToast';
  import { useRouter } from 'vue-router';
  
  export default defineComponent({
    name: 'OrdersView',
    setup() {
      const router = useRouter();
      const orders = ref<Array<any>>([]);
      const { showToast } = useToast();
  
      const headers = [
        { title: 'Código', value: 'id', sortable: true },
        { title: 'Valor final', value: 'final_value', sortable: true },
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
  
      const editOrder = (order: any) => {
        router.push({ name: 'OrderView', params: { id: order.id } });
      };
  
      const deleteOrder = async (orderId: number) => {
        try {
          await axios.delete(`/api/orders/${orderId}`);
          fetchOrders();
        } catch (error) {
          showToast('Erro ao deletar pedido');
        }
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
      };
    },
  });
  </script>
  
  <style scoped>
  .v-container {
    padding-top: 50px;
  }
  </style>