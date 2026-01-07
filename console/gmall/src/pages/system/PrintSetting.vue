<template>
  <el-form :model="printSettings" label-width="180px" class="settings-form">
    <el-form-item label="默认打印机">
      <el-input v-model="printSettings.defaultPrinter" placeholder="请输入默认打印机名称" />
    </el-form-item>
    <el-form-item label="打印方向">
      <el-radio-group v-model="printSettings.printOrientation">
        <el-radio label="portrait">纵向</el-radio>
        <el-radio label="landscape">横向</el-radio>
      </el-radio-group>
    </el-form-item>
    <el-form-item label="纸张大小">
      <el-select v-model="printSettings.paperSize" placeholder="请选择纸张大小">
        <el-option label="A4" value="A4" />
        <el-option label="A5" value="A5" />
        <el-option label="热敏纸80mm" value="80mm" />
        <el-option label="热敏纸58mm" value="58mm" />
      </el-select>
    </el-form-item>
    <el-form-item label="打印份数">
      <el-input-number v-model="printSettings.printCopies" :min="1" :max="10" placeholder="请输入打印份数" />
    </el-form-item>
    <el-form-item label="打印速度">
      <el-select v-model="printSettings.printSpeed" placeholder="请选择打印速度">
        <el-option label="低速" value="low" />
        <el-option label="中速" value="medium" />
        <el-option label="高速" value="high" />
      </el-select>
    </el-form-item>
    <el-form-item label="打印浓度">
      <el-slider v-model="printSettings.printDensity" :min="1" :max="10" :marks="{1: '淡', 5: '中', 10: '浓'}" />
    </el-form-item>
    <el-form-item label="自动打印订单">
      <el-switch v-model="printSettings.autoPrintOrder" />
    </el-form-item>
    <el-form-item label="自动打印发货单">
      <el-switch v-model="printSettings.autoPrintDelivery" />
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="savePrintSettings">保存打印设置</el-button>
      <el-button @click="testPrint">测试打印</el-button>
    </el-form-item>
  </el-form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { ElMessage } from 'element-plus'

// 打印设置
const printSettings = reactive({
  defaultPrinter: '',
  printOrientation: 'portrait',
  paperSize: 'A4',
  printCopies: 1,
  printSpeed: 'medium',
  printDensity: 5,
  autoPrintOrder: false,
  autoPrintDelivery: false
})

// 保存打印设置
function savePrintSettings() {
  // 这里可以添加表单验证和API调用
  ElMessage.success('打印设置保存成功')
}

// 测试打印
function testPrint() {
  // 这里可以添加测试打印的API调用
  ElMessage.success('测试打印成功')
}
</script>

<style scoped>
.settings-form {
  margin-top: 20px;
}
</style>