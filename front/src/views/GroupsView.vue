<template>
  <v-container style="padding: 50px;">
    <v-row justify="space-between" align="center" class="mb-4" style="margin: 0;">
      <h2>Grupos</h2>
      <v-btn color="primary" @click="openForm">Novo</v-btn>
    </v-row>
    <v-data-table 
      :items="groups" 
      :headers="[
        { title: 'Nome', value: 'name', sortable: true },
        { title: 'Usuários', value: 'users', sortable: false },
        { title: 'Ações', value: 'actions', sortable: false }
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
                <v-icon>mdi-pencil</v-icon>
                Editar
              </v-list-item-title>
            </v-list-item>
            <v-list-item @click="deleteGroup(item.id)">
              <v-list-item-title>
                <v-icon>mdi-delete</v-icon>
                Excluir
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
      </template>
    </v-data-table>
    <GroupForm :dialog="isFormOpen" :groupData="selectedGroup" @close="isFormOpen = false" @saved="fetchGroups" />
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';
import GroupForm from '@/components/GroupForm.vue';
import { useToast } from '@/composables/useToast';

export default defineComponent({
  name: 'GroupsView',
  components: { GroupForm },
  setup() {
    const groups = ref<Array<any>>([]);
    const isFormOpen = ref(false);
    const selectedGroup = ref<any>(null);

    const { showToast } = useToast();

    const fetchGroups = async () => {
      try {
        const response = await axios.get('/api/groups');
        
        groups.value = response.data;
      } catch (error) {
        showToast('Erro ao buscar grupos');
      }
    };

    const openForm = () => {
      selectedGroup.value = null;
      isFormOpen.value = true;
    };

    const editGroup = (group: any) => {
      selectedGroup.value = group;
      isFormOpen.value = true;
    };

    const deleteGroup = async (groupId: number) => {
      try {
        const confirmed = window.confirm('Deseja realmente excluir este grupo?');

        if (!confirmed) {
          return;
        }

        await axios.delete(`/api/groups/${groupId}`);
        fetchGroups();
      } catch (error) {
        showToast('Erro ao deletar grupo');
      }
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
    };
  },
});
</script>

<style scoped>
.v-container {
  padding-top: 50px;
}
</style>