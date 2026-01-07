<template>
  <div class="search-toolbar">
    <slot name="search"></slot>
    <div class="spacer"></div>
    <el-button @click="reset">重置</el-button>
    <el-button type="primary" :loading="loading" @click="query">查询</el-button>
    <slot name="actions"></slot>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'reset'): void
  (e: 'query'): void
}>()

function reset() {
  emit('reset')
}

function query() {
  emit('query')
}
</script>

<style scoped>
.search-toolbar {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 12px;
}

.spacer {
  flex: 1;
}
</style>