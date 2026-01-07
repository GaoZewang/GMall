<template>
  <div class="page">
    <el-card shadow="never">
      <!-- 搜索栏（使用组件） -->
      <SearchToolbar :loading="loading" @reset="reset" @query="handleQuery">
        <template #search>
          <el-input
            v-model="query.category_name"
            placeholder="搜索分类名称"
            clearable
            style="max-width: 320px"
            @keyup.enter="load(1)"
          />
          <el-select v-model="query.category_status" placeholder="状态" clearable style="width: 140px">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </template>
        <template #actions>
          <el-button type="primary" @click="router.push('/category/create')">新增分类</el-button>
        </template>
      </SearchToolbar>

      <el-table 
        :data="rows" 
        v-loading="loading" 
        class="table" 
        row-key="id"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      >
        <el-table-column label="ID" prop="id" width="80" />
        <el-table-column label="分类名称" prop="category_name" min-width="200" />
        <el-table-column label="父级ID" prop="parent_id" width="100" />
        <!-- ✅ 状态：Switch -->
        <el-table-column label="状态" width="140">
          <template #default="{ row }">
            <div class="statusWrap">
              <el-switch
                :model-value="row.category_status"
                :active-value="1"
                :inactive-value="0"
                :loading="statusLoading[row.id] === true"
                @change="(v:number)=>onChangeStatus(row, v)"
              />
              <span class="statusText">{{ row.category_status === 1 ? '启用' : '禁用' }}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" @click="onView(row)">查看</el-button>
            <el-button link @click="onEdit(row)">编辑</el-button>
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
import { ElMessage } from 'element-plus'
import { adminCategoryListApi, type CategoryItem } from '../../api/category'
import SearchToolbar from '../../components/SearchToolbar.vue'

const router = useRouter()

const loading = ref(false)
const allRows = ref<CategoryItem[]>([])
const rows = ref<CategoryItem[]>([])

const query = reactive({
  category_name: '',
  category_status: '' as number | '',
})

const pagination = reactive({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
})

// 每行状态请求 loading
const statusLoading = reactive<Record<number, boolean>>({})

// 获取所有分类数据
async function getAllCategories() {
  try {
    // 传递完整的查询参数，确保浏览器调试时能看到请求参数
    const data = await adminCategoryListApi({
      category_name: query.category_name || undefined,
      category_status: query.category_status === '' ? undefined : query.category_status,
    })
    
    // 确保返回的是数组
    if (Array.isArray(data)) {
      return data
    } else if (data && data.data) {
      return Array.isArray(data.data) ? data.data : [data.data]
    } else {
      return []
    }
  } catch (error) {
   
    return []
  }
}

// 构建分类树
function buildCategoryTree(categories: CategoryItem[]) {
  // 创建分类映射，确保每个分类都有children数组
  const categoryMap = new Map<number, CategoryItem>()
  categories.forEach(category => {
    categoryMap.set(category.id, {
      ...category,
      children: category.children || []
    })
  })
  
  // 确保所有分类都有hasChildren属性
  categoryMap.forEach(category => {
    if (category.children && category.children.length > 0) {
      category.hasChildren = true
    } else {
      category.hasChildren = false
    }
  })
  
  // 筛选出顶级分类
  const topLevelCategories = categories.filter(item => item.parent_id === 0)
  console.log('顶级分类:', topLevelCategories)
  
  return {
    allCategories: Array.from(categoryMap.values()),
    topLevelCategories
  }
}

async function load(page = pagination.current_page) {
  loading.value = true
  try {
    // 获取所有分类数据
    const allCategories = await getAllCategories()
    
    if (allCategories.length === 0) {
      console.log('没有获取到分类数据')
      rows.value = []
      pagination.total = 0
      pagination.last_page = 1
      pagination.current_page = page
      return
    }
    
    // 构建分类树
    const { allCategories: fullCategoryList, topLevelCategories } = buildCategoryTree(allCategories)
    
    // 如果没有顶级分类，直接显示所有分类
    const displayCategories = topLevelCategories.length > 0 ? topLevelCategories : fullCategoryList
    
    // 计算总条数
    pagination.total = displayCategories.length
    // 计算总页数
    pagination.last_page = Math.ceil(displayCategories.length / pagination.per_page)
    
    // 计算当前页的分类
    const start = (page - 1) * pagination.per_page
    const end = start + pagination.per_page
    const currentPageCategories = displayCategories.slice(start, end)
    
    console.log('当前页分类:', currentPageCategories)
    
    // 设置当前页的分类数据
    rows.value = currentPageCategories
    pagination.current_page = page
  } catch (error) {
    console.error('加载分类数据失败:', error)
    rows.value = []
  } finally {
    loading.value = false
  }
}



function reset() {
  query.category_name = ''
  query.category_status = ''
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

async function onChangeStatus(row: CategoryItem, nextStatus: number) {
  const prev = row.category_status
  // 避免重复点击
  if (statusLoading[row.id]) return
  statusLoading[row.id] = true
  // 先乐观更新
  row.category_status = nextStatus

  try {
    // 这里需要调用修改状态的API，暂时注释
    // await adminCategoryStatusApi({ id: row.id, status: nextStatus })
    ElMessage.success('操作成功')
  } catch {
    // 失败回滚
    row.category_status = prev
  } finally {
    statusLoading[row.id] = false
  }
}

function onView(row: CategoryItem) {
  router.push(`/category/${row.id}`)
}

function onEdit(row: CategoryItem) {
  router.push(`/category/edit/${row.id}`)
}

onMounted(load)
</script>

<style scoped>
.statusWrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.statusText {
  font-size: 14px;
  color: #606266;
}

.pager {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
  padding: 0 8px;
}

.total {
  font-size: 14px;
  color: #606266;
}
</style>
