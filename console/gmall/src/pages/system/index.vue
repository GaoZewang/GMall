<template>
  <div class="page">
    <el-card shadow="never">
      <!-- <div class="title">系统设置</div> -->
      <el-tabs v-model="activeTab" class="settings-tabs" style="margin-top: 20px;">
        <!-- 微信支付设置 -->
        <el-tab-pane label="微信支付设置" name="wechat">
          <WechatPayment />
        </el-tab-pane>

        <!-- 支付宝设置 -->
        <el-tab-pane label="支付宝设置" name="alipay">
          <AlipayPayment />
        </el-tab-pane>

        <!-- 短信设置 -->
        <el-tab-pane label="短信设置" name="sms">
          <SmsSetting />
        </el-tab-pane>

        <!-- 打印设置 -->
        <el-tab-pane label="打印设置" name="print">
          <PrintSetting />
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import WechatPayment from './WechatPayment.vue'
import AlipayPayment from './AlipayPayment.vue'
import SmsSetting from './SmsSetting.vue'
import PrintSetting from './PrintSetting.vue'

// 当前激活的标签页
const activeTab = ref('wechat')
</script>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.title {
  font-weight: 900;
  font-size: 18px;
}

.settings-tabs :deep(.el-tabs__content) {
  padding: 20px 0;
}
</style>