<template>
  <div class="page">
    <el-card shadow="never" v-loading="loading">
      <div v-if="info" class="grid">
        <el-card shadow="never" class="panel">
          <div class="panelTitle">角色信息</div>

          <div class="name">{{ info.name }}</div>

          <div class="tags">
            <el-tag :type="info.status === 1 ? 'success' : 'info'">
              {{ info.status === 1 ? '启用' : '禁用' }}
            </el-tag>
          </div>

          <div class="kv">
            <div class="k">角色ID</div><div class="v">{{ info.id }}</div>
            <div class="k">角色标识</div><div class="v">{{ info.slug || '未设置' }}</div>
            <div class="k">角色描述</div><div class="v">{{ info.description }}</div>
            <div class="k">创建时间</div><div class="v">{{ info.created_at || '未知' }}</div>
            <div class="k">更新时间</div><div class="v">{{ info.updated_at || '未知' }}</div>
          </div>
        </el-card>
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminRoleInfoApi, type Role } from '../../api/role'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const info = ref<Role | null>(null)

async function load() {
  const id = Number(route.params.id)
  if (!id) return
  loading.value = true
  try {
    const res = await adminRoleInfoApi(id)
    info.value = res
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;}
.mt12{margin-top:12px;}

.grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
}

.panel {
  background-color: #fafafa;
}

.panelTitle {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 16px;
}

.name {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 12px;
}

.tags {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
}

.kv {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.k {
  font-weight: bold;
  color: #606266;
  min-width: 100px;
}

.v {
  color: #303133;
  word-break: break-all;
}
</style>