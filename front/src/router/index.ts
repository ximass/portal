import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuth } from '@/composables/auth';

import Login from '@/views/Login.vue';
import Register from '@/views/Register.vue';
import GroupsView from '@/views/GroupsView.vue';
import UsersView from '@/views/UsersView.vue';
import Home from '@/views/Home.vue';
import ProcessView from '@/views/ProcessView.vue';
import ProcessesView from '@/views/ProcessesView.vue';
import CustomersView from '@/views/CustomersView.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { requiresAuth: true },
  },
  {
    path: '/processes',
    name: 'ProcessesView',
    component: ProcessesView,
    meta: { requiresAuth: true, requiresAdmin: false },
  },
  {
    path: '/processes/:id',
    name: 'ProcessView',
    component: ProcessView,
    meta: { requiresAuth: true, requiresAdmin: false },
  },
  {
    path: '/customers',
    name: 'CustomersView',
    component: CustomersView,
    meta: { requiresAuth: true, requiresAdmin: false },
  },
  {
    path: '/groups',
    name: 'GroupsView',
    component: GroupsView,
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/users',
    name: 'UsersView',
    component: UsersView,
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