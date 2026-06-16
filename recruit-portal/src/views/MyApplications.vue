<template>
  <div class="my-applications">
    <div class="page-header">
      <h2>我的报名</h2>
      <el-select v-model="statusFilter" placeholder="全部状态" clearable @change="fetchApplications">
        <el-option label="待审核" value="pending" />
        <el-option label="审核中" value="reviewing" />
        <el-option label="面试已安排" value="interview_scheduled" />
        <el-option label="面试已完成" value="interview_completed" />
        <el-option label="已通过" value="accepted" />
        <el-option label="已拒绝" value="rejected" />
      </el-select>
    </div>

    <div v-loading="loading" class="application-list">
      <el-empty v-if="applications.length === 0 && !loading" description="暂无报名记录" />

      <el-card v-for="app in applications" :key="app.id" class="app-card" shadow="hover">
        <div class="app-header">
          <div class="app-info">
            <h3>{{ app.recruitment_post?.title }}</h3>
            <p class="club-name">{{ app.recruitment_post?.club?.name }}</p>
          </div>
          <el-tag size="large" :type="statusType(app.status)">
            {{ statusText(app.status) }}
          </el-tag>
        </div>

        <div class="app-body">
          <div class="app-detail">
            <span class="label">报名动机：</span>
            <span class="value">{{ app.motivation || '未填写' }}</span>
          </div>
          <div class="app-detail" v-if="app.experience">
            <span class="label">相关经历：</span>
            <span class="value">{{ app.experience }}</span>
          </div>
        </div>

        <div class="interview-section" v-if="app.interview_results?.length">
          <el-divider />
          <h4>面试信息</h4>
          <div v-for="result in app.interview_results" :key="result.id" class="interview-info">
            <div class="interview-item">
              <el-icon><Calendar /></el-icon>
              <span>第 {{ result.round }} 轮面试</span>
            </div>
            <div class="interview-item" v-if="result.interview_slot">
              <el-icon><Clock /></el-icon>
              <span>
                {{ formatDateTime(result.interview_slot.start_time) }} - {{ formatTime(result.interview_slot.end_time) }}
              </span>
            </div>
            <div class="interview-item" v-if="result.interview_slot?.location">
              <el-icon><Location /></el-icon>
              <span>{{ result.interview_slot.location }}</span>
            </div>
            <div class="interview-item" v-if="result.score !== null">
              <el-icon><Star /></el-icon>
              <span>得分：{{ result.score }}</span>
            </div>
            <div class="interview-item result" v-if="result.result !== 'pending'">
              <el-tag :type="result.result === 'passed' ? 'success' : result.result === 'rejected' ? 'danger' : 'warning'" size="small">
                {{ resultText(result.result) }}
              </el-tag>
            </div>
            <div class="interview-item feedback" v-if="result.feedback">
              <span class="label">评语：</span>
              <span>{{ result.feedback }}</span>
            </div>
          </div>
        </div>

        <div class="app-footer">
          <span class="apply-time">
            报名时间：{{ formatDate(app.created_at) }}
          </span>
          <div class="actions">
            <el-button type="primary" size="small" @click="viewDetail(app)">
              查看详情
            </el-button>
            <el-button
              v-if="app.status === 'pending' || app.status === 'reviewing'"
              type="success"
              size="small"
              @click="selectSlot(app)"
            >
              选择面试时段
            </el-button>
          </div>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getApplications } from '@/api/application'
import { ElMessage } from 'element-plus'

const router = useRouter()

const applications = ref([])
const loading = ref(false)
const statusFilter = ref('')

const fetchApplications = async () => {
  loading.value = true
  try {
    const res = await getApplications()
    let data = res.data
    if (statusFilter.value) {
      data = data.filter(item => item.status === statusFilter.value)
    }
    applications.value = data
  } catch (error) {
    console.error('获取报名列表失败', error)
  } finally {
    loading.value = false
  }
}

const viewDetail = (app) => {
  router.push(`/posts/${app.recruitment_post_id}`)
}

const selectSlot = (app) => {
  ElMessage.info('请先前往岗位详情页选择面试时段')
  router.push(`/posts/${app.recruitment_post_id}`)
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

const formatDateTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return `${date.toLocaleDateString('zh-CN', { month: 'long', day: 'numeric', weekday: 'short' })} ${date.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })}`
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchApplications()
})
</script>

<style scoped>
.my-applications {
  padding: 20px 0;
}

.page-header {
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-header h2 {
  margin: 0;
  color: #303133;
}

.application-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.app-card {
  transition: box-shadow 0.3s;
}

.app-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.app-info h3 {
  margin: 0 0 6px 0;
  font-size: 18px;
  color: #303133;
}

.club-name {
  margin: 0;
  color: #606266;
  font-size: 14px;
}

.app-body {
  margin-bottom: 16px;
}

.app-detail {
  margin-bottom: 10px;
}

.app-detail:last-child {
  margin-bottom: 0;
}

.label {
  color: #909399;
  font-size: 14px;
}

.value {
  color: #606266;
  font-size: 14px;
}

.interview-section h4 {
  margin: 0 0 12px 0;
  color: #303133;
  font-size: 15px;
}

.interview-info {
  background: #f5f7fa;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 8px;
}

.interview-item {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  color: #606266;
  font-size: 14px;
}

.interview-item:last-child {
  margin-bottom: 0;
}

.interview-item.result {
  margin-top: 8px;
}

.interview-item.feedback {
  margin-top: 8px;
}

.interview-item.feedback .label {
  color: #909399;
}

.app-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 16px;
  border-top: 1px solid #f0f0f0;
}

.apply-time {
  color: #909399;
  font-size: 13px;
}

.actions {
  display: flex;
  gap: 8px;
}
</style>
