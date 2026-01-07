<template>
  <div class="image-upload">
    <el-upload
      :multiple="multiple"
      :show-file-list="false"
      :http-request="handleUpload"
      :before-upload="beforeUpload"
      :disabled="disabled"
    >
      <el-button type="primary" plain :loading="loading">
        {{ buttonText }}
      </el-button>
    </el-upload>
    <div class="tip" v-if="tip">{{ tip }}</div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ElMessage } from 'element-plus'

const props = defineProps<{
  buttonText: string
  tip?: string
  multiple?: boolean
  disabled?: boolean
  beforeUpload?: (file: File) => boolean | Promise<boolean>
}>()

const emit = defineEmits<{
  (e: 'success', urls: string[]): void
  (e: 'error', error: any): void
}>()

const loading = ref(false)

async function handleUpload(options: any) {
  const { file, onSuccess, onError } = options
  loading.value = true
  try {
    // 模拟上传，实际项目中替换为真实上传接口
    // const res = await uploadApi(file)
    // 这里使用模拟URL
    const mockUrl = `https://picsum.photos/seed/${Date.now()}/400/300`
    
    // 延迟模拟网络请求
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    const urls = props.multiple ? [mockUrl, mockUrl] : [mockUrl]
    emit('success', urls)
    onSuccess && onSuccess(urls)
  } catch (error: any) {
    ElMessage.error('上传失败：' + (error.message || '未知错误'))
    emit('error', error)
    onError && onError(error)
  } finally {
    loading.value = false
  }
}

function beforeUpload(file: File) {
  if (props.beforeUpload) {
    return props.beforeUpload(file)
  }
  
  // 默认图片验证
  const isImage = file.type.startsWith('image/')
  if (!isImage) {
    ElMessage.error('只能上传图片文件')
    return false
  }
  
  const isLt2M = file.size / 1024 / 1024 < 2
  if (!isLt2M) {
    ElMessage.error('图片大小不能超过2MB')
    return false
  }
  
  return true
}
</script>

<style scoped>
.image-upload {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.tip {
  color: var(--sub);
  font-size: 12px;
  line-height: 1.5;
}
</style>