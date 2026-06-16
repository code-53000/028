import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '@/stores/user'

const routes = [
  {
    path: '/',
    redirect: '/clubs'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/Register.vue'),
    meta: { guest: true }
  },
  {
    path: '/clubs',
    name: 'ClubList',
    component: () => import('@/views/ClubList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/clubs/:id',
    name: 'ClubDetail',
    component: () => import('@/views/ClubDetail.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/posts',
    name: 'PostList',
    component: () => import('@/views/PostList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/posts/:id',
    name: 'PostDetail',
    component: () => import('@/views/PostDetail.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/my-applications',
    name: 'MyApplications',
    component: () => import('@/views/MyApplications.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/my-interviews',
    name: 'MyInterviews',
    component: () => import('@/views/MyInterviews.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true, requiresLeader: true },
    redirect: '/dashboard',
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/Dashboard.vue')
      },
      {
        path: 'applications',
        name: 'DashboardApplications',
        component: () => import('@/views/dashboard/Applications.vue')
      },
      {
        path: 'interview-slots',
        name: 'DashboardInterviewSlots',
        component: () => import('@/views/dashboard/InterviewSlots.vue')
      },
      {
        path: 'results',
        name: 'DashboardResults',
        component: () => import('@/views/dashboard/InterviewResults.vue')
      },
      {
        path: 'posts',
        name: 'DashboardPosts',
        component: () => import('@/views/dashboard/Posts.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const userStore = useUserStore()
  
  if (to.meta.requiresAuth && !userStore.isLoggedIn) {
    next({ path: '/login', query: { redirect: to.fullPath } })
  } else if (to.meta.requiresLeader && userStore.user?.role !== 'club_leader' && userStore.user?.role !== 'admin') {
    next({ path: '/clubs' })
  } else if (to.meta.guest && userStore.isLoggedIn) {
    next({ path: '/clubs' })
  } else {
    next()
  }
})

export default router
