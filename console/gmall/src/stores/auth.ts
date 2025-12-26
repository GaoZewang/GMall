import { defineStore } from 'pinia'
import type { Platform } from '../platform'
import { PLATFORM } from '../platform'

export type AdminUser = {
  username: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('gmall_token') || '',
    platform: (localStorage.getItem('gmall_platform') as Platform) || PLATFORM,
    user: (localStorage.getItem('gmall_user')
      ? (JSON.parse(localStorage.getItem('gmall_user') as string) as AdminUser)
      : null) as AdminUser | null,
  }),
  actions: {
    setToken(token) {
      this.token = token
      localStorage.setItem('gmall_token', token)
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
      this.user = null
      localStorage.removeItem('gmall_token')
      localStorage.removeItem('gmall_user')
    },
  },
})
