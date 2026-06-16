<template>
  <div class="applications-page">
    <div class="page-header">
      <h2>报名管理</h2>
      <div class="filters">
        <el-select v-model="filters.status" placeholder="全部状态" clearable @change="fetchApplications">
          <el-option label="待审核" value="pending" />
          <el-option label="审核中" value="reviewing" />
          <el-option label="面试已安排" value="interview_scheduled" />
          <el-option label="面试已完成" value="interview_completed" />
          <el-option label="已通过" value="accepted" />
          <el-option label="已拒绝" value="rejected" />
        </el-select>
        <el-input
          v-model="filters.keyword"
          placeholder="搜索姓名/学号"
          clearable
          class="search-input"
          @input="debounceSearch"
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
      </div>
    </div>

    <el-card>
      <el-table :data="applications" v-loading="loading" border>
        <el-table-column prop="user.name" label="姓名" width="100" />
        <el-table-column prop="user.student_id" label="学号" width="120" />
        <el-table-column prop="user.major" label="专业" width="150" />
        <el-table-column prop="recruitment_post.title" label="报名岗位" width="180" />
        <el-table-column prop="status" label="状态" width="120">
          <template #default="{ row }">
            <el-tag size="small" :type="statusType(row.status)">
              {{ statusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="报名时间" width="170">
          <template #default="{ row }">
            {{ formatDate(row.created_at) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" link @click="viewDetail(row)">
              查看
            </el-button>
            <el-button type="success" size="small" link @click="updateStatus(row, 'reviewing')" v-if="row.status === 'pending'">
              通过审核
            </el-button>
            <el-button type="danger" size="small" link @click="rejectApplication(row)">
              拒绝
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
          @current-change="fetchApplications"
        />
      </div>
    </el-card>

    <el-dialog v-model="detailVisible" title="报名详情" width="600px">
      <div v-if="currentApplication" class="detail-content">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="姓名">{{ currentApplication.user?.name }}</el-descriptions-item>
          <el-descriptions-item label="学号">{{ currentApplication.user?.student_id }}</el-descriptions-item>
          <el-descriptions-item label="专业">{{ currentApplication.user?.major }}</el-descriptions-item>
          <el-descriptions-item label="年级">{{ currentApplication.user?.grade }}</el-descriptions-item>
          <el-descriptions-item label="电话">{{ currentApplication.user?.phone }}</el-descriptions-item>
          <el-descriptions-item label="邮箱">{{ currentApplication.user?.email }}</el-descriptions-item>
          <el-descriptions-item label="报名岗位" :span="2">
            {{ currentApplication.recruitment_post?.title }}
          </el-descriptions-item>
          <el-descriptions-item label="报名状态">
            <el-tag :type="statusType(currentApplication.status)">
              {{ statusText(currentApplication.status) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="报名时间">
            {{ formatDate(currentApplication.created_at) }}
          </el-descriptions-item>
        </el-descriptions>

        <div class="detail-section">
          <h4>报名动机</h4>
          <p>{{ currentApplication.motivation || '未填写' }}</p>
        </div>

        <div class="detail-section">
          <h4>相关经历</h4>
          <p>{{ currentApplication.experience || '未填写' }}</p>
        </div>

        <div class="detail-section">
          <h4>技能特长</h4>
          <p>{{ currentApplication.skills || '未填写' }}</p>
        </div>

        <div v-if="currentApplication.interview_results?.length" class="detail-section">
          <h4>面试记录</h4>
          <div v-for="result in currentApplication.interview_results" :key="result.id" class="interview-record">
            <el-divider />
            <p><strong>第 {{ result.round }} 轮面试</strong></p>
            <p>结果：
              <el-tag size="small" :type="result.result === 'passed' ? 'success' : result.result === 'rejected' ? 'danger' : 'warning'">
                {{ resultText(result.result) }}
              </el-tag>
            </p>
            <p v-if="result.score">得分：{{ result.score }}</p>
            <p v-if="result.comment">评语：{{ result.comment }}</p>
          </div>
        </div>
      </div>

      <template #footer>
        <el-button @click="detailVisible = false">关闭</el-button>
        <el-button type="primary" @click="goToResult(currentApplication)" v-if="currentApplication?.status === 'interview_scheduled' || currentApplication?.status === 'interview_completed'">
          记录面试结果
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getClubApplications, updateApplicationStatus } from '@/api/application'
import { ElMessage, ElMessageBox } from 'element-plus'

const router = useRouter()

const applications = ref([])
const loading = ref(false)
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

const filters = reactive({
  status: '',
  keyword: ''
})

const detailVisible = ref(false)
const currentApplication = ref(null)

let searchTimer = null

const fetchApplications = async () => {
  loading.value = true
  try {
    const res = await getClubApplications({
      page: page.value,
      per_page: pageSize.value,
      status: filters.status || undefined
    })
    applications.value = res.data.data || res.data
    total.value = res.data.total || applications.value.length
  } catch (error) {
    console.error('获取报名列表失败', error)
  } finally {
    loading.value = false
  }
}

const debounceSearch = () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    fetchApplications()
  }, 300)
}

const viewDetail = (row) => {
  currentApplication.value = row
  detailVisible.value = true
}

const updateStatus = async (row, status) => {
  try {
    await ElMessageBox.confirm(`确定要将该报名状态更新为"${statusText(status)}"吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await updateApplicationStatus(row.id, { status })
    ElMessage.success('状态更新成功')
    fetchApplications()
  } catch (e) {
    if (e !== 'cancel') {
      console.error(e)
    }
  }
}

const rejectApplication = async (row) => {
  try {
    await ElMessageBox.confirm('确定要拒绝该报名吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await updateApplicationStatus(row.id, { status: 'rejected' })
    ElMessage.success('已拒绝')
    fetchApplications()
  } catch (e) {
    if (e !== 'cancel') {
      console.error(e)
    }
  }
}

const goToResult = (application) => {
  detailVisible.value = false
  router.push({ path: '/dashboard/results', query: { application_id: application?.id } })
}

const statusType = (status) => {
  const types = {
    pending: 'warning',
    reviewing: '',
    interview_scheduled: 'primary',
    interview_completed: '',
    accepted: 'success',
    rejected: 'danger'
  }
  return types[status] || ''
}

const statusText = (status) => {
  const texts = {
    pending: '待审核',
    reviewing: '审核中',
    interview_scheduled: '面试已安排',
    interview_completed: '面试已完成',
    accepted: '已通过',
    rejected: '已拒绝'
  }
  return texts[status] || status
}

const resultText = (result) => {
  const texts = {
    pending: '待面试',
    passed: '通过',
    rejected: '未通过',
    waitlist: '待定'
  }
  return texts[result] || result
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleString('zh-CN')
}

onMounted(() => {
  fetchApplications()
})
</script>

<style scoped>
.applications-page {
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

.filters {
  display: flex;
  gap: 12px;
}

.search-input {
  width: 200px;
}

.pagination {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.detail-content {
  padding: 10px 0;
}

.detail-section {
  margin-top: 20px;
}

.detail-section h4 {
  margin: 0 0 8px 0;
  color: #303133;
  font-size: 14px;
}

.detail-section p {
  margin: 0;
  color: #606266;
  line-height: 1.6;
}

.interview-record {
  margin-top: 12px;
}

.interview-record p {
  margin: 6px 0;
}
</style>
