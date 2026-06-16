<template>
  <div class="post-detail" v-loading="loading">
    <el-button type="primary" link @click="goBack" class="back-btn">
      <el-icon><ArrowLeft /></el-icon>
      返回列表
    </el-button>

    <div v-if="post" class="post-content">
      <div class="post-header">
        <div class="post-title-row">
          <h1>{{ post.title }}</h1>
          <el-tag size="large" :type="post.status === 'open' ? 'success' : 'info'">
            {{ post.status === 'open' ? '报名中' : '已结束' }}
          </el-tag>
        </div>
        <div class="post-meta">
          <span class="club-name" @click="goToClub(post.club?.id)">
            <el-icon><OfficeBuilding /></el-icon>
            {{ post.club?.name }}
          </span>
          <span class="quota">
            <el-icon><User /></el-icon>
            招 {{ post.quota }} 人
          </span>
          <span v-if="post.deadline" class="deadline">
            <el-icon><Clock /></el-icon>
            截止：{{ formatDate(post.deadline) }}
          </span>
        </div>
      </div>

      <el-row :gutter="24">
        <el-col :span="16">
          <el-card class="info-card">
            <template #header>
              <span class="card-title">岗位介绍</span>
            </template>
            <div class="content-section">
              <h4>岗位职责</h4>
              <p>{{ post.description || '暂无' }}</p>
            </div>
            <div class="content-section">
              <h4>任职要求</h4>
              <pre>{{ post.requirements || '暂无' }}</pre>
            </div>
            <div class="content-section">
              <h4>福利与收获</h4>
              <pre>{{ post.benefits || '暂无' }}</pre>
            </div>
          </el-card>

          <el-card class="slots-card">
            <template #header>
              <span class="card-title">可选面试时段</span>
            </template>
            <div v-if="!slots.length" class="empty-slots">
              <el-empty description="暂无可选面试时段" :image-size="80" />
            </div>
            <div v-else class="slots-list">
              <div
                v-for="slot in slots"
                :key="slot.id"
                class="slot-item"
                :class="{
                  'disabled': slot.status !== 'open' || slot.booked_count >= slot.capacity,
                  'selected': selectedSlotId === slot.id
                }"
                @click="selectSlot(slot)"
              >
                <div class="slot-time">
                  <el-icon><Calendar /></el-icon>
                  <span>{{ formatDateTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}</span>
                </div>
                <div class="slot-info">
                  <span class="location">
                    <el-icon><Location /></el-icon>
                    {{ slot.location || '待定' }}
                  </span>
                  <span class="capacity">
                    {{ slot.booked_count }}/{{ slot.capacity }} 人
                  </span>
                </div>
                <el-tag size="small" :type="slot.status === 'open' && slot.booked_count < slot.capacity ? 'success' : 'info'">
                  {{ slot.status === 'open' && slot.booked_count < slot.capacity ? '可选择' : slot.status === 'full' ? '已满' : '已关闭' }}
                </el-tag>
              </div>
            </div>
          </el-card>
        </el-col>

        <el-col :span="8">
          <div class="apply-sidebar">
            <el-card class="apply-card">
              <template #header>
                <span class="card-title">立即报名</span>
              </template>
              
              <div v-if="hasApplied" class="applied-tip">
                <el-result icon="success" title="您已报名该岗位" sub-title="可在「我的报名」中查看进度" />
              </div>
              
              <el-form v-else ref="formRef" :model="form" :rules="rules" label-position="top">
                <el-form-item label="报名动机" prop="motivation">
                  <el-input
                    v-model="form.motivation"
                    type="textarea"
                    :rows="3"
                    placeholder="请说明您为什么想加入这个社团/岗位"
                  />
                </el-form-item>
                <el-form-item label="相关经历" prop="experience">
                  <el-input
                    v-model="form.experience"
                    type="textarea"
                    :rows="3"
                    placeholder="请描述您的相关经历（选填）"
                  />
                </el-form-item>
                <el-form-item label="技能特长" prop="skills">
                  <el-input
                    v-model="form.skills"
                    type="textarea"
                    :rows="2"
                    placeholder="请列出您的技能特长（选填）"
                  />
                </el-form-item>
                <el-form-item>
                  <el-button
                    type="primary"
                    class="apply-btn"
                    :loading="submitting"
                    :disabled="post.status !== 'open'"
                    @click="handleApply"
                  >
                    提交报名
                  </el-button>
                </el-form-item>
              </el-form>
            </el-card>

            <el-card class="tips-card">
              <template #header>
                <span class="card-title">报名须知</span>
              </template>
              <ul class="tips-list">
                <li>请如实填写个人信息</li>
                <li>提交后请等待社团审核</li>
                <li>审核通过后可选择面试时段</li>
                <li>请准时参加面试</li>
              </ul>
            </el-card>
          </div>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getPost } from '@/api/post'
import { getInterviewSlots, selectInterviewSlot } from '@/api/interviewSlot'
import { createApplication, getApplications } from '@/api/application'
import { ElMessage, ElMessageBox } from 'element-plus'

const route = useRoute()
const router = useRouter()

