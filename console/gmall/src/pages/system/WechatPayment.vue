<template>
  <el-form :model="paymentSettings" label-width="180px" class="settings-form">
    <el-form-item label="启用微信支付">
      <el-switch v-model="paymentSettings.enableWechat" />
    </el-form-item>
    <el-form-item label="微信支付商户号">
      <el-input v-model="paymentSettings.wechatMerchantId" placeholder="请输入微信支付商户号" />
    </el-form-item>
    
    <div class="v2-sub-title">V2版本配置</div>
    <el-form-item label="微信支付API密钥(V2)">
      <el-input v-model="paymentSettings.wechatApiKey" type="password" placeholder="请输入微信支付API密钥(V2)" show-password />
    </el-form-item>
    
    <div class="v2-sub-title">V3版本配置</div>
    <el-form-item label="微信支付API密钥(V3)">
      <el-input v-model="paymentSettings.wechatApiKeyV3" type="password" placeholder="请输入微信支付API密钥(V3)" show-password />
    </el-form-item>
    <el-form-item label="API证书序列号">
      <el-input v-model="paymentSettings.wechatCertSerialNo" placeholder="请输入API证书序列号" />
    </el-form-item>
    <el-form-item label="API证书私钥(PEM格式)">
      <el-upload
        class="cert-uploader"
        :limit="1"
        accept=".pem,.key"
        :file-list="wechatPrivateKeyFiles"
        :on-success="handlePrivateKeyUpload"
        :on-remove="handlePrivateKeyRemove"
        :before-upload="beforeCertUpload"
        action="#"
        :auto-upload="false"
      >
        <el-button type="primary">点击上传</el-button>
        <template #tip>
          <div class="el-upload__tip">仅支持PEM或KEY格式文件</div>
        </template>
      </el-upload>
    </el-form-item>
    <el-form-item label="微信支付平台证书(PEM格式)">
      <el-upload
        class="cert-uploader"
        :limit="1"
        accept=".pem"
        :file-list="wechatPlatformCertFiles"
        :on-success="handlePlatformCertUpload"
        :on-remove="handlePlatformCertRemove"
        :before-upload="beforeCertUpload"
        action="#"
        :auto-upload="false"
      >
        <el-button type="primary">点击上传</el-button>
        <template #tip>
          <div class="el-upload__tip">仅支持PEM格式文件</div>
        </template>
      </el-upload>
    </el-form-item>
    
    <el-form-item label="微信支付回调URL">
      <el-input v-model="paymentSettings.wechatCallbackUrl" placeholder="请输入微信支付回调URL" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="saveWechatSettings">保存微信支付设置</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'

// 微信支付证书文件列表
const wechatPrivateKeyFiles = ref([])
const wechatPlatformCertFiles = ref([])

// 微信支付设置
const paymentSettings = reactive({
  enableWechat: true,
  wechatMerchantId: '',
  wechatApiKey: '',
  wechatApiKeyV3: '',
  wechatCertSerialNo: '',
  wechatPrivateKey: '',
  wechatPlatformCert: '',
  wechatCallbackUrl: ''
})

// 证书上传前验证
function beforeCertUpload(file: any) {
  const allowedTypes = ['.pem', '.key']
  const fileExt = file.name.substring(file.name.lastIndexOf('.')).toLowerCase()
  if (!allowedTypes.includes(fileExt)) {
    ElMessage.error('仅支持PEM或KEY格式文件')
    return false
  }
  
  const isLt2M = file.size / 1024 / 1024 < 2
  if (!isLt2M) {
    ElMessage.error('文件大小不能超过2MB')
    return false
  }
  
  // 读取文件内容
  const reader = new FileReader()
  reader.onload = (e) => {
    const content = e.target?.result as string
    if (file.name.includes('private') || fileExt === '.key') {
      paymentSettings.wechatPrivateKey = content
      handlePrivateKeyUpload(file)
    } else {
      paymentSettings.wechatPlatformCert = content
      handlePlatformCertUpload(file)
    }
  }
  reader.readAsText(file)
  
  return false // 阻止自动上传，使用手动读取
}

// 处理私钥上传成功
function handlePrivateKeyUpload(file: any) {
  wechatPrivateKeyFiles.value = [file]
  ElMessage.success('私钥文件上传成功')
}

// 处理私钥移除
function handlePrivateKeyRemove() {
  wechatPrivateKeyFiles.value = []
  paymentSettings.wechatPrivateKey = ''
  ElMessage.success('私钥文件已移除')
}

// 处理平台证书上传成功
function handlePlatformCertUpload(file: any) {
  wechatPlatformCertFiles.value = [file]
  ElMessage.success('平台证书上传成功')
}

// 处理平台证书移除
function handlePlatformCertRemove() {
  wechatPlatformCertFiles.value = []
  paymentSettings.wechatPlatformCert = ''
  ElMessage.success('平台证书已移除')
}

// 保存微信支付设置
function saveWechatSettings() {
  // 这里可以添加表单验证和API调用
  ElMessage.success('微信支付设置保存成功')
}
</script>

<style scoped>
.settings-form {
  margin-top: 20px;
}

.v2-sub-title {
  font-weight: 700;
  font-size: 14px;
  margin: 16px 0 8px 0;
  color: #606266;
  background-color: #f5f7fa;
  padding: 4px 12px;
  border-radius: 4px;
  display: inline-block;
}

.cert-uploader {
  margin-top: 8px;
}

.cert-uploader :deep(.el-upload-list) {
  margin-top: 8px;
}
</style>