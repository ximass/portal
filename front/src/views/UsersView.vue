<template>
  <v-container style="padding: 50px">
    <v-row
      justify="space-between"
      align="center"
      class="mb-4"
      style="margin: 0"
    >
      <h2>Usuários</h2>
    </v-row>
    <v-data-table :items="users" :headers="headers" class="elevation-1">
      <template #item.admin="{ item }">
        {{ item.admin ? 'Sim' : 'Não' }}
      </template>
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editUser(item)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item @click="item.id !== null && deleteUser(item.id)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <UserForm
      :dialog="isFormOpen"
      :userData="selectedUser"
      @close="isFormOpen = false"
      @saved="handleUserSaved"
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
import UserForm from '../components/UserForm.vue';
import type { User } from '../types/types';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

export default defineComponent({
  name: 'UsersView',
  components: { UserForm, ConfirmDialog },
  setup() {
    const isFormOpen = ref(false);
    const { showToast } = useToast();
    const {
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      openConfirm,
      closeConfirm,
      handleConfirm,
    } = useConfirm();

    const users = ref<User[]>([]);
    const selectedUser = ref<User>({
      id: null,
      name: '',
      email: '',
      admin: false,
    });

    const headers = [
      { title: 'Nome', value: 'name', sortable: true },
      { title: 'Email', value: 'email', sortable: true },
      { title: 'Administrador', value: 'admin', sortable: true },
      { title: 'Ações', value: 'actions', sortable: false },
    ];

    const fetchUsers = async () => {
      try {
        const response = await axios.get('/api/users');
        users.value = response.data;
      } catch (error) {
        showToast('Erro ao buscar usuários');
      }
    };

    const editUser = (user: User) => {
      selectedUser.value = { ...user };
      isFormOpen.value = true;
    };

    const handleUserSaved = () => {
      isFormOpen.value = false;
      fetchUsers();
    };

    const deleteUser = async (id: number) => {
      openConfirm(
        'Tem certeza que deseja excluir este usuário?',
        async () => {
          try {
            await axios.delete(`/api/users/${id}`);
            fetchUsers();
            showToast('Usuário excluído com sucesso', 'success');
          } catch (error) {
            showToast('Erro ao excluir usuário');
          }
        },
        'Confirmar exclusão'
      );
    };

    onMounted(() => {
      fetchUsers();
    });

    return {
      users,
      isFormOpen,
      selectedUser,
      headers,
      editUser,
      fetchUsers,
      handleUserSaved,
      deleteUser,
      showToast,
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
