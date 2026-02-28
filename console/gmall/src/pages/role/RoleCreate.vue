<template>
  <div class="page">
    <el-card shadow="never">
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px" class="form">
        <!-- 基本信息 -->
        <div class="sectionTitle">基本信息</div>
        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="角色名称" prop="name">
              <el-input v-model="form.name" placeholder="请输入角色名称" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="角色标识" prop="slug">
              <el-input v-model="form.slug" placeholder="请输入角色标识，用于代码中识别" />
            </el-form-item>
          </el-col>

          <el-col :xs="24">
            <el-form-item label="角色描述" prop="description">
              <el-input v-model="form.description" type="textarea" :rows="3" placeholder="请输入角色描述，说明该角色的职责与权限" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12" v-if="isEdit">
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

        <!-- 权限选择 -->
        <div class="sectionTitle">权限选择</div>
        <el-form-item label="关联权限">
          <el-tree
            ref="treeRef"
            v-loading="loadingPermissions"
            :data="permissions"
            :props="permissionTreeProps"
            node-key="id"
            show-checkbox
            default-expand-all
            :default-checked-keys="form.permissions"
            @check="handleCheckChange"
            class="permission-tree"
          />
        </el-form-item>

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
  adminRoleCreateApi, 
  adminRoleInfoApi, 
  adminRoleUpdateApi,
  type Role 
} from '../../api/role'
import { adminPermissionListApi, type Permission } from '../../api/permission'

const route = useRoute()
const router = useRouter()

const formRef = ref<FormInstance>()
const loading = ref(false)
const permissions = ref<Permission[]>([])
const loadingPermissions = ref(false)
const treeRef = ref<any>()

const isEdit = computed(() => !!route.params.id)
const roleId = computed(() => Number(route.params.id || 0))

const form = reactive({
  id: 0,
  name: '',
  slug: '',
  description: '',
  status: 1,
  permissions: [] as number[]
})

const rules: FormRules = {
  name: [
    { required: true, message: '请输入角色名称', trigger: 'blur' },
    { min: 1, max: 50, message: '角色名称长度在 1 到 50 个字符', trigger: 'blur' }
  ],
  slug: [
    { required: true, message: '请输入角色标识', trigger: 'blur' },
    { min: 1, max: 50, message: '角色标识长度在 1 到 50 个字符', trigger: 'blur' }
  ],
  description: [
    { required: true, message: '请输入角色描述', trigger: 'blur' },
    { max: 200, message: '角色描述长度不能超过 200 个字符', trigger: 'blur' }
  ],
  status: [
    { required: isEdit.value, message: '请选择状态', trigger: 'change' }
  ]
}

// 权限树形结构配置
const permissionTreeProps = {
  children: 'children',
  label: 'name'
}

// 加载权限列表
async function loadPermissions() {
  loadingPermissions.value = true
  try {
    const res = await adminPermissionListApi({})
    permissions.value = res || []
  } catch (error) {
    console.error('加载权限列表失败:', error)
    ElMessage.error('加载权限列表失败')
  } finally {
    loadingPermissions.value = false
  }
}

async function loadRoleInfo() {
  if (!isEdit.value) return
  
  loading.value = true
  try {
    const data = await adminRoleInfoApi(roleId.value)
    form.id = data.id
    form.name = data.name
    form.slug = data.slug || ''
    form.description = data.description
    form.status = data.status
    // 假设API返回的角色详情中包含permissions字段
    form.permissions = data.permissions || []
  } finally {
    loading.value = false
  }
}

async function submit() {
  if (!formRef.value) return
  await formRef.value.validate()
  
  // 确保获取当前选中的权限
  if (treeRef.value) {
    form.permissions = treeRef.value.getCheckedKeys()
  }
  
  loading.value = true
  try {
    if (isEdit.value) {
      await adminRoleUpdateApi({
        id: form.id,
        name: form.name,
        slug: form.slug,
        description: form.description,
        status: form.status,
        permissions: form.permissions
      })
      ElMessage.success('更新成功')
    } else {
      await adminRoleCreateApi({
        name: form.name,
        slug: form.slug,
        description: form.description,
        permissions: form.permissions
      })
      ElMessage.success('创建成功')
    }
    router.push('/role')
  } catch (error) {
    console.error('保存角色失败:', error)
    ElMessage.error('保存失败')
  } finally {
    loading.value = false
  }
}

function resetForm() {
  if (!formRef.value) return
  formRef.value.resetFields()
}

// 处理权限选择变化
function handleCheckChange() {
  // 使用 treeRef 来获取所有选中的权限 ID
  if (treeRef.value) {
    form.permissions = treeRef.value.getCheckedKeys()
  }
}

onMounted(async () => {
  await loadPermissions()
  if (isEdit.value) {
    loadRoleInfo()
  }
})
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;}
.form{max-width: 800px;}
.sectionTitle{font-weight:900;margin:10px 0;}
.statusText{margin-left: 8px;color: var(--sub);font-size: 14px;}
.permission-tree{
  max-height: 400px;
  overflow-y: auto;
  padding: 10px;
}
</style>