import axios from 'axios'
import { ElMessage } from 'element-plus'
import router from '../router'
import { useAuthStore } from '../stores/auth'
import { refreshTokenApi } from './auth'

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

// 是否正在刷新 token
let isRefreshing = false
// 存储等待刷新 token 后重试的请求队列
let refreshSubscribers: Array<(token: string) => void> = []

export const http = axios.create({
  baseURL: (import.meta as any).env.VITE_API_BASE_URL,
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
// 响应：统一解包 + 统一错误提示 + token 刷新
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
    // 如果是登录失效相关的code，执行与401/403相同的处理
    if ([401011, 401012, 401013, 401014, 401015].includes(body.code)) {
      const auth = useAuthStore()
      // 如果有 refreshToken，尝试刷新
      if (auth.refreshToken && !isRefreshing) {
        return refreshTokenAndRetry(res.config as any)
      }
      // 否则执行登出
      auth.logout()
      if (router.currentRoute.value.path !== '/login') router.push('/login')
      ElMessage.error('登录已失效，请重新登录')
      return Promise.reject(new Error('登录已失效，请重新登录'))
    }

    const msg = body.msg || '操作失败'
    ElMessage.error(msg)
    return Promise.reject(new Error(msg))
  },
  (err) => {
    const auth = useAuthStore()
    const status = err?.response?.status
    const data = err?.response?.data
    const code = data?.code

    // 检查是否是登录失效相关的错误
    const isAuthError = status === 401 || 
                      status === 403 || 
                      [401011, 401012, 401013, 401014, 401015].includes(code as number)

    if (isAuthError) {
      // 如果有 refreshToken 且不在刷新中，尝试刷新
      if (auth.refreshToken && !isRefreshing) {
        return refreshTokenAndRetry(err.config as any)
      }
      // 否则执行登出
      auth.logout()
      if (router.currentRoute.value.path !== '/login') router.push('/login')
      ElMessage.error('登录已失效，请重新登录')
      return Promise.reject(err)
    }

    // 其他错误提示
    ElMessage.error(getHttpErrorMessage(err))
    return Promise.reject(err)
  }
)

// 刷新 token 并重试请求
async function refreshTokenAndRetry(config: any) {
  const auth = useAuthStore()
  
  try {
    isRefreshing = true
    // 调用刷新 token API
    const res = await refreshTokenApi({ refresh_token: auth.refreshToken })
    
    // 更新 token
    auth.setToken(res.access_token, res.refresh_token)
    
    // 更新当前请求的 token
    if (config.headers) {
      config.headers.Authorization = `Bearer ${res.access_token}`
    }
    
    // 重试所有等待的请求
    refreshSubscribers.forEach(cb => cb(res.access_token))
    refreshSubscribers = []
    
    // 重试当前请求
    return http(config)
  } catch (err) {
    // 刷新失败，执行登出
    auth.logout()
    if (router.currentRoute.value.path !== '/login') router.push('/login')
    ElMessage.error('登录已失效，请重新登录')
    return Promise.reject(err)
  } finally {
    isRefreshing = false
  }
}
