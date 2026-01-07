<template>
  <el-form :model="smsSettings" label-width="180px" class="settings-form">
    <el-form-item label="短信服务商">
      <el-select v-model="smsSettings.smsProvider" placeholder="请选择短信服务商">
        <el-option label="阿里云" value="aliyun" />
        <el-option label="腾讯云" value="tencent" />
        <el-option label="华为云" value="huawei" />
      </el-select>
    </el-form-item>
    <el-form-item label="API密钥ID">
      <el-input v-model="smsSettings.apiKeyId" placeholder="请输入API密钥ID" />
    </el-form-item>
    <el-form-item label="API密钥Secret">
      <el-input v-model="smsSettings.apiKeySecret" type="password" placeholder="请输入API密钥Secret" show-password />
    </el-form-item>
    <el-form-item label="短信签名">
      <el-input v-model="smsSettings.smsSign" placeholder="请输入短信签名" />
    </el-form-item>
    <el-form-item label="验证码模板ID">
      <el-input v-model="smsSettings.verifyCodeTemplateId" placeholder="请输入验证码模板ID" />
    </el-form-item>
    <el-form-item label="通知短信模板ID">
      <el-input v-model="smsSettings.noticeTemplateId" placeholder="请输入通知短信模板ID" />
    </el-form-item>
    <el-form-item label="营销短信模板ID">
      <el-input v-model="smsSettings.marketingTemplateId" placeholder="请输入营销短信模板ID" />
    </el-form-item>
    <el-form-item label="短信发送频率限制(秒)">
      <el-input-number v-model="smsSettings.smsSendInterval" :min="1" :max="3600" placeholder="请输入短信发送频率限制" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="saveSmsSettings">保存短信设置</el-button>
      <el-button @click="testSms">测试发送</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { ElMessage } from 'element-plus'

// 短信设置
const smsSettings = reactive({
  smsProvider: 'aliyun',
  apiKeyId: '',
  apiKeySecret: '',
  smsSign: '',
  verifyCodeTemplateId: '',
  noticeTemplateId: '',
  marketingTemplateId: '',
  smsSendInterval: 60
})

// 保存短信设置
function saveSmsSettings() {
  // 这里可以添加表单验证和API调用
  ElMessage.success('短信设置保存成功')
}

// 测试短信发送
function testSms() {
  // 这里可以添加测试短信发送的API调用
  ElMessage.success('测试短信发送成功')
}
</script>

<style scoped>
.settings-form {
  margin-top: 20px;
}
</style>