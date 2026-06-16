<template>
  <div class="post-list">
    <div class="page-header">
      <h2>招新岗位</h2>
      <div class="search-bar">
        <el-input
          v-model="keyword"
          placeholder="搜索岗位"
          clearable
          class="search-input"
          @input="searchPosts"
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
      </div>
    </div>

    <el-row :gutter="20">
      <el-col :span="12" v-for="post in posts" :key="post.id">
        <el-card class="post-card" shadow="hover" @click="goToDetail(post.id)">
          <div class="post-header">
            <div class="club-info">
              <el-avatar :size="40" class="club-avatar">
                {{ post.club?.name?.charAt(0) }}
              </el-avatar>
              <div>
                <span class="club-name">{{ post.club?.name }}</span>
                <el-tag size="small" class="status-tag" :type="post.status === 'open' ? 'success' : 'info'">
                  {{ post.status === 'open' ? '报名中' : '已结束' }}
                </el-tag>
              </div>
            </div>
          </div>
          <h3 class="post-title">{{ post.title }}</h3>
          <p class="post-desc">{{ post.description }}</p>
          <div class="post-footer">
            <span class="quota">
              <el-icon><User /></el-icon>
              招 {{ post.quota }} 人
            </span>
            <el-button type="primary" size="small" link>
              查看详情
              <el-icon><ArrowRight /></el-icon>
            </el-button>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <div v-if="posts.length === 0" class="empty-state">
      <el-empty description="暂无招新岗位" />
    </div>

    <div class="pagination">
      <el-pagination
        v-model:current-page="page"
        v-model:page-size="pageSize"
        :total="total"
        layout="prev, pager, next"
        @current-change="fetchPosts"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getPosts } from '@/api/post'

const router = useRouter()

const posts = ref([])
const keyword = ref('')
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)

let searchTimer = null

const fetchPosts = async () => {
  try {
    const res = await getPosts({
      page: page.value,
      per_page: pageSize.value,
      keyword: keyword.value || undefined
    })
    posts.value = res.data.data
    total.value = res.data.total
  } catch (error) {
    console.error('获取岗位列表失败', error)
  }
}

const searchPosts = () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    fetchPosts()
  }, 300)
}

const goToDetail = (id) => {
  router.push(`/posts/${id}`)
}

onMounted(() => {
  fetchPosts()
})
</script>

<style scoped>
.post-list {
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

.search-input {
  width: 300px;
}

.post-card {
  margin-bottom: 20px;
  cursor: pointer;
  transition: transform 0.2s;
}

.post-card:hover {
  transform: translateY(-4px);
}

.post-header {
  margin-bottom: 12px;
}

.club-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.club-avatar {
  background: #ecf5ff;
  color: #409EFF;
}

.club-name {
  color: #606266;
  font-size: 14px;
  margin-right: 8px;
}

.status-tag {
  margin-left: 8px;
}

.post-title {
  margin: 0 0 10px 0;
  font-size: 18px;
  color: #303133;
}

.post-desc {
  color: #606266;
  font-size: 14px;
  line-height: 1.6;
  margin: 0 0 16px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 44px;
}

.post-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
}

.quota {
  color: #909399;
  font-size: 13px;
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
