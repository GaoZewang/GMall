import { defineStore } from 'pinia'
import type { Platform } from '../platform'
import { PLATFORM } from '../platform'

export type AdminUser = {
  username: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('gmall_token') || '',
    refreshToken: localStorage.getItem('gmall_refresh_token') || '',
    platform: (localStorage.getItem('gmall_platform') as Platform) || PLATFORM,
    user: (localStorage.getItem('gmall_user')
      ? (JSON.parse(localStorage.getItem('gmall_user') as string) as AdminUser)
      : null) as AdminUser | null,
  }),
  actions: {
    setToken(token, refreshToken?) {
      this.token = token
      localStorage.setItem('gmall_token', token)
      if (refreshToken) {
        this.refreshToken = refreshToken
        localStorage.setItem('gmall_refresh_token', refreshToken)
      }
    },
    setPlatform(p: Platform) {
      this.platform = p
      localStorage.setItem('gmall_platform', p)
    },
    setUser(user: AdminUser | null) {
      this.user = user
      if (user) localStorage.setItem('gmall_user', JSON.stringify(user))
      else localStorage.removeItem('gmall_user')
    },
    logout() {
      this.token = ''
      this.refreshToken = ''
      this.user = null
      localStorage.removeItem('gmall_token')
      localStorage.removeItem('gmall_refresh_token')
      localStorage.removeItem('gmall_user')
    },
  },
})
