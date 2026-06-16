<template>
  <div class="club-detail" v-loading="loading">
    <el-button type="primary" link @click="goBack" class="back-btn">
      <el-icon><ArrowLeft /></el-icon>
      返回列表
    </el-button>

    <div v-if="club" class="club-info">
      <div class="club-header">
        <div class="club-avatar">
          <el-icon size="48"><OfficeBuilding /></el-icon>
        </div>
        <div class="club-meta">
          <h1>{{ club.name }}</h1>
          <div class="club-tags">
            <el-tag>{{ club.category }}</el-tag>
            <span class="member-count">
              <el-icon><User /></el-icon>
              {{ club.member_count }} 名成员
            </span>
          </div>
        </div>
      </div>

      <el-card class="description-card">
        <template #header>
          <span class="card-title">社团介绍</span>
        </template>
        <p>{{ club.description || '暂无介绍' }}</p>
      </el-card>

      <el-card class="posts-card">
        <template #header>
          <div class="card-header">
            <span class="card-title">招新岗位</span>
            <span class="post-count">共 {{ club.recruitment_posts?.length || 0 }} 个岗位</span>
          </div>
        </template>

        <el-empty v-if="!club.recruitment_posts?.length" description="暂无招新岗位" />

        <el-row :gutter="16" v-else>
          <el-col :span="12" v-for="post in club.recruitment_posts" :key="post.id">
            <el-card class="post-card" shadow="hover" @click="goToPost(post.id)">
              <h3>{{ post.title }}</h3>
              <div class="post-meta">
                <span class="quota">招 {{ post.quota }} 人</span>
                <el-tag size="small" :type="post.status === 'open' ? 'success' : 'info'">
                  {{ post.status === 'open' ? '报名中' : '已结束' }}
                </el-tag>
              </div>
              <p class="post-desc">{{ post.description }}</p>
              <div class="post-footer">
                <el-button type="primary" size="small" link>
                  查看详情
                  <el-icon><ArrowRight /></el-icon>
                </el-button>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </el-card>

      <el-card v-if="club.leaders?.length" class="leaders-card">
        <template #header>
          <span class="card-title">社团负责人</span>
        </template>
        <div class="leaders-list">
          <div class="leader-item" v-for="leader in club.leaders" :key="leader.id">
            <el-avatar :size="40">{{ leader.name?.charAt(0) }}</el-avatar>
            <div class="leader-info">
              <span class="leader-name">{{ leader.name }}</span>
              <span class="leader-role">社长</span>
            </div>
          </div>
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getClub } from '@/api/club'

const route = useRoute()
const router = useRouter()

const club = ref(null)
const loading = ref(false)

const fetchClub = async () => {
  loading.value = true
  try {
    const res = await getClub(route.params.id)
    club.value = res.data
  } catch (error) {
    console.error('获取社团详情失败', error)
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.push('/clubs')
}

const goToPost = (id) => {
  router.push(`/posts/${id}`)
}

onMounted(() => {
  fetchClub()
})
</script>

<style scoped>
.club-detail {
  padding: 20px 0;
}

.back-btn {
  margin-bottom: 16px;
}

.club-info {
  margin-top: 16px;
}

.club-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
  padding: 24px;
  background: #fff;
  border-radius: 8px;
}

.club-avatar {
  width: 80px;
  height: 80px;
  background: #ecf5ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #409EFF;
}

.club-meta h1 {
  margin: 0 0 10px 0;
  font-size: 24px;
  color: #303133;
}

.club-tags {
  display: flex;
  align-items: center;
  gap: 16px;
}

.member-count {
  color: #909399;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.description-card,
.posts-card,
.leaders-card {
  margin-bottom: 20px;
}

.card-title {
  font-weight: 600;
  color: #303133;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.post-count {
  color: #909399;
  font-size: 14px;
}

.post-card {
  margin-bottom: 16px;
  cursor: pointer;
}

.post-card h3 {
  margin: 0 0 10px 0;
  font-size: 16px;
  color: #303133;
}

.post-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.quota {
  color: #606266;
  font-size: 14px;
}

.post-desc {
  color: #606266;
  font-size: 14px;
  margin: 0 0 12px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.post-footer {
  text-align: right;
}

.leaders-list {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
}

.leader-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.leader-info {
  display: flex;
  flex-direction: column;
}

.leader-name {
  font-weight: 500;
  color: #303133;
}

.leader-role {
  color: #909399;
  font-size: 13px;
}
</style>
