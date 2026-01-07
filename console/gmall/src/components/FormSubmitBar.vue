<template>
  <div class="submit-bar">
    <el-button
      v-if="showCancel"
      size="large"
      @click="onCancel"
    >
      {{ cancelText }}
    </el-button>

    <el-button
      type="primary"
      size="large"
      @click="onSubmit"
    >
      {{ submitText }}
    </el-button>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  submitText?: string
  cancelText?: string
  showCancel?: boolean
}>()

const emit = defineEmits<{
  (e: 'submit'): void
  (e: 'cancel'): void
}>()

function onSubmit() {
  emit('submit')
}

function onCancel() {
  emit('cancel')
}
</script>

<style scoped>
.submit-bar {
  margin: 32px 0;
  padding-top: 16px;
  display: flex;
  justify-content: center;
  gap: 16px;
}

.submit-bar .el-button {
  min-width: 220px;
  height: 48px;
  font-size: 16px;
  font-weight: 600;
}
</style>
