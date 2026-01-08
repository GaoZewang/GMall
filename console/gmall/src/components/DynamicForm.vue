<template>
  <el-form :model="formData" label-width="180px" class="dynamic-form">
    <template v-for="(item, index) in formItems" :key="index">
      <!-- 输入框 -->
      <el-form-item :label="item.label" v-if="item.type === 'input' || item.type === 'password'">
        <el-input
          v-model="formData[item.field]"
          :placeholder="item.placeholder"
          :maxlength="item.maxlength"
          :show-word-limit="item.showWordLimit"
          :type="item.type === 'password' ? 'password' : 'text'"
          :show-password="item.showPassword"
        />
      </el-form-item>

      <!-- 多行文本框 -->
      <el-form-item :label="item.label" v-else-if="item.type === 'textarea'">
        <el-input
          v-model="formData[item.field]"
          type="textarea"
          :placeholder="item.placeholder"
          :rows="item.rows"
          :maxlength="item.maxlength"
          :show-word-limit="item.showWordLimit"
          :monospaced="item.monospaced"
        />
      </el-form-item>

      <!-- 开关 -->
      <el-form-item :label="item.label" v-else-if="item.type === 'switch'">
        <el-switch v-model="formData[item.field]" />
      </el-form-item>

      <!-- 下拉选择 -->
      <el-form-item :label="item.label" v-else-if="item.type === 'select'">
        <el-select v-model="formData[item.field]" :placeholder="item.placeholder">
          <el-option
            v-for="option in item.options"
            :key="option.value"
            :label="option.label"
            :value="option.value"
          />
        </el-select>
      </el-form-item>

      <!-- 单选组 -->
      <el-form-item :label="item.label" v-else-if="item.type === 'radioGroup'">
        <el-radio-group v-model="formData[item.field]">
          <el-radio
            v-for="option in item.options"
            :key="option.value"
            :label="option.value"
          >{{ option.label }}</el-radio>
        </el-radio-group>
      </el-form-item>

      <!-- 数字输入框 -->
      <el-form-item :label="item.label" v-else-if="item.type === 'inputNumber'">
        <el-input-number
          v-model="formData[item.field]"
          :min="item.min"
          :max="item.max"
          :placeholder="item.placeholder"
        />
      </el-form-item>

      <!-- 滑块 -->
      <el-form-item :label="item.label" v-else-if="item.type === 'slider'">
        <el-slider
          v-model="formData[item.field]"
          :min="item.min"
          :max="item.max"
          :marks="item.marks"
        />
      </el-form-item>

      <!-- 文件上传 -->
      <el-form-item :label="item.label" v-else-if="item.type === 'upload'">
        <el-upload
          class="logo-uploader"
          :limit="item.limit"
          :accept="item.accept"
          :file-list="fileLists[item.field] || []"
          :on-success="(file) => handleUploadSuccess(file, item.field)"
          :on-remove="() => handleUploadRemove(item.field)"
          :before-upload="beforeUpload"
          :http-request="(options) => handleUploadRequest(options, item.field)"
          action="#"
          :auto-upload="true"
        >
          <el-button type="primary">点击上传</el-button>
          <template #tip>
            <div class="el-upload__tip">仅支持{{ item.accept.replace(/\./g, '').replace(/,/g, '、') }}格式文件</div>
          </template>
        </el-upload>
        <div v-if="formData[item.field]" class="cert-preview">
          <el-tag size="small">已上传</el-tag>
          <!-- 如果是图片，可以显示预览 -->
          <el-image
            v-if="item.field === 'siteLogo' && formData[item.field]"
            :src="formData[item.field]"
            fit="cover"
            style="width: 200px; height: 60px; border-radius: 4px; margin-top: 8px;"
          />
        </div>
      </el-form-item>
    </template>
  </el-form>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { adminUploadSingle } from '../api/upload'

// Props
const props = defineProps<{
  template: string
  formData: any
}>()

// Emits
const emit = defineEmits<{
  update: [formData: any]
}>()

// 表单数据
const formData = reactive<any>({ ...props.formData })

// 文件列表
const fileLists = reactive<any>({})

// 解析模板
const formItems = ref<any[]>([])

// 初始化表单
function initForm() {
  try {
    const templateObj = JSON.parse(props.template)
    formItems.value = templateObj.form || []
    
    // 初始化文件列表
    formItems.value.forEach(item => {
      if (item.type === 'upload' && formData[item.field]) {
        fileLists[item.field] = [{
          name: `${item.field}_${Date.now()}`,
          url: formData[item.field],
          uid: `${item.field}_${Date.now()}`
        }]
      }
    })
  } catch (error) {
    console.error('解析模板失败:', error)
    formItems.value = []
  }
}

// 文件上传前验证
function beforeUpload(file: any) {
  const allowedTypes = ['.jpg', '.jpeg', '.png', '.gif', '.svg', '.pem', '.key', '.crt']
  const fileExt = file.name.substring(file.name.lastIndexOf('.')).toLowerCase()
  if (!allowedTypes.includes(fileExt)) {
    ElMessage.error('文件格式不支持')
    return false
  }
  
  const isLt2M = file.size / 1024 / 1024 < 2
  if (!isLt2M) {
    ElMessage.error('文件大小不能超过2MB')
    return false
  }
  
  return true
}

// 自定义文件上传处理
async function handleUploadRequest(options: any, field: string) {
  try {
    const { file } = options
    let scene = 'default'
    if (field.includes('wechat')) {
      scene = 'wechat'
    } else if (field.includes('alipay')) {
      scene = 'alipay'
    } else if (field.includes('logo')) {
      scene = 'logo'
    }
    
    const url = await adminUploadSingle(file, scene)
    formData[field] = url
    fileLists[field] = [file]
    ElMessage.success('文件上传成功')
    options.onSuccess(file)
    emit('update', formData)
  } catch (error) {
    console.error('文件上传失败:', error)
    ElMessage.error('文件上传失败')
    options.onError(error)
  }
}

// 处理文件上传成功
function handleUploadSuccess(file: any, field: string) {
  fileLists[field] = [file]
}

// 处理文件移除
function handleUploadRemove(field: string) {
  fileLists[field] = []
  formData[field] = ''
  emit('update', formData)
  ElMessage.success('文件已移除')
}

// 监听表单数据变化
watch(
  () => formData,
  (newVal) => {
    emit('update', newVal)
  },
  { deep: true }
)

// 监听模板变化
watch(
  () => props.template,
  () => {
    initForm()
  },
  { immediate: true }
)

// 监听外部formData变化
watch(
  () => props.formData,
  (newVal) => {
    Object.assign(formData, newVal)
  },
  { deep: true, immediate: true }
)

// 组件挂载时初始化
onMounted(() => {
  initForm()
})
</script>

<style scoped>
.dynamic-form {
  margin-top: 20px;
}

.cert-preview {
  margin-top: 12px;
  display: inline-block;
}

.logo-uploader {
  margin-top: 8px;
}

.logo-uploader :deep(.el-upload-list) {
  margin-top: 8px;
}
</style>
