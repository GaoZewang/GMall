<template>
  <div class="page">
    <el-card shadow="never">


      <el-form ref="formRef" :model="form" :rules="rules" label-width="96px" class="form">
        <!-- 基本信息 -->
        <div class="sectionTitle">基本信息</div>
        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="关联管理人员ID" prop="admin_user_id">
              <el-input-number v-model="form.admin_user_id" :min="1" style="width: 100%" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="商户名称" prop="name">
              <el-input v-model="form.name" placeholder="请输入商户名称" />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 图片（上传+URL） -->
        <div class="sectionTitle">商户Logo</div>

        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="Logo" prop="logo">
              <div class="uploadLine">
                <el-upload
                  class="uploader"
                  :show-file-list="false"
                  :http-request="uploadLogoRequest"
                  :before-upload="beforeImage"
                >
                  <el-button type="primary" plain :loading="uploadingLogo">
                    {{ form.logo ? '重新上传' : '上传Logo' }}
                  </el-button>
                </el-upload>

                <el-input v-model="form.logo" placeholder="也可粘贴图片URL" />
              </div>
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="预览">
              <el-image
                v-if="form.logo"
                :src="form.logo"
                fit="cover"
                style="width: 72px; height: 72px; border-radius: 14px"
                :preview-src-list="[form.logo]"
                preview-teleported
              />
              <div v-else class="tip">上传Logo后显示预览</div>
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 联系信息 -->
        <div class="sectionTitle">联系信息</div>
        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="联系电话" prop="contact_phone">
              <el-input v-model="form.contact_phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="地址" prop="address">
              <el-input v-model="form.address" placeholder="请输入地址" />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 地理位置 -->
        <div class="sectionTitle">地理位置</div>
        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="纬度" prop="lat">
              <el-input-number v-model="form.lat" :min="-90" :max="90" :step="0.000001" style="width: 100%" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="经度" prop="lng">
              <el-input-number v-model="form.lng" :min="-180" :max="180" :step="0.000001" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 地图坐标拾取 -->
        <div class="sectionTitle">地图坐标拾取</div>
        <el-row :gutter="12">
          <el-col :xs="24">
            <el-form-item label="地图">
              <MapPicker 
                v-model="mapCoordinate" 
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </el-card>

    <!-- 底部居中保存按钮 -->
    <div class="submit-bar">
      <el-button type="primary" size="large" :loading="submitting" @click="submit">保存</el-button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import type { FormInstance, FormRules, UploadRequestOptions } from 'element-plus'
import { ElMessage } from 'element-plus'
import { adminMerchantCreateApi, adminMerchantInfoApi, adminMerchantUpdateApi } from '../../api/merchant'
import { adminUploadSingle } from '../../api/upload'
import PageHeader from '../../components/PageHeader.vue'
// import MapPicker from '../../components/MapPicker.vue'
import MapPicker from '../../components/MapCoordinatePicker/index.vue'

const router = useRouter()
const route = useRoute()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const loading = ref(false)

// 获取商户ID
const merchantId = computed(() => {
  const id = route.params.id
  return id ? Number(id) : 0
})

// 判断是否为编辑模式
const isEdit = computed(() => merchantId.value > 0)

const form = reactive({
  admin_user_id: 0,
  name: '',
  logo: '',
  address: '',
  contact_phone: '',
  lat: 0,
  lng: 0
})

const rules: FormRules = {
  admin_user_id: [{ required: true, message: '请输入关联管理人员ID', trigger: 'blur' }],
  name: [{ required: true, message: '请输入商户名称', trigger: 'blur' }],
  contact_phone: [{ required: true, message: '请输入联系电话', trigger: 'blur' }],
  address: [{ required: true, message: '请输入地址', trigger: 'blur' }],
  lat: [{ required: true, message: '请输入纬度', trigger: 'blur' }],
  lng: [{ required: true, message: '请输入经度', trigger: 'blur' }],
  logo: [{ required: true, message: '请上传/填写Logo', trigger: 'blur' }],
}

// 地图坐标 - 适配MapCoordinatePicker的Location类型
const mapCoordinate = ref({
  lng: form.lng || null,
  lat: form.lat || null,
  address: form.address || ''
})

// 监听地图坐标变化，同步到表单
watch(
  () => mapCoordinate.value,
  (newValue) => {
    form.lng = newValue.lng || 0
    form.lat = newValue.lat || 0
    form.address = newValue.address || ''
  },
  { deep: true }
)

