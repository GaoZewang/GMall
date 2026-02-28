import { http } from './http'

export type Permission = {
  id: number
  name: string
  code: string
  route_url: string
  icon: string
  description: string
  parent_id: number
  is_show?: number
  status?: number
  children?: Permission[]
}

export type PermissionList = Permission[]

export type PermissionDetail = Permission

export type CreatePermissionPayload = {
  name: string
  code: string
  description: string
  is_show: number
  parent_id: number
  route_url: string
  icon: string
}

export type UpdatePermissionPayload = {
  id: number
  name: string
  icon: string
  code: string
  route_url: string
  description: string
  is_show: number
  parent_id: number
}

export type DeletePermissionPayload = {
  id: number
}

/**
 * 获取权限列表
 * GET /admin/permission/list
 */
export function adminPermissionListApi(params: {
  name?: string
  url?: string
}) {
  return http.get('/admin/permission/list', { params }) as Promise<PermissionList>
}

/**
 * 获取权限详情
 * GET /admin/permission/info
 */
export function adminPermissionInfoApi(id: number) {
  return http.get('/admin/permission/info', { params: { id } }) as Promise<PermissionDetail>
}

/**
 * 创建权限
 * POST /admin/permission/create
 */
export function adminPermissionCreateApi(payload: CreatePermissionPayload) {
  return http.post('/admin/permission/create', payload) as Promise<void>
}

/**
 * 更新权限
 * POST /admin/permission/update
 */
export function adminPermissionUpdateApi(payload: UpdatePermissionPayload) {
  return http.post('/admin/permission/update', payload) as Promise<void>
}

/**
 * 删除权限
 * POST /admin/permission/del
 */
export function adminPermissionDeleteApi(payload: DeletePermissionPayload) {
  return http.post('/admin/permission/del', payload) as Promise<void>
}
