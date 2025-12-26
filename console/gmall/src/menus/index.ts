import { PLATFORM, type Platform } from '../platform'
import adminMenus from './admin'
import merchantMenus from './merchant'
import storeMenus from './store'

export function getMenus(platform: Platform = PLATFORM) {
  if (platform === 'admin') return adminMenus
  if (platform === 'merchant') return merchantMenus
  return storeMenus
}
