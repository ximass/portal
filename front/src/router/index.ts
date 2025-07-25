import {
  createRouter,
  createWebHistory,
  type RouteRecordRaw,
} from 'vue-router';
import { useAuth } from '../composables/auth';

import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Home from '../views/Home.vue';
import OrdersView from '../views/OrdersView.vue';
import OrderView from '../views/OrderView.vue';
import GroupsView from '../views/GroupsView.vue';
import UsersView from '../views/UsersView.vue';
import ProcessView from '../views/ProcessView.vue';
import ProcessesView from '../views/ProcessesView.vue';
import CustomersView from '../views/CustomersView.vue';
import MaterialsView from '../views/MaterialsView.vue';
import SheetsView from '../views/SheetsView.vue';
import BarsView from '../views/BarsView.vue';
import ComponentsView from '../views/ComponentsView.vue';
import MercosurCommonNomenclatureView from '../views/MercosurCommonNomenclatureView.vue';
import StatesView from '../views/StatesView.vue';
import PartPrint from '../views/PartPrint.vue';

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
    path: '/materials',
    name: 'MaterialsView',
    component: MaterialsView,
    meta: { requiresAuth: true, requiresAdmin: false },
  },
  {
    path: '/sheets',
    name: 'SheetsView',
    component: SheetsView,
    meta: { requiresAuth: true, requiresAdmin: false },
  },
  {
    path: '/bars',
    name: 'BarsView',
    component: BarsView,
    meta: { requiresAuth: true, requiresAdmin: false },
  },
  {
    path: '/components',
    name: 'ComponentsView',
    component: ComponentsView,
    meta: { requiresAuth: true, requiresAdmin: false },
  },
  {
    path: '/mercosur-common-nomenclatures',
    name: 'MercosurCommonNomenclatureView',
    component: MercosurCommonNomenclatureView,
    meta: { requiresAuth: true, requiresAdmin: false },
  },
  {
    path: '/states',
    name: 'StatesView',
    component: StatesView,
    meta: { requiresAuth: true, requiresAdmin: false },
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
    path: '/parts/:id/print',
    name: 'PartPrint',
    component: PartPrint,
    props: true,
  },
  {
    path: '/order/parts/print',
    name: 'PartPrintAll',
    component: PartPrint,
    props: route => ({ order_id: route.query.order_id }),
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

// @ts-ignore
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

  //@ts-ignore
  if (requiresAdmin && (!user.value || !user.value.admin)) {
    return next({ name: 'Home' });
  }

  next();
});

export default router;
