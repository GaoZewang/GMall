import { http } from './http'

export type GoodsItem = {
  id: number
  merchant_id: number
  goods_name: string
  subtitle: string
  category_id: number
  cover_image: string
  goods_status: number // 1/0
}

export type GoodsListResp = {
  list: GoodsItem[]
  pagination: {
    total: number
    per_page: number
    current_page: number
    last_page: number
  }
}

export function adminGoodsListApi(params: {
  page?: number
  per_page?: number
  goods_name?: string
  merchant_id?: number
  goods_status?: number
}) {
  return http.get('/admin/goods/list', { params }) as Promise<GoodsListResp>
}

export type GoodsSku = {
  id: number
  goods_id: number
  merchant_id: number
  sku_code: string
  bar_code: string
  attrs: Record<string, string>
  cost_price: string
  base_price: string
  status: number
  created_at: string
  updated_at: string
}
/**
 * 详情：GET /admin/goods/info?id=xx
 */
export type GoodsInfo = {
  id: number
  merchant_id: number
  goods_name: string
  subtitle: string
  category_id: number
  cover_image: string
  images: string[]
  description: string
  attrs_template: {
    attrs: Record<string, string>
    specs: Array<{ name: string; values: string[] }>
  }
  goods_status: number
  is_deleted: number
  created_at: string
  updated_at: string
  sku: GoodsSku[]
}
export function adminGoodsInfoApi(id: number) {
  return http.get('/admin/goods/info', { params: { id } }) as Promise<GoodsItem & Record<string, any>>
}
/**
 * 新增商品：POST /admin/goods/create
 */
export type CreateGoodsPayload = {
  goods_name: string
  subtitle: string
  category_id: number
  brand_id: number
  cover_image: string
  images: string[]
  description: string
  attrs_template: Record<string, string[]>
  sku_list: Array<{
    sku_code: string
    bar_code: string
    attrs: Record<string, string>
    cost_price: number
    base_price: number
  }>
}
export function adminGoodsCreateApi(payload: CreateGoodsPayload) {
  return http.post('/admin/goods/create', payload) as Promise<{ id: number } | any>
}

/** 修改状态：GET 版本（如果你后端是 POST，把 http.get 改成 http.post） */
export function adminGoodsStatusApi(params: { id: number; status: number }) {
//   return http.get('/admin/goods/status', { params }) as Promise<true>
  // 如果是 POST：
  return http.post('/admin/goods/status', params) as Promise<true>
}

/** 删除商品：GET /admin/goods/delete */
export function adminGoodsDeleteApi(params: { id: number }) {
  return http.get('/admin/goods/delete', { params }) as Promise<true>
}

/**
 * 编辑商品：POST /admin/goods/update
 */
export type UpdateGoodsPayload = {
  id: number
  goods_name: string
  good_name?: string
  subtitle: string
  category_id: number
  brand_id: number
  cover_image: string
  images: string[]
  description: string
  attrs_template: {
    specs: Array<{ name: string; values: string[] }>
    attrs: Record<string, string>
  }
  sku_list: Array<{
    id?: number
    sku_code: string
    bar_code: string
    attrs: Record<string, string>
    cost_price: number
    base_price: number
  }>
}

export function adminGoodsUpdateApi(payload: UpdateGoodsPayload) {
  return http.post('/admin/goods/update', payload) as Promise<true | any>
}


