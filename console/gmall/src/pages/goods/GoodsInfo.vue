<template>
  <div class="page">
    <el-card shadow="never" v-loading="loading">

      <div v-if="info" class="grid">
        <!-- 左侧：基本信息 -->
        <el-card shadow="never" class="panel">
          <div class="panelTitle">基本信息</div>

          <div class="name">{{ info.goods_name }}</div>
          <div class="sub">{{ info.subtitle }}</div>

          <div class="tags">
            <el-tag :type="info.goods_status === 1 ? 'success' : 'info'">
              {{ info.goods_status === 1 ? '上架' : '下架' }}
            </el-tag>
            <el-tag type="danger" v-if="info.is_deleted === 1">已删除</el-tag>
          </div>

          <div class="kv">
            <!-- <div class="k">商品ID</div><div class="v">{{ info.id }}</div> -->
            <div class="k">商品编码</div><div class="v">{{ info.goods_code }}</div>
            <!-- <div class="k">商户ID</div><div class="v">{{ info.merchant_id }}</div> -->
            <div class="k">商户名称</div><div class="v">{{ info.merchant_name }}</div>
            <div class="k">店铺名称</div><div class="v">{{ info.shop_name }}</div>
            <div class="k">类目ID</div><div class="v">{{ info.category_name}}</div>
            <div class="k">创建时间</div><div class="v">{{ info.created_at }}</div>
            <div class="k">更新时间</div><div class="v">{{ info.updated_at }}</div>
          </div>

          <el-divider />

          <!-- 属性模板 -->
          <div class="panelTitle">属性</div>
          <div v-if="attrPairs.length" class="attrs">
            <div class="attrRow" v-for="a in attrPairs" :key="a.key">
              <div class="attrKey">{{ a.key }}</div>
              <div class="attrVal">{{ a.value }}</div>
            </div>
          </div>
          <div v-else class="empty">暂无属性</div>

          <el-divider />

          <div class="panelTitle">规格</div>
          <div v-if="info.attrs_template?.specs?.length" class="specs">
            <div class="spec" v-for="sp in info.attrs_template.specs" :key="sp.name">
              <div class="specName">{{ sp.name }}</div>
              <div class="specVals">
                <el-tag v-for="v in sp.values" :key="v" effect="plain">{{ v }}</el-tag>
              </div>
            </div>
          </div>
          <div v-else class="empty">暂无规格</div>
        </el-card>

        <!-- 右侧：图片 -->
        <el-card shadow="never" class="panel">
          <div class="panelTitle">图片</div>

          <div class="coverRow">
            <el-image
              :src="info.cover_image"
              fit="cover"
              class="cover"
              :preview-src-list="previewList"
              preview-teleported
            />
            <div class="hint">
              <div class="hn">封面</div>
              <div class="hs">点击可预览</div>
            </div>
          </div>

          <el-divider />

          <div class="panelTitle">轮播图</div>
          <el-carousel height="200px" indicator-position="outside" v-if="info.images?.length">
            <el-carousel-item v-for="(img, idx) in info.images" :key="idx">
              <el-image
                :src="img"
                fit="cover"
                class="banner"
                :preview-src-list="previewList"
                preview-teleported
              />
            </el-carousel-item>
          </el-carousel>
          <div v-else class="empty">暂无轮播图</div>
        </el-card>
      </div>

      <!-- 图文详情 -->
      <el-card v-if="info" shadow="never" class="mt12">
        <div class="panelTitle">图文详情</div>
        <div class="desc" v-html="info.description"></div>
      </el-card>

      <!-- SKU 列表 -->
      <el-card v-if="info" shadow="never" class="mt12">
        <div class="panelTitle">SKU</div>

        <el-table :data="info.sku" class="table" row-key="id">
          <el-table-column label="ID" prop="id" width="80" />
          <el-table-column label="SKU编码" prop="sku_code" min-width="160" />
          <el-table-column label="条码" prop="bar_code" min-width="150" />

          <el-table-column label="规格" min-width="220">
            <template #default="{ row }">
              <div class="skuAttrs">
                <el-tag
                  v-for="(val, key) in row.attrs"
                  :key="key"
                  effect="plain"
                >
                  {{ key }}：{{ val }}
                </el-tag>
              </div>
            </template>
          </el-table-column>

          <el-table-column label="成本价" prop="cost_price" width="120" />
          <el-table-column label="售价" prop="base_price" width="120" />

          <el-table-column label="状态" width="100">
            <template #default="{ row }">
              <el-tag :type="row.status === 1 ? 'success' : 'info'">
                {{ row.status === 1 ? '启用' : '禁用' }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="更新时间" prop="updated_at" min-width="170" />
        </el-table>
      </el-card>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { adminGoodsInfoApi, type GoodsInfo } from '../../api/goods'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const info = ref<GoodsInfo | null>(null)

const previewList = computed(() => {
  const imgs: string[] = []
  if (info.value?.cover_image) imgs.push(info.value.cover_image)
  if (info.value?.images?.length) imgs.push(...info.value.images)
  return imgs
})

const attrPairs = computed(() => {
  const attrs = info.value?.attrs_template?.attrs || {}
  return Object.keys(attrs).map((k) => ({ key: k, value: attrs[k] }))
})

async function load() {
  const id = Number(route.params.id)
  if (!id) return
  loading.value = true
  try {
    const res = await adminGoodsInfoApi(id)
    info.value = {
      id: res.id,
      merchant_id: res.merchant_id,
      merchant_name: res.merchant_name,
      shop_name: res.shop_name,
      goods_name: res.goods_name,
      subtitle: res.subtitle,
      category_id: res.category_id,
      category_name: res.category_name,
      goods_code:res.goods_code,
      cover_image: res.cover_image,
      images: res.images || [],
      description: res.description || '',
      attrs_template: res.attrs_template || { attrs: {}, specs: [] },
      goods_status: res.goods_status,
      is_deleted: res.is_deleted,
      created_at: res.created_at,
      updated_at: res.updated_at,
      sku: res.sku || []
    }
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;}
.mt12{margin-top:12px;}

.head{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;}
.title{font-weight:900;}
.ops{display:flex;gap:10px;align-items:center;}

.grid{
  margin-top: 12px;
  display:grid;
  grid-template-columns: 1fr 420px;
  gap: 16px;
}
@media (max-width: 1024px){
  .grid{grid-template-columns:1fr;}
}

.panel{border-radius:16px;}
.panelTitle{font-weight:900;margin-bottom:16px;font-size:16px;}

.coverRow{display:flex;flex-direction:column;align-items:center;gap:12px;margin-bottom:8px;}
.cover{width:100%;max-width:320px;height:240px;border-radius:16px;object-fit:cover;}
.banner{width:100%;height:280px;border-radius:16px;object-fit:cover;}

/* 优化轮播图容器 */
:deep(.el-carousel) {
  border-radius: 16px;
  overflow: hidden;
}

:deep(.el-carousel__container) {
  background-color: #f5f7fa;
}

:deep(.el-carousel__item) {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 优化图片预览 */
:deep(.el-image) {
  cursor: pointer;
  transition: transform 0.3s ease;
}

:deep(.el-image:hover) {
  transform: scale(1.02);
}

/* 优化提示文本 */
.hint {
  text-align: center;
  width: 100%;
  margin-top: 12px;
  padding: 0 16px;
}

.hn {
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 6px;
  color: var(--text);
}

.hs {
  color: var(--sub);
  font-size: 12px;
  line-height: 1.4;
}

.name{font-weight:900;font-size:18px;}
.sub{margin-top:6px;color:var(--sub);font-size:12px;line-height:1.4;}
.tags{margin-top:10px;display:flex;gap:10px;flex-wrap:wrap;}

.kv{
  margin-top:12px;
  display:grid;
  grid-template-columns: 90px 1fr;
  gap: 8px 12px;
}
.k{color:var(--sub);font-size:12px;}
.v{font-weight:700;word-break:break-all;}

.attrs{display:flex;flex-direction:column;gap:8px;}
.attrRow{
  display:flex;justify-content:space-between;gap:12px;
  padding:10px;border:1px solid var(--line);border-radius:12px;background:#fff;
}
.attrKey{color:var(--sub);font-size:12px;font-weight:800;}
.attrVal{font-weight:800;}

.specs{display:flex;flex-direction:column;gap:12px;}
.spec{padding:10px;border:1px solid var(--line);border-radius:12px;background:#fff;}
.specName{font-weight:900;}
.specVals{margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;}

.desc{
  padding: 10px;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: #fff;
  overflow: auto;
}
/* 让富文本更像简洁后台 */
.desc :deep(p){margin:0 0 10px;}
.desc :deep(img){max-width:100%;border-radius:12px;}

.table{width:100%;}
.skuAttrs{display:flex;gap:8px;flex-wrap:wrap;}

.empty{color:var(--sub);font-size:12px;padding:10px 0;}
</style>
