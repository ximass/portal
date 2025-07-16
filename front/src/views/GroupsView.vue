<template>
  <v-container style="padding: 50px">
    <v-row
      justify="space-between"
      align="center"
      class="mb-4"
      style="margin: 0"
    >
      <h2>Grupos</h2>
      <v-btn color="primary" @click="openForm">Novo</v-btn>
    </v-row>
    <v-data-table
      :items="groups"
      :headers="[
        { title: 'Nome', value: 'name', sortable: true },
        { title: 'Usuários', value: 'users', sortable: false },
        { title: 'Ações', value: 'actions', sortable: false },
      ]"
      class="elevation-1"
    >
      <template #item.users="{ item }">
        {{ item.users.map(user => user.name).join(', ') }}
      </template>
      <template #item.actions="{ item }">
        <v-menu offset-y>
          <template #activator="{ props }">
            <v-btn icon v-bind="props" variant="text">
              <v-icon>mdi-dots-vertical</v-icon>
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="editGroup(item)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item @click="deleteGroup(item.id!)">
              <v-list-item-title>
                <v-icon class="me-2">mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <GroupForm
      :dialog="isFormOpen"
      :groupData="selectedGroup"
      @close="isFormOpen = false"
      @saved="fetchGroups"
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
import GroupForm from '../components/GroupForm.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import type { Group } from '../types/types';

export default defineComponent({
  name: 'GroupsView',
  components: { GroupForm, ConfirmDialog },
  setup() {
    const groups = ref<Group[]>([]);
    const isFormOpen = ref(false);
    const selectedGroup = ref<Group | null>(null);

    const { showToast } = useToast();
    const {
      isConfirmDialogOpen,
      confirmTitle,
      confirmMessage,
      openConfirm,
      closeConfirm,
      handleConfirm,
    } = useConfirm();

    const fetchGroups = async () => {
      try {
        const response = await axios.get('/api/groups');

        groups.value = response.data.map((group: any) => ({
          ...group,
          users: group.users ?? [],
        }));
      } catch (error) {
        showToast('Erro ao buscar grupos');
      }
    };

    const openForm = () => {
      selectedGroup.value = null;
      isFormOpen.value = true;
    };

    const editGroup = (group: Group) => {
      selectedGroup.value = group;
      isFormOpen.value = true;
    };

    const deleteGroup = async (id: number) => {
      openConfirm(
        'Deseja realmente excluir este grupo?',
        async () => {
          try {
            await axios.delete(`/api/groups/${id}`);
            fetchGroups();
            showToast('Grupo excluído com sucesso', 'success');
          } catch (error) {
            showToast('Erro ao deletar grupo');
          }
        },
        'Confirmar exclusão'
      );
    };

    onMounted(() => {
      fetchGroups();
    });
    return {
      groups,
      isFormOpen,
      selectedGroup,
      openForm,
      editGroup,
      deleteGroup,
      fetchGroups,
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
