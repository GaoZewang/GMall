<template>
  <div class="page">
    <el-card shadow="never">
      <PageHeader :title="'查看商户'">
        <el-button @click="back">返回</el-button>
        <el-button type="primary" @click="edit">编辑</el-button>
      </PageHeader>

      <el-form :model="merchantInfo" label-width="120px" class="detail-form">
        <!-- 基本信息 -->
        <div class="sectionTitle">基本信息</div>
        <el-row :gutter="20">
          <el-col :xs="24" :md="12">
            <el-form-item label="商户ID">
              <el-input v-model="merchantInfo.id" readonly />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :md="12">
            <el-form-item label="商户名称">
              <el-input v-model="merchantInfo.name" readonly />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :xs="24" :md="12">
            <el-form-item label="关联管理人员ID">
              <el-input v-model="merchantInfo.admin_user_id" readonly />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :md="12">
            <el-form-item label="联系电话">
              <el-input v-model="merchantInfo.contact_phone" readonly />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 图片 -->
        <div class="sectionTitle">商户Logo</div>
        <el-row :gutter="20">
          <el-col :xs="24" :md="12">
            <el-form-item label="Logo URL">
              <el-input v-model="merchantInfo.logo" readonly />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :md="12">
            <el-form-item label="Logo预览">
              <el-image
                v-if="merchantInfo.logo"
                :src="merchantInfo.logo"
                fit="cover"
                style="width: 120px; height: 120px; border-radius: 8px"
                :preview-src-list="[merchantInfo.logo]"
                preview-teleported
              />
              <div v-else class="no-logo">暂无Logo</div>
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 地理位置 -->
        <div class="sectionTitle">地理位置</div>
        <el-row :gutter="20">
          <el-col :xs="24" :md="12">
            <el-form-item label="地址">
              <el-input v-model="merchantInfo.address" readonly />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :xs="24" :md="12">
            <el-form-item label="纬度">
              <el-input v-model="merchantInfo.lat" readonly />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :md="12">
            <el-form-item label="经度">
              <el-input v-model="merchantInfo.lng" readonly />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 地图坐标 -->
        <div class="sectionTitle">地图位置</div>
        <el-row :gutter="20">
          <el-col :xs="24">
            <el-form-item label="地图">
              <MapPicker 
                v-model="mapCoordinate" 
                :disabled="true"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 其他信息 -->
        <div class="sectionTitle">其他信息</div>
        <el-row :gutter="20">
          <el-col :xs="24" :md="12">
            <el-form-item label="创建时间">
              <el-input v-model="merchantInfo.created_at" readonly />
            </el-form-item>
          </el-col>
          <el-col :xs="24" :md="12">
            <el-form-item label="更新时间">
              <el-input v-model="merchantInfo.updated_at" readonly />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { adminMerchantInfoApi } from '../../api/merchant'
import PageHeader from '../../components/PageHeader.vue'
import MapPicker from '../../components/MapCoordinatePicker/index.vue'

const router = useRouter()
const route = useRoute()

// 商户ID
const merchantId = ref<number>(0)

// 商户信息
const merchantInfo = reactive({
  id: '',
  name: '',
  admin_user_id: '',
  contact_phone: '',
  logo: '',
  address: '',
  lat: '',
  lng: '',
  created_at: '',
  updated_at: ''
})

// 地图坐标
const mapCoordinate = ref({
  lng: null,
  lat: null,
  address: ''
})

// 加载商户详情
async function loadMerchantInfo() {
  try {
    const res = await adminMerchantInfoApi({ id: merchantId.value })
    const data = res as any
    
    // 填充表单数据
    merchantInfo.id = data.id || ''
    merchantInfo.name = data.name || ''
    merchantInfo.admin_user_id = data.admin_user_id || ''
    merchantInfo.contact_phone = data.contact_phone || ''
    merchantInfo.logo = data.logo || ''
    merchantInfo.address = data.address || ''
    merchantInfo.lat = data.lat || ''
    merchantInfo.lng = data.lng || ''
    merchantInfo.created_at = data.created_at || ''
    merchantInfo.updated_at = data.updated_at || ''
    
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
  }
}

// 返回列表页
function back() {
  router.push('/merchant')
}

// 跳转到编辑页
function edit() {
  router.push(`/merchant/edit/${merchantId.value}`)
}

// 组件挂载时加载数据
onMounted(() => {
  // 获取商户ID
  const id = route.params.id
  if (id) {
    merchantId.value = Number(id)
    loadMerchantInfo()
  } else {
    router.push('/merchant')
  }
})
</script>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding-bottom: 20px;
}

.sectionTitle {
  font-weight: 900;
  margin: 16px 0 12px 0;
  font-size: 16px;
}

.detail-form {
  margin-top: 20px;
}

.detail-form :deep(.el-input__wrapper) {
  background-color: #f5f7fa;
  border-color: #e4e7ed;
}

.no-logo {
  width: 120px;
  height: 120px;
  background-color: #f5f7fa;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #909399;
  font-size: 14px;
  border-radius: 4px;
  border: 1px solid #e4e7ed;
}
</style>
