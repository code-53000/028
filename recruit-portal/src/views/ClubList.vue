<template>
  <div class="club-list">
    <div class="page-header">
      <h2>社团列表</h2>
      <div class="search-bar">
        <el-input
          v-model="keyword"
          placeholder="搜索社团名称"
          clearable
          class="search-input"
          @input="searchClubs"
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
        <el-select v-model="category" placeholder="分类筛选" clearable @change="fetchClubs">
          <el-option label="学术科技" value="学术科技" />
          <el-option label="实践创业" value="实践创业" />
          <el-option label="文化艺术" value="文化艺术" />
          <el-option label="体育运动" value="体育运动" />
        </el-select>
      </div>
    </div>

    <el-row :gutter="20">
      <el-col :span="8" v-for="club in clubs" :key="club.id">
        <el-card class="club-card" shadow="hover" @click="goToDetail(club.id)">
          <template #header>
            <div class="card-header">
              <span class="club-name">{{ club.name }}</span>
              <el-tag size="small" :type="categoryType(club.category)">{{ club.category }}</el-tag>
            </div>
          </template>
          <div class="card-body">
            <p class="description">{{ club.description || '暂无介绍' }}</p>
            <div class="card-footer">
              <span class="members">
                <el-icon><User /></el-icon>
                {{ club.member_count }} 人
              </span>
              <span class="posts">
                <el-icon><Postcard /></el-icon>
                {{ club.recruitment_posts_count || 0 }} 个岗位
              </span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <div v-if="clubs.length === 0" class="empty-state">
      <el-empty description="暂无社团" />
    </div>

    <div class="pagination">
      <el-pagination
        v-model:current-page="page"
        v-model:page-size="pageSize"
        :total="total"
        layout="prev, pager, next"
        @current-change="fetchClubs"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getClubs } from '@/api/club'

const router = useRouter()

const clubs = ref([])
const keyword = ref('')
const category = ref('')
const page = ref(1)
const pageSize = ref(12)
const total = ref(0)

let searchTimer = null

const fetchClubs = async () => {
  try {
    const res = await getClubs({
      page: page.value,
      per_page: pageSize.value,
      keyword: keyword.value || undefined,
      category: category.value || undefined
    })
    clubs.value = res.data.data
    total.value = res.data.total
  } catch (error) {
    console.error('获取社团列表失败', error)
  }
}

const searchClubs = () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    fetchClubs()
  }, 300)
}

const goToDetail = (id) => {
  router.push(`/clubs/${id}`)
}

const categoryType = (category) => {
  const types = {
    '学术科技': '',
    '实践创业': 'success',
    '文化艺术': 'warning',
    '体育运动': 'danger'
  }
  return types[category] || ''
}

onMounted(() => {
  fetchClubs()
})
</script>

<style scoped>
.club-list {
  padding: 20px 0;
}

.page-header {
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.page-header h2 {
  margin: 0;
  color: #303133;
}

.search-bar {
  display: flex;
  gap: 12px;
}

.search-input {
  width: 280px;
}

.club-card {
  margin-bottom: 20px;
  cursor: pointer;
  transition: transform 0.2s;
}

.club-card:hover {
  transform: translateY(-4px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.club-name {
  font-weight: 600;
  font-size: 16px;
  color: #303133;
}

.card-body {
  min-height: 120px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.description {
  color: #606266;
  font-size: 14px;
  line-height: 1.6;
  margin: 0 0 16px 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  color: #909399;
  font-size: 13px;
}

.card-footer span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.empty-state {
  padding: 60px 0;
}

.pagination {
  display: flex;
  justify-content: center;
  margin-top: 30px;
}
</style>
