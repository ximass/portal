<template>
  <v-navigation-drawer
    app
    :value="drawerOpen"
    :mini-variant="!drawerOpen"
    expand-on-hover
    :rail="rail"
    permanent
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
        v-if="homeMenuItem"
        :prepend-icon="homeMenuItem.icon"
        :title="homeMenuItem.title"
        :value="homeMenuItem.route"
        @click="$router.push(homeMenuItem.route)"
      ></v-list-item>
      <v-list-item
        v-if="orderMenuItem"
        :prepend-icon="orderMenuItem.icon"
        :title="orderMenuItem.title"
        :value="orderMenuItem.route"
        @click="$router.push(orderMenuItem.route)"
      ></v-list-item>

      <v-list-group value="Cadastros" prepend-icon="mdi-folder">
        <template v-slot:activator="{ props: groupProps }">
          <v-list-item v-bind="groupProps" title="Cadastros"></v-list-item>
        </template>
        <v-list-item
          v-for="item in basicMenuItems"
          :key="item.title"
          :prepend-icon="item.icon"
          :title="item.title"
          :value="item.route"
          @click="$router.push(item.route)"
        ></v-list-item>
      </v-list-group>

      <v-list-group
        v-if="adminMenuItems.length"
        value="Administração"
        prepend-icon="mdi-shield-account"
      >
        <template v-slot:activator="{ props: groupProps }">
          <v-list-item v-bind="groupProps" title="Administração"></v-list-item>
        </template>
        <v-list-item
          v-for="item in adminMenuItems"
          :key="item.title"
          :prepend-icon="item.icon"
          :title="item.title"
          :value="item.route"
          @click="$router.push(item.route)"
        ></v-list-item>
      </v-list-group>
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
      required: true,
    },
  },
  setup(props) {
    const rail = ref(true);

    const menuItems = [
      { title: 'Tela inicial', route: '/home', admin: false, icon: 'mdi-home' },
      {
        title: 'Orçamentos',
        route: '/orders',
        admin: false,
        icon: 'mdi-checkbook',
      },
      {
        title: 'Materiais',
        route: '/materials',
        admin: false,
        icon: 'mdi-cube',
      },
      {
        title: 'Chapas',
        route: '/sheets',
        admin: false,
        icon: 'mdi-animation-outline',
      },
      {
        title: 'Barras',
        route: '/bars',
        admin: false,
        icon: 'mdi-color-helper',
      },
      {
        title: 'Componentes',
        route: '/components',
        admin: false,
        icon: 'mdi-screw-lag',
      },
      {
        title: 'Processos',
        route: '/processes',
        admin: false,
        icon: 'mdi-cogs',
      },
      {
        title: 'NCM',
        route: '/mercosur-common-nomenclatures',
        admin: false,
        icon: 'mdi-file-document-outline',
      },
      {
        title: 'Estados',
        route: '/states',
        admin: false,
        icon: 'mdi-map',
      },
      {
        title: 'Clientes',
        route: '/customers',
        admin: false,
        icon: 'mdi-account-multiple',
      },
      { title: 'Usuários', route: '/users', admin: true, icon: 'mdi-account' },
      {
        title: 'Grupos',
        route: '/groups',
        admin: true,
        icon: 'mdi-account-group',
      },
    ];

    const homeMenuItem = computed(() =>
      menuItems.find(item => item.route === '/home')
    );
    const orderMenuItem = computed(() =>
      menuItems.find(item => item.route === '/orders')
    );

    const basicMenuItems = computed(() =>
      menuItems.filter(
        item =>
          !item.admin && item.route !== '/orders' && item.route !== '/home'
      )
    );

    const adminMenuItems = computed(() =>
      props.user && props.user.admin ? menuItems.filter(item => item.admin) : []
    );

    return {
      homeMenuItem,
      orderMenuItem,
      basicMenuItems,
      adminMenuItems,
      rail,
      props,
    };
  },
});
</script>
