<template>
  <div class="page-header">
    <div class="title">{{ title }}</div>
    <div class="ops">
      <slot></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  title: string
}>()
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap;
}

.title {
  font-weight: 900;
}

.ops {
  display: flex;
  gap: 10px;
  align-items: center;
}
</style>