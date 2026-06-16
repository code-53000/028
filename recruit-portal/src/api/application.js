import request from './request'

export const getApplications = (params) => {
  return request.get('/applications', { params })
}

export const getApplication = (id) => {
  return request.get(`/applications/${id}`)
}

export const createApplication = (data) => {
  return request.post('/applications', data)
}

export const getClubApplications = (params) => {
  return request.get('/club/applications', { params })
}

export const updateApplicationStatus = (id, data) => {
  return request.put(`/applications/${id}/status`, data)
}
