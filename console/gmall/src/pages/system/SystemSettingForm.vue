<template>
  <div class="setting-form-container">
    <template v-if="setting.set_template">
      <!-- 使用动态表单组件 -->
      <DynamicForm
        :template="setting.set_template"
        :form-data="formData"
        @update="handleFormUpdate"
      />
    </template>
    <!-- 默认配置（JSON编辑器，当没有模板时使用） -->
    <template v-else>
      <el-form :model="formData" label-width="180px" class="settings-form">
        <el-form-item label="配置内容">
          <el-input
            v-model="jsonContent"
            type="textarea"
            placeholder="请输入配置内容（JSON格式）"
            :rows="12"
            monospaced
          />
        </el-form-item>
      </el-form>
    </template>
    
    <div style="margin-top: 20px;">
      <el-button type="primary" :loading="saving" @click="handleSave">保存配置</el-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { updateSystemSettingApi, type SystemSetting } from '../../api/systemSetting'
import DynamicForm from '../../components/DynamicForm.vue'

// Props
const props = defineProps<{
  setting: SystemSetting
}>()

// Emits
const emit = defineEmits<{
  save: [setting: SystemSetting]
}>()

// 表单数据
const formData = reactive<any>({})
// JSON内容（用于默认配置的编辑）
const jsonContent = ref('')
// 保存状态
const saving = ref(false)

// 处理表单更新
function handleFormUpdate(newFormData: any) {
  // 更新表单数据
  Object.assign(formData, newFormData)
}

// 初始化表单数据
function initFormData() {
  // 清空表单数据
  Object.keys(formData).forEach(key => {
      delete (formData as any)[key]

  })
  
  if (props.setting.set_content) {
    try {
      // 解析JSON字符串为对象
      const contentObj = typeof props.setting.set_content === 'string' 
        ? JSON.parse(props.setting.set_content) 
        : props.setting.set_content
      
      // 复制配置内容到表单数据
      Object.assign(formData, contentObj)
      
      // 如果是默认配置，设置JSON内容
      if (!['site_config', 'wechat_pay', 'wechat', 'alipay', 'sms', 'print'].includes(props.setting.set_tag)) {
        jsonContent.value = JSON.stringify(contentObj, null, 2)
      }
    } catch (error) {
      console.error('解析配置内容失败:', error)
      // 解析失败时，使用空对象
      Object.assign(formData, {})
    }
  } else {
    // 没有content时，使用空对象
    Object.assign(formData, {})
  }
}

// 处理保存配置
async function handleSave() {
  saving.value = true
  try {
    let content: any = formData
    
    // 如果是默认配置，解析JSON内容
    if (!['site_config', 'wechat_pay', 'wechat', 'alipay', 'sms', 'print'].includes(props.setting.set_tag)) {
      try {
        content = JSON.parse(jsonContent.value)
      } catch (error) {
        ElMessage.error('配置内容必须是有效的JSON格式')
        return
      }
    }
    
    // 调用API更新配置
    await updateSystemSettingApi({
      id: props.setting.id,
      tag: props.setting.set_tag,
      name: props.setting.set_name,
      content: JSON.stringify(content)
    })
    
    ElMessage.success('配置保存成功')
    emit('save', {
      ...props.setting,
      set_content: content
    })
  } catch (error) {
    ElMessage.error('配置保存失败')
    console.error('保存配置失败:', error)
  } finally {
    saving.value = false
  }
}

// 监听setting变化，重新初始化表单数据
watch(
  () => props.setting,
  () => {
    initFormData()
  },
  { deep: true }
)

// 组件挂载时初始化表单数据
onMounted(() => {
  initFormData()
})
</script>

<style scoped>
.setting-form-container {
  margin-top: 20px;
}

.settings-form {
  margin-top: 20px;
}

.v2-sub-title {
  font-weight: 700;
  font-size: 14px;
  margin: 16px 0 8px 0;
  color: #606266;
  background-color: #f5f7fa;
  padding: 4px 12px;
  border-radius: 4px;
  display: inline-block;
}

.logo-preview {
  margin-top: 12px;
  border: 1px dashed #dcdfe6;
  padding: 8px;
  border-radius: 4px;
  display: inline-block;
}

.logo-uploader {
  margin-top: 8px;
}

.logo-uploader :deep(.el-upload-list) {
  margin-top: 8px;
}

.cert-uploader {
  margin-top: 8px;
}

.cert-uploader :deep(.el-upload-list) {
  margin-top: 8px;
}

.cert-preview {
  margin-top: 12px;
  display: inline-block;
}
</style>