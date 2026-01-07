<template>
  <div class="map-picker">
    <!-- 搜索栏 -->
    <div class="search-bar">
      <el-input v-model="searchKeyword" placeholder="输入地址搜索" clearable @keyup.enter="handleSearch" :disabled="loading">
        <template #append>
          <el-button @click="handleSearch" :loading="loading">搜索</el-button>
        </template>
      </el-input>
    </div>

    <!-- 经纬度和地址显示 -->
    <div class="info-row">
      <div class="info-item">
        <span class="info-label">经纬度：</span>
        <el-input v-model="displayLngLat" placeholder="输入经纬度，如：116.39,39.90" size="small" @keyup.enter="handleRegeoCode" />
      </div>
      <div class="info-item">
        <span class="info-label">地址：</span>
        <el-input v-model="displayAddress" disabled placeholder="自动获取地址" size="small" />
      </div>
      <div class="info-item">
        <el-button type="primary" size="small" @click="handleRegeoCode" :loading="loading">经纬度 -> 地址</el-button>
      </div>
    </div>

    <!-- 地图容器 -->
    <div class="map-container">
      <div ref="mapRef" class="map" :id="mapId">
        <!-- 加载状态 -->
        <div v-if="loading" class="map-loading">
          <el-skeleton style="width: 100%; height: 100%;" animated />
          <div class="loading-text">地图加载中...</div>
        </div>
        <!-- 错误状态 -->
        <div v-else-if="error" class="map-error">
          <el-icon class="error-icon"><WarningFilled /></el-icon>
          <div class="error-text">{{ error }}</div>
          <el-button type="primary" size="small" @click="retryLoadMap">重试</el-button>
        </div>
      </div>
      <div v-if="!error" class="map-tip">点击地图拾取坐标点</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { ElMessage } from 'element-plus'
import { WarningFilled } from '@element-plus/icons-vue'
// 引入Leaflet库
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Leaflet类型声明（如果需要）
declare module 'leaflet' {
  interface Marker {
    _icon?: HTMLElement
  }
}

// Props
const props = defineProps<{
  modelValue?: [number, number] // [lng, lat]
  zoom?: number
}>()

// Emits
const emit = defineEmits<{
  'update:modelValue': [value: [number, number]]
  'coordinate-change': [lng: number, lat: number]
  'address-change': [address: string]
}>()

// 组件状态
const mapRef = ref<HTMLElement | null>(null)
const searchKeyword = ref('')
const mapId = `map-${Date.now()}`
const loading = ref(false)
const error = ref('')
const displayLngLat = ref('') // 显示的经纬度字符串
const displayAddress = ref('') // 显示的地址
let map: any = null
let marker: any = null

// Nominatim API配置
const nominatimBaseUrl = 'https://nominatim.openstreetmap.org'

// 初始化地图
async function initMap() {
  if (!mapRef.value) {
    error.value = '地图容器未找到'
    return
  }
  
  loading.value = true
  error.value = ''
  
  try {
    // 初始化Leaflet地图实例
    map = L.map(mapRef.value, {
      center: props.modelValue ? [props.modelValue[1], props.modelValue[0]] : [39.915, 116.404], // 注意Leaflet使用[lat, lng]顺序
      zoom: props.zoom || 13,
      zoomControl: true,
      attributionControl: true
    })
    
    // 添加OpenStreetMap图层
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      maxZoom: 19
    }).addTo(map)
    
    // 调试：检查地图是否成功初始化
    console.log('Map initialized:', map)
    
    // 地图加载完成事件
    map.on('load', () => {
      console.log('Map loading completed')
      
      // 优化Canvas性能，设置willReadFrequently: true
      optimizeCanvases()
      
      // 初始化标记
      if (props.modelValue) {
        addMarker(props.modelValue)
        // 更新显示的经纬度
        displayLngLat.value = `${props.modelValue[0]},${props.modelValue[1]}`
        // 初始转换地址
        regeoCode(props.modelValue)
      } else {
        // 如果没有初始坐标，添加默认标记
        const defaultCenter = map.getCenter()
        if (defaultCenter) {
          const defaultLnglat: [number, number] = [defaultCenter.lng, defaultCenter.lat] // [lng, lat]顺序
          addMarker(defaultLnglat)
          displayLngLat.value = `${defaultLnglat[0]},${defaultLnglat[1]}`
          regeoCode(defaultLnglat)
        }
      }
    })
    
    // Canvas性能优化函数
    function optimizeCanvases() {
      // 找到地图容器内的所有canvas元素
      const canvases = mapRef.value?.querySelectorAll('canvas') || []
      canvases.forEach(canvas => {
        if (canvas instanceof HTMLCanvasElement && !canvas.hasAttribute('willReadFrequently')) {
          // 直接为现有canvas设置属性
          canvas.setAttribute('willReadFrequently', 'true')
          
          // 尝试重新获取context以应用新属性
          try {
            const ctx = canvas.getContext('2d', { willReadFrequently: true })
            if (ctx) {
              // 保存当前画布内容
              const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)
              // 重新设置画布尺寸以应用新属性
              const width = canvas.width
              const height = canvas.height
              canvas.width = width
              canvas.height = height
              // 恢复画布内容
              ctx.putImageData(imageData, 0, 0)
            }
          } catch (e) {
            console.warn('Failed to optimize canvas:', e)
          }
        }
      })
    }
    
    // 使用MutationObserver监听DOM变化，优化动态创建的canvas
    if (mapRef.value) {
      const observer = new MutationObserver((mutations) => {
        mutations.forEach(mutation => {
          // 检查是否有新的canvas元素添加
          mutation.addedNodes.forEach(node => {
            if (node instanceof HTMLCanvasElement) {
              optimizeCanvases()
            } else if (node instanceof Element) {
              // 检查子元素中是否有canvas
              const canvases = node.querySelectorAll('canvas')
              if (canvases.length > 0) {
                optimizeCanvases()
              }
            }
          })
        })
      })
      
      // 开始观察地图容器的DOM变化
      observer.observe(mapRef.value, {
        childList: true,
        subtree: true
      })
    }
    
    // 点击地图拾取坐标
    map.on('click', (e: any) => {
      const lng = e.latlng.lng
      const lat = e.latlng.lat
      updateCoordinate(lng, lat)
      addMarker([lng, lat])
      displayLngLat.value = `${lng},${lat}`
      // 逆地理编码获取地址
      regeoCode([lng, lat])
    })
    
    loading.value = false
  } catch (err: any) {
    loading.value = false
    error.value = err.message || '地图初始化失败'
    console.error('地图加载错误:', err)
    ElMessage.error('地图加载失败: ' + error.value)
  }
}

