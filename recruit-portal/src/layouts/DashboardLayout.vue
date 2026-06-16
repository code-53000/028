<template>
  <div class="dashboard-layout">
    <el-container>
      <el-aside width="220px" class="sidebar">
        <div class="sidebar-header">
          <el-icon size="28" color="#fff"><DataAnalysis /></el-icon>
          <span>管理后台</span>
        </div>
        <el-menu
          :default-active="activeMenu"
          class="sidebar-menu"
          background-color="#304156"
          text-color="#bfcbd9"
          active-text-color="#409EFF"
          @select="handleMenuSelect"
        >
          <el-menu-item index="dashboard">
            <el-icon><Odometer /></el-icon>
            <span>数据概览</span>
          </el-menu-item>
          <el-menu-item index="applications">
            <el-icon><Document /></el-icon>
            <span>报名管理</span>
          </el-menu-item>
          <el-menu-item index="interview-slots">
            <el-icon><Calendar /></el-icon>
            <span>面试时段</span>
          </el-menu-item>
          <el-menu-item index="results">
            <el-icon><Edit /></el-icon>
            <span>面试结果</span>
          </el-menu-item>
          <el-menu-item index="posts">
            <el-icon><Postcard /></el-icon>
            <span>招新岗位</span>
          </el-menu-item>
        </el-menu>
      </el-aside>
      <el-container>
        <el-header class="dashboard-header">
          <div class="header-left">
            <el-breadcrumb separator="/">
              <el-breadcrumb-item :to="{ path: '/clubs' }">首页</el-breadcrumb-item>
              <el-breadcrumb-item>管理后台</el-breadcrumb-item>
              <el-breadcrumb-item>{{ currentPageTitle }}</el-breadcrumb-item>
            </el-breadcrumb>
          </div>
          <div class="header-right">
            <el-dropdown @command="handleCommand">
              <span class="user-info">
                <el-avatar :size="32">{{ user?.name?.charAt(0) }}</el-avatar>
                <span class="user-name">{{ user?.name }}</span>
                <el-icon><ArrowDown /></el-icon>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="profile">个人中心</el-dropdown-item>
                  <el-dropdown-item command="home">返回前台</el-dropdown-item>
                  <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </el-header>
        <el-main class="dashboard-main">
          <router-view />
        </el-main>
      </el-container>
    </el-container>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import { ElMessage, ElMessageBox } from 'element-plus'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const user = computed(() => userStore.user)

const activeMenu = computed(() => {
  const path = route.path
  if (path.includes('/dashboard/applications')) return 'applications'
  if (path.includes('/dashboard/interview-slots')) return 'interview-slots'
  if (path.includes('/dashboard/results')) return 'results'
  if (path.includes('/dashboard/posts')) return 'posts'
  return 'dashboard'
})

const currentPageTitle = computed(() => {
  const titles = {
    'dashboard': '数据概览',
    'applications': '报名管理',
    'interview-slots': '面试时段',
    'results': '面试结果',
    'posts': '招新岗位'
  }
  return titles[activeMenu.value] || '数据概览'
})

const handleMenuSelect = (key) => {
  const routes = {
    'dashboard': '/dashboard',
    'applications': '/dashboard/applications',
    'interview-slots': '/dashboard/interview-slots',
    'results': '/dashboard/results',
    'posts': '/dashboard/posts'
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
  } else if (command === 'home') {
    router.push('/clubs')
  } else if (command === 'profile') {
    ElMessage.info('个人中心功能开发中')
  }
}
</script>

<style scoped>
.dashboard-layout {
  min-height: 100vh;
}

.sidebar {
  background-color: #304156;
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 1001;
}

.sidebar-header {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: #fff;
  font-size: 18px;
  font-weight: 600;
  border-bottom: 1px solid #1f2d3d;
}

.sidebar-menu {
  border-right: none;
}

:deep(.el-menu) {
  border-right: none;
}

.dashboard-header {
  background: #fff;
  border-bottom: 1px solid #e4e7ed;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 64px;
  margin-left: 220px;
  position: fixed;
  top: 0;
  right: 0;
  left: 0;
  z-index: 1000;
}

.header-left {
  padding-left: 20px;
}

.header-right {
  padding-right: 20px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  color: #606266;
}

.user-name {
  font-size: 14px;
}

.dashboard-main {
  margin-left: 220px;
  margin-top: 64px;
  padding: 20px;
  background-color: #f0f2f5;
  min-height: calc(100vh - 64px);
}
</style>
