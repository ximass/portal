<template>
  <v-dialog v-model="internalDialog" max-width="600px">
    <v-card>
      <v-card-title>
        <span class="text-h5">{{
          isEdit ? 'Editar cliente' : 'Novo cliente'
        }}</span>
      </v-card-title>
      <v-card-text>
        <v-form ref="form" @submit.prevent="submitForm">
          <v-text-field
            label="Nome"
            v-model="formData.name"
            required
            :rules="[v => !!v || 'Nome é obrigatório']"
          ></v-text-field>
          <v-text-field label="Email" v-model="formData.email"></v-text-field>
          <v-text-field
            label="Telefone"
            v-model="formData.phone"
          ></v-text-field>
          <v-text-field label="CNPJ" v-model="formData.cnpj"></v-text-field>
          <v-text-field label="CPF" v-model="formData.cpf"></v-text-field>
          <v-textarea label="Endereço" v-model="formData.address"></v-textarea>
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn @click="closeDialog">Cancelar</v-btn>
        <v-btn color="primary" @click="submitForm">Salvar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import { defineComponent, ref, watch } from 'vue';
import axios from 'axios';
import { useToast } from '../composables/useToast';
import { useMisc } from '../composables/misc';
import type { VForm } from 'vuetify/components';

export default defineComponent({
  name: 'CustomerView',
  props: {
    dialog: { type: Boolean, required: true },
    customerData: { type: Object, default: () => ({}) },
    isEdit: { type: Boolean, default: false },
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const form = ref<VForm | null>(null);
    const formData = ref<any>({});
    const internalDialog = ref(props.dialog);

    const { formatPhone, formatCnpj, formatCpf } = useMisc();
    const { showToast } = useToast();

    watch(
      formData,
      newVal => {
        newVal.phone = formatPhone(newVal.phone || '');
        newVal.cnpj = formatCnpj(newVal.cnpj || '');
        newVal.cpf = formatCpf(newVal.cpf || '');
      },
      { deep: true }
    );

    watch(
      () => props.dialog,
      newVal => {
        internalDialog.value = newVal;
      }
    );

    watch(
      () => props.customerData,
      newVal => {
        formData.value = { ...newVal };
      },
      { immediate: true }
    );

    const closeDialog = () => {
      emit('close');
    };

    const submitForm = async () => {
      try {
        const validation = await form.value?.validate();

        if (validation && validation.valid) {
          if (props.isEdit) {
            await axios.put(
              `/api/customers/${formData.value.id}`,
              formData.value
            );
          } else {
            await axios.post('/api/customers', formData.value);
          }
          showToast('Cliente salvo com sucesso!', 'success');
          emit('saved');
        }
      } catch (error) {
        let errorMsg = 'Erro ao salvar grupo';

        if (
          typeof error === 'object' &&
          error !== null &&
          'response' in error
        ) {
          const err = error as { response?: { data?: { message?: string } } };
          errorMsg = err.response?.data?.message || errorMsg;
        }
        showToast(errorMsg);
      }
    };

    return {
      internalDialog,
      form,
      formData,
      closeDialog,
      submitForm,
    };
  },
});
</script>
