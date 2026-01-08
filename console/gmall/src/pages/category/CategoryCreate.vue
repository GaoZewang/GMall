<template>
  <div class="page">
    <el-card shadow="never">


      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="120px"
        class="form"
      >
         <el-form-item label="父级分类" prop="parent_id">
          <el-select
            v-model="form.parent_id"
            placeholder="请选择父级分类"
            clearable
          >
            <el-option label="顶级分类" :value="0" />
            <el-option
              v-for="category in categoryOptions"
              :key="category.id"
              :label="category.category_name"
              :value="category.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="分类名称" prop="category_name">
          <el-input
            v-model="form.category_name"
            placeholder="请输入分类名称"
            clearable
          />
        </el-form-item>

     

        <el-form-item label="是否为叶子节点" prop="is_leaf">
          <el-radio-group v-model="form.is_leaf">
            <el-radio :label="1">是</el-radio>
            <el-radio :label="0">否</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="submit" :loading="loading">保存</el-button>
          <el-button @click="resetForm">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, FormInstance, FormRules } from 'element-plus'
import { 
  adminCategoryListApi, 
  adminCategoryInfoApi, 
  adminCategoryCreateApi, 
  adminCategoryUpdateApi,
  type CategoryItem 
} from '../../api/category'

const route = useRoute()
const router = useRouter()

const formRef = ref<FormInstance>()
const loading = ref(false)

const isEdit = computed(() => !!route.params.id)
const categoryOptions = ref<CategoryItem[]>([])

const form = reactive({
  id: 0,
  category_name: '',
  parent_id: 0,
  is_leaf: 1
})

const rules: FormRules = {
  category_name: [
    { required: true, message: '请输入分类名称', trigger: 'blur' },
    { min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
  ],
  parent_id: [
    { required: true, message: '请选择父级分类', trigger: 'change' }
  ],
  is_leaf: [
    { required: true, message: '请选择是否为叶子节点', trigger: 'change' }
  ]
}

async function loadCategories() {
  try {
    const res = await adminCategoryListApi()
    // 处理分类选项，确保只显示顶级分类和一级子分类，便于选择
    let categories: any[] = []
    if (Array.isArray(res)) {
      categories = res
    } else if (res && res.data) {
      categories = res.data
    }
    categoryOptions.value = categories
  } catch (error) {
    console.error('获取分类列表失败:', error)
  }
}

async function loadEditData() {
  const id = Number(route.params.id)
  if (!id) return
  loading.value = true
  try {
    const data = await adminCategoryInfoApi(id)
    form.id = data.id
    form.category_name = data.category_name
    form.parent_id = data.parent_id
    form.is_leaf = data.is_leaf
  } finally {
    loading.value = false
  }
}

async function submit() {
  if (!formRef.value) return
  await formRef.value.validate()
  loading.value = true
  try {
    if (isEdit.value) {
      await adminCategoryUpdateApi({
        id: form.id,
        category_name: form.category_name,
        parent_id: form.parent_id,
        is_leaf: form.is_leaf
      })
      ElMessage.success('编辑成功')
    } else {
      await adminCategoryCreateApi({
        category_name: form.category_name,
        parent_id: form.parent_id,
        is_leaf: form.is_leaf
      })
      ElMessage.success('创建成功')
    }
    // back()
  } finally {
    loading.value = false
  }
}

function resetForm() {
  if (!formRef.value) return
  formRef.value.resetFields()
}

onMounted(() => {
  loadCategories()
  if (isEdit.value) {
    loadEditData()
  }
})
</script>

<style scoped>
.head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.title {
  font-size: 20px;
  font-weight: bold;
}

.form {
  max-width: 600px;
}
</style>
