<template>
  <div class="page">
    <el-card shadow="never" v-loading="loading">
      <div v-if="info" class="info">
        <el-card shadow="never" class="panel">
          <div class="panelTitle">用户信息</div>
          <div class="kv">
            <div class="k">用户ID</div><div class="v">{{ info.id }}</div>
            <div class="k">用户姓名</div><div class="v">{{ info.username }}</div>
            <div class="k">昵称</div><div class="v">{{ info.nickname }}</div>
            <div class="k">手机号</div><div class="v">{{ info.phone }}</div>
            <div class="k">余额</div><div class="v">{{ info.balance || 0 }}</div>
            <div class="k">创建时间</div><div class="v">{{ info.created_at }}</div>
            <div class="k">更新时间</div><div class="v">{{ info.updated_at }}</div>
          </div>
        </el-card>

        <div class="actions">
          <el-button type="primary" @click="onRecharge">充值</el-button>
          <el-button type="warning" @click="onResetPassword">重置密码</el-button>
          <el-button @click="router.push('/user')">返回列表</el-button>
        </div>
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
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, FormInstance, FormRules } from 'element-plus'
import { 
  adminUserInfoApi, 
  adminUserChangeBalanceApi, 
  adminUserResetPasswordApi,
  type User 
} from '../../api/user'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const info = ref<User | null>(null)

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

async function load() {
  const id = Number(route.params.id)
  if (!id) return
  loading.value = true
  try {
    const data = await adminUserInfoApi(id)
    info.value = data
  } catch (error) {
    console.error('加载用户详情失败:', error)
    ElMessage.error('加载用户详情失败')
    router.push('/user')
  } finally {
    loading.value = false
  }
}

function onRecharge() {
  if (!info.value) return
  rechargeForm.id = info.value.id
  rechargeForm.username = info.value.username
  rechargeForm.currentBalance = info.value.balance || 0
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
    load()
  } catch (error) {
    console.error('充值失败:', error)
    ElMessage.error('充值失败')
  } finally {
    rechargeLoading.value = false
  }
}

function onResetPassword() {
  if (!info.value) return
  resetPasswordForm.id = info.value.id
  resetPasswordForm.username = info.value.username
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

.panel{border-radius:16px;margin-bottom:16px;}
.panelTitle{font-weight:900;margin-bottom:16px;font-size:16px;}

.kv{
  display:grid;
  grid-template-columns: 100px 1fr;
  gap: 8px 12px;
}
.k{color:var(--sub);font-size:12px;}
.v{font-weight:700;word-break:break-all;}

.actions{
  display:flex;
  gap:10px;
  margin-top:16px;
}

.dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