// 经纬度转地址（逆地理编码）- 使用Nominatim API
async function regeoCode(lnglat: [number, number]) {
  try {
    const response = await fetch(`${nominatimBaseUrl}/reverse?format=json&lat=${lnglat[1]}&lon=${lnglat[0]}&zoom=18&addressdetails=1`)
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    const data = await response.json()
    
    if (data.display_name) {
      const address = data.display_name
      displayAddress.value = address
      emit('address-change', address)
    } else {
      console.error('根据经纬度查询地址失败:', data)
      displayAddress.value = '地址获取失败'
      emit('address-change', '')
    }
  } catch (err) {
    console.error('逆地理编码失败:', err)
    displayAddress.value = '地址获取失败'
    emit('address-change', '')
  }
}

// 处理手动输入经纬度转换
function handleRegeoCode() {
  if (!displayLngLat.value || !map) return
  
  try {
    const lnglatStr = displayLngLat.value.trim()
    const lnglatArr = lnglatStr.split(',').map(Number)
    
    if (lnglatArr.length !== 2 || isNaN(lnglatArr[0]) || isNaN(lnglatArr[1])) {
      ElMessage.warning('请输入有效的经纬度，格式为：经度,纬度')
      return
    }
    
    const lnglat: [number, number] = [lnglatArr[0], lnglatArr[1]]
    updateCoordinate(lnglat[0], lnglat[1])
    addMarker(lnglat)
    regeoCode(lnglat)
  } catch (err) {
    console.error('经纬度转换失败:', err)
    ElMessage.error('经纬度转换失败，请重试')
  }
}

// 重试加载地图
function retryLoadMap() {
  initMap()
}

// 添加标记
function addMarker(lnglat: [number, number]) {
  if (!map) {
    console.warn('Cannot add marker: map is not initialized')
    return
  }
  
  console.log('Adding marker at:', lnglat)
  
  try {
    // Leaflet使用[lat, lng]顺序
    const latlng = [lnglat[1], lnglat[0]]
    
    if (marker) {
      // 更新现有标记位置
      marker.setLatLng(latlng)
      console.log('Updated existing marker position')
    } else {
      // 创建新标记，使用Leaflet默认样式
      marker = L.marker(latlng as [number, number], {
        draggable: true,
        title: '拖拽调整位置' // 标记标题
      }).addTo(map)
      
      console.log('Created new marker:', marker)
      
      // 标记拖拽结束后更新坐标
      marker.on('dragend', (e: any) => {
        const lng = e.target.getLatLng().lng
        const lat = e.target.getLatLng().lat
        updateCoordinate(lng, lat)
        displayLngLat.value = `${lng},${lat}`
        regeoCode([lng, lat])
      })
    }
    
    // 设置地图中心
    map.setView(latlng)
    console.log('Map center set to:', lnglat)
    
    // 轻微调整缩放级别，确保标记清晰可见
    map.setZoom(15)
    console.log('Map zoom set to:', map.getZoom())
    
  } catch (e) {
    console.error('Failed to add marker:', e)
    ElMessage.error('添加标记失败')
  }
}

// 更新坐标
function updateCoordinate(lng: number, lat: number) {
  const coordinate: [number, number] = [lng, lat]
  emit('update:modelValue', coordinate)
  emit('coordinate-change', lng, lat)
}

