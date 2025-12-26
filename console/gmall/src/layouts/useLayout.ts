import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { PLATFORM_LABEL } from '../platform'
import { getMenus } from '../menus'
import { getUserInfoApi } from '../api/auth'

export function useLayout() {
  const route = useRoute()
  const router = useRouter()
  const auth = useAuthStore()

  /** 菜单（按平台） */
  const menus = computed(() => getMenus(auth.platform))

  /** 平台文案（按平台） */
  const platformLabel = computed(() => PLATFORM_LABEL[auth.platform])

  /** 标题（来自路由 meta.title） */
  const pageTitle = computed(() => (route.meta.title as string) || 'Gmall')

  /** 菜单高亮 */
  const active = computed(() => route.path)

  /** 顶栏右侧用户名 */
  const userLabel = computed(() => auth.user?.username || '管理员')

  /** 防止重复请求 */
  const loadingMe = ref(false)

  async function fetchMe() {
    if (!auth.token) return
    if (auth.user?.username) return
    if (loadingMe.value) return

    loadingMe.value = true
    try {
      const me = await getUserInfoApi()
      auth.setUser({ username: me.username })
    } catch {
      // 失败不在这里提示（http.ts 里会统一提示 / 401 会自动跳登录）
    } finally {
      loadingMe.value = false
    }
  }

  // Layout 初始化时拉一次
  onMounted(() => {
    fetchMe()
  })

  // token 变化时也拉一次（比如刷新、重新登录）
  watch(
    () => auth.token,
    (t) => {
      if (t) fetchMe()
      else auth.setUser(null)
    }
  )

  function goLogin() {
    router.push('/login')
  }

  function onLogout() {
    auth.logout()
    router.push('/login')
  }

  return {
    menus,
    platformLabel,
    pageTitle,
    active,
    userLabel,
    goLogin,
    onLogout,
    fetchMe,      // 需要时可手动刷新
    loadingMe,    // 需要时可用于显示 loading
  }
}
