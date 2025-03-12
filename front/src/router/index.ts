import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuth } from '@/composables/auth';

import Login from '@/views/Login.vue';
import Register from '@/views/Register.vue';
import Home from '@/views/Home.vue';
import GroupView from '@/views/GroupView.vue';
import UserView from '@/views/UserView.vue';
import OrdersView from '@/views/OrdersView.vue';
import OrderView from '@/views/OrderView.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: '/orders',
    name: 'OrdersView',
    component: OrdersView,
    meta: { requiresAuth: true },
  },
  {
    path: '/order/:id',
    name: 'OrderView',
    component: OrderView,
    meta: { requiresAuth: true },
  },
  {
    path: '/groups',
    name: 'GroupView',
    component: GroupView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/users',
    name: 'UserView',
    component: UserView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresGuest: true },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const { fetchUser, user, isAuthenticated } = useAuth();
  await fetchUser();

  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest);
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin);

  if (requiresAuth && !isAuthenticated.value) {
    return next({ name: 'Login' });
  }

  if (requiresGuest && isAuthenticated.value) {
    return next({ name: 'Home' });
  }

  if (requiresAdmin && (!user.value || !user.value.admin)) {
    return next({ name: 'Home' });
  }

  next();
});

export default router;