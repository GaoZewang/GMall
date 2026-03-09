import { http } from './http'

export type User = {
  id: number
  username: string
  nickname: string
  phone: string
  balance: number
  created_at?: string
  updated_at?: string
}

export type UserListResp = {
  list: User[]
  pagination: {
    total: number
    per_page: number
    current_page: number
    last_page: number
  }
}

export type UserDetail = User

export type ChangeBalancePayload = {
  id: number
  balance: number
}

export type ResetPasswordPayload = {
  id: number
  password: string
}

/**
 * 获取用户列表
 * GET /admin/user/list
 */
export function adminUserListApi(params: {
  page?: number
  per_page?: number
  username?: string
  phone?: string
  nickname?: string
}) {
  return http.get('/admin/user/list', { params }) as Promise<UserListResp>
}

/**
 * 获取用户详情
 * GET /admin/user/info
 */
export function adminUserInfoApi(id: number) {
  return http.get('/admin/user/info', { params: { id } }) as Promise<UserDetail>
}

/**
 * 用户充值
 * POST /admin/user/changeBalance
 */
export function adminUserChangeBalanceApi(payload: ChangeBalancePayload) {
  return http.post('/admin/user/changeBalance', payload) as Promise<void>
}

/**
 * 用户重置密码
 * POST /admin/user/resetPassword
 */
export function adminUserResetPasswordApi(payload: ResetPasswordPayload) {
  return http.post('/admin/user/resetPassword', payload) as Promise<void>
}
