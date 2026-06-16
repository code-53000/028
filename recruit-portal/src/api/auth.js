import request from './request'

export const login = (data) => {
  return request.post('/login', data)
}

export const register = (data) => {
  return request.post('/register', data)
}

export const logout = () => {
  return request.post('/logout')
}

export const getCurrentUser = () => {
  return request.get('/me')
}
