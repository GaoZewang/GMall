<template>
  <div class="page">
    <el-card shadow="never" v-loading="loading">
      <div v-if="info" class="grid">
        <el-card shadow="never" class="panel">
          <div class="panelTitle">权限信息</div>

          <div class="name">{{ info.name }}</div>

          <div class="tags">
            <el-tag :type="info.is_show === 1 ? 'success' : 'info'">
              {{ info.is_show === 1 ? '显示' : '隐藏' }}
            </el-tag>
          </div>

          <div class="kv">
            <div class="k">权限ID</div><div class="v">{{ info.id }}</div>
            <div class="k">权限标识</div><div class="v">{{ info.code }}</div>
            <div class="k">权限路径</div><div class="v">{{ info.route_url }}</div>
            <div class="k">权限图标</div><div class="v">{{ info.icon }}</div>
            <div class="k">父级ID</div><div class="v">{{ info.parent_id }}</div>
            <div class="k">权限描述</div><div class="v">{{ info.description }}</div>
            <div class="k">状态</div><div class="v">{{ info.status === 1 ? '启用' : '禁用' }}</div>
          </div>

          <el-divider v-if="info.children && info.children.length > 0" />

          <div v-if="info.children && info.children.length > 0" class="panelTitle">子权限</div>
          <el-tree
            v-if="info.children && info.children.length > 0"
            :data="info.children"
            :props="treeProps"
            node-key="id"
            class="child-permissions"
          >
            <template #default="{ node, data }">
              <div class="tree-node">
                <span>{{ data.name }}</span>
                <div class="node-actions">
                  <el-button link type="primary" size="small" @click="onView(data)">查看</el-button>
                  <el-button link size="small" @click="onEdit(data)">编辑</el-button>
                </div>
              </div>
            </template>
          </el-tree>
        </el-card>
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminPermissionInfoApi, type Permission } from '../../api/permission'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const info = ref<Permission | null>(null)

// 树形结构配置
const treeProps = {
  children: 'children',
  label: 'name'
}

async function load() {
  const id = Number(route.params.id)
  if (!id) return
  loading.value = true
  try {
    const res = await adminPermissionInfoApi(id)
    info.value = res
  } finally {
    loading.value = false
  }
}

function onView(data: Permission) {
  router.push(`/permission/${data.id}`)
}

function onEdit(data: Permission) {
  router.push(`/permission/edit/${data.id}`)
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

.child-permissions {
  margin-top: 12px;
}

.tree-node {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.node-actions {
  display: flex;
  gap: 8px;
}
</style>