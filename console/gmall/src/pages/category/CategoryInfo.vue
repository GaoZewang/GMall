<template>
  <div class="page">
    <el-card shadow="never" v-loading="loading">
      <div class="head">
        <div class="title">分类详情</div>
        <div class="ops">
          <el-button @click="back">返回</el-button>
        </div>
      </div>

      <div v-if="info" class="grid">
        <el-card shadow="never" class="panel">
          <div class="panelTitle">基本信息</div>

          <div class="name">{{ info.category_name }}</div>

          <div class="tags">
            <el-tag :type="info.category_status === 1 ? 'success' : 'info'">
              {{ info.category_status === 1 ? '启用' : '禁用' }}
            </el-tag>
            <el-tag type="info">
              {{ info.is_leaf === 1 ? '叶子节点' : '非叶子节点' }}
            </el-tag>
          </div>

          <div class="kv">
            <div class="k">分类ID</div><div class="v">{{ info.id }}</div>
            <div class="k">父级ID</div><div class="v">{{ info.parent_id }}</div>
            <div class="k">父级名称</div><div class="v">{{ info.parent_info.category_name }}</div>
            <div class="k">分类级别</div><div class="v">{{ info.category_level }}</div>
            <div class="k">排序</div><div class="v">{{ info.sort }}</div>
            <div class="k">创建时间</div><div class="v">{{ info.created_at }}</div>
            <div class="k">更新时间</div><div class="v">{{ info.updated_at }}</div>
          </div>

          <el-divider />

          <div class="panelTitle">属性模板</div>
          <div v-if="info.attrs_template" class="attrsTemplate">
            {{ info.attrs_template }}
          </div>
          <div v-else class="empty">暂无属性模板</div>
        </el-card>
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { adminCategoryInfoApi, type CategoryInfo } from '../../api/category'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const info = ref<CategoryInfo | null>(null)

async function load() {
  const id = Number(route.params.id)
  if (!id) return
  loading.value = true
  try {
    const data = await adminCategoryInfoApi(id)
    info.value = data
  } catch (error) {
    console.error('获取分类详情失败:', error)
  } finally {
    loading.value = false
  }
}

function back() {
  router.back()
}

onMounted(load)
</script>

<style scoped>
.head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.title {
  font-size: 20px;
  font-weight: bold;
}

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

.sub {
  font-size: 14px;
  color: #606266;
  margin-bottom: 16px;
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
  color: #909399;
  font-size: 14px;
}

.v {
  color: #303133;
  font-size: 14px;
  font-weight: 500;
}

.mt12 {
  margin-top: 12px;
}

.desc {
  padding: 16px;
  background-color: #fff;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  min-height: 200px;
}

.empty {
  color: #909399;
  text-align: center;
  padding: 20px;
  background-color: #fff;
  border: 1px dashed #ebeef5;
  border-radius: 4px;
}

.attrsTemplate {
  padding: 16px;
  background-color: #fff;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  min-height: 100px;
  white-space: pre-wrap;
}
</style>
