<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
      <h2>Clientes</h2>
      <v-btn color="primary" @click="openForm">Novo</v-btn>
    </v-row>

    <v-data-table :items="customers" :headers="headers">
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
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import CustomerView from '../components/CustomerView.vue';

export default defineComponent({
  name: 'CustomersView',
  components: { CustomerView },
  setup() {
    const customers = ref<any[]>([]);
    const dialog = ref(false);
    const isEdit = ref(false);
    const formData = ref<any>({});

    const headers = [
      { title: 'Nome', value: 'name' },
      { title: 'Email', value: 'email' },
      { title: 'Telefone', value: 'phone' },
      { title: 'CNPJ', value: 'cnpj' },
      { title: 'CPF', value: 'cpf' },
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
      if (!confirm('Deseja excluir este cliente?')) return;
      await axios.delete(`/api/customers/${id}`);
      fetchCustomers();
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
    };
  },
});
</script>