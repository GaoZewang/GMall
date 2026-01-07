<template>
  <el-form :model="paymentSettings" label-width="180px" class="settings-form">
    <el-form-item label="启用支付宝">
      <el-switch v-model="paymentSettings.enableAlipay" />
    </el-form-item>
    <el-form-item label="支付宝APPID">
      <el-input v-model="paymentSettings.alipayAppId" placeholder="请输入支付宝APPID" />
    </el-form-item>
    <el-form-item label="支付宝私钥">
      <el-input v-model="paymentSettings.alipayPrivateKey" type="textarea" placeholder="请输入支付宝私钥" :rows="4" />
    </el-form-item>
    <el-form-item label="支付宝公钥">
      <el-input v-model="paymentSettings.alipayPublicKey" type="textarea" placeholder="请输入支付宝公钥" :rows="4" />
    </el-form-item>
    <el-form-item label="支付宝回调URL">
      <el-input v-model="paymentSettings.alipayCallbackUrl" placeholder="请输入支付宝回调URL" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="saveAlipaySettings">保存支付宝设置</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { ElMessage } from 'element-plus'

// 支付宝设置
const paymentSettings = reactive({
  enableAlipay: true,
  alipayAppId: '',
  alipayPrivateKey: '',
  alipayPublicKey: '',
  alipayCallbackUrl: ''
})

// 保存支付宝设置
function saveAlipaySettings() {
  // 这里可以添加表单验证和API调用
  ElMessage.success('支付宝设置保存成功')
}
</script>

<style scoped>
.settings-form {
  margin-top: 20px;
}
</style>