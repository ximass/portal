<template>
  <v-dialog v-model="dialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{
          isEdit ? 'Editar grupo' : 'Novo grupo'
        }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field
            label="Nome do grupo"
            v-model="group.name"
            :rules="[v => !!v || 'Nome é obrigatório']"
            required
          >
          </v-text-field>
          <v-autocomplete
            v-model="group.user_ids"
            :items="users"
            item-text="nome"
            item-value="id"
            label="Usuários"
            multiple
            chips
            clearable
            hide-selected
            :loading="loadingUsers"
            @update:search="fetchUsers"
          >
          </v-autocomplete>
          <v-autocomplete
            v-model="group.permission_ids"
            :items="permissions"
            item-title="title"
            item-value="id"
            label="Permissões"
            multiple
            chips
            clearable
            :loading="loadingPermissions"
          >
            <template #chip="{ item, props }">
              <v-chip
                v-bind="props"
                :text="item.raw.title"
              />
            </template>
            <template #item="{ item, props }">
              <v-list-item
                v-bind="props"
                :title="item.raw.title"
                :subtitle="item.raw.subtitle"
              />
            </template>
          </v-autocomplete>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="outlined" @click="close">Cancelar</v-btn>
            <v-btn variant="flat" color="primary" type="submit">Salvar</v-btn>
          </v-card-actions>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, ref, watch } from 'vue';
import type { VForm } from 'vuetify/components';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { usePermissions } from '../composables/usePermissions';
import type { Group } from '../types/types';

export default defineComponent({
  name: 'GroupForm',
  props: {
    dialog: {
      type: Boolean,
      required: true,
    },
    groupData: {
      type: Object as () => Group | null,
      default: null,
    },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const form = ref<VForm | null>(null);
    const group = ref<{ name: string; user_ids: number[]; permission_ids: number[] }>({
      name: '',
      user_ids: [],
      permission_ids: [],
    });
    const users = ref<Array<{ id: number; title: string }>>([]);
    const permissions = ref<Array<{ id: number; title: string; subtitle: string }>>([]);
    const loadingUsers = ref(false);
    const loadingPermissions = ref(false);

    const { showToast } = useToast();
    const { translatePermission, getPermissionDescription } = usePermissions();

    watch(
      () => props.groupData,
      newData => {
        if (newData) {
          group.value = {
            name: newData.name,
            user_ids: newData.users.map((user: { id: any }) => user.id),
            permission_ids: newData.permissions?.map((permission: { id: any }) => permission.id) || [],
          };
        } else {
          group.value = {
            name: '',
            user_ids: [],
            permission_ids: [],
          };
        }
      },
      { immediate: true }
    );

    const fetchUsers = async (search: string) => {
      if (!search) return;

      loadingUsers.value = true;
      try {
        const response = await axios.get('/api/users/search', {
          params: { search },
        });

        if (response.data) {
          users.value = response.data.map((user: any) => ({
            id: user.id,
            title: user.name,
          }));
        }
      } catch (error) {
        let errorMsg = 'Erro ao buscar usuários';

        if (
          typeof error === 'object' &&
          error !== null &&
          'response' in error
        ) {
          errorMsg = (error as any).response?.data?.message || errorMsg;
        }

        showToast(errorMsg);
      } finally {
        loadingUsers.value = false;
      }
    };

    const fetchPermissions = async () => {
      loadingPermissions.value = true;
      try {
        const response = await axios.get('/api/permissions');

        if (response.data) {
          permissions.value = response.data.map((permission: any) => ({
            id: permission.id,
            title: translatePermission(permission.name),
            subtitle: getPermissionDescription(permission.name),
          }));
        }
      } catch (error) {
        let errorMsg = 'Erro ao buscar permissões';

        if (
          typeof error === 'object' &&
          error !== null &&
          'response' in error
        ) {
          errorMsg = (error as any).response?.data?.message || errorMsg;
        }

        showToast(errorMsg);
      } finally {
        loadingPermissions.value = false;
      }
    };

    const submitForm = async () => {
      const validation = await form.value?.validate();

      if (validation && validation.valid) {
        try {
          const payload = {
            name: group.value.name,
            user_ids: group.value.user_ids,
            permission_ids: group.value.permission_ids,
          };

          if (props.groupData) {
            await axios.put(`/api/groups/${props.groupData.id}`, payload);
          } else {
            await axios.post('/api/groups', payload);
          }
          emit('saved');
          showToast('Grupo salvo com sucesso!', 'success');
          close();
        } catch (error) {
          let errorMsg = 'Erro ao salvar grupo';

          if (
            typeof error === 'object' &&
            error !== null &&
            'response' in error
          ) {
            errorMsg = (error as any).response?.data?.message || errorMsg;
          }

          showToast(errorMsg);
        }
      }
    };

    const close = () => {
      emit('close');
    };

    // Carregar permissões ao montar o componente
    fetchPermissions();

    return {
      form,
      group,
      users,
      permissions,
      loadingUsers,
      loadingPermissions,
      fetchUsers,
      fetchPermissions,
      submitForm,
      close,
      isEdit: !!props.groupData,
    };
  },
});
</script>
