<template>
  <div class="page">
    <el-card shadow="never">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px" class="form">
        <!-- 基本信息 -->
        <div class="sectionTitle">基本信息</div>
        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="商户ID" prop="merchant_id">
              <el-input-number v-model="form.merchant_id" :min="1" style="width: 100%" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="管理员ID" prop="admin_user_id">
              <el-input-number v-model="form.admin_user_id" :min="1" style="width: 100%" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="店铺名称" prop="name">
              <el-input v-model="form.name" placeholder="请输入店铺名称" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="联系电话" prop="contact_phone">
              <el-input v-model="form.contact_phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>

          <el-col :xs="24">
            <el-form-item label="店铺地址" prop="address">
              <el-input v-model="form.address" type="textarea" :rows="3" placeholder="请输入店铺地址" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="状态" prop="status">
              <el-switch
                v-model="form.status"
                :active-value="1"
                :inactive-value="0"
              />
              <span class="statusText">{{ form.status === 1 ? '启用' : '禁用' }}</span>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item>
          <el-button type="primary" @click="submit" :loading="loading">保存</el-button>
          <el-button @click="resetForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, FormInstance, FormRules } from 'element-plus'
import { 
  adminShopCreateApi, 
  adminShopInfoApi, 
  adminShopUpdateApi,
  type Shop 
} from '../../api/shop'

const route = useRoute()
const router = useRouter()

const formRef = ref<FormInstance>()
const loading = ref(false)

const isEdit = computed(() => !!route.params.id)
const shopId = computed(() => Number(route.params.id || 0))

const form = reactive({
  id: 0,
  merchant_id: 0,
  admin_user_id: 0,
  name: '',
  address: '',
  contact_phone: '',
  status: 1
})

const rules: FormRules = {
  merchant_id: [
    { required: true, message: '请输入商户ID', trigger: 'blur' },
    { type: 'number', min: 1, message: '商户ID必须大于0', trigger: 'blur' }
  ],
  admin_user_id: [
    { required: true, message: '请输入管理员ID', trigger: 'blur' },
    { type: 'number', min: 1, message: '管理员ID必须大于0', trigger: 'blur' }
  ],
  name: [
    { required: true, message: '请输入店铺名称', trigger: 'blur' },
    { min: 1, max: 255, message: '店铺名称长度在 1 到 255 个字符', trigger: 'blur' }
  ],
  contact_phone: [
    { pattern: /^1[3-9]\d{9}$/, message: '请输入有效的手机号码', trigger: 'blur' }
  ],
  address: [
    { max: 500, message: '店铺地址长度不能超过 500 个字符', trigger: 'blur' }
  ]
}

async function loadShopInfo() {
  if (!isEdit.value) return
  
  loading.value = true
  try {
    const data = await adminShopInfoApi(shopId.value)
    form.id = data.id
    form.merchant_id = data.merchant_id
    form.admin_user_id = data.admin_user_id
    form.name = data.name
    form.address = data.address
    form.contact_phone = data.contact_phone
    form.status = data.status
  } finally {
    loading.value = false
  }
}

async function submit() {
  if (!formRef.value) return
  await formRef.value.validate()
  
  loading.value = true
  try {
    if (isEdit.value) {
      await adminShopUpdateApi({
        id: form.id,
        merchant_id: form.merchant_id,
        admin_user_id: form.admin_user_id,
        name: form.name,
        address: form.address,
        contact_phone: form.contact_phone,
        status: form.status
      })
      ElMessage.success('更新成功')
    } else {
      await adminShopCreateApi({
        merchant_id: form.merchant_id,
        admin_user_id: form.admin_user_id,
        name: form.name,
        address: form.address,
        contact_phone: form.contact_phone,
        status: form.status
      })
      ElMessage.success('创建成功')
    }
    router.push('/shop')
  } catch (error) {
    console.error('保存店铺失败:', error)
    ElMessage.error('保存失败')
  } finally {
    loading.value = false
  }
}

function resetForm() {
  if (!formRef.value) return
  formRef.value.resetFields()
}

onMounted(() => {
  if (isEdit.value) {
    loadShopInfo()
  }
})
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;}
.form{max-width: 800px;}
.sectionTitle{font-weight:900;margin:10px 0;}
.statusText{margin-left: 8px;color: var(--sub);font-size: 14px;}
</style>