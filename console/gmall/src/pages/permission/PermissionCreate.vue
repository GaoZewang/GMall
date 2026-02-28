<template>
  <div class="page">
    <el-card shadow="never">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px" class="form">
        <!-- 基本信息 -->
        <div class="sectionTitle">基本信息</div>
        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="权限名称" prop="name">
              <el-input v-model="form.name" placeholder="请输入权限名称" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="权限标识" prop="code">
              <el-input v-model="form.code" placeholder="请输入权限标识" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="权限路径" prop="route_url">
              <el-input v-model="form.route_url" placeholder="请输入权限路径" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="权限图标" prop="icon">
              <el-input v-model="form.icon" placeholder="请输入权限图标" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="父级权限" prop="parent_id">
              <el-select v-model="form.parent_id" placeholder="请选择父级权限">
                <el-option label="顶级权限" :value="0" />
                <el-option
                  v-for="permission in permissionOptions"
                  :key="permission.id"
                  :label="permission.name"
                  :value="permission.id"
                />
              </el-select>
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="是否显示" prop="is_show">
              <el-switch
                v-model="form.is_show"
                :active-value="1"
                :inactive-value="0"
              />
              <span class="statusText">{{ form.is_show === 1 ? '是' : '否' }}</span>
            </el-form-item>
          </el-col>

          <el-col :xs="24">
            <el-form-item label="权限描述" prop="description">
              <el-input v-model="form.description" type="textarea" :rows="3" placeholder="请输入权限描述" />
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
  adminPermissionCreateApi, 
  adminPermissionInfoApi, 
  adminPermissionUpdateApi,
  adminPermissionListApi,
  type Permission 
} from '../../api/permission'

const route = useRoute()
const router = useRouter()

const formRef = ref<FormInstance>()
const loading = ref(false)
const permissionOptions = ref<Permission[]>([])

const isEdit = computed(() => !!route.params.id)
const permissionId = computed(() => Number(route.params.id || 0))

const form = reactive({
  id: 0,
  name: '',
  code: '',
  route_url: '',
  icon: '',
  description: '',
  parent_id: 0,
  is_show: 1
})

const rules: FormRules = {
  name: [
    { required: true, message: '请输入权限名称', trigger: 'blur' },
    { min: 1, max: 50, message: '权限名称长度在 1 到 50 个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入权限标识', trigger: 'blur' },
    { min: 1, max: 50, message: '权限标识长度在 1 到 50 个字符', trigger: 'blur' }
  ],
  route_url: [
    { required: true, message: '请输入权限路径', trigger: 'blur' }
  ],
  icon: [
    { required: true, message: '请输入权限图标', trigger: 'blur' }
  ],
  description: [
    { required: true, message: '请输入权限描述', trigger: 'blur' },
    { max: 200, message: '权限描述长度不能超过 200 个字符', trigger: 'blur' }
  ],
  parent_id: [
    { required: true, message: '请选择父级权限', trigger: 'change' }
  ],
  is_show: [
    { required: true, message: '请选择是否显示', trigger: 'change' }
  ]
}

// 加载权限列表，用于父级权限选择
async function loadPermissions() {
  try {
    const res = await adminPermissionListApi({})
    // 扁平化权限列表，方便选择
    const flattenPermissions = (permissions: Permission[]): Permission[] => {
      let result: Permission[] = []
      permissions.forEach(permission => {
        result.push(permission)
        if (permission.children && permission.children.length > 0) {
          result = [...result, ...flattenPermissions(permission.children)]
        }
      })
      return result
    }
    permissionOptions.value = flattenPermissions(res || [])
  } catch (error) {
    console.error('加载权限列表失败:', error)
  }
}

async function loadPermissionInfo() {
  if (!isEdit.value) return
  
  loading.value = true
  try {
    const data = await adminPermissionInfoApi(permissionId.value)
    form.id = data.id
    form.name = data.name
    form.code = data.code || ''
    form.route_url = data.route_url || ''
    form.icon = data.icon || ''
    form.description = data.description
    form.parent_id = data.parent_id
    form.is_show = data.is_show || 1
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
      await adminPermissionUpdateApi({
        id: form.id,
        name: form.name,
        icon: form.icon,
        code: form.code,
        route_url: form.route_url,
        description: form.description,
        is_show: form.is_show,
        parent_id: form.parent_id
      })
      ElMessage.success('更新成功')
    } else {
      await adminPermissionCreateApi({
        name: form.name,
        code: form.code,
        description: form.description,
        is_show: form.is_show,
        parent_id: form.parent_id,
        route_url: form.route_url,
        icon: form.icon
      })
      ElMessage.success('创建成功')
    }
    router.push('/permission')
  } catch (error) {
    console.error('保存权限失败:', error)
    ElMessage.error('保存失败')
  } finally {
    loading.value = false
  }
}

function resetForm() {
  if (!formRef.value) return
  formRef.value.resetFields()
}

onMounted(async () => {
  await loadPermissions()
  if (isEdit.value) {
    loadPermissionInfo()
  }
})
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;}
.form{max-width: 800px;}
.sectionTitle{font-weight:900;margin:10px 0;}
.statusText{margin-left: 8px;color: var(--sub);font-size: 14px;}
</style>