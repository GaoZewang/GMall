import type { MenuItem } from './types'

export const adminMenus: MenuItem[] = [
  { title: '概览', path: '/' },
  { title: '商品管理', path: '/goods' },
  { title: '订单管理', path: '/orders' },
  { title: '用户管理', path: '/users' },
  { title: '系统设置', path: '/system' },
  { title: '商户管理', path: '/merchant' },
]

export default adminMenus
