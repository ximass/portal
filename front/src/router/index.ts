import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import Login from '@/views/Login.vue';
import Register from '@/views/Register.vue';
import GroupView from '@/views/GroupView.vue';
import UserView from '@/views/UserView.vue';
import Home from '@/views/Home.vue';
import { useAuth } from '@/composables/auth';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    name: 'Home',
    component: Home,
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

router.beforeEach((to, from, next) => {
  const authToken = localStorage.getItem('authToken');
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest);
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin);
  const { user } = useAuth();

  if (requiresAuth && !authToken) {
    return next({ name: 'Login' });
  }

  if (requiresGuest && authToken) {
    return next({ name: 'Home' });
  }

  if (requiresAdmin) {
    try {
      if (!user.value || !user.value.admin) {
        return next({ name: 'Home' });
      }
    } catch (error) {
      return next({ name: 'Home' });
    }
  }

  next();
});

export default router;