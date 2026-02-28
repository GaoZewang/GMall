<template>
  <div class="page">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <SearchToolbar :loading="loading" @reset="reset" @query="handleQuery">
        <template #search>
          <el-input
            v-model="query.name"
            placeholder="搜索权限名称"
            clearable
            style="max-width: 320px"
            @keyup.enter="load"
          />
          <el-input
            v-model="query.url"
            placeholder="搜索权限路径"
            clearable
            style="max-width: 320px"
            @keyup.enter="load"
          />
        </template>
        <template #actions>
          <el-button type="primary" @click="router.push('/permission/create')">新增权限</el-button>
        </template>
      </SearchToolbar>

      <!-- 权限列表（折叠列表） -->
      <el-tree
        v-loading="loading"
        :data="permissionTree"
        :props="treeProps"
        node-key="id"
        default-expand-all
        class="permission-tree"
      >
        <template #default="{ node, data }">
          <div class="tree-node">
            <span>{{ data.name }}</span>
            <div class="node-actions">
              <el-button link type="primary" size="small" @click="onView(data)">查看</el-button>
              <el-button link size="small" @click="onEdit(data)">编辑</el-button>
              <el-button link type="danger" size="small" @click="onDelete(data)">删除</el-button>
            </div>
          </div>
        </template>
      </el-tree>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { adminPermissionListApi, adminPermissionDeleteApi, type Permission } from '../../api/permission'
import SearchToolbar from '../../components/SearchToolbar.vue'

const router = useRouter()

const loading = ref(false)
const permissions = ref<Permission[]>([])

const query = reactive({
  name: '',
  url: ''
})

// 树形结构配置
const treeProps = {
  children: 'children',
  label: 'name'
}

// 计算树形数据
const permissionTree = computed(() => {
  return permissions.value
})

async function load() {
  loading.value = true
  try {
    const res = await adminPermissionListApi({
      name: query.name || undefined,
      url: query.url || undefined
    })
    permissions.value = res || []
  } catch (error) {
    console.error('加载权限列表失败:', error)
    ElMessage.error('加载权限列表失败')
  } finally {
    loading.value = false
  }
}

function reset() {
  query.name = ''
  query.url = ''
  load()
}

function handleQuery() {
  load()
}

function onView(data: Permission) {
  router.push(`/permission/${data.id}`)
}

function onEdit(data: Permission) {
  router.push(`/permission/edit/${data.id}`)
}

async function onDelete(data: Permission) {
  try {
    await ElMessageBox.confirm(
      `确认删除权限「${data.name}」？`,
      '确认操作',
      { type: 'warning', confirmButtonText: '确认', cancelButtonText: '取消' }
    )
    
    loading.value = true
    await adminPermissionDeleteApi({ id: data.id })
    
    // 从列表中移除该权限
    const removeFromTree = (nodes: Permission[]) => {
      for (let i = 0; i < nodes.length; i++) {
        if (nodes[i].id === data.id) {
          nodes.splice(i, 1)
          return true
        }
        if (nodes[i].children && nodes[i].children.length > 0) {
          if (removeFromTree(nodes[i].children!)) {
            return true
          }
        }
      }
      return false
    }
    
    removeFromTree(permissions.value)
    
    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
      console.error('删除权限失败:', error)
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => load())
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;}

.permission-tree {
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