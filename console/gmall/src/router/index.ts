import { createRouter, createWebHistory } from 'vue-router'
import Login from '../pages/Login.vue'
import Layout from '../layouts/Layout.vue'
import Dashboard from '../pages/Dashboard.vue'
import Goods from '../pages/goods/Goods.vue'
import Orders from '../pages/order/Orders.vue'
import Users from '../pages/user/Users.vue'
import Merchant from '../pages/merchant/merchant.vue'
import System from '../pages/System.vue'
import Stores from '../pages/stores/Stores.vue'
import { useAuthStore } from '../stores/auth'
const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      component: Login,
      meta: { title: '登录' },
    },
    {
      path: '/',
      component: Layout,
      children: [
        { path: '', component: Dashboard, meta: { title: '概览' } },
        { path: 'goods', component: Goods, meta: { title: '商品管理' } },
        { path: 'orders', component: Orders, meta: { title: '订单管理' } },
        { path: 'users', component: Users, meta: { title: '用户管理' } },
        { path: 'merchant', component: Merchant, meta: { title: '商户管理'}},
        { path: 'stores', component: Stores, meta: { title: '店铺管理' } },
        { path: 'system', component: System, meta: { title: '系统设置' } },
     
      ],
    },
    // 兜底：其它路径都回首页（会被守卫拦到 login）
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  // 未登录：除了 /login 之外全部跳到 /login
  if (!auth.token && to.path !== '/login') {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  // 已登录：访问 /login 直接回首页
  if (auth.token && to.path === '/login') {
    return { path: '/' }
  }
})

export default router