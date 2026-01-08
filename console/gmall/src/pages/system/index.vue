<template>
  <div class="page">
    <el-card shadow="never">
      <!-- <div class="title">系统设置</div> -->
      <el-tabs v-model="activeTab" class="settings-tabs" style="margin-top: 20px;" v-loading="loading || loadingDetail">
        <!-- 动态生成配置标签页 -->
        <el-tab-pane
          v-for="setting in settings"
          :key="setting.id"
          :label="setting.set_name"
          :name="setting.id.toString()"
        >
          <SystemSettingForm
            v-if="currentSetting"
            :setting="currentSetting"
            @save="handleSave"
          />
          <div v-else-if="loadingDetail" class="loading-detail">
            <el-skeleton :rows="5" animated />
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { ElMessage } from 'element-plus'
import SystemSettingForm from './SystemSettingForm.vue'
import { getSystemSettingListApi, getSystemSettingInfoApi, type SystemSetting, type SystemSettingListItem } from '../../api/systemSetting'

// 配置列表（只包含基本信息）
const settings = ref<SystemSettingListItem[]>([])
// 完整配置详情映射
const settingDetails = ref<Map<number, SystemSetting>>(new Map())
// 加载状态
const loading = ref(false)
// 当前激活的标签页
const activeTab = ref('')
// 当前加载详情的状态
const loadingDetail = ref(false)

// 计算当前选中的完整配置
const currentSetting = computed(() => {
  const id = parseInt(activeTab.value)
  return settingDetails.value.get(id)
})

// 加载配置列表
async function loadSettings() {
  loading.value = true
  try {
    const data = await getSystemSettingListApi({
      page: 1,
      per_page: 100
    })
    settings.value = data.list || []
    // 设置默认激活的标签页
    if (settings.value.length > 0) {
      activeTab.value = settings.value[0].id.toString()

      // 加载默认选中的配置详情
      await loadSettingDetail(settings.value[0].id)
    }
  } catch (error) {
    ElMessage.error('加载配置列表失败')
    console.error('加载配置列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 加载配置详情
async function loadSettingDetail(id: number) {
  // 如果已经有详情，不需要重复加载
  if (settingDetails.value.has(id)) {
    return
  }
  
  loadingDetail.value = true
  try {
    const setting = await getSystemSettingInfoApi({ id })
    settingDetails.value.set(id, setting)
  } catch (error) {
    ElMessage.error('加载配置详情失败')
    console.error('加载配置详情失败:', error)
  } finally {
    loadingDetail.value = false
  }
}

// 监听标签页切换，加载对应配置详情
watch(
  () => activeTab.value,
  async (newVal) => {
    if (newVal) {
      const id = parseInt(newVal)
      await loadSettingDetail(id)
    }
  }
)

// 处理保存配置
async function handleSave(setting: SystemSetting) {
  // 更新详情映射
  settingDetails.value.set(setting.id, setting)
  // 保存逻辑已在SystemSettingForm组件中处理，这里不需要重复显示提示
}

// 组件挂载时加载配置列表
onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.title {
  font-weight: 900;
  font-size: 18px;
}

.settings-tabs :deep(.el-tabs__content) {
  padding: 20px 0;
  width: 80%;
}

.settings-tabs :deep(.el-slider) {
  padding: 20px 0;
  width: 98%;
}

</style>