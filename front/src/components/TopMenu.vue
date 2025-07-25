<template>
  <v-app-bar app :color="isDark ? 'secundary' : 'white'" :dark="isDark">
    <v-app-bar-nav-icon @click="toggleDrawer" />
    <v-spacer />
    <div class="logo-header">
      <v-img :src="logo" alt="Logo" contain />
    </div>
    <v-menu v-model="menu" offset-y>
      <template #activator="{ props }">
        <v-btn icon v-bind="props">
          <v-avatar>
            <v-icon>mdi-dots-vertical</v-icon>
          </v-avatar>
        </v-btn>
      </template>
      <v-list>
        <v-list-item @click="goToProfile">
          <v-list-item-title>Meu perfil</v-list-item-title>
        </v-list-item>
        <v-list-item @click="onLogout">
          <v-list-item-title>Sair</v-list-item-title>
        </v-list-item>
        <v-divider></v-divider>
        <v-list-item>
          <v-row>
            <v-col>
              <v-list-item-title>Modo escuro</v-list-item-title>
            </v-col>
            <v-col style="margin-top: -15px">
              <v-switch v-model="isDark" @change="toggleDarkMode"></v-switch>
            </v-col>
          </v-row>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-app-bar>
</template>

<script lang="ts">
import '../assets/styles/global.css';
import { defineComponent, ref, onMounted } from 'vue';
import { useAuth } from '../composables/auth';
import { useRouter } from 'vue-router';
import { useTheme } from 'vuetify';

import logo from '@/assets/images/logo_horizontal.png';

export default defineComponent({
  name: 'TopMenu',
  emits: ['toggleDrawer'],
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  setup(_props, { emit }) {
    const { isAuthenticated, logout } = useAuth();
    const router = useRouter();
    const menu = ref(false);
    const theme = useTheme();
    const isDark = ref(theme.global.name.value === 'dark');

    const toggleDrawer = () => {
      emit('toggleDrawer');
    };

    const onLogout = () => {
      logout();
    };

    const goToProfile = () => {
      router.push('/profile');
    };

    const toggleDarkMode = () => {
      if (isDark.value) {
        theme.global.name.value = 'dark';
        localStorage.setItem('theme', 'dark');
      } else {
        theme.global.name.value = 'light';
        localStorage.setItem('theme', 'light');
      }
      document.documentElement.setAttribute(
        'data-theme',
        theme.global.name.value
      );
    };

    onMounted(() => {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme) {
        theme.global.name.value = savedTheme;
        isDark.value = savedTheme === 'dark';
      }
    });

    return {
      isAuthenticated,
      logo,
      onLogout,
      goToProfile,
      menu,
      isDark,
      toggleDarkMode,
      toggleDrawer,
    };
  },
});
</script>
