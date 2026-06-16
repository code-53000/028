<template>
  <div class="my-interviews">
    <div class="page-header">
      <h2>我的面试安排</h2>
    </div>

    <div v-loading="loading" class="interview-list">
      <el-empty v-if="interviews.length === 0 && !loading" description="暂无面试安排" />

      <el-timeline v-else>
        <el-timeline-item
          v-for="item in interviews"
          :key="item.id"
          :timestamp="formatDate(item.interview_slot?.start_time)"
          :type="timelineType(item.result)"
          placement="top"
        >
          <el-card class="interview-card" shadow="hover">
            <div class="card-header">
              <h3>{{ item.application?.recruitment_post?.title }}</h3>
              <el-tag :type="resultType(item.result)">
                {{ resultText(item.result) }}
              </el-tag>
            </div>
            <div class="card-body">
              <div class="info-row">
                <el-icon><OfficeBuilding /></el-icon>
                <span class="club-name">{{ item.application?.recruitment_post?.club?.name }}</span>
              </div>
              <div class="info-row">
                <el-icon><Calendar /></el-icon>
                <span>第 {{ item.round }} 轮面试</span>
              </div>
              <div class="info-row">
                <el-icon><Clock /></el-icon>
                <span>
                  {{ formatDateTime(item.interview_slot?.start_time) }} - {{ formatTime(item.interview_slot?.end_time) }}
                </span>
              </div>
              <div class="info-row" v-if="item.interview_slot?.location">
                <el-icon><Location /></el-icon>
                <span>{{ item.interview_slot.location }}</span>
              </div>
              <div class="info-row" v-if="item.score !== null">
                <el-icon><Star /></el-icon>
                <span>得分：{{ item.score }} 分</span>
              </div>
            </div>
            <div class="card-footer" v-if="item.comment || item.feedback">
              <el-divider />
              <div v-if="item.comment" class="comment">
                <span class="label">面试官评语：</span>
                <p>{{ item.comment }}</p>
              </div>
              <div v-if="item.feedback" class="feedback">
                <span class="label">反馈：</span>
                <p>{{ item.feedback }}</p>
              </div>
            </div>
          </el-card>
        </el-timeline-item>
      </el-timeline>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getInterviewResults } from '@/api/interviewResult'

const interviews = ref([])
const loading = ref(false)

const fetchInterviews = async () => {
  loading.value = true
  try {
    const res = await getInterviewResults()
    interviews.value = res.data.data || res.data
  } catch (error) {
    console.error('获取面试安排失败', error)
  } finally {
    loading.value = false
  }
}

const timelineType = (result) => {
  if (result === 'passed') return 'success'
  if (result === 'rejected') return 'danger'
  if (result === 'waitlist') return 'warning'
  return 'primary'
}

const resultType = (result) => {
  const types = {
    pending: 'primary',
    passed: 'success',
    rejected: 'danger',
    waitlist: 'warning'
  }
  return types[result] || ''
}

const resultText = (result) => {
  const texts = {
    pending: '待面试',
    passed: '已通过',
    rejected: '未通过',
    waitlist: '待定'
  }
  return texts[result] || result
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'short' })
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchInterviews()
})
</script>

<style scoped>
.my-interviews {
  padding: 20px 0;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h2 {
  margin: 0;
  color: #303133;
}

.interview-list {
  max-width: 700px;
  margin: 0 auto;
}

.interview-card {
  margin-left: 16px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.card-header h3 {
  margin: 0;
  font-size: 16px;
  color: #303133;
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #606266;
  font-size: 14px;
}

.club-name {
  font-weight: 500;
  color: #303133;
}

.card-footer {
  margin-top: 12px;
}

.comment,
.feedback {
  margin-top: 8px;
}

.label {
  color: #909399;
  font-size: 13px;
}

.comment p,
.feedback p {
  margin: 4px 0 0 0;
  color: #606266;
  font-size: 14px;
  line-height: 1.6;
}
</style>
