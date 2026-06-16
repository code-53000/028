import request from './request'

export const getInterviewResults = (params) => {
  return request.get('/interview-results', { params })
}

export const createInterviewResult = (data) => {
  return request.post('/interview-results', data)
}

export const updateInterviewResult = (id, data) => {
  return request.put(`/interview-results/${id}`, data)
}
