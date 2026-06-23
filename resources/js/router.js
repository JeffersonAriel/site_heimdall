import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from './stores/auth';

// ─── E-commerce (Public) ─────────────────────────────────
import EcommerceHome from './Pages/Ecommerce/Home.vue';
import Cart          from './Pages/Ecommerce/Cart.vue';
import Checkout      from './Pages/Ecommerce/Checkout.vue';
import Login         from './Pages/Ecommerce/Login.vue';

// ─── Customer Account (Protected) ───────────────────────
import CustomerPanel  from './Pages/Customer/Panel.vue';
import CustomerHelp   from './Pages/Customer/HelpDesk.vue';

// ─── ERP Core ───────────────────────────────────────────
import ErpDashboard       from './Pages/ERP/Dashboard.vue';
import CRMDashboard       from './Pages/ERP/CRMDashboard.vue';
import BIDashboard        from './Pages/ERP/BIDashboard.vue';
import AIDashboard        from './Pages/ERP/AIDashboard.vue';
import FinancialDashboard from './Pages/ERP/FinancialDashboard.vue';
import StockDashboard     from './Pages/ERP/StockDashboard.vue';
import ProductionDashboard from './Pages/ERP/ProductionDashboard.vue';
import HelpDeskPanel      from './Pages/ERP/HelpDeskPanel.vue';
import FiscalPanel        from './Pages/ERP/FiscalPanel.vue';
import ErpLogin           from './Pages/ERP/Login.vue';

const routes = [
  // ─── E-commerce (public) ─────────────────────────────
  { path: '/',         component: EcommerceHome, name: 'home'     },
  { path: '/carrinho', component: Cart,           name: 'cart'     },
  { path: '/checkout', component: Checkout,       name: 'checkout' },
  { path: '/login',    component: Login,          name: 'login'    },

  // ─── Customer area (protected) ───────────────────────
  {
    path: '/minha-conta',
    component: CustomerPanel,
    name: 'my-account',
    meta: { requiresAuth: true },
  },
  {
    path: '/minha-conta/suporte',
    component: CustomerHelp,
    name: 'my-account.helpdesk',
    meta: { requiresAuth: true },
  },

  // ─── ERP login & internal (meta: erp) ────────────────
  { path: '/erp/login',      component: ErpLogin,            name: 'erp.login' },
  { path: '/erp',            component: ErpDashboard,        name: 'erp.dashboard',   meta: { erp: true } },
  { path: '/erp/crm',        component: CRMDashboard,        name: 'erp.crm',         meta: { erp: true } },
  { path: '/erp/bi',         component: BIDashboard,         name: 'erp.bi',          meta: { erp: true } },
  { path: '/erp/ai',         component: AIDashboard,         name: 'erp.ai',          meta: { erp: true } },
  { path: '/erp/financeiro', component: FinancialDashboard,  name: 'erp.financial',   meta: { erp: true } },
  { path: '/erp/estoque',    component: StockDashboard,      name: 'erp.stock',       meta: { erp: true } },
  { path: '/erp/producao',   component: ProductionDashboard, name: 'erp.production',  meta: { erp: true } },
  { path: '/erp/suporte',    component: HelpDeskPanel,       name: 'erp.helpdesk',    meta: { erp: true } },
  { path: '/erp/fiscal',     component: FiscalPanel,         name: 'erp.fiscal',      meta: { erp: true } },
];

const routerBase = window.location.pathname.startsWith('/~jeff2892') ? '/~jeff2892/' : '/';

const router = createRouter({
  history: createWebHistory(routerBase),
  routes,
  scrollBehavior: () => ({ top: 0 }),
});

// Navigation guard
router.beforeEach((to) => {
  const authStore = useAuthStore();
  
  // Set appropriate Authorization headers dynamically
  authStore.setAuthForPath(to.path);

  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return { name: 'login' };
  }

  if (to.meta.erp && !authStore.isErpLoggedIn) {
    return { name: 'erp.login' };
  }

  if (to.name === 'erp.login' && authStore.isErpLoggedIn) {
    return { name: 'erp.dashboard' };
  }
});

export default router;
