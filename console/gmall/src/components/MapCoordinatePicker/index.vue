<template>
  <div class="map-picker">
    <el-input
      v-model="keyword"
      placeholder="输入地址搜索，回车定位"
      clearable
      :disabled="disabled"
      @keyup.enter="search"
    />

    <div ref="mapRef" class="map-container" />

    <div class="info">
      <div>经度：{{ modelValue.lng ?? '-' }}</div>
      <div>纬度：{{ modelValue.lat ?? '-' }}</div>
      <div>地址：{{ modelValue.address || '-' }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useAMapLoader } from './useAMapLoader'

interface Location {
  lng: number | null
  lat: number | null
  address: string
}

const props = defineProps<{
  modelValue: Location
  disabled?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', val: Location): void
}>()

const mapRef = ref<HTMLDivElement>()
const keyword = ref('')

let map: any
let marker: any
let geocoder: any
let placeSearch: any
let observer: MutationObserver | null = null
onMounted(async () => {
  try {
    const AMap = await useAMapLoader()
    // 确保mapRef.value存在
    if (!mapRef.value) {
      console.error('mapRef.value is null')
      return
    }
    // 初始化地图
    map = new AMap.Map(mapRef.value, {
      zoom: 10,
      center: props.modelValue.lng
        ? [props.modelValue.lng, props.modelValue.lat]
        : [116.397428, 39.90923]
    })
    // Canvas性能优化函数
    const optimizeCanvases = () => {
      const canvases = mapRef.value?.querySelectorAll('canvas') || []
      canvases.forEach(canvas => {
        if (canvas instanceof HTMLCanvasElement) {
          canvas.setAttribute('willReadFrequently', 'true')
        }
      })
    }
    
    // 地图加载完成后优化canvas
    map.on('complete', () => {
      optimizeCanvases()
      setTimeout(optimizeCanvases, 100)
      setTimeout(optimizeCanvases, 500)
    })
  
    // 使用MutationObserver监听DOM变化
    observer = new MutationObserver((mutations) => {
      let needsOptimize = false
      mutations.forEach(mutation => {
        if (mutation.addedNodes.length > 0) needsOptimize = true
        if (mutation.type === 'attributes' && mutation.target instanceof HTMLCanvasElement) needsOptimize = true
        if (mutation.type === 'childList') needsOptimize = true
      })
      if (needsOptimize) {
        setTimeout(optimizeCanvases, 0)
      }
    })
    
    observer.observe(mapRef.value, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['class', 'style', 'src']
    })
    // 初始检查
    setTimeout(optimizeCanvases, 100)
    setTimeout(optimizeCanvases, 500)
    setTimeout(optimizeCanvases, 1000)
    // 初始化标记
    marker = new AMap.Marker({
      anchor: 'bottom-center'
    })
    map.add(marker)
    // 初始化地理编码器和地点搜索
    geocoder = new AMap.Geocoder()
    placeSearch = new AMap.PlaceSearch({
      pageSize: 5,
      map: map // 将搜索结果显示在地图上
    })
    // 回显已有坐标
    if (props.modelValue.lng && props.modelValue.lat) {
      marker.setPosition([props.modelValue.lng, props.modelValue.lat])
    }

    // 绑定地图点击事件
    if (!props.disabled) {
      map.on('click', onMapClick)
    }
  } catch (e) {
    console.error('Error initializing MapCoordinatePicker:', e)
  }
})

function onMapClick(e: any) {
  // 确保e.lnglat存在
  if (!e || !e.lnglat) {
    return
  }
  
  const { lng, lat } = e.lnglat
  
  marker.setPosition([lng, lat])
  
  // 直接更新坐标，不依赖地址解析
  emit('update:modelValue', {
    lng,
    lat,
    address: ''
  })
  // 并行获取地址信息
  geocoder.getAddress([lng, lat], (status: string, result: any) => {
    if (status === 'complete' && result.regeocode) {
      emit('update:modelValue', {
        lng,
        lat,
        address: result.regeocode.formattedAddress
      })
    } else {
      // 处理常见错误码
      if (status === 'error') {
        console.error('错误信息:', result)
        if (result.errorCode === 'INVALID_USER_SCODE') {
          console.warn('高德地图API密钥无效，请在useAMapLoader.ts中替换为自己的密钥')
        } else {
          console.warn(`获取地址失败: ${result.errorMsg || '未知错误'}`)
        }
      }
    }
  })
}

function search() {
    if (!keyword.value) return
    placeSearch.search(keyword.value, (status: string, result: any) => {
    if (status === 'complete') {
      if (result.poiList?.pois?.length) {
        const poi = result.poiList.pois[0]
        // 确保poi.location存在
        if (poi.location) {
          let lng, lat
          try {
            // 尝试多种方式获取经纬度
            if (typeof poi.location.toArray === 'function') {
              [lng, lat] = poi.location.toArray()
            } else {
              lng = poi.location.lng
              lat = poi.location.lat
            }
            map.setCenter([lng, lat])
            marker.setPosition([lng, lat])
            emit('update:modelValue', {
              lng,
              lat,
              address: poi.name + (poi.address ? ' ' + poi.address : '')
            })
          } catch (e) {
            console.error('Failed to extract lnglat:', e, poi.location)
          }
        } else {
          console.error('POI has no location:', poi)
        }
      } else {
        console.warn('No POIs found in search result')
        console.error('未找到匹配的地址')
        // 未找到匹配的地址
      }
    } else {
        console.error('Search failed:', status)
        console.error('搜索失败，请重试')
    }
  })
}

// 组件卸载时清理
onUnmounted(() => {
  // 断开MutationObserver，避免内存泄漏
  if (observer) {
    observer.disconnect()
    observer = null
  }
})
</script>

<style scoped>
.map-picker {
  width: 100%;
}

.map-container {
  height: 360px;
  margin-top: 8px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
}

.info {
  margin-top: 6px;
  font-size: 12px;
  color: #606266;
  line-height: 18px;
}
</style>