// 地名搜索 - 使用Nominatim API
async function handleSearch() {
  if (!searchKeyword.value || !map) return
  
  try {
    const response = await fetch(`${nominatimBaseUrl}/search?format=json&q=${encodeURIComponent(searchKeyword.value)}&limit=5&addressdetails=1`)
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    const data = await response.json()
    
    if (data && data.length > 0) {
      const result = data[0]
      const lng = parseFloat(result.lon)
      const lat = parseFloat(result.lat)
      const address = result.display_name
      
      // 更新坐标和地址
      updateCoordinate(lng, lat)
      addMarker([lng, lat])
      displayLngLat.value = `${lng},${lat}`
      displayAddress.value = address
      emit('address-change', address)
    } else {
      ElMessage.warning('未找到匹配的地址')
    }
  } catch (err) {
    console.error('搜索地址失败:', err)
    ElMessage.error('搜索失败，请重试')
  }
}

// 监听modelValue变化
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue && map) {
      addMarker(newValue)
      // 更新显示的经纬度
      displayLngLat.value = `${newValue[0]},${newValue[1]}`
      // 转换地址
      regeoCode(newValue)
    }
  },
  { deep: true }
)

// 组件挂载时初始化地图
onMounted(() => {
  // 延迟初始化，确保DOM已渲染
  setTimeout(() => {
    initMap()
  }, 100)
})

// 组件卸载时清理
onUnmounted(() => {
  if (map) {
    // 移除所有事件监听器
    map.off('load')
    map.off('click')
    // 清除所有覆盖物
    map.eachLayer((layer: any) => {
      if (layer instanceof L.Marker || layer instanceof L.Popup) {
        map.removeLayer(layer)
      }
    })
    // 销毁地图实例
    map.remove()
    map = null
  }
  if (marker) {
    marker.off('dragend')
    marker = null
  }
})
</script>

<style scoped>
.map-picker {
  display: flex;
  flex-direction: column;
  gap: 16px;
  background: var(--bg-primary);
  border-radius: var(--border-radius-lg);
  padding: 20px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
  width: 100%;
}

/* 搜索栏样式 */
.search-bar {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.search-bar :deep(.el-input) {
  flex: 1;
  min-width: 200px;
}

/* 信息行样式 */
.info-row {
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 8px;
  padding: 12px;
  background: var(--bg-secondary);
  border-radius: var(--border-radius-md);
  border: 1px solid var(--border-light);
}

.info-item {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: nowrap;
  flex: 1;
  min-width: 280px;
}

.info-label {
  white-space: nowrap;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
  min-width: 60px;
}

.info-item :deep(.el-input) {
  flex: 1;
  min-width: 180px;
  max-width: 350px;
}

.info-item :deep(.el-input__wrapper) {
  border-radius: var(--border-radius-md);
  border-color: var(--border-light);
  transition: all var(--transition-fast);
}

.info-item :deep(.el-input__wrapper:hover) {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--bg-tertiary);
}

.info-item :deep(.el-button) {
  min-width: 140px;
  border-radius: var(--border-radius-md);
  transition: all var(--transition-fast);
}

.info-item :deep(.el-button:hover) {
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.search-bar :deep(.el-input__wrapper) {
  border-radius: var(--border-radius-md);
  border-color: var(--border-light);
  transition: all var(--transition-fast);
}

.search-bar :deep(.el-input__wrapper:hover) {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--bg-tertiary);
}

.search-bar :deep(.el-input__wrapper.is-focus) {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--bg-tertiary);
}

.search-bar :deep(.el-button) {
  border-radius: var(--border-radius-md);
  transition: all var(--transition-fast);
}

/* 地图容器样式 */
.map-container {
  position: relative;
  border-radius: var(--border-radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-light);
}

.map {
  width: 100%;
  height: 450px;
  background: var(--bg-secondary);
}

.map-tip {
  position: absolute;
  bottom: 16px;
  right: 16px;
  background: rgba(255, 255, 255, 0.95);
  padding: 8px 16px;
  border-radius: var(--border-radius-md);
  font-size: 13px;
  color: var(--text-secondary);
  z-index: 100;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
  backdrop-filter: blur(10px);
}

/* 加载状态样式 */
.map-loading {
  position: relative;
  width: 100%;
  height: 100%;
  background: var(--bg-secondary);
}

.loading-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: var(--text-secondary);
  font-size: 15px;
  z-index: 10;
  background: rgba(255, 255, 255, 0.95);
  padding: 12px 24px;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  backdrop-filter: blur(10px);
}

:deep(.el-skeleton) {
  background: var(--bg-secondary);
}

/* 错误状态样式 */
.map-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  background: var(--bg-secondary);
  color: var(--text-secondary);
  padding: 30px;
  text-align: center;
  border-radius: var(--border-radius-lg);
}

.error-icon {
  font-size: 64px;
  color: var(--warning-color);
  margin-bottom: 20px;
  opacity: 0.8;
}

.error-text {
  font-size: 15px;
  margin-bottom: 20px;
  line-height: 1.6;
  color: var(--text-regular);
}

.map-error .el-button {
  margin-top: 12px;
  border-radius: var(--border-radius-md);
  padding: 8px 24px;
  font-weight: 600;
  transition: all var(--transition-fast);
}

.map-error .el-button:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}
</style>