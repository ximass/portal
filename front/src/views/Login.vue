<template>
  <v-container fluid>
    <v-row align="center" justify="center" style="height: 100vh">
      <v-col cols="12" sm="6" md="4">
        <v-card>
          <v-img :src="logo" contain height="100" class="mt-8"></v-img>
          <v-card-text>
            <v-card-title class="justify-center">Faça login</v-card-title>
            <v-form
              ref="form"
              v-model="isFormValid"
              @submit.prevent="loginHandler"
            >
              <v-text-field
                label="Email"
                v-model="email"
                type="email"
                required
                :rules="[v => !!v || 'Email é obrigatório']"
              >
              </v-text-field>
              <v-text-field
                label="Senha"
                v-model="password"
                type="password"
                required
                :rules="[v => !!v || 'Senha é obrigatória']"
              >
              </v-text-field>
              <v-btn type="submit" color="primary" block>Entrar</v-btn>
            </v-form>
          </v-card-text>
          <v-card-actions class="justify-center">
            <router-link to="/register"
              >Não possui uma conta? Registre-se</router-link
            >
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/auth';
import { useToast } from '../composables/useToast';

import logo from '@/assets/images/logo_horizontal.png';

export default defineComponent({
  name: 'Login',
  setup() {
    const form = ref();
    const isFormValid = ref(false);
    const email = ref('');
    const password = ref('');
    const router = useRouter();
    const { login } = useAuth();
    const { showToast } = useToast();

    const loginHandler = async () => {
      try {
        const validation = await form.value.validate();

        if (!validation.valid) return;

        await login(email.value, password.value);
        router.push('/');
      } catch (err) {
        showToast('Credenciais inválidas');
      }
    };

    return { form, logo, isFormValid, email, password, loginHandler };
  },
});
</script>
