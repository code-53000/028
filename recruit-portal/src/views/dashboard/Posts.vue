<template>
  <div class="posts-page">
    <div class="page-header">
      <h2>招新岗位</h2>
      <el-button type="primary" @click="openCreateDialog">
        <el-icon><Plus /></el-icon>
        发布岗位
      </el-button>
    </div>

    <el-card>
      <el-table :data="posts" v-loading="loading" border>
        <el-table-column prop="title" label="岗位名称" width="180" />
        <el-table-column prop="description" label="简介" show-overflow-tooltip />
        <el-table-column prop="quota" label="招新人数" width="100" />
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="row.status === 'open' ? 'success' : row.status === 'draft' ? 'info' : 'danger'">
              {{ statusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="deadline" label="截止时间" width="170">
          <template #default="{ row }">
            {{ row.deadline ? formatDate(row.deadline) : '无限制' }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" link @click="editPost(row)">
              编辑
            </el-button>
            <el-button v-if="row.status === 'draft'" type="success" size="small" link @click="publishPost(row)">
              发布
            </el-button>
            <el-button v-if="row.status === 'open'" type="warning" size="small" link @click="closePost(row)">
              关闭
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination">
        <el-pagination
          v-model:current-page="page"
          v-model:page-size="pageSize"
          :total="total"
          layout="prev, pager, next, total"
          @current-change="fetchPosts"
        />
      </div>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="editingPost ? '编辑岗位' : '发布岗位'" width="650px">
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="岗位名称" prop="title">
          <el-input v-model="form.title" placeholder="请输入岗位名称" />
        </el-form-item>
        <el-form-item label="岗位职责" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请描述岗位职责"
          />
        </el-form-item>
        <el-form-item label="任职要求" prop="requirements">
          <el-input
            v-model="form.requirements"
            type="textarea"
            :rows="3"
            placeholder="请列出任职要求"
          />
        </el-form-item>
        <el-form-item label="福利与收获" prop="benefits">
          <el-input
            v-model="form.benefits"
            type="textarea"
            :rows="3"
            placeholder="加入后能获得什么"
          />
        </el-form-item>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="招新人数" prop="quota">
              <el-input-number v-model="form.quota" :min="1" :max="100" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="截止时间" prop="deadline">
              <el-date-picker
                v-model="form.deadline"
                type="datetime"
                placeholder="选择截止时间"
                style="width: 100%"
                format="YYYY-MM-DD HH:mm"
                value-format="YYYY-MM-DD HH:mm:ss"
              />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button :loading="submitting" @click="handleSaveDraft">保存草稿</el-button>
        <el-button type="primary" :loading="submitting" @click="handlePublish">
          {{ editingPost ? '保存' : '立即发布' }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getPosts, createPost, updatePost } from '@/api/post'
import { useUserStore } from '@/stores/user'
import { ElMessage, ElMessageBox } from 'element-plus'

const userStore = useUserStore()

const posts = ref([])
const loading = ref(false)
const submitting = ref(false)
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

const dialogVisible = ref(false)
const editingPost = ref(null)
const formRef = ref(null)

const form = reactive({
  title: '',
  description: '',
  requirements: '',
  benefits: '',
  quota: 10,
  deadline: null
})

const rules = {
  title: [
    { required: true, message: '请输入岗位名称', trigger: 'blur' }
  ],
  quota: [
    { required: true, message: '请输入招新人数', trigger: 'blur' }
  ]
}

const fetchPosts = async () => {
  loading.value = true
  try {
    const user = userStore.user
    let clubId = null
    if (user?.clubs?.length) {
      clubId = user.clubs[0].id
    }
    
    const res = await getPosts({
      page: page.value,
      per_page: pageSize.value,
      club_id: clubId
    })
    posts.value = res.data.data || res.data
    total.value = res.data.total || posts.value.length
  } catch (error) {
    console.error('获取岗位列表失败', error)
  } finally {
    loading.value = false
  }
}

const openCreateDialog = () => {
  editingPost.value = null
  Object.assign(form, {
    title: '',
    description: '',
    requirements: '',
    benefits: '',
    quota: 10,
    deadline: null
  })
  dialogVisible.value = true
}

const editPost = (post) => {
  editingPost.value = post
  Object.assign(form, {
    title: post.title,
    description: post.description,
    requirements: post.requirements,
    benefits: post.benefits,
    quota: post.quota,
    deadline: post.deadline
  })
  dialogVisible.value = true
}

const handleSaveDraft = async () => {
  await submitPost('draft')
}

const handlePublish = async () => {
  if (editingPost.value) {
    await submitPost(null)
  } else {
    await submitPost('open')
  }
}

const submitPost = async (status) => {
  if (!formRef.value) return

  try {
    await formRef.value.validate()
    submitting.value = true

    const user = userStore.user
    let clubId = null
    if (user?.clubs?.length) {
      clubId = user.clubs[0].id
    }

    const data = { ...form }
    if (status) {
      data.status = status
    }
    if (clubId && !editingPost.value) {
      data.club_id = clubId
    }

    if (editingPost.value) {
      await updatePost(editingPost.value.id, data)
      ElMessage.success('更新成功')
    } else {
      await createPost(data)
      ElMessage.success('创建成功')
    }

    dialogVisible.value = false
    fetchPosts()
  } catch (error) {
    // 错误已处理
  } finally {
    submitting.value = false
  }
}

const publishPost = async (post) => {
  try {
    await ElMessageBox.confirm('确定要发布该岗位吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await updatePost(post.id, { status: 'open' })
    ElMessage.success('发布成功')
    fetchPosts()
  } catch (e) {
    if (e !== 'cancel') {
      console.error(e)
    }
  }
}

const closePost = async (post) => {
  try {
    await ElMessageBox.confirm('确定要关闭该岗位吗？关闭后学生将无法报名。', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await updatePost(post.id, { status: 'closed' })
    ElMessage.success('已关闭')
    fetchPosts()
  } catch (e) {
    if (e !== 'cancel') {
      console.error(e)
    }
  }
}

const statusText = (status) => {
  const texts = {
    draft: '草稿',
    open: '招聘中',
    closed: '已关闭'
  }
  return texts[status] || status
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('zh-CN')
}

onMounted(() => {
  fetchPosts()
})
</script>

<style scoped>
.posts-page {
  padding: 0;
}

.page-header {
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-header h2 {
  margin: 0;
  color: #303133;
}

.pagination {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}
</style>
