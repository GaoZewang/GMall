import type { AxiosError } from 'axios'

export function getErrorMessage(err: unknown): string {
  const e = err as AxiosError<any>

  // axios 无响应：断网/跨域/超时
  if (!e.response) {
    return e.message || '网络异常，请稍后重试'
  }

  const data = e.response.data
  // 兼容常见后端结构
  return (
    data?.message ||
    data?.msg ||
    (typeof data === 'string' ? data : '') ||
    `请求失败（${e.response.status}）`
  )
}
