<template>
  <div class="page">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <SearchToolbar :loading="loading" @reset="reset" @query="handleQuery">
        <template #search>
          <el-input
            v-model="query.name"
            placeholder="搜索店铺名称"
            clearable
            style="max-width: 320px"
            @keyup.enter="load(1)"
          />
          <el-input-number
            v-model="query.merchant_id"
            placeholder="商户ID"
            :min="1"
            style="max-width: 200px"
          />
        </template>
        <template #actions>
          <el-button type="primary" @click="router.push('/shop/create')">新增店铺</el-button>
        </template>
      </SearchToolbar>

      <!-- 店铺列表 -->
      <el-table :data="rows" v-loading="loading" class="table" row-key="id">
        <el-table-column label="ID" prop="id" width="80" />
        <el-table-column label="店铺名称" prop="name" min-width="200" />
        <el-table-column label="商户ID" prop="merchant_id" width="100" />
        <el-table-column label="商户名称" prop="merchant_name" min-width="150" />
        <el-table-column label="管理员" prop="admin_user_name" min-width="150" />
        <el-table-column label="地址" prop="address" min-width="200" />
        <el-table-column label="联系电话" prop="contact_phone" width="140" />
        <el-table-column label="余额" prop="balance" width="120" />
        <el-table-column label="营业额" prop="revenue" width="120" />
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
        <el-table-column label="创建时间" prop="created_at" width="180" />
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
import { ElMessage } from 'element-plus'
import { adminShopListApi, adminShopDeleteApi, type Shop } from '../../api/shop'
import SearchToolbar from '../../components/SearchToolbar.vue'

const router = useRouter()

const loading = ref(false)
const rows = ref<Shop[]>([])

const query = reactive({
  name: '',
  merchant_id: 0
})

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
    const res = await adminShopListApi({
      page,
      per_page: pagination.per_page,
      merchant_id: query.merchant_id,
      name: query.name || undefined
    })
    rows.value = res.list || []
    pagination.total = res.pagination?.total || 0
    pagination.per_page = res.pagination?.per_page || 10
    pagination.current_page = res.pagination?.current_page || 1
    pagination.last_page = res.pagination?.last_page || 1
  } catch (error) {
    console.error('加载店铺列表失败:', error)
    ElMessage.error('加载店铺列表失败')
  } finally {
    loading.value = false
  }
}

function reset() {
  query.name = ''
  query.merchant_id = 0
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

async function onChangeStatus(row: Shop, nextStatus: number) {
  const prev = row.status
  // 避免重复点击
  if (statusLoading[row.id]) return
  statusLoading[row.id] = true
  // 先乐观更新
  row.status = nextStatus

  try {
    // 这里需要调用更新状态的API，暂时注释
    // await adminShopUpdateApi({ id: row.id, status: nextStatus })
    ElMessage.success('操作成功')
  } catch {
    // 失败回滚
    row.status = prev
    ElMessage.error('操作失败')
  } finally {
    statusLoading[row.id] = false
  }
}

function onView(row: Shop) {
  router.push(`/shop/${row.id}`)
}

function onEdit(row: Shop) {
  router.push(`/shop/edit/${row.id}`)
}

async function onDelete(row: Shop) {
  try {
    await ElMessageBox.confirm(
      `确认删除店铺「${row.name}」？`,
      '确认操作',
      { type: 'warning', confirmButtonText: '确认', cancelButtonText: '取消' }
    )
    
    loading.value = true
    await adminShopDeleteApi({ id: row.id })
    
    // 从列表中移除该店铺
    const index = rows.value.findIndex(item => item.id === row.id)
    if (index > -1) {
      rows.value.splice(index, 1)
      pagination.total--
    }
    
    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
      console.error('删除店铺失败:', error)
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