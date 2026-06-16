import request from './request'

export const getPosts = (params) => {
  return request.get('/recruitment-posts', { params })
}

export const getPost = (id) => {
  return request.get(`/recruitment-posts/${id}`)
}

export const createPost = (data) => {
  return request.post('/recruitment-posts', data)
}

export const updatePost = (id, data) => {
  return request.put(`/recruitment-posts/${id}`, data)
}
