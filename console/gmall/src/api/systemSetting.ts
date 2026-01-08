import { http } from './http'

// 系统配置列表项（只包含基本信息）
export interface SystemSettingListItem {
  id: number
  set_tag: string
  set_name: string
}

// 系统配置详情项（包含完整信息）
export interface SystemSetting {
  id: number
  set_tag: string
  set_name: string
  set_content: any
  set_template: string
  create_time: string
  update_time: string
}

export interface Pagination {
  total: number
  per_page: number
  current_page: number
  last_page: number
}

export interface ListResponse {
  code: number
  msg: string
  data: {
    list: SystemSettingListItem[]
    pagination: Pagination
  }
}

export interface InfoResponse {
  code: number
  msg: string
  data: SystemSetting
}

export interface BaseResponse {
  code: number
  msg: string
  data: any
}

// 获取系统配置列表
export function getSystemSettingListApi(params: {
  page: number
  per_page: number
}): Promise<ListResponse['data']> {
  return http.get('/admin/setting/list', { params })
}

// 获取系统配置详情
export function getSystemSettingInfoApi(params: {
  id: number
}): Promise<InfoResponse['data']> {
  return http.get('/admin/setting/info', { params })
}

// 更新系统配置
export function updateSystemSettingApi(params: {
  id: number
  tag: string
  name: string
  content: string
}): Promise<BaseResponse['data']> {
  return http.post('/admin/setting/update', params)
}