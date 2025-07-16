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
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn @click="close">Cancelar</v-btn>
            <v-btn color="primary" type="submit">Salvar</v-btn>
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
    const group = ref<{ name: string; user_ids: number[] }>({
      name: '',
      user_ids: [],
    });
    const users = ref<Array<{ id: number; title: string }>>([]);
    const loadingUsers = ref(false);

    const { showToast } = useToast();

    watch(
      () => props.groupData,
      newData => {
        if (newData) {
          group.value = {
            name: newData.name,
            user_ids: newData.users.map((user: { id: any }) => user.id),
          };
        } else {
          group.value = {
            name: '',
            user_ids: [],
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

    const submitForm = async () => {
      const validation = await form.value?.validate();

      if (validation && validation.valid) {
        try {
          const payload = {
            name: group.value.name,
            user_ids: group.value.user_ids,
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

    return {
      form,
      group,
      users,
      loadingUsers,
      fetchUsers,
      submitForm,
      close,
      isEdit: !!props.groupData,
    };
  },
});
</script>
