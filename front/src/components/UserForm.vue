<template>
  <v-dialog v-model="internalDialog" max-width="500px">
    <v-card>
      <v-card-title>
        <span class="text-h5">Editar usuário</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-row dense>
            <v-col cols="12">
              <v-text-field
                label="Nome"
                v-model="user.name"
                :rules="[v => !!v || 'Nome é obrigatório']"
                required
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="12">
              <v-text-field
                label="Email"
                v-model="user.email"
                :rules="[v => !!v || 'Email é obrigatório']"
                required
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row dense>
            <v-col cols="6">
              <v-switch v-model="user.admin" label="Administrador"></v-switch>
            </v-col>
            <v-col cols="6">
              <v-switch v-model="user.enabled" label="Habilitado"></v-switch>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn variant="text" @click="close">Cancelar</v-btn>
        <v-btn color="primary" @click="submitForm">Atualizar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, ref, watch } from 'vue';
import axios from '../plugins/axios';
import { useToast } from '../composables/useToast';

export default defineComponent({
  name: 'UserForm',
  props: {
    dialog: { type: Boolean, required: true },
    userData: { type: Object, default: null },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast();
    const user = ref({
      id: 0,
      name: '',
      email: '',
      admin: false,
      enabled: false,
    });
    const internalDialog = ref(props.dialog);
    const form = ref();

    watch(
      () => props.dialog,
      val => {
        internalDialog.value = val;
      }
    );

    watch(
      () => props.userData,
      newData => {
        if (newData) {
          //@ts-ignore
          user.value = { ...newData };
        }
      },
      { immediate: true }
    );

    const close = () => {
      emit('close');
    };

    const submitForm = async () => {
      if (!user.value.name || !user.value.email) {
        showToast('Preencha todos os campos');
        return;
      }

      try {
        await axios.put(`/api/users/${user.value.id}`, user.value);
        emit('saved');
        showToast('Usuário atualizado com sucesso!', 'success');
      } catch (error) {
        showToast('Erro ao atualizar usuário');
      }
    };

    return { internalDialog, user, form, close, submitForm, showToast };
  },
});
</script>

<style scoped></style>
