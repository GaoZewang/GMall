import { http } from './http'

// 商户类型定义
export type Merchant = {
  id: number
  name: string
  balance: number
  revenue: number
  logo: string
  contact_phone: string
  status: number
  created_at: string
  updated_at: string
}

// 商户分页响应
export type MerchantListResp = {
  list: Merchant[]
  pagination: {
    total: number
    per_page: number
    current_page: number
    last_page: number
  }
}

/**
 * 获取商户列表：GET /admin/merchant/list
 */
export function adminMerchantListApi(params: {
  page?: number
  per_page?: number
  name?: string
}) {
  return http.get('/admin/merchant/list', { params }) as Promise<MerchantListResp>
}

// 商户创建参数
export type CreateMerchantPayload = {
  admin_user_id: number
  name: string
  logo: string
  address: string
  contact_phone: string
  lat: number
  lng: number
}

/**
 * 创建商户：POST /admin/merchant/create
 */
export function adminMerchantCreateApi(payload: CreateMerchantPayload) {
  return http.post('/admin/merchant/create', payload) as Promise<{ id: number } | any>
}

// 商户编辑参数
export type UpdateMerchantPayload = {
  id: number
  admin_user_id: number
  name: string
  logo: string
  address: string
  contact_phone: string
  lat: number
  lng: number
}

/**
 * 编辑商户：POST /admin/merchant/update
 */
export function adminMerchantUpdateApi(payload: UpdateMerchantPayload) {
  return http.post('/admin/merchant/update', payload) as Promise<true | any>
}

/**
 * 删除商户：GET /admin/merchant/delete
 */
export function adminMerchantDeleteApi(params: { id: number }) {
  return http.get('/admin/merchant/delete', { params }) as Promise<true>
}

/**
 * 商户详情：GET /admin/merchant/info?id=xx
 */
export function adminMerchantInfoApi(params: { id: number }) {
  return http.get('/admin/merchant/info', { params }) as Promise<Merchant & Record<string, any>>
}
