<template>
  <div class="app-container">
    <el-container>
      <el-header v-if="isLoggedIn && !isDashboardPage" class="header">
        <div class="header-content">
          <div class="logo" @click="goHome">
            <el-icon size="28" color="#409EFF"><School /></el-icon>
            <span class="title">社团招新系统</span>
          </div>
          <div class="nav">
            <el-menu
              :default-active="activeMenu"
              mode="horizontal"
              :ellipsis="false"
              @select="handleMenuSelect"
            >
              <el-menu-item index="clubs">
                <el-icon><OfficeBuilding /></el-icon>
                <span>社团列表</span>
              </el-menu-item>
              <el-menu-item index="posts">
                <el-icon><Postcard /></el-icon>
                <span>招新岗位</span>
              </el-menu-item>
              <el-menu-item index="my-applications">
                <el-icon><Document /></el-icon>
                <span>我的报名</span>
              </el-menu-item>
              <el-menu-item index="my-interviews">
                <el-icon><Calendar /></el-icon>
                <span>面试安排</span>
              </el-menu-item>
              <el-menu-item v-if="isClubLeader" index="dashboard">
                <el-icon><DataAnalysis /></el-icon>
                <span>管理后台</span>
              </el-menu-item>
            </el-menu>
          </div>
          <div class="user-info">
            <el-dropdown @command="handleCommand">
              <span class="user-name">
                <el-icon><User /></el-icon>
                {{ user?.name }}
                <el-icon class="el-icon--right"><ArrowDown /></el-icon>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="profile">个人中心</el-dropdown-item>
                  <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </el-header>
      <el-main class="main-content">
        <router-view />
      </el-main>
    </el-container>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { ElMessage, ElMessageBox } from 'element-plus'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const user = computed(() => userStore.user)
const isLoggedIn = computed(() => userStore.isLoggedIn)
const isClubLeader = computed(() => userStore.user?.role === 'club_leader')
const isDashboardPage = computed(() => route.path.startsWith('/dashboard'))

const activeMenu = computed(() => {
  const path = route.path
  if (path.includes('/clubs')) return 'clubs'
  if (path.includes('/posts')) return 'posts'
  if (path.includes('/my-applications')) return 'my-applications'
  if (path.includes('/my-interviews')) return 'my-interviews'
  if (path.includes('/dashboard')) return 'dashboard'
  return 'clubs'
})

const goHome = () => {
  router.push('/')
}

const handleMenuSelect = (key) => {
  const routes = {
    'clubs': '/clubs',
    'posts': '/posts',
    'my-applications': '/my-applications',
    'my-interviews': '/my-interviews',
    'dashboard': '/dashboard'
  }
  router.push(routes[key])
}

const handleCommand = async (command) => {
  if (command === 'logout') {
    try {
      await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
      await userStore.logout()
      ElMessage.success('已退出登录')
      router.push('/login')
    } catch (e) {
      // 用户取消
    }
  } else if (command === 'profile') {
    ElMessage.info('个人中心功能开发中')
  }
}

onMounted(() => {
  userStore.checkAuth()
})
</script>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.header {
  background-color: #fff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 0;
  height: 64px;
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.title {
  font-size: 20px;
  font-weight: 600;
  color: #303133;
}

.nav {
  flex: 1;
  margin: 0 30px;
}

.nav :deep(.el-menu) {
  border-bottom: none;
}

.user-info {
  cursor: pointer;
}

.user-name {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #606266;
}

.main-content {
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
  padding: 20px;
  box-sizing: border-box;
}
</style>