// 监听表单坐标变化，同步到地图
watch(
  [() => form.lng, () => form.lat, () => form.address],
  ([newLng, newLat, newAddress]) => {
    mapCoordinate.value = {
      lng: newLng || null,
      lat: newLat || null,
      address: newAddress || ''
    }
  },
  { deep: true }
)

/** 上传 */
const uploadingLogo = ref(false)

function beforeImage(file: File) {
  const isImg = file.type.startsWith('image/')
  const okSize = file.size / 1024 / 1024 <= 5
  if (!isImg) ElMessage.error('只能上传图片文件')
  if (!okSize) ElMessage.error('图片大小不能超过 5MB')
  return isImg && okSize
}

async function uploadLogoRequest(options: UploadRequestOptions) {
  const file = options.file as File
  uploadingLogo.value = true
  try {
    const url = await adminUploadSingle(file, 'merchant')
    form.logo = url
    ElMessage.success('Logo上传成功')
    options.onSuccess?.({ url } as any)
  } catch (e) {
    options.onError?.(e as any)
  } finally {
    uploadingLogo.value = false
  }
}

/** 构建 payload */
function buildPayload() {
  return {
    admin_user_id: form.admin_user_id,
    name: form.name.trim(),
    logo: form.logo.trim(),
    address: form.address.trim(),
    contact_phone: form.contact_phone.trim(),
    lat: form.lat,
    lng: form.lng
  }
}

// 加载商户详情
async function loadMerchantInfo() {
  if (!isEdit.value) return
  
  loading.value = true
  try {
    const res = await adminMerchantInfoApi({ id: merchantId.value })
    const data = res as any
    
    // 填充表单数据
    form.admin_user_id = data.admin_user_id || 0
    form.name = data.name || ''
    form.logo = data.logo || ''
    form.address = data.address || ''
    form.contact_phone = data.contact_phone || ''
    form.lat = data.lat || 0
    form.lng = data.lng || 0
    
    // 更新地图坐标
    mapCoordinate.value = {
      lng: data.lng || null,
      lat: data.lat || null,
      address: data.address || ''
    }
  } catch (error) {
    ElMessage.error('加载商户详情失败')
    console.error('加载商户详情失败:', error)
    router.push('/merchant')
  } finally {
    loading.value = false
  }
}

// 构建更新payload
function buildUpdatePayload() {
  const payload = buildPayload()
  return {
    ...payload,
    id: merchantId.value
  }
}

async function submit() {
  await formRef.value?.validate()

  let payload: any
  if (isEdit.value) {
    payload = buildUpdatePayload()
  } else {
    payload = buildPayload()
  }

  submitting.value = true
  try {
    if (isEdit.value) {
      await adminMerchantUpdateApi(payload)
      ElMessage.success('更新成功')
      router.push('/merchant') // 更新成功后跳转到列表页
    } else {
      const res = await adminMerchantCreateApi(payload as any)
      ElMessage.success('创建成功')
      router.push('/merchant') // 创建成功后跳转到列表页
    }
  } finally {
    submitting.value = false
  }
}

// 组件挂载时初始化
onMounted(async () => {
  if (isEdit.value) {
    await loadMerchantInfo()
  }
  
  // 更新地图坐标
  mapCoordinate.value = {
    lng: form.lng || null,
    lat: form.lat || null,
    address: form.address || ''
  }
})

</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;padding-bottom:88px;} /* 预留右下角保存按钮空间 */

.form{margin-top:12px;}
.sectionTitle{font-weight:900;margin:10px 0;}
.sectionTop{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;}
.sectionOps{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.mt12{margin-top:12px;}

.tip{color:var(--sub);font-size:12px;line-height:1.5;}

.uploadLine{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.uploader{display:inline-block;}

/* 搜索栏样式 */
.search-bar {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

/* 地图容器样式 */
.map-container {
  position: relative;
  margin-top: 10px;
}

.map {
  width: 100%;
  height: 400px;
  border: 1px solid var(--border-color);
  border-radius: var(--border-radius-md);
}

.map-tip {
  position: absolute;
  bottom: 10px;
  right: 10px;
  background: rgba(255, 255, 255, 0.8);
  padding: 5px 10px;
  border-radius: var(--border-radius-sm);
  font-size: 12px;
  color: var(--text-secondary);
  z-index: 100;
}

/* 底部居中保存按钮 */
.submit-bar {
  position: sticky;
  border-radius: 5px;
  bottom: 0;
  padding: 16px 0;
  background: #fff;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: center;
  z-index: 10;
}

.submit-bar .el-button {
  min-width: 220px;
  height: 48px;
  font-size: 16px;
  font-weight: 600;
}
</style>