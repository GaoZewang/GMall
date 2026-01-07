<template>
  <div class="page">
    <el-card shadow="never">
      <PageHeader :title="isEdit ? '编辑商品' : '新增商品'">
        <el-button @click="back">返回</el-button>
      </PageHeader>

      <el-form ref="formRef" :model="form" :rules="rules" label-width="96px" class="form">
        <!-- 基本信息 -->
        <div class="sectionTitle">基本信息</div>
        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="商品名称" prop="goods_name">
              <el-input v-model="form.goods_name" placeholder="例如：苹果 iPhone 16" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="副标题" prop="subtitle">
              <el-input v-model="form.subtitle" placeholder="一句话卖点" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="类目" prop="category_id">
              <el-input-number v-model="form.category_id" :min="1" style="width: 100%" />
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="品牌" prop="brand_id">
              <el-input-number v-model="form.brand_id" :min="1" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 图片（上传+URL） -->
        <div class="sectionTitle">图片</div>

        <el-row :gutter="12">
          <el-col :xs="24" :md="12">
            <el-form-item label="封面图" prop="cover_image">
              <div class="uploadLine">
                <el-upload
                  class="uploader"
                  :show-file-list="false"
                  :http-request="uploadCoverRequest"
                  :before-upload="beforeImage"
                >
                  <el-button type="primary" plain :loading="uploadingCover">
                    {{ form.cover_image ? '重新上传' : '上传封面' }}
                  </el-button>
                </el-upload>

                <el-input v-model="form.cover_image" placeholder="也可粘贴图片URL" />
              </div>
            </el-form-item>
          </el-col>

          <el-col :xs="24" :md="12">
            <el-form-item label="预览">
              <el-image
                v-if="form.cover_image"
                :src="form.cover_image"
                fit="cover"
                style="width: 72px; height: 72px; border-radius: 14px"
                :preview-src-list="previewList"
                preview-teleported
              />
              <div v-else class="tip">上传封面后显示预览</div>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="轮播图">
          <div class="imgs">
            <div class="uploadLine">
              <el-upload
                multiple
                :show-file-list="false"
                :http-request="uploadBannerRequest"
                :before-upload="beforeImage"
              >
                <el-button type="primary" plain :loading="uploadingBanner">
                  上传轮播图（可多选）
                </el-button>
              </el-upload>

              <div class="tip">上传成功会自动加入下方列表</div>
            </div>

            <div class="imgRow" v-for="(img, idx) in form.images" :key="idx">
              <el-image
                v-if="img"
                :src="img"
                fit="cover"
                style="width: 56px; height: 56px; border-radius: 12px"
                :preview-src-list="previewList"
                preview-teleported
              />
              <el-input v-model="form.images[idx]" placeholder="也可粘贴图片URL" />
              <el-button @click="removeImage(idx)">删除</el-button>
            </div>

            <el-button @click="addImage">手动添加一行URL</el-button>
          </div>
        </el-form-item>

        <!-- 详情 -->
        <div class="sectionTitle">图文详情（HTML）</div>
        <el-form-item prop="description" label="详情">
          <el-input v-model="form.description" type="textarea" :rows="6" placeholder="<p>图文详情</p>" />
        </el-form-item>

        <!-- 规格模板 -->
        <div class="sectionTop">
          <div class="sectionTitle">规格模板（可自定义）</div>
          <div class="sectionOps">
            <el-input v-model="skuPrefix" placeholder="SKU前缀，例如：IP16" style="width: 220px" />
            <el-button type="primary" plain @click="addSpec">添加规格项</el-button>
            <el-button @click="fillSkuCodes">生成 SKU 编码</el-button>
          </div>
        </div>

        <div class="specWrap">
          <el-card v-for="(sp, idx) in specs" :key="idx" shadow="never" class="specCard">
            <div class="specHead">
              <el-input
                v-model="sp.key"
                placeholder="规格名，例如：颜色/容量"
                style="max-width: 240px"
                @blur="syncSkuAttrsKeys"
              />
              <div class="specOps">
                <el-button @click="addSpecValue(idx)" plain>添加值</el-button>
                <el-button @click="removeSpec(idx)" type="danger" plain>删除规格</el-button>
              </div>
            </div>

            <div class="specVals">
              <div class="valRow" v-for="(v, vIdx) in sp.values" :key="vIdx">
                <el-input
                  v-model="sp.values[vIdx]"
                  placeholder="规格值，例如：黑色"
                  @blur="onSpecValueBlur(idx, vIdx)"
                />
                <el-button @click="removeSpecValue(idx, vIdx)">删除</el-button>
              </div>
            </div>

            <el-divider />

            <div class="mapTitle">值→代码（可选）</div>
            <div class="mapWrap">
              <div class="mapRow" v-for="(v, vIdx) in sp.values" :key="`m_${vIdx}`">
                <div class="mapLeft">
                  <span class="mapLabel">{{ (v || '').trim() || '（空）' }}</span>
                </div>
                <el-input
                  v-model="sp.codes[(v || '').trim()]"
                  placeholder="例如：黑色填 B，白色填 W"
                  style="max-width: 220px"
                  :disabled="!(v || '').trim()"
                />
              </div>
              <div class="tip">不填则兜底：数字/英文保留；中文建议用映射更准确。</div>
            </div>
          </el-card>

          <div v-if="specs.length === 0" class="tip">
            当前无规格：你仍可手动添加 SKU（attrs 为空对象）。
          </div>

          <!-- ✅ 一键生成 SKU：放在规格模块右下角 -->
          <div class="specFooter">
            <el-button type="primary" :disabled="specs.length === 0" @click="buildSkuFromSpecs">
              一键生成 SKU
            </el-button>
          </div>
        </div>

        <!-- SKU 列表 -->
        <div class="sectionTop mt12">
          <div class="sectionTitle">SKU 列表（可自定义）</div>
          <div class="sectionOps">
            <el-button type="primary" plain @click="addSku">添加 SKU</el-button>
          </div>
        </div>

        <el-table :data="form.sku_list" class="table" row-key="__key">
          <el-table-column label="SKU编码" min-width="170">
            <template #default="{ row }">
              <el-input v-model="row.sku_code" placeholder="例如：IP16-B-128G" />
            </template>
          </el-table-column>

          <el-table-column label="条码" min-width="170">
            <template #default="{ row }">
              <el-input v-model="row.bar_code" placeholder="6900..." />
            </template>
          </el-table-column>

          <el-table-column label="规格属性" min-width="320">
            <template #default="{ row }">
              <template v-if="specs.length > 0">
                <div class="skuAttrGrid">
                  <div v-for="(sp, sIdx) in specs" :key="sIdx" class="skuAttrItem">
                    <div class="skuAttrKey">{{ sp.key || '规格' }}</div>
                    <el-select
                      v-model="row.attrs[sp.key]"
                      placeholder="请选择"
                      clearable
                      style="width: 150px"
                      :disabled="!sp.key"
                      @change="onSkuAttrsChange(row)"
                    >
                      <el-option v-for="opt in cleanedValues(sp.values)" :key="opt" :label="opt" :value="opt" />
                    </el-select>
                  </div>
                </div>
              </template>
              <template v-else>
                <el-tag effect="plain">无规格</el-tag>
              </template>
            </template>
          </el-table-column>

          <el-table-column label="成本价" width="140">
            <template #default="{ row }">
              <el-input-number v-model="row.cost_price" :min="0" :step="1" style="width: 120px" />
            </template>
          </el-table-column>

          <el-table-column label="售价" width="140">
            <template #default="{ row }">
              <el-input-number v-model="row.base_price" :min="0" :step="1" style="width: 120px" />
            </template>
          </el-table-column>

          <el-table-column label="操作" width="90" fixed="right">
            <template #default="{ $index }">
              <el-button link type="danger" @click="removeSku($index)">删除</el-button>
            </template>
          </el-table-column>
        </el-table>

        <!-- <div class="tip mt12">
          - 有规格：提交 attrs_template + sku_list（每条 SKU 的 attrs 对应规格值）<br />
          - 无规格：attrs_template 为空，SKU 的 attrs 为空对象
        </div> -->
      </el-form>
    </el-card>

    <!-- ✅ 底部居中保存按钮 -->
