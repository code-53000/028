import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login, register, logout, getCurrentUser } from '@/api/auth'
import { ElMessage } from 'element-plus'

export const useUserStore = defineStore('user', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('token') || '')
  const isLoggedIn = computed(() => !!token.value && !!user.value)

  const setToken = (newToken) => {
    token.value = newToken
    localStorage.setItem('token', newToken)
  }

  const setUser = (newUser) => {
    user.value = newUser
  }

  const clearAuth = () => {
    user.value = null
    token.value = ''
    localStorage.removeItem('token')
  }

  const doLogin = async (credentials) => {
    try {
      const res = await login(credentials)
      setToken(res.data.token)
      setUser(res.data.user)
      ElMessage.success('登录成功')
      return res.data
    } catch (error) {
      ElMessage.error(error.response?.data?.message || '登录失败')
      throw error
    }
  }

  const doRegister = async (data) => {
    try {
      const res = await register(data)
      setToken(res.data.token)
      setUser(res.data.user)
      ElMessage.success('注册成功')
      return res.data
    } catch (error) {
      ElMessage.error(error.response?.data?.message || '注册失败')
      throw error
    }
  }

  const doLogout = async () => {
    try {
      await logout()
    } catch (e) {
      // 忽略登出错误
    }
    clearAuth()
  }

  const fetchCurrentUser = async () => {
    try {
      const res = await getCurrentUser()
      setUser(res.data)
      return res.data
    } catch (error) {
      clearAuth()
      throw error
    }
  }

  const checkAuth = async () => {
    if (token.value && !user.value) {
      try {
        await fetchCurrentUser()
      } catch (e) {
        // token 失效
      }
    }
  }

  return {
    user,
    token,
    isLoggedIn,
    setToken,
    setUser,
    clearAuth,
    login: doLogin,
    register: doRegister,
    logout: doLogout,
    fetchCurrentUser,
    checkAuth
  }
})
