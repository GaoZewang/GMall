import type { MenuItem } from './types'

export const adminMenus: MenuItem[] = [
  { title: '概览', path: '/' },
  { title: '商品管理', path: '/goods' },
  { title: '商户管理', path: '/merchant' },
  { title: '店铺管理', path: '/shop' },
  { title: '分类管理', path: '/category' },
  { title: '订单管理', path: '/orders' },
  { title: '用户管理', path: '/user' },
  { title: '系统设置', path: '/system',
    children: [
      { title: '配置中心', path: '/system' },
      { title: '角色管理', path: '/role' },
      { title: '菜单管理', path: '/permission' },
    ]},
]

export default adminMenus
