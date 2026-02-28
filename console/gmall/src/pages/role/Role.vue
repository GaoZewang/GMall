<template>
  <div class="page">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <SearchToolbar :loading="loading" @reset="reset" @query="handleQuery">
        <template #actions>
          <el-button type="primary" @click="router.push('/role/create')">新增角色</el-button>
        </template>
      </SearchToolbar>

      <!-- 角色列表 -->
      <el-table :data="rows" v-loading="loading" class="table" row-key="id">
        <el-table-column label="ID" prop="id" width="80" />
        <el-table-column label="角色名称" prop="name" min-width="150" />
        <el-table-column label="角色标识" prop="slug" min-width="150" />
        <el-table-column label="角色描述" prop="description" min-width="200" />
        <el-table-column label="状态" width="150">
          <template #default="{ row }">
            <div class="statusWrap">
              <el-switch
                :model-value="row.status"
                :active-value="1"
                :inactive-value="0"
                :loading="statusLoading[row.id] === true"
                @change="(v:number)=>onChangeStatus(row, v)"
              />
              <span class="statusText">{{ row.status === 1 ? '启用' : '禁用' }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="onView(row)">查看</el-button>
            <el-button link @click="onEdit(row)">编辑</el-button>
            <el-button link type="danger" @click="onDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pager">
        <div class="total">共 {{ pagination.total }} 条</div>
        <el-pagination
          background
          layout="prev, pager, next, sizes"
          :total="pagination.total"
          :page-size="pagination.per_page"
          :current-page="pagination.current_page"
          :page-sizes="[10, 20, 50, 100]"
          @current-change="onPageChange"
          @size-change="onSizeChange"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { adminRoleListApi, adminRoleDeleteApi, adminRoleUpdateApi, type Role } from '../../api/role'
import SearchToolbar from '../../components/SearchToolbar.vue'

const router = useRouter()

const loading = ref(false)
const rows = ref<Role[]>([])

const pagination = reactive({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
})

// 每行状态请求 loading
const statusLoading = reactive<Record<number, boolean>>({})

async function load(page = pagination.current_page) {
  loading.value = true
  try {
    const res = await adminRoleListApi({
      page,
      per_page: pagination.per_page
    })
    rows.value = res.list || []
    pagination.total = res.pagination?.total || 0
    pagination.per_page = res.pagination?.per_page || 10
    pagination.current_page = res.pagination?.current_page || 1
    pagination.last_page = res.pagination?.last_page || 1
  } catch (error) {
    console.error('加载角色列表失败:', error)
    ElMessage.error('加载角色列表失败')
  } finally {
    loading.value = false
  }
}

function reset() {
  pagination.per_page = 10
  load(1)
}

function handleQuery() {
  load(1)
}

function onPageChange(p: number) {
  load(p)
}

function onSizeChange(size: number) {
  pagination.per_page = size
  load(1)
}

async function onChangeStatus(row: Role, nextStatus: number) {
  const prev = row.status
  // 避免重复点击
  if (statusLoading[row.id]) return
  statusLoading[row.id] = true
  // 先乐观更新
  row.status = nextStatus

  try {
    await adminRoleUpdateApi({
      id: row.id,
      name: row.name,
      slug: row.slug || '',
      description: row.description,
      status: nextStatus
    })
    ElMessage.success('操作成功')
  } catch {
    // 失败回滚
    row.status = prev
    ElMessage.error('操作失败')
  } finally {
    statusLoading[row.id] = false
  }
}

function onView(row: Role) {
  router.push(`/role/${row.id}`)
}

function onEdit(row: Role) {
  router.push(`/role/edit/${row.id}`)
}

async function onDelete(row: Role) {
  try {
    await ElMessageBox.confirm(
      `确认删除角色「${row.name}」？`,
      '确认操作',
      { type: 'warning', confirmButtonText: '确认', cancelButtonText: '取消' }
    )
    
    loading.value = true
    await adminRoleDeleteApi({ id: row.id })
    
    // 从列表中移除该角色
    const index = rows.value.findIndex(item => item.id === row.id)
    if (index > -1) {
      rows.value.splice(index, 1)
      pagination.total--
    }
    
    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
      console.error('删除角色失败:', error)
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => load(1))
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;}
.table{width:100%;}
.statusWrap{display:flex;align-items:center;gap:10px;}
.statusText{font-size:12px;color:var(--sub);font-weight:700;}
.pager{display:flex;justify-content:space-between;align-items:center;margin-top:12px;gap:10px;flex-wrap:wrap;}
.total{color:var(--sub);font-size:12px;}
</style>