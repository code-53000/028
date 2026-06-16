<template>
  <div class="dashboard">
    <div class="page-header">
      <h2>数据概览</h2>
    </div>

    <el-row :gutter="20" class="stats-row">
      <el-col :span="6">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon primary">
              <el-icon size="32"><Document /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.totalApplications || 0 }}</div>
              <div class="stat-label">总报名数</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon success">
              <el-icon size="32"><Check /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.passed || 0 }}</div>
              <div class="stat-label">已通过</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon warning">
              <el-icon size="32"><Clock /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.pending || 0 }}</div>
              <div class="stat-label">待处理</div>
            </div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon info">
              <el-icon size="32"><Calendar /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stats.totalSlots || 0 }}</div>
              <div class="stat-label">面试场次</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20">
      <el-col :span="12">
        <el-card class="panel-card">
          <template #header>
            <div class="panel-header">
              <span>最新报名</span>
              <el-button type="primary" link @click="goToApplications">查看全部</el-button>
            </div>
          </template>
          <el-table :data="recentApplications" v-loading="loading">
            <el-table-column prop="user.name" label="姓名" width="100" />
            <el-table-column prop="recruitment_post.title" label="岗位" width="150" />
            <el-table-column prop="status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag size="small" :type="statusType(row.status)">
                  {{ statusText(row.status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="created_at" label="报名时间">
              <template #default="{ row }">
                {{ formatDate(row.created_at) }}
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card class="panel-card">
          <template #header>
            <div class="panel-header">
              <span>即将进行的面试</span>
              <el-button type="primary" link @click="goToSlots">查看全部</el-button>
            </div>
          </template>
          <el-table :data="upcomingInterviews" v-loading="loading">
            <el-table-column prop="start_time" label="时间" width="180">
              <template #default="{ row }">
                {{ formatDateTime(row.start_time) }}
              </template>
            </el-table-column>
            <el-table-column prop="location" label="地点" />
            <el-table-column prop="booked_count" label="已预约" width="80">
              <template #default="{ row }">
                {{ row.booked_count }}/{{ row.capacity }}
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getClubApplications } from '@/api/application'
import { getClubInterviewSlots } from '@/api/interviewSlot'

const router = useRouter()

const loading = ref(false)
const recentApplications = ref([])
const upcomingInterviews = ref([])
const stats = reactive({
  totalApplications: 0,
  passed: 0,
  pending: 0,
  totalSlots: 0
})

const fetchData = async () => {
  loading.value = true
  try {
    const [appsRes, slotsRes] = await Promise.all([
      getClubApplications({ per_page: 5 }),
      getClubInterviewSlots({ per_page: 5 })
    ])

    recentApplications.value = appsRes.data.data || appsRes.data
    upcomingInterviews.value = slotsRes.data.data || slotsRes.data

    const allApps = appsRes.data.data || appsRes.data
    stats.totalApplications = appsRes.data.total || allApps.length
    stats.passed = allApps.filter(a => a.status === 'accepted' || a.status === 'interview_completed').length
    stats.pending = allApps.filter(a => a.status === 'pending' || a.status === 'reviewing').length
    stats.totalSlots = slotsRes.data.total || (slotsRes.data.data || slotsRes.data).length
  } catch (error) {
    console.error('获取数据失败', error)
  } finally {
    loading.value = false
  }
}

const goToApplications = () => {
  router.push('/dashboard/applications')
}

const goToSlots = () => {
  router.push('/dashboard/interview-slots')
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

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('zh-CN')
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return `${date.toLocaleDateString('zh-CN', { month: 'short', day: 'numeric' })} ${date.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })}`
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.dashboard {
  padding: 0;
}

.page-header {
  margin-bottom: 20px;
}

.page-header h2 {
  margin: 0;
  color: #303133;
}

.stats-row {
  margin-bottom: 20px;
}

.stat-card {
  border-radius: 8px;
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
}

.stat-icon.primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.success {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-icon.warning {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon.info {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-value {
  font-size: 24px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: #909399;
}

.panel-card {
  border-radius: 8px;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  color: #303133;
}
</style>
