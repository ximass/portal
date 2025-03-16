<template>
  <v-app>
    <TopMenu v-if="isAuthenticated" :user="user" @toggleDrawer="drawerOpen = !drawerOpen" />
    <SideMenu v-if="isAuthenticated" :user="user" :drawerOpen="drawerOpen" />
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import TopMenu from './components/TopMenu.vue';
import SideMenu from './components/SideMenu.vue';
import { useAuth } from './composables/auth';

export default defineComponent({
  name: 'App',
  components: { TopMenu, SideMenu },
  setup() {
    const { fetchUser, isAuthenticated, user } = useAuth();
    const drawerOpen = ref(false);

    fetchUser();

    return {
      isAuthenticated,
      user,
      drawerOpen
    };
  },
});
</script>