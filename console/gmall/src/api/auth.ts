import { http } from './http'
import type { Platform } from '../platform'

export function loginApi(payload: { username: string; password: string; platform: Platform }) {
  return http.post('/admin/login', payload) as Promise<{ access_token: string; refresh_token: string }>
}

export function refreshTokenApi(refreshToken: string) {
  return http.get('/admin/refresh', {
    headers: {
      Authorization: `Bearer ${refreshToken}`
    }
  }) as Promise<{ access_token: string; refresh_token: string }>
}

export function getUserInfoApi() {
  return http.get('/admin/getUserInfo') as Promise<{ username: string }>
}
