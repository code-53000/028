<template>
  <div class="slots-page">
    <div class="page-header">
      <h2>面试时段</h2>
      <el-button type="primary" @click="openCreateDialog">
        <el-icon><Plus /></el-icon>
        新增时段
      </el-button>
    </div>

    <el-card>
      <el-table :data="slots" v-loading="loading" border>
        <el-table-column prop="start_time" label="开始时间" width="180">
          <template #default="{ row }">
            {{ formatDateTime(row.start_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="end_time" label="结束时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.end_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="recruitment_post.title" label="对应岗位" width="180" />
        <el-table-column prop="location" label="地点" />
        <el-table-column label="预约情况" width="120">
          <template #default="{ row }">
            {{ row.booked_count }}/{{ row.capacity }} 人
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="row.status === 'open' ? 'success' : row.status === 'full' ? 'warning' : 'info'">
              {{ row.status === 'open' ? '开放' : row.status === 'full' ? '已满' : '关闭' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" link @click="editSlot(row)">
              编辑
            </el-button>
            <el-button type="danger" size="small" link @click="deleteSlot(row)">
              删除
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
          @current-change="fetchSlots"
        />
      </div>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="editingSlot ? '编辑时段' : '新增时段'" width="500px">
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="对应岗位" prop="recruitment_post_id">
          <el-select v-model="form.recruitment_post_id" placeholder="请选择招新岗位" style="width: 100%">
            <el-option
              v-for="post in posts"
              :key="post.id"
              :label="post.title"
              :value="post.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="开始时间" prop="start_time">
          <el-date-picker
            v-model="form.start_time"
            type="datetime"
            placeholder="选择开始时间"
            style="width: 100%"
            format="YYYY-MM-DD HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="结束时间" prop="end_time">
          <el-date-picker
            v-model="form.end_time"
            type="datetime"
            placeholder="选择结束时间"
            style="width: 100%"
            format="YYYY-MM-DD HH:mm"
            value-format="YYYY-MM-DD HH:mm:ss"
          />
        </el-form-item>
        <el-form-item label="地点" prop="location">
          <el-input v-model="form.location" placeholder="请输入面试地点" />
        </el-form-item>
        <el-form-item label="容量" prop="capacity">
          <el-input-number v-model="form.capacity" :min="1" :max="100" style="width: 100%" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="form.status">
            <el-radio value="open">开放</el-radio>
            <el-radio value="closed">关闭</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="form.notes" type="textarea" :rows="2" placeholder="选填" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitting" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getClubInterviewSlots, createInterviewSlot, updateInterviewSlot, deleteInterviewSlot } from '@/api/interviewSlot'
import { getPosts } from '@/api/post'
import { ElMessage, ElMessageBox } from 'element-plus'

const slots = ref([])
const posts = ref([])
const loading = ref(false)
const submitting = ref(false)
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

const dialogVisible = ref(false)
const editingSlot = ref(null)
const formRef = ref(null)

const form = reactive({
  recruitment_post_id: null,
  start_time: null,
  end_time: null,
  location: '',
  capacity: 5,
  status: 'open',
  notes: ''
})

const rules = {
  recruitment_post_id: [
    { required: true, message: '请选择招新岗位', trigger: 'change' }
  ],
  start_time: [
    { required: true, message: '请选择开始时间', trigger: 'change' }
  ],
  end_time: [
    { required: true, message: '请选择结束时间', trigger: 'change' }
  ],
  capacity: [
    { required: true, message: '请输入容量', trigger: 'blur' }
  ]
}

const fetchSlots = async () => {
  loading.value = true
  try {
    const res = await getClubInterviewSlots({
      page: page.value,
      per_page: pageSize.value
    })
    slots.value = res.data.data || res.data
    total.value = res.data.total || slots.value.length
  } catch (error) {
    console.error('获取面试时段失败', error)
  } finally {
    loading.value = false
  }
}

const fetchPosts = async () => {
  try {
    const res = await getPosts()
    posts.value = res.data.data || res.data
  } catch (error) {
    console.error('获取岗位列表失败', error)
  }
}

const openCreateDialog = () => {
  editingSlot.value = null
  Object.assign(form, {
    recruitment_post_id: posts.value[0]?.id || null,
    start_time: null,
    end_time: null,
    location: '',
    capacity: 5,
    status: 'open',
    notes: ''
  })
  dialogVisible.value = true
}

const editSlot = (slot) => {
  editingSlot.value = slot
  Object.assign(form, {
    recruitment_post_id: slot.recruitment_post_id,
    start_time: slot.start_time,
    end_time: slot.end_time,
    location: slot.location,
    capacity: slot.capacity,
    status: slot.status,
    notes: slot.notes || ''
  })
  dialogVisible.value = true
}

const handleSubmit = async () => {
  if (!formRef.value) return

  try {
    await formRef.value.validate()
    submitting.value = true

    if (editingSlot.value) {
      await updateInterviewSlot(editingSlot.value.id, form)
      ElMessage.success('更新成功')
    } else {
      await createInterviewSlot(form)
      ElMessage.success('创建成功')
    }

    dialogVisible.value = false
    fetchSlots()
  } catch (error) {
    // 错误已处理
  } finally {
    submitting.value = false
  }
}

const deleteSlot = async (slot) => {
  try {
    await ElMessageBox.confirm('确定要删除该时段吗？如果已有学生预约将无法删除。', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await deleteInterviewSlot(slot.id)
    ElMessage.success('删除成功')
    fetchSlots()
  } catch (e) {
    if (e !== 'cancel') {
      console.error(e)
    }
  }
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN', {
    month: 'short',
    day: 'numeric',
    weekday: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('zh-CN', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchSlots()
  fetchPosts()
})
</script>

<style scoped>
.slots-page {
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
