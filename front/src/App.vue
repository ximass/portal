<template>
  <v-app>
    <TopMenu v-if="isAuthenticated && !isPrintRoute" :user="user" @toggleDrawer="drawerOpen = !drawerOpen" />
    <SideMenu v-if="isAuthenticated && !isPrintRoute" :user="user" :drawerOpen="drawerOpen" />
    <v-main>
      <router-view />
    </v-main>
    <v-snackbar v-if="!isPrintRoute" v-model="isToastVisible" timeout="3000" :color="toastColor">
       {{ toastMessage }}
     </v-snackbar>
  </v-app>
</template>

<script lang="ts">
import { defineComponent, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import TopMenu from './components/TopMenu.vue';
import SideMenu from './components/SideMenu.vue';
import { useAuth } from './composables/auth';
import { useToast } from './composables/useToast';
import './assets/styles/global.css';

export default defineComponent({
  name: 'App',
  components: { TopMenu, SideMenu },
  setup() {
    const { isToastVisible, toastMessage, toastColor } = useToast();
    const { fetchUser, isAuthenticated, user } = useAuth();
    const drawerOpen = ref(false);
    const route = useRoute();
    const isPrintRoute = computed(() => route.name === 'SetPrint');

    fetchUser();

    return {
      isToastVisible,
      toastMessage,
      toastColor,
      isAuthenticated,
      user,
      drawerOpen,
      isPrintRoute
    };
  },
});
</script>