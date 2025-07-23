<template>
  <v-container style="padding: 50px">
    <v-row
      justify="space-between"
      align="center"
      class="mb-4"
      style="margin: 0"
    >
      <h2>Clientes</h2>
      <v-btn color="primary" @click="openForm">Novo</v-btn>
    </v-row>

    <v-data-table :items="customers" :headers="headers">
      <template #item.state.name="{ item }">
        {{ item.state?.name || '-' }}
      </template>
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editCustomer(item)">
              <v-list-item-title>Editar</v-list-item-title>
            </v-list-item>
            <v-list-item @click="deleteCustomer(item.id)">
              <v-list-item-title>Excluir</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <CustomerView
      :dialog="dialog"
      :isEdit="isEdit"
      :customerData="formData"
      @close="dialog = false"
      @saved="handleSaved"
    />

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
import CustomerView from '../components/CustomerView.vue';
import { useConfirm } from '../composables/useConfirm';
import ConfirmDialog from '../components/ConfirmDialog.vue';

export default defineComponent({
  name: 'CustomersView',
  components: { CustomerView, ConfirmDialog },
  setup() {
    const customers = ref<any[]>([]);
    const dialog = ref(false);
    const isEdit = ref(false);
    const formData = ref<any>({});
    const {
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      openConfirm,
      closeConfirm,
      handleConfirm,
    } = useConfirm();
    const { showToast } = useToast();

    const headers = [
      { title: 'Nome', value: 'name' },
      { title: 'Email', value: 'email' },
      { title: 'Telefone', value: 'phone' },
      { title: 'CNPJ', value: 'cnpj' },
      { title: 'CPF', value: 'cpf' },
      { title: 'Estado', value: 'state.name' },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchCustomers = async () => {
      const { data } = await axios.get('/api/customers');
      customers.value = data;
    };

    const openForm = () => {
      formData.value = {};
      isEdit.value = false;
      dialog.value = true;
    };

    const editCustomer = (customer: any) => {
      formData.value = { ...customer };
      isEdit.value = true;
      dialog.value = true;
    };

    const handleSaved = () => {
      dialog.value = false;
      fetchCustomers();
    };

    const deleteCustomer = async (id: number) => {
      openConfirm(
        'Tem certeza que deseja excluir este cliente?',
        async () => {
          try {
            await axios.delete(`/api/customers/${id}`);
            fetchCustomers();
            showToast('Cliente excluído com sucesso!', 'success');
          } catch (error) {
            showToast('Erro ao excluir cliente.', 'error');
          }
        },
        'Excluir cliente'
      );
    };

    onMounted(() => fetchCustomers());

    return {
      customers,
      headers,
      dialog,
      isEdit,
      formData,
      fetchCustomers,
      openForm,
      editCustomer,
      deleteCustomer,
      handleSaved,
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      closeConfirm,
      handleConfirm,
    };
  },
});
</script>
