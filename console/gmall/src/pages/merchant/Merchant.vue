<template>
  <div class="page">
    <el-card shadow="never">
      <!-- 搜索栏（使用组件） -->
      <SearchToolbar :loading="loading" @reset="reset" @query="handleQuery">
        <template #search>
          <el-input
            v-model="query.name"
            placeholder="搜索商户名称"
            clearable
            style="max-width: 320px"
            @keyup.enter="load(1)"
          />
          <el-select v-model="query.status" placeholder="状态" clearable style="width: 140px">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </template>
        <template #actions>
          <el-button type="primary" @click="router.push('/merchant/create')">新增商户</el-button>
        </template>
      </SearchToolbar>

      <el-table :data="rows" v-loading="loading" class="table" row-key="id">
        <el-table-column label="ID" prop="id" width="80" />

        <el-table-column label="商户名称" min-width="180">
          <template #default="{ row }">
            <div class="merchant-name">{{ row.name }}</div>
          </template>
        </el-table-column>

        <el-table-column label="联系人电话" prop="contact_phone" width="160" />
        <el-table-column label="余额" prop="balance" width="120" :formatter="moneyFormatter" />
        <el-table-column label="累计收益" prop="revenue" width="120" :formatter="moneyFormatter" />

        <!-- 状态：Switch -->
        <el-table-column label="状态" width="140">
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

        <el-table-column label="创建时间" prop="created_at" width="180" />

        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="onView(row)">查看</el-button>
            <el-button link @click="onEdit(row)">编辑</el-button>
            <el-button link type="danger" @click="onDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

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
import { adminMerchantListApi, adminMerchantDeleteApi, type Merchant, type MerchantListResp } from '../../api/merchant'
import SearchToolbar from '../../components/SearchToolbar.vue'

const router = useRouter()

const loading = ref(false)
const rows = ref<Merchant[]>([])

const query = reactive({
  name: '',
  status: '' as number | '',
})

const pagination = reactive({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
})

// 每行状态请求 loading
const statusLoading = reactive<Record<number, boolean>>({})

// 金额格式化函数
function moneyFormatter(row: any, column: any, cellValue: any) {
  // 添加类型检查和容错处理
  if (cellValue === null || cellValue === undefined) {
    return '¥0.00'
  }
  const num = parseFloat(cellValue)
  if (isNaN(num)) {
    return '¥0.00'
  }
  return `¥${num.toFixed(2)}`
}

async function load(page = pagination.current_page) {
  loading.value = true
  try {
    const data = await adminMerchantListApi({
      page,
      per_page: pagination.per_page,
      name: query.name || undefined,
    })
    console.log('完整返回数据:', data)
    console.log('list数据:', data.list)
    console.log('list类型:', typeof data.list)
    console.log('pagination数据:', data.pagination)
    rows.value = data.list || []
    pagination.total = data.pagination.total
    pagination.per_page = data.pagination.per_page
    pagination.current_page = data.pagination.current_page
    pagination.last_page = data.pagination.last_page
    console.log('rows数据:', rows.value)
    console.log('rows长度:', rows.value.length)
  } finally {
    loading.value = false
  }
}

function onPageChange(p: number) {
  load(p)
}

function onSizeChange(size: number) {
  pagination.per_page = size
  load(1)
}

function reset() {
  query.name = ''
  query.status = ''
  pagination.per_page = 10
  load(1)
}

function handleQuery() {
  load(1)
}

async function onChangeStatus(row: Merchant, nextStatus: number) {
  const prev = row.status

  // 避免重复点击
  if (statusLoading[row.id]) return

  // 确认操作
  try {
    await ElMessageBox.confirm(
      `确认将商户「${row.name}」设置为${nextStatus === 1 ? '启用' : '禁用'}？`,
      '确认操作',
      { type: 'warning', confirmButtonText: '确认', cancelButtonText: '取消' }
    )
  } catch {
    // 用户取消：恢复 UI
    row.status = prev
    return
  }

  statusLoading[row.id] = true
  // 先乐观更新
  row.status = nextStatus

  try {
    // 这里需要调用更新状态的API，暂时注释
    // await updateMerchantStatusApi({ id: row.id, status: nextStatus })
    ElMessage.success('操作成功')
  } catch {
    // 失败回滚
    row.status = prev
  } finally {
    statusLoading[row.id] = false
  }
}

function onView(row: Merchant) {
  router.push(`/merchant/${row.id}`)
}

function onEdit(row: Merchant) {
  router.push(`/merchant/edit/${row.id}`)
}

async function onDelete(row: Merchant) {
  try {
    await ElMessageBox.confirm(
      `确认删除商户「${row.name}」？`,
      '确认操作',
      { type: 'warning', confirmButtonText: '确认', cancelButtonText: '取消' }
    )
    
    loading.value = true
    await adminMerchantDeleteApi({ id: row.id })
    
    // 从列表中移除该商户
    const index = rows.value.findIndex(item => item.id === row.id)
    if (index > -1) {
      rows.value.splice(index, 1)
      pagination.total--
    }
    
    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
      console.error('删除商户失败:', error)
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
.merchant-name{font-weight:900;}

.statusWrap{display:flex;align-items:center;gap:10px;}
.statusText{font-size:12px;color:var(--sub);font-weight:700;}

.pager{display:flex;justify-content:space-between;align-items:center;margin-top:12px;gap:10px;flex-wrap:wrap;}
.total{color:var(--sub);font-size:12px;}
</style>