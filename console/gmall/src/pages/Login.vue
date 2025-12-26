<template>
  <div class="login">
    <div class="panel">
      <div class="brand">
        <div class="logo">GM</div>
        <div class="meta">
          <div class="name">Gmall</div>
          <div class="desc">{{ platformLabel }} 登录</div>
        </div>
      </div>

      <el-card shadow="never" class="card">
        <div class="title">登录</div>

        <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
          <el-form-item label="账号" prop="username">
            <el-input v-model.trim="form.username" placeholder="请输入账号" clearable />
          </el-form-item>

          <el-form-item label="密码" prop="password">
            <el-input v-model="form.password" type="password" show-password placeholder="请输入密码" clearable />
          </el-form-item>

          <el-button class="btn" type="primary" size="large" :loading="loading" @click="onSubmit">
            登录
          </el-button>
        </el-form>

        <div class="tip">POST /auth/login（platform={{ platform }}）</div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import type { FormInstance, FormRules } from 'element-plus'
import { useRouter } from 'vue-router'
import { PLATFORM, PLATFORM_LABEL } from '../platform'
import { useAuthStore } from '../stores/auth'
import { loginApi } from '../api/auth'

const router = useRouter()
const auth = useAuthStore()

const platform = PLATFORM
auth.setPlatform(platform)

const platformLabel = computed(() => PLATFORM_LABEL[platform])

const formRef = ref<FormInstance>()
const loading = ref(false)

const form = reactive({
  username: '',
  password: '',
})

const rules: FormRules = {
  username: [{ required: true, message: '请输入账号', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }],
}

async function onSubmit() {
  await formRef.value?.validate()
  loading.value = true
  try {
    const res = await loginApi({ 
      username: form.username, 
      password: form.password, 
      platform: PLATFORM 
    })
    let token =  res.access_token
    auth.setToken(token)
    router.push('/')
  } catch {
    loading.value = false
    // 不用写提示，http.ts 已经弹了 msg
  }
}
</script>

<style scoped>
.login{min-height:100vh;display:grid;place-items:center;padding:24px;}
.panel{width:420px;}
.brand{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
.logo{
  width:44px;height:44px;border-radius:14px;
  background:#FF6700;color:#fff;font-weight:900;
  display:grid;place-items:center;
}
.name{font-weight:900;font-size:18px;line-height:1;}
.desc{font-size:12px;color:var(--sub);margin-top:4px;}
.card{border-radius:16px;}
.title{font-weight:900;font-size:18px;margin-bottom:8px;}
.btn{width:100%;height:44px;border-radius:12px;font-weight:900;}
.tip{margin-top:10px;font-size:12px;color:var(--sub);text-align:center;}
</style>
