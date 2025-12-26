export type Platform = 'admin' | 'merchant' | 'store'

export const PLATFORM = (import.meta.env.VITE_PLATFORM || 'admin') as Platform

export const PLATFORM_LABEL: Record<Platform, string> = {
    admin: '总后台',
    merchant: '商户后台',
    store: '店铺后台',
}
