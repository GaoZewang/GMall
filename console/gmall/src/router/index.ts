import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// 路由懒加载
const Login = () => import('../pages/Login.vue')
const Layout = () => import('../layouts/Layout.vue')
const Dashboard = () => import('../pages/Dashboard.vue')
const Goods = () => import('../pages/goods/Goods.vue')
const GoodsInfo = () => import('../pages/goods/GoodsInfo.vue')
const Orders = () => import('../pages/order/Orders.vue')
const User = () => import('../pages/user/User.vue')
const UserInfo = () => import('../pages/user/UserInfo.vue')
const Merchant = () => import('../pages/merchant/Merchant.vue')
const MerchantCreate = () => import('../pages/merchant/MerchantCreate.vue')
const MerchantDetail = () => import('../pages/merchant/MerchantDetail.vue')
const System = () => import('../pages/system/index.vue')
const Shop = () => import('../pages/shop/Stores.vue')
const ShopCreate = () => import('../pages/shop/ShopCreate.vue')
const ShopInfo = () => import('../pages/shop/ShopInfo.vue')
const Category = () => import('../pages/category/Category.vue')
const CategoryInfo = () => import('../pages/category/CategoryInfo.vue')
const CategoryCreate = () => import('../pages/category/CategoryCreate.vue')
const GoodsCreate = () => import('../pages/goods/GoodsCreate.vue')
const Role = () => import('../pages/role/Role.vue')
const RoleCreate = () => import('../pages/role/RoleCreate.vue')
const RoleInfo = () => import('../pages/role/RoleInfo.vue')
const Permission = () => import('../pages/permission/Permission.vue')
const PermissionCreate = () => import('../pages/permission/PermissionCreate.vue')
const PermissionInfo = () => import('../pages/permission/PermissionInfo.vue')
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
        { path: 'shop/create', component: ShopCreate, meta: { title: '新增店铺' } },
        { path: 'shop/edit/:id', component: ShopCreate, meta: { title: '编辑店铺' } },
        { path: 'shop/:id', component: ShopInfo, meta: { title: '店铺详情' } },

        { path: 'category', component: Category, meta: { title: '分类管理' } },
        { path: 'category/create', component: CategoryCreate, meta: { title: '新增分类' } },
        { path: 'category/edit/:id', component: CategoryCreate, meta: { title: '编辑分类' } },
        { path: 'category/:id', component: CategoryInfo, meta: { title: '分类详情' } },
        
        { path: 'orders', component: Orders, meta: { title: '订单管理' } },
        { path: 'user', component: User, meta: { title: '用户管理' } },
        { path: 'user/:id', component: UserInfo, meta: { title: '用户详情' } },
        
        { path: 'system', component: System, meta: { title: '系统设置' } },
        { path: 'role', component: Role, meta: { title: '角色管理' } },
        { path: 'role/create', component: RoleCreate, meta: { title: '新增角色' } },
        { path: 'role/edit/:id', component: RoleCreate, meta: { title: '编辑角色' } },
        { path: 'role/:id', component: RoleInfo, meta: { title: '角色详情' } },
        { path: 'permission', component: Permission, meta: { title: '权限管理' } },
        { path: 'permission/create', component: PermissionCreate, meta: { title: '新增权限' } },
        { path: 'permission/edit/:id', component: PermissionCreate, meta: { title: '编辑权限' } },
        { path: 'permission/:id', component: PermissionInfo, meta: { title: '权限详情' } },
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