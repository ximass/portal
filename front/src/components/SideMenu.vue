<template>
  <v-navigation-drawer 
    app 
    v-model="drawerOpen"
    :rail="rail"
    permanent
    @click="rail = false"
  >
    <v-list-item
      :prepend-avatar="props.user?.avatar || 'mdi-account'"
      :title="props.user?.name || 'Usuário'"
      nav
    >
      <template v-slot:append>
        <v-btn
          icon="mdi-chevron-left"
          variant="text"
          @click.stop="rail = !rail"
        ></v-btn>
      </template>
    </v-list-item>

    <v-divider></v-divider>

    <v-list density="compact" nav>
      <v-list-item 
        v-for="item in filteredMenuItems" 
        :key="item.title" 
        :prepend-icon="item.icon" 
        :title="item.title" 
        :value="item.route"
        @click="$router.push(item.route)"
      >
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script lang="ts">
import { defineComponent, computed, ref } from 'vue';

export default defineComponent({
  name: 'SideMenu',
  props: {
    user: {
      type: Object,
      required: true,
    },
    drawerOpen: {
      type: Boolean,
      required: true
    }
  },
  setup(props) {
    const rail = ref(true);

    const menuItems = [
      { title: 'Tela inicial', route: '/home', admin: false, icon: 'mdi-home' },
      { title: 'Orçamentos', route: '/orders', admin: false, icon: 'mdi-checkbook' },
      { title: 'Materiais', route: '/materials', admin: false, icon: 'mdi-cube' },
      { title: 'Chapas', route: '/sheets', admin: false, icon: 'mdi-animation-outline' },
      { title: 'Barras', route: '/bars', admin: false, icon: 'mdi-color-helper' },
      { title: 'Componentes', route: '/components', admin: false, icon: 'mdi-screw-lag' },
      { title: 'Processos', route: '/processes', admin: false, icon: 'mdi-cogs' },
      { title: 'Clientes', route: '/customers', admin: false, icon: 'mdi-account-multiple' },
      { title: 'Usuários', route: '/users', admin: true, icon: 'mdi-account' },
      { title: 'Grupos', route: '/groups', admin: true, icon: 'mdi-account-group' },
    ];

    const filteredMenuItems = computed(() => {
      return menuItems.filter(item => {
        if (item.admin) {
          return props.user && props.user.admin;
        }
        return true;
      });
    });

    return { 
      filteredMenuItems,
      rail,
      props
    };
  },
});
</script>