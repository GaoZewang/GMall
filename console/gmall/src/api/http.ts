import axios from 'axios'
import { ElMessage } from 'element-plus'
import router from '../router'
import { useAuthStore } from '../stores/auth'

type ApiResp<T = any> = {
  code: number
  msg: string
  data: T
}

function getHttpErrorMessage(err: any): string {
  if (!err?.response) return err?.message || '网络异常，请稍后重试'
  const status = err.response.status
  const data = err.response.data
  return data?.msg || data?.message || `请求失败（${status}）`
}

export const http = axios.create({
  baseURL:'http://127.0.0.1:8787',
  timeout: 15000,
  headers: { 'Content-Type': 'application/json' },
})

// 请求：带 token
http.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (auth.token) {
    config.headers = config.headers 
    config.headers.Authorization = `Bearer ${auth.token}`
  }
  return config
})

// 响应：统一解包 + 统一错误提示
http.interceptors.response.use(
  (res) => {
    const body = res.data as ApiResp<any>

    // 如果不是你们的统一结构（兜底）
    if (!body || typeof body !== 'object' || !('code' in body)) {
      return res.data
    }

    // 业务成功
    if (body.code === 200) {
      return body.data
    }

    // 业务失败（非200）
    const msg = body.msg || '操作失败'
    ElMessage.error(msg)
    return Promise.reject(new Error(msg))
  },
  (err) => {
    const auth = useAuthStore()
    const status = err?.response?.status

    // 如果后端使用 HTTP 401/403 表示未登录/无权限
    if (status === 401) {
      auth.logout()
      if (router.currentRoute.value.path !== '/login') router.push('/login')
      ElMessage.error('登录已失效，请重新登录')
      return Promise.reject(err)
    }

    if (status === 403) {
      ElMessage.error('没有权限')
      return Promise.reject(err)
    }

    ElMessage.error(getHttpErrorMessage(err))
    return Promise.reject(err)
  }
)
