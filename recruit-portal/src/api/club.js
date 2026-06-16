import request from './request'

export const getClubs = (params) => {
  return request.get('/clubs', { params })
}

export const getClub = (id) => {
  return request.get(`/clubs/${id}`)
}

export const createClub = (data) => {
  return request.post('/clubs', data)
}

export const updateClub = (id, data) => {
  return request.put(`/clubs/${id}`, data)
}
