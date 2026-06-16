<template>
  <div class="results-page">
    <div class="page-header">
      <h2>面试结果</h2>
      <div class="filters">
        <el-select v-model="filters.result" placeholder="全部结果" clearable @change="fetchResults">
          <el-option label="待面试" value="pending" />
          <el-option label="通过" value="passed" />
          <el-option label="未通过" value="rejected" />
          <el-option label="待定" value="waitlist" />
        </el-select>
      </div>
    </div>

    <el-card>
      <el-table :data="results" v-loading="loading" border>
        <el-table-column prop="application.user.name" label="学生姓名" width="100" />
        <el-table-column prop="application.recruitment_post.title" label="应聘岗位" width="150" />
        <el-table-column prop="round" label="轮次" width="80" />
        <el-table-column prop="interview_slot.start_time" label="面试时间" width="170">
          <template #default="{ row }">
            {{ formatDateTime(row.interview_slot?.start_time) }}
          </template>
        </el-table-column>
        <el-table-column prop="score" label="得分" width="100" />
        <el-table-column prop="result" label="结果" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="resultType(row.result)">
              {{ resultText(row.result) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" link @click="editResult(row)">
              编辑
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
          @current-change="fetchResults"
        />
      </div>
    </el-card>

    <el-dialog v-model="dialogVisible" :title="editingResult ? '编辑面试结果' : '记录面试结果'" width="550px">
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="学生">
          <el-input v-model="currentStudentName" disabled />
        </el-form-item>
        <el-form-item label="面试轮次" prop="round">
          <el-input-number v-model="form.round" :min="1" :max="10" />
        </el-form-item>
        <el-form-item label="面试得分" prop="score">
          <el-input-number v-model="form.score" :min="0" :max="100" style="width: 100%" />
        </el-form-item>
        <el-form-item label="面试结果" prop="result">
          <el-radio-group v-model="form.result">
            <el-radio value="pending">待面试</el-radio>
            <el-radio value="passed">通过</el-radio>
            <el-radio value="rejected">未通过</el-radio>
            <el-radio value="waitlist">待定</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="面试评语" prop="comment">
          <el-input v-model="form.comment" type="textarea" :rows="3" placeholder="请输入面试评语" />
        </el-form-item>
        <el-form-item label="反馈给学生" prop="feedback">
          <el-input v-model="form.feedback" type="textarea" :rows="2" placeholder="学生可见的反馈信息" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitting" @click="handleSubmit">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { createInterviewResult, updateInterviewResult } from '@/api/interviewResult'
import { getClubApplications } from '@/api/application'
import { ElMessage } from 'element-plus'

const route = useRoute()

const results = ref([])
const applications = ref([])
const loading = ref(false)
const submitting = ref(false)
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

const filters = reactive({
  result: ''
})

const dialogVisible = ref(false)
const editingResult = ref(null)
const formRef = ref(null)
const currentApplicationId = ref(null)

const form = reactive({
  application_id: null,
  interview_slot_id: null,
  round: 1,
  score: null,
  result: 'pending',
  comment: '',
  feedback: ''
})

const rules = {
  round: [
    { required: true, message: '请输入面试轮次', trigger: 'blur' }
  ],
  result: [
    { required: true, message: '请选择面试结果', trigger: 'change' }
  ]
}

const currentStudentName = computed(() => {
  const app = applications.value.find(a => a.id === currentApplicationId.value)
  return app?.user?.name || ''
})

const fetchResults = async () => {
  loading.value = true
  try {
    const res = await getClubApplications({
      page: page.value,
      per_page: pageSize.value
    })
    applications.value = res.data.data || res.data
    total.value = res.data.total || applications.value.length

    results.value = []
    applications.value.forEach(app => {
      if (app.interview_results?.length) {
        app.interview_results.forEach(r => {
          results.value.push({
            ...r,
            application: app
          })
        })
      }
    })
  } catch (error) {
    console.error('获取面试结果失败', error)
  } finally {
    loading.value = false
  }
}

const editResult = (row) => {
  editingResult.value = row
  currentApplicationId.value = row.application_id
  Object.assign(form, {
    application_id: row.application_id,
    interview_slot_id: row.interview_slot_id,
    round: row.round,
    score: row.score,
    result: row.result,
    comment: row.comment || '',
    feedback: row.feedback || ''
  })
  dialogVisible.value = true
}

const handleSubmit = async () => {
  if (!formRef.value) return

  try {
    await formRef.value.validate()
    submitting.value = true

    if (editingResult.value) {
      await updateInterviewResult(editingResult.value.id, form)
      ElMessage.success('更新成功')
    } else {
      await createInterviewResult(form)
      ElMessage.success('保存成功')
    }

    dialogVisible.value = false
    fetchResults()
  } catch (error) {
    // 错误已处理
  } finally {
    submitting.value = false
  }
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
    passed: '通过',
    rejected: '未通过',
    waitlist: '待定'
  }
  return texts[result] || result
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('zh-CN', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchResults()
  
  if (route.query.application_id) {
    // 可以根据 application_id 预填充表单
  }
})
</script>

<style scoped>
.results-page {
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
