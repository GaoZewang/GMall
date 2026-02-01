import { http } from './http'

export type Shop = {
  id: number
  merchant_id: number
  admin_user_id: number
  balance: number
  revenue: number
  name: string
  address: string
  contact_phone: string
  status: number
  created_at: string
  updated_at: string
  merchant_name?: string
  admin_user_name?: string
}

export type ShopListResp = {
  list: Shop[]
  pagination: {
    total: number
    per_page: number
    current_page: number
    last_page: number
  }
}

export type ShopDetail = Shop

export type CreateShopPayload = {
  merchant_id: number
  admin_user_id: number
  name: string
  address?: string
  contact_phone?: string
  status?: number
}

export type UpdateShopPayload = {
  id: number
  merchant_id?: number
  admin_user_id?: number
  name?: string
  address?: string
  contact_phone?: string
  status?: number
}

/**
 * 获取店铺列表
 * GET /admin/shop/list
 */
export function adminShopListApi(params: {
  page?: number
  per_page?: number
  merchant_id: number
  name?: string
}) {
  return http.get('/admin/shop/list', { params }) as Promise<ShopListResp>
}

/**
 * 获取店铺详情
 * GET /admin/shop/info
 */
export function adminShopInfoApi(id: number) {
  return http.get('/admin/shop/info', { params: { id } }) as Promise<ShopDetail>
}

/**
 * 创建店铺
 * POST /admin/shop/create
 */
export function adminShopCreateApi(payload: CreateShopPayload) {
  return http.post('/admin/shop/create', payload) as Promise<{ id: number }>
}

/**
 * 更新店铺
 * POST /admin/shop/update
 */
export function adminShopUpdateApi(payload: UpdateShopPayload) {
  return http.post('/admin/shop/update', payload) as Promise<void>
}

/**
 * 删除店铺
 * GET /admin/shop/delete
 */
export function adminShopDeleteApi(params: { id: number }) {
  return http.get('/admin/shop/delete', { params }) as Promise<void>
}