const post = ref(null)
const slots = ref([])
const loading = ref(false)
const submitting = ref(false)
const hasApplied = ref(false)
const currentApplication = ref(null)
const selectedSlotId = ref(null)
const selectingSlot = ref(false)

const formRef = ref(null)
const form = reactive({
  motivation: '',
  experience: '',
  skills: ''
})

const rules = {
  motivation: [
    { required: true, message: '请填写报名动机', trigger: 'blur' }
  ]
}

const fetchPost = async () => {
  loading.value = true
  try {
    const res = await getPost(route.params.id)
    post.value = res.data
    fetchSlots()
    checkApplied()
  } catch (error) {
    console.error('获取岗位详情失败', error)
  } finally {
    loading.value = false
  }
}

const fetchSlots = async () => {
  try {
    const res = await getInterviewSlots({ recruitment_post_id: route.params.id })
    slots.value = res.data
  } catch (error) {
    console.error('获取面试时段失败', error)
  }
}

const checkApplied = async () => {
  try {
    const res = await getApplications()
    const app = res.data.find(item => item.recruitment_post_id == route.params.id)
    hasApplied.value = !!app
    currentApplication.value = app || null
    
    if (app && app.interview_results?.length > 0) {
      selectedSlotId.value = app.interview_results[0].interview_slot_id
    }
  } catch (error) {
    console.error('检查报名状态失败', error)
  }
}

const selectSlot = async (slot) => {
  if (!hasApplied.value) {
    ElMessage.warning('请先报名该岗位')
    return
  }
  
  if (slot.status !== 'open' || slot.booked_count >= slot.capacity) {
    ElMessage.warning('该时段不可选择')
    return
  }
  
  if (selectedSlotId.value === slot.id) {
    return
  }
  
  try {
    await ElMessageBox.confirm(
      `确定选择 ${formatDateTime(slot.start_time)} 的面试时段吗？`,
      '选择面试时段',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'info'
      }
    )
    
    selectingSlot.value = true
    await selectInterviewSlot(slot.id, {
      application_id: currentApplication.value.id
    })
    
    ElMessage.success('面试时段选择成功！')
    selectedSlotId.value = slot.id
    fetchSlots()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('选择时段失败', error)
    }
  } finally {
    selectingSlot.value = false
  }
}

const handleApply = async () => {
  if (!formRef.value) return
  
  try {
    await formRef.value.validate()
    submitting.value = true
    
    const res = await createApplication({
      recruitment_post_id: route.params.id,
      ...form
    })
    
    ElMessage.success('报名成功！')
    hasApplied.value = true
    currentApplication.value = res.data
  } catch (error) {
    // 错误已处理
  } finally {
    submitting.value = false
  }
}

const goBack = () => {
  router.push('/posts')
}

const goToClub = (id) => {
  if (id) router.push(`/clubs/${id}`)
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('zh-CN')
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return `${date.toLocaleDateString('zh-CN', { month: 'long', day: 'numeric', weekday: 'short' })} ${date.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })}`
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchPost()
})
</script>

<style scoped>
.post-detail {
  padding: 20px 0;
}

.back-btn {
  margin-bottom: 16px;
}

.post-header {
  background: #fff;
  padding: 24px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.post-title-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
}

.post-title-row h1 {
  margin: 0;
  font-size: 24px;
  color: #303133;
}

.post-meta {
  display: flex;
  align-items: center;
  gap: 24px;
  color: #606266;
  font-size: 14px;
}

.post-meta span {
  display: flex;
  align-items: center;
  gap: 6px;
}

.club-name {
  cursor: pointer;
  color: #409EFF;
}

.info-card,
.slots-card {
  margin-bottom: 20px;
}

.card-title {
  font-weight: 600;
  color: #303133;
}

.content-section {
  margin-bottom: 20px;
}

.content-section:last-child {
  margin-bottom: 0;
}

.content-section h4 {
  margin: 0 0 10px 0;
  color: #303133;
  font-size: 15px;
}

.content-section p,
.content-section pre {
  color: #606266;
  line-height: 1.8;
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
  font-family: inherit;
  font-size: 14px;
}

.slots-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.slot-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  background: #f5f7fa;
  border-radius: 8px;
  border: 1px solid #e4e7ed;
}

.slot-item.disabled {
  opacity: 0.6;
}

.slot-item.selected {
  border-color: #409EFF;
  background: #ecf5ff;
}

.slot-time {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  color: #303133;
}

.slot-info {
  display: flex;
  gap: 16px;
  color: #606266;
  font-size: 13px;
}

.location,
.capacity {
  display: flex;
  align-items: center;
  gap: 4px;
}

.empty-slots {
  padding: 20px 0;
}

.apply-sidebar {
  position: sticky;
  top: 84px;
}

.apply-card {
  margin-bottom: 16px;
}

.apply-btn {
  width: 100%;
}

.applied-tip {
  padding: 20px 0;
}

.tips-card {
  background: #fdf6ec;
}

.tips-card :deep(.el-card__header) {
  background: #fdf6ec;
  border-bottom-color: #faecd8;
}

.tips-list {
  margin: 0;
  padding-left: 20px;
  color: #e6a23c;
  font-size: 14px;
  line-height: 2;
}
</style>
