declare const AMap: any
let amapPromise: Promise<typeof AMap> | null = null
let apiKey = import.meta.env.VITE_GAODE_MAP_KEY || ''
let securityJsCode =  import.meta.env.VITE_SECURITY_JS_CODE || ''
export function useAMapLoader() {
  if (!amapPromise) {
    amapPromise = new Promise((resolve, reject) => {
        if ((window as any).AMap) {
            resolve((window as any).AMap)
            console.log('AMap already loaded')
            return
        }
        // ⭐ 高德官方要求：先注入安全配置
        (window as any)._AMapSecurityConfig = {
            securityJsCode: securityJsCode
        }
        const script = document.createElement('script')
        script.type = 'text/javascript'
        script.async = true
        script.src =
            'https://webapi.amap.com/maps?v=2.0&key='+apiKey+'&plugin=AMap.Geocoder,AMap.PlaceSearch,AMap.AutoComplete'
        script.onload = () => {
            resolve((window as any).AMap)
        }

        script.onerror = () => {
            reject(new Error('AMap JS API 加载失败'))
        }

        document.head.appendChild(script)
    })
  }

  return amapPromise
}
