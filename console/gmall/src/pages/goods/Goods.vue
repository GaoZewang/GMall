<template>
  <div class="page">
    <el-card shadow="never">
      <!-- 搜索栏（使用组件） -->
      <SearchToolbar :loading="loading" @reset="reset" @query="handleQuery">
        <template #search>
          <el-input
            v-model="query.goods_name"
            placeholder="搜索商品名称"
            clearable
            style="max-width: 320px"
            @keyup.enter="load(1)"
          />
          <el-select v-model="query.goods_status" placeholder="状态" clearable style="width: 140px">
            <el-option label="上架" :value="1" />
            <el-option label="下架" :value="0" />
          </el-select>
        </template>
        <template #actions>
          <el-button type="primary" @click="router.push('/goods/create')">新增商品</el-button>
        </template>
      </SearchToolbar>

      <el-table :data="rows" v-loading="loading" class="table" row-key="id">
        <el-table-column label="ID" prop="id" width="80" />

        <el-table-column label="封面" width="84">
          <template #default="{ row }">
            <el-image
              :src="row.cover_image"
              fit="cover"
              style="width: 56px; height: 56px; border-radius: 12px"
              :preview-src-list="[row.cover_image]"
              preview-teleported
            />
          </template>
        </el-table-column>

        <el-table-column label="商品信息" min-width="180">
          <template #default="{ row }">
            <div class="gname">{{ row.goods_name }}</div>
            <div class="gsub">{{ row.subtitle }}</div>
          </template>
        </el-table-column>

        <el-table-column label="现有库存" min-width="80">
            <template #default="{ row }">
              <div class="">10</div>
            </template>
        </el-table-column>

        <el-table-column label="累计销量" min-width="80">
            <template #default="{ row }">
              <div class="">100</div>
            </template>
        </el-table-column>
        
        <!-- <el-table-column label="商户ID" prop="merchant_id" width="110" /> -->
        <!-- <el-table-column label="类目ID" prop="category_id" width="110" value="10"/> -->

        <!-- ✅ 状态：Switch -->
        <el-table-column label="状态" width="150">
          <template #default="{ row }">
            <div class="statusWrap">
              <el-switch
                :model-value="row.goods_status"
                :active-value="1"
                :inactive-value="0"
                :loading="statusLoading[row.id] === true"
                @change="(v:number)=>onChangeStatus(row, v)"
              />
              <span class="statusText">{{ row.goods_status === 1 ? '上架' : '下架' }}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="150" fixed="right">
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
import { adminGoodsListApi, adminGoodsStatusApi, adminGoodsDeleteApi, type GoodsItem } from '../../api/goods'
import SearchToolbar from '../../components/SearchToolbar.vue'

const router = useRouter()

const loading = ref(false)
const rows = ref<GoodsItem[]>([])

const query = reactive({
  goods_name: '',
  goods_status: '' as number | '',
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
    const data = await adminGoodsListApi({
      page,
      per_page: pagination.per_page,
      goods_name: query.goods_name || undefined,
      goods_status: query.goods_status === '' ? undefined : query.goods_status,
    })

    rows.value = data.list || []
    pagination.total = data.pagination.total
    pagination.per_page = data.pagination.per_page
    pagination.current_page = data.pagination.current_page
    pagination.last_page = data.pagination.last_page
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
  query.goods_name = ''
  query.goods_status = ''
  pagination.per_page = 10
  load(1)
}

function handleQuery() {
  load(1)
}

async function onChangeStatus(row: GoodsItem, nextStatus: number) {
  const prev = row.goods_status

  // 避免重复点击
  if (statusLoading[row.id]) return

  // 可选：确认
  try {
    await ElMessageBox.confirm(
      `确认将商品「${row.goods_name}」设置为${nextStatus === 1 ? '上架' : '下架'}？`,
      '确认操作',
      { type: 'warning', confirmButtonText: '确认', cancelButtonText: '取消' }
    )
  } catch {
    // 用户取消：恢复 UI
    row.goods_status = prev
    return
  }

  statusLoading[row.id] = true
  // 先乐观更新
  row.goods_status = nextStatus

  try {
    await adminGoodsStatusApi({ id: row.id, status: nextStatus })
    ElMessage.success('操作成功')
  } catch {
    // 失败回滚
    row.goods_status = prev
  } finally {
    statusLoading[row.id] = false
  }
}

function onView(row: GoodsItem) {
  router.push(`/goods/${row.id}`)
}

function onEdit(row: GoodsItem) {
  router.push(`/goods/edit/${row.id}`)
}

async function onDelete(row: GoodsItem) {
  try {
    await ElMessageBox.confirm(
      `确认删除商品「${row.goods_name}」？`,
      '确认操作',
      { type: 'warning', confirmButtonText: '确认', cancelButtonText: '取消' }
    )
    
    loading.value = true
    await adminGoodsDeleteApi({ id: row.id })
    
    // 从列表中移除该商品
    const index = rows.value.findIndex(item => item.id === row.id)
    if (index > -1) {
      rows.value.splice(index, 1)
      pagination.total--
    }
    
    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
      console.error('删除商品失败:', error)
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
.gname{font-weight:900;}
.gsub{margin-top:4px;color:var(--sub);font-size:12px;line-height:1.2;}

.statusWrap{display:flex;align-items:center;gap:10px;}
.statusText{font-size:12px;color:var(--sub);font-weight:700;}

.pager{display:flex;justify-content:space-between;align-items:center;margin-top:12px;gap:10px;flex-wrap:wrap;}
.total{color:var(--sub);font-size:12px;}
</style>
