<template>
  <div class="page">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <SearchToolbar :loading="loading" @reset="reset" @query="handleQuery">
        <template #search>
          <el-input
            v-model="query.username"
            placeholder="搜索用户姓名"
            clearable
            style="max-width: 200px"
            @keyup.enter="load(1)"
          />
          <el-input
            v-model="query.phone"
            placeholder="搜索手机号"
            clearable
            style="max-width: 200px"
            @keyup.enter="load(1)"
          />
          <el-input
            v-model="query.nickname"
            placeholder="搜索昵称"
            clearable
            style="max-width: 200px"
            @keyup.enter="load(1)"
          />
        </template>
      </SearchToolbar>

      <el-table 
        :data="rows" 
        v-loading="loading" 
        class="table" 
        row-key="id"
      >
        <el-table-column label="用户姓名" prop="username" min-width="150" />
        <el-table-column label="昵称" prop="nickname" min-width="150" />
        <el-table-column label="手机号" prop="phone" width="150" />
        <el-table-column label="余额" prop="balance" width="100">
          <template #default="{ row }">
            <span>{{ row.balance || 0 }}</span>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" prop="created_at" min-width="170" />

        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="onView(row)">查看</el-button>
            <el-button link @click="onRecharge(row)">充值</el-button>
            <el-button link type="danger" @click="onResetPassword(row)">重置密码</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pager">
        <div class="total">共 {{ pagination.total }} 条</div>
        <el-pagination
          background
          layout="prev, pager, next, sizes"
          :total="pagination.total"
          :page-size="pagination.per_page"
          :current-page="pagination.current_page"
          :page-sizes="[10, 20, 50, 100]"
          @current-change="onPageChange"
          @size-change="onSizeChange"
        />
      </div>
    </el-card>

    <!-- 充值对话框 -->
    <el-dialog
      v-model="rechargeDialogVisible"
      title="用户充值"
      width="400px"
    >
      <el-form :model="rechargeForm" :rules="rechargeRules" ref="rechargeFormRef">
        <el-form-item label="用户" prop="username">
          <el-input v-model="rechargeForm.username" disabled />
        </el-form-item>
        <el-form-item label="当前余额" prop="currentBalance">
          <el-input v-model="rechargeForm.currentBalance" disabled />
        </el-form-item>
        <el-form-item label="充值金额" prop="balance">
          <el-input-number v-model="rechargeForm.balance" :min="0.01" :step="0.01" style="width: 100%" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="rechargeDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleRecharge" :loading="rechargeLoading">确定</el-button>
        </span>
      </template>
    </el-dialog>

    <!-- 重置密码对话框 -->
    <el-dialog
      v-model="resetPasswordDialogVisible"
      title="重置密码"
      width="400px"
    >
      <el-form :model="resetPasswordForm" :rules="resetPasswordRules" ref="resetPasswordFormRef">
        <el-form-item label="用户" prop="username">
          <el-input v-model="resetPasswordForm.username" disabled />
        </el-form-item>
        <el-form-item label="新密码" prop="password">
          <el-input v-model="resetPasswordForm.password" type="password" placeholder="请输入新密码" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="resetPasswordDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="handleResetPassword" :loading="resetPasswordLoading">确定</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, FormInstance, FormRules } from 'element-plus'
import { 
  adminUserListApi, 
  adminUserChangeBalanceApi, 
  adminUserResetPasswordApi,
  type User 
} from '../../api/user'
import SearchToolbar from '../../components/SearchToolbar.vue'

const router = useRouter()

const loading = ref(false)
const rows = ref<User[]>([])

const query = reactive({
  username: '',
  phone: '',
  nickname: '',
})

const pagination = reactive({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
})

// 充值相关
const rechargeDialogVisible = ref(false)
const rechargeLoading = ref(false)
const rechargeFormRef = ref<FormInstance>()
const rechargeForm = reactive({
  id: 0,
  username: '',
  currentBalance: 0,
  balance: 0,
})

const rechargeRules: FormRules = {
  balance: [
    { required: true, message: '请输入充值金额', trigger: 'blur' },
    { type: 'number', min: 0.01, message: '充值金额必须大于0', trigger: 'blur' }
  ]
}

// 重置密码相关
const resetPasswordDialogVisible = ref(false)
const resetPasswordLoading = ref(false)
const resetPasswordFormRef = ref<FormInstance>()
const resetPasswordForm = reactive({
  id: 0,
  username: '',
  password: '',
})

const resetPasswordRules: FormRules = {
  password: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { min: 6, message: '密码长度不能少于6位', trigger: 'blur' }
  ]
}

async function load(page = pagination.current_page) {
  loading.value = true
  try {
    const res = await adminUserListApi({
      page,
      per_page: pagination.per_page,
      username: query.username || undefined,
      phone: query.phone || undefined,
      nickname: query.nickname || undefined,
    })
    
    rows.value = res.list || []
    pagination.total = res.pagination?.total || 0
    pagination.per_page = res.pagination?.per_page || 10
    pagination.current_page = page
    pagination.last_page = res.pagination?.last_page || 1
  } catch (error) {
    console.error('加载用户列表失败:', error)
    ElMessage.error('加载用户列表失败')
    rows.value = []
  } finally {
    loading.value = false
  }
}

function reset() {
  query.username = ''
  query.phone = ''
  query.nickname = ''
  pagination.per_page = 10
  load(1)
}

function handleQuery() {
  load(1)
}

function onPageChange(p: number) {
  load(p)
}

function onSizeChange(size: number) {
  pagination.per_page = size
  load(1)
}

function onView(row: User) {
  router.push(`/user/${row.id}`)
}

function onRecharge(row: User) {
  rechargeForm.id = row.id
  rechargeForm.username = row.username
  rechargeForm.currentBalance = row.balance || 0
  rechargeForm.balance = 0
  rechargeDialogVisible.value = true
}

async function handleRecharge() {
  if (!rechargeFormRef.value) return
  await rechargeFormRef.value.validate()
  
  rechargeLoading.value = true
  try {
    await adminUserChangeBalanceApi({
      id: rechargeForm.id,
      balance: rechargeForm.balance
    })
    ElMessage.success('充值成功')
    rechargeDialogVisible.value = false
    load(pagination.current_page)
  } catch (error) {
    console.error('充值失败:', error)
    ElMessage.error('充值失败')
  } finally {
    rechargeLoading.value = false
  }
}

function onResetPassword(row: User) {
  resetPasswordForm.id = row.id
  resetPasswordForm.username = row.username
  resetPasswordForm.password = ''
  resetPasswordDialogVisible.value = true
}

async function handleResetPassword() {
  if (!resetPasswordFormRef.value) return
  await resetPasswordFormRef.value.validate()
  
  resetPasswordLoading.value = true
  try {
    await adminUserResetPasswordApi({
      id: resetPasswordForm.id,
      password: resetPasswordForm.password
    })
    ElMessage.success('密码重置成功')
    resetPasswordDialogVisible.value = false
  } catch (error) {
    console.error('密码重置失败:', error)
    ElMessage.error('密码重置失败')
  } finally {
    resetPasswordLoading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;}

.pager {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
  padding: 0 8px;
}

.total {
  font-size: 14px;
  color: #606266;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