<div class="submit-bar">
  <el-button type="primary" size="large" :loading="submitting" @click="submit">保存</el-button>
    </div>
  </div>
  
</template>

<script setup lang="ts">
import { computed, reactive, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import type { FormInstance, FormRules, UploadRequestOptions } from 'element-plus'
import { ElMessage } from 'element-plus'
import { adminGoodsCreateApi, adminGoodsInfoApi, adminGoodsUpdateApi } from '../../api/goods'
import { adminUploadSingle } from '../../api/upload'
import PageHeader from '../../components/PageHeader.vue'

const router = useRouter()
const route = useRoute()
const formRef = ref<FormInstance>()
const submitting = ref(false)
const loading = ref(false)

// 获取商品ID
const goodsId = computed(() => {
  const id = route.params.id
  return id ? Number(id) : 0
})

// 判断是否为编辑模式
const isEdit = computed(() => goodsId.value > 0)

// 用于保存SKU的ID，在编辑模式下使用
const skuIds = ref<Map<string, number>>(new Map())

type CreateGoodsPayload2 = {
  goods_name: string
  subtitle: string
  category_id: number
  brand_id: number
  cover_image: string
  images: string[]
  description: string
  attrs_template: Record<string, string[]>
  sku_list: Array<{
    sku_code: string
    bar_code: string
    attrs: Record<string, string>
    cost_price: number
    base_price: number
  }>
}

type Spec = {
  key: string
  values: string[]
  codes: Record<string, string>
}
type SkuRow = {
  __key: string
  sku_code: string
  bar_code: string
  attrs: Record<string, string>
  cost_price: number
  base_price: number
}

const skuPrefix = ref('')

const specs = reactive<Spec[]>([])

const form = reactive<{
  goods_name: string
  subtitle: string
  category_id: number
  brand_id: number
  cover_image: string
  images: string[]
  description: string
  sku_list: SkuRow[]
}>({
  goods_name: '',
  subtitle: '',
  category_id: 1,
  brand_id: 1,
  cover_image: '',
  images: [],
  description: '',
  sku_list: [],
})

const previewList = computed(() => {
  const arr: string[] = []
  if (form.cover_image) arr.push(form.cover_image)
  if (form.images.length) arr.push(...form.images.filter(Boolean))
  return arr
})

const rules: FormRules = {
  goods_name: [{ required: true, message: '请输入商品名称', trigger: 'blur' }],
  subtitle: [{ required: true, message: '请输入副标题', trigger: 'blur' }],
  category_id: [{ required: true, message: '请选择类目', trigger: 'change' }],
  brand_id: [{ required: true, message: '请输入品牌', trigger: 'change' }],
  cover_image: [{ required: true, message: '请上传/填写封面图', trigger: 'blur' }],
  description: [{ required: true, message: '请输入图文详情 HTML', trigger: 'blur' }],
}

function cleanedValues(values: string[]) {
  return values.map(v => (v || '').trim()).filter(Boolean)
}

/** 图片：手动添加/删除一行 */
function addImage() {
  form.images.push('')
}
function removeImage(i: number) {
  form.images.splice(i, 1)
}

/** 上传 */
const uploadingCover = ref(false)
const uploadingBanner = ref(false)

function beforeImage(file: File) {
  const isImg = file.type.startsWith('image/')
  const okSize = file.size / 1024 / 1024 <= 5
  if (!isImg) ElMessage.error('只能上传图片文件')
  if (!okSize) ElMessage.error('图片大小不能超过 5MB')
  return isImg && okSize
}

async function uploadCoverRequest(options: UploadRequestOptions) {
  const file = options.file as File
  uploadingCover.value = true
  try {
    const url = await adminUploadSingle(file, 'goods')
    form.cover_image = url
    ElMessage.success('封面上传成功')
    options.onSuccess?.({ url } as any)
  } catch (e) {
    options.onError?.(e as any)
  } finally {
    uploadingCover.value = false
  }
}

async function uploadBannerRequest(options: UploadRequestOptions) {
  const file = options.file as File
  uploadingBanner.value = true
  try {
    const url = await adminUploadSingle(file, 'goods')
    form.images.push(url)
    ElMessage.success('轮播图上传成功')
    options.onSuccess?.({ url } as any)
  } catch (e) {
    options.onError?.(e as any)
  } finally {
    uploadingBanner.value = false
  }
}

/** 规格模板 */
function addSpec() {
  specs.push({ key: '', values: [''], codes: {} })
}
function removeSpec(i: number) {
  specs.splice(i, 1)
  syncSkuAttrsKeys()
}
function addSpecValue(i: number) {
  specs[i].values.push('')
}
function removeSpecValue(i: number, vi: number) {
  const raw = specs[i].values[vi] || ''
  const oldVal = raw.trim()
  specs[i].values.splice(vi, 1)
  if (oldVal && specs[i].codes[oldVal]) delete specs[i].codes[oldVal]
}
function onSpecValueBlur(specIndex: number, valueIndex: number) {
  const v = (specs[specIndex].values[valueIndex] || '').trim()
  specs[specIndex].values[valueIndex] = v
  syncSkuAttrsKeys()
}

/** 同步 SKU attrs keys */
function syncSkuAttrsKeys() {
  const keys = specs.map(s => (s.key || '').trim()).filter(Boolean)
  form.sku_list.forEach(sku => {
    Object.keys(sku.attrs).forEach(k => {
      if (!keys.includes(k)) delete sku.attrs[k]
    })
    keys.forEach(k => {
      if (!(k in sku.attrs)) sku.attrs[k] = ''
    })
  })
}

/** SKU 行操作 */
function addSku() {
  const row: SkuRow = {
    __key: `${Date.now()}_${Math.random().toString(16).slice(2)}`,
    sku_code: '',
    bar_code: '',
    attrs: {},
    cost_price: 0,
    base_price: 0,
  }
  specs.forEach(s => {
    const k = (s.key || '').trim()
    if (k) row.attrs[k] = ''
  })
  form.sku_list.push(row)
}
function removeSku(i: number) {
  form.sku_list.splice(i, 1)
}

/** 值->code（优先映射） */
function valueToCode(val: string, spec: Spec) {
  const v = (val || '').trim()
  if (!v) return ''

  const mapped = spec.codes?.[v]
  if (mapped && mapped.trim()) return mapped.trim().toUpperCase()

  if (/^[0-9a-zA-Z]+$/.test(v)) return v.toUpperCase()
  if (/[0-9]/.test(v)) return v.toUpperCase()

  // 中文兜底（建议用映射更准确）
  return v[0]?.toUpperCase?.() || ''
}

/** 根据 attrs 生成 sku_code */
function buildSkuCodeFromAttrs(attrs: Record<string, string>) {
  const prefix = skuPrefix.value.trim()
  const parts: string[] = []

  for (const sp of specs) {
    const key = (sp.key || '').trim()
    if (!key) continue
    const val = attrs[key] || ''
    const code = valueToCode(val, sp)
    if (code) parts.push(code)
  }

  const body = parts.filter(Boolean).join('-')
  if (!prefix) return body || ''
  return body ? `${prefix}-${body}` : prefix
}

/** 只补空 sku_code（不自动新增 SKU） */
function fillSkuCodes() {
  if (form.sku_list.length === 0) {
    ElMessage.warning('请先添加至少一条 SKU')
    return
  }
  form.sku_list.forEach((row) => {
    if (!row.sku_code?.trim()) {
      // 无规格时：无法从 attrs 生成，只能给个前缀（你也可以改成提示用户手填）
      if (specs.length === 0) row.sku_code = skuPrefix.value?.trim() ? `${skuPrefix.value.trim()}-SKU` : ''
      else row.sku_code = buildSkuCodeFromAttrs(row.attrs || {})
    }
  })
}

function onSkuAttrsChange(row: SkuRow) {
  if (specs.length > 0 && !row.sku_code?.trim()) {
    row.sku_code = buildSkuCodeFromAttrs(row.attrs || {})
  }
}

/** 一键生成 SKU（规格笛卡尔积） */
function buildSkuFromSpecs() {
  const clean = specs
    .map(s => ({
      key: (s.key || '').trim(),
      values: cleanedValues(s.values),
      codes: s.codes || {},
    }))
    .filter(s => s.key && s.values.length)

  if (clean.length === 0) {
    ElMessage.warning('没有有效规格（规格名+至少一个值）')
    return
  }

  specs.splice(0, specs.length, ...clean.map(s => ({ key: s.key, values: s.values, codes: s.codes })))

  const combos = cartesian(clean.map(s => ({ key: s.key, values: s.values })))

  form.sku_list = combos.map((attrs) => ({
    __key: `${Date.now()}_${Math.random().toString(16).slice(2)}`,
    sku_code: buildSkuCodeFromAttrs(attrs),
    bar_code: '',
    attrs,
    cost_price: 0,
    base_price: 0,
  }))
}

function cartesian(items: { key: string; values: string[] }[]) {
  let res: Record<string, string>[] = [{}]
  for (const it of items) {
    const next: Record<string, string>[] = []
    for (const r of res) {
      for (const v of it.values) {
        next.push({ ...r, [it.key]: v })
      }
    }
    res = next
  }
  return res
}

/** 构建 payload */
function buildPayload(): CreateGoodsPayload2 {
  const attrs_template: Record<string, string[]> = {}
  specs.forEach(s => {
    const k = (s.key || '').trim()
    const vals = cleanedValues(s.values)
    if (k && vals.length) attrs_template[k] = vals
  })

  const sku_list = form.sku_list.map(s => ({
    sku_code: (s.sku_code || '').trim(),
    bar_code: (s.bar_code || '').trim(),
    attrs: Object.keys(attrs_template).length > 0 ? (s.attrs || {}) : {},
    cost_price: Number(s.cost_price || 0),
    base_price: Number(s.base_price || 0),
  }))

  return {
    goods_name: (form.goods_name || '').trim(),
    subtitle: (form.subtitle || '').trim(),
    category_id: Number(form.category_id),
    brand_id: Number(form.brand_id),
    cover_image: (form.cover_image || '').trim(),
    images: form.images.map(i => (i || '').trim()).filter(Boolean),
    description: form.description || '',
    attrs_template,
    sku_list,
  }
}

// 加载商品详情
async function loadGoodsInfo() {
  if (!isEdit.value) return
  
  loading.value = true
  try {
    const res = await adminGoodsInfoApi(goodsId.value)
    const data = res as any
    
    // 填充基本信息
    form.goods_name = data.goods_name || ''
    form.subtitle = data.subtitle || ''
    form.category_id = data.category_id || 0
    form.brand_id = data.brand_id || 0
    form.cover_image = data.cover_image || ''
    form.images = data.images || []
    form.description = data.description || ''
    
    // 填充规格模板
    specs.splice(0, specs.length)
    if (data.attrs_template?.specs?.length) {
      data.attrs_template.specs.forEach((spec: any) => {
        specs.push({
          key: spec.name || '',
          values: spec.values || [],
          codes: {}
        })
      })
    }
    
    // 填充SKU列表
    form.sku_list = []
    if (data.sku?.length) {
      data.sku.forEach((sku: any) => {
        const __key = `${Date.now()}_${Math.random().toString(16).slice(2)}`
        form.sku_list.push({
          __key,
          sku_code: sku.sku_code || '',
          bar_code: sku.bar_code || '',
          attrs: sku.attrs || {},
          cost_price: Number(sku.cost_price || 0),
          base_price: Number(sku.base_price || 0)
        })
        // 保存SKU的ID映射
        if (sku.id) {
          skuIds.value.set(__key, sku.id)
        }
      })
    }
  } catch (error) {
    ElMessage.error('加载商品详情失败')
    console.error('加载商品详情失败:', error)
    router.push('/goods')
  } finally {
    loading.value = false
  }
}

// 构建更新payload
function buildUpdatePayload() {
  const payload: any = buildPayload()
  payload.id = goodsId.value
  // 同时保留goods_name和good_name参数，确保与接口兼容
  payload.good_name = payload.goods_name
  
  // 转换attrs_template格式
  payload.attrs_template = {
    specs: [],
    attrs: {}
  }
  
  // 添加规格
  specs.forEach(s => {
    const k = (s.key || '').trim()
    const vals = cleanedValues(s.values)
    if (k && vals.length) {
      payload.attrs_template.specs.push({
        name: k,
        values: vals
      })
    }
  })
  
  // 添加SKU ID
  payload.sku_list = form.sku_list.map(s => {
    const skuData = {
      sku_code: (s.sku_code || '').trim(),
      bar_code: (s.bar_code || '').trim(),
      attrs: s.attrs || {},
      cost_price: Number(s.cost_price || 0),
      base_price: Number(s.base_price || 0)
    }
    
    // 如果是编辑模式，添加SKU ID
    const skuId = skuIds.value.get(s.__key)
    if (skuId) {
        ;(skuData as any).id = skuId
    } 
    return skuData
  })
  
  return payload
}

async function submit() {
  await formRef.value?.validate()

  let payload: any
  if (isEdit.value) {
    payload = buildUpdatePayload()
  } else {
    payload = buildPayload()
  }

  if (payload.sku_list.length === 0) {
    ElMessage.warning('请至少添加一个 SKU（或使用“一键生成 SKU”）')
    return
  }

  // 有规格时：补空 sku_code
  if (payload.attrs_template?.specs?.length > 0 || Object.keys(payload.attrs_template || {}).length > 0) {
    form.sku_list.forEach(r => {
      if (!r.sku_code?.trim()) r.sku_code = buildSkuCodeFromAttrs(r.attrs || {})
    })
  }

  submitting.value = true
  try {
    if (isEdit.value) {
      await adminGoodsUpdateApi(payload)
      ElMessage.success('更新成功')
      router.push(`/goods/${goodsId.value}`)
    } else {
      const res = await adminGoodsCreateApi(payload as any)
      ElMessage.success('创建成功')
      const id = res?.id
      if (id) router.push(`/goods/${id}`)
      else router.push('/goods')
    }
  } finally {
    submitting.value = false
  }
}

// 组件挂载时，如果是编辑模式则加载商品详情
onMounted(() => {
  if (isEdit.value) {
    loadGoodsInfo()
  }
})

function back() {
  router.back()
}
</script>

<style scoped>
.page{display:flex;flex-direction:column;gap:12px;padding-bottom:88px;} /* 预留右下角保存按钮空间 */


.form{margin-top:12px;}
.sectionTitle{font-weight:900;margin:10px 0;}
.sectionTop{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;}
.sectionOps{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.mt12{margin-top:12px;}

.tip{color:var(--sub);font-size:12px;line-height:1.5;}

.uploadLine{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.uploader{display:inline-block;}

.imgs{display:flex;flex-direction:column;gap:10px;}
.imgRow{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}

.specWrap{display:flex;flex-direction:column;gap:10px;}
.specCard{border-radius:16px;}
.specHead{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:10px;}
.specOps{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.specVals{display:flex;flex-direction:column;gap:10px;}
.valRow{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}

.mapTitle{font-weight:900;margin:6px 0;}
.mapWrap{display:flex;flex-direction:column;gap:10px;}
.mapRow{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.mapLeft{min-width:120px;}
.mapLabel{color:var(--sub);font-size:12px;font-weight:800;}

.specFooter{display:flex;justify-content:flex-end;margin-top:8px;}

.table{width:100%;}
.skuAttrGrid{display:flex;gap:12px;flex-wrap:wrap;}
.skuAttrItem{display:flex;align-items:center;gap:8px;}
.skuAttrKey{color:var(--sub);font-size:12px;font-weight:800;}

/* 底部居中保存按钮 */
.saveBar{
  position: fixed;
  left: 60%;
  bottom: 24px;
  z-index: 2000;
}
/* .submit-bar {
  margin: 32px 0;
  display: flex;
  justify-content: center;
} */

.submit-bar .el-button {
  min-width: 220px;
  height: 48px;
  font-size: 16px;
  font-weight: 600;
}

.submit-bar {
  position: sticky;
  border-radius: 5px;
  bottom: 0;
  padding: 16px 0;
  background: #fff;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: center;
  z-index: 10;
}

</style>
