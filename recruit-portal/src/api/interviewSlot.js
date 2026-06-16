import request from './request'

export const getInterviewSlots = (params) => {
  return request.get('/interview-slots', { params })
}

export const getClubInterviewSlots = (params) => {
  return request.get('/club/interview-slots', { params })
}

export const createInterviewSlot = (data) => {
  return request.post('/interview-slots', data)
}

export const updateInterviewSlot = (id, data) => {
  return request.put(`/interview-slots/${id}`, data)
}

export const deleteInterviewSlot = (id) => {
  return request.delete(`/interview-slots/${id}`)
}

export const selectInterviewSlot = (slotId, data) => {
  return request.post(`/interview-slots/${slotId}/select`, data)
}
