import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// 路由懒加载
const Login = () => import('../pages/Login.vue')
const Layout = () => import('../layouts/Layout.vue')
const Dashboard = () => import('../pages/Dashboard.vue')
const Goods = () => import('../pages/goods/Goods.vue')
const GoodsInfo = () => import('../pages/goods/GoodsInfo.vue')
const Orders = () => import('../pages/order/Orders.vue')
const Users = () => import('../pages/user/Users.vue')
const Merchant = () => import('../pages/merchant/Merchant.vue')
const MerchantCreate = () => import('../pages/merchant/MerchantCreate.vue')
const MerchantDetail = () => import('../pages/merchant/MerchantDetail.vue')
const System = () => import('../pages/system/index.vue')
const Shop = () => import('../pages/shop/Stores.vue')
const Category = () => import('../pages/category/Category.vue')
const CategoryInfo = () => import('../pages/category/CategoryInfo.vue')
const CategoryCreate = () => import('../pages/category/CategoryCreate.vue')
const GoodsCreate = () => import('../pages/goods/GoodsCreate.vue')
const router = createRouter({
  history: createWebHistory(),
  routes: [
    {path: '/login',component: Login,meta: { title: '登录' }},
    {path: '/',component: Layout,
      children: [
        { path: '', component: Dashboard, meta: { title: '概览' } },
        { path: 'goods', component: Goods, meta: { title: '商品管理' } },
        { path: 'goods/create', component: GoodsCreate, meta: { title: '新增商品' } },
        { path: 'goods/edit/:id', component: GoodsCreate, meta: { title: '编辑商品' } },
        { path: 'goods/:id', component: GoodsInfo, meta: { title: '商品详情' } },
        
        { path: 'merchant', component: Merchant, meta: { title: '商户管理' }},
        { path: 'merchant/create', component: MerchantCreate, meta: { title: '新增商户' } },
        { path: 'merchant/edit/:id', component: MerchantCreate, meta: { title: '编辑商户' } },
        { path: 'merchant/:id', component: MerchantDetail, meta: { title: '商户详情' } },

        { path: 'shop', component: Shop, meta: { title: '店铺管理' } },

        { path: 'category', component: Category, meta: { title: '分类管理' } },
        { path: 'category/create', component: CategoryCreate, meta: { title: '新增分类' } },
        { path: 'category/edit/:id', component: CategoryCreate, meta: { title: '编辑分类' } },
        { path: 'category/:id', component: CategoryInfo, meta: { title: '分类详情' } },
        
        { path: 'orders', component: Orders, meta: { title: '订单管理' } },
        { path: 'users', component: Users, meta: { title: '用户管理' } },
        
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