import { http } from './http'

export type CategoryItem = {
  id: number
  parent_id: number
  category_name: string
  category_status: number
  children: CategoryItem[]
  hasChildren?: boolean
}

export type CategoryListData = {
  data: CategoryItem[]
  pagination?: {
    total: number
    per_page: number
    current_page: number
    last_page: number
  }
}

export function adminCategoryListApi(params?: {
  page?: number
  per_page?: number
  category_name?: string
  category_status?: number
}) {
  return http.get('/admin/category/list', { params }) as Promise<CategoryListData>
}

export type CategoryParentInfo = {
  id: number
  category_name: string
}

export type CategoryInfo = {
  id: number
  parent_id: number
  category_level: number
  category_name: string
  tree_path: string
  sort: number
  is_leaf: number
  category_status: number
  attrs_template: string | null
  created_at: string
  updated_at: string
  parent_info: CategoryParentInfo
}

export function adminCategoryInfoApi(id: number) {
  return http.get('/admin/category/info', { params: { id } }) as Promise<CategoryInfo>
}

export type CreateCategoryPayload = {
  category_name: string
  parent_id: number
  is_leaf: number
}

export function adminCategoryCreateApi(payload: CreateCategoryPayload) {
  return http.post('/admin/category/create', payload)
}

export type UpdateCategoryPayload = {
  id: number
  category_name: string
  parent_id: number
  is_leaf: number
}

export function adminCategoryUpdateApi(payload: UpdateCategoryPayload) {
  return http.post('/admin/category/update', payload)
}
