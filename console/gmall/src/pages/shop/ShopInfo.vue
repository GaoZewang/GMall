<template>
  <div class="page">
    <el-card shadow="never" v-loading="loading">
      <div v-if="info" class="grid">
        <el-card shadow="never" class="panel">
          <div class="panelTitle">基本信息</div>

          <div class="name">{{ info.name }}</div>

          <div class="tags">
            <el-tag :type="info.status === 1 ? 'success' : 'info'">
              {{ info.status === 1 ? '启用' : '禁用' }}
            </el-tag>
          </div>

          <div class="kv">
            <div class="k">店铺ID</div><div class="v">{{ info.id }}</div>
            <div class="k">商户ID</div><div class="v">{{ info.merchant_id }}</div>
            <div class="k">商户名称</div><div class="v">{{ info.merchant_name || '未知' }}</div>
            <div class="k">管理员ID</div><div class="v">{{ info.admin_user_id }}</div>
            <div class="k">管理员名称</div><div class="v">{{ info.admin_user_name || '未知' }}</div>
            <div class="k">联系电话</div><div class="v">{{ info.contact_phone }}</div>
            <div class="k">余额</div><div class="v">{{ info.balance }}</div>
            <div class="k">营业额</div><div class="v">{{ info.revenue }}</div>
            <div class="k">创建时间</div><div class="v">{{ info.created_at }}</div>
            <div class="k">更新时间</div><div class="v">{{ info.updated_at }}</div>
          </div>

          <el-divider />

          <div class="panelTitle">店铺地址</div>
          <div class="address">{{ info.address || '未设置' }}</div>
        </el-card>
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminShopInfoApi, type Shop } from '../../api/shop'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const info = ref<Shop | null>(null)

async function load() {
  const id = Number(route.params.id)
  if (!id) return
  loading.value = true
  try {
    const res = await adminShopInfoApi(id)
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

.address {
  font-size: 16px;
  line-height: 1.6;
  color: #303133;
  background-color: #ffffff;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #e4e7ed;
}
</style>