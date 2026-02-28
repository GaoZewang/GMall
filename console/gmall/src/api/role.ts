import { http } from './http'

export type Role = {
  id: number
  name: string
  slug?: string
  description: string
  status: number
  created_at?: string
  updated_at?: string
  permissions?: number[]
}

export type RoleListResp = {
  list: Role[]
  pagination: {
    total: number
    per_page: number
    current_page: number
    last_page: number
  }
}

export type RoleDetail = Role

export type CreateRolePayload = {
  name: string
  slug: string
  description: string
  permissions: number[]
}

export type UpdateRolePayload = {
  id: number
  name: string
  slug: string
  description: string
  status: number
  permissions: number[]
}

export type DeleteRolePayload = {
  id: number
}

/**
 * 获取角色列表
 * GET /admin/role/list
 */
export function adminRoleListApi(params: {
  page?: number
  per_page?: number
}) {
  return http.get('/admin/role/list', { params }) as Promise<RoleListResp>
}

/**
 * 获取角色详情
 * GET /admin/role/info
 */
export function adminRoleInfoApi(id: number) {
  return http.get('/admin/role/info', { params: { id } }) as Promise<RoleDetail>
}

/**
 * 创建角色
 * POST /admin/role/create
 */
export function adminRoleCreateApi(payload: CreateRolePayload) {
  return http.post('/admin/role/create', payload) as Promise<void>
}

/**
 * 更新角色
 * POST /admin/role/update
 */
export function adminRoleUpdateApi(payload: UpdateRolePayload) {
  return http.post('/admin/role/update', payload) as Promise<void>
}

/**
 * 删除角色
 * POST /admin/role/del
 */
export function adminRoleDeleteApi(payload: DeleteRolePayload) {
  return http.post('/admin/role/del', payload) as Promise<void>
}
