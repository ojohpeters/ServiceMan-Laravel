import { createSlice, createAsyncThunk } from '@reduxjs/toolkit'
import axios from 'axios'

const initialState = {
  requests: [],
  currentRequest: null,
  isLoading: false,
  error: null,
}

// Async thunks
export const fetchServiceRequests = createAsyncThunk(
  'serviceRequests/fetchRequests',
  async (params, { rejectWithValue }) => {
    try {
      const token = localStorage.getItem('auth_token')
      const response = await axios.get('/api/service-requests', {
        headers: { Authorization: `Bearer ${token}` },
        params
      })
      return response.data
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || 'Failed to fetch service requests')
    }
  }
)

export const createServiceRequest = createAsyncThunk(
  'serviceRequests/createRequest',
  async (data, { rejectWithValue }) => {
    try {
      const token = localStorage.getItem('auth_token')
      const response = await axios.post('/api/service-requests', data, {
        headers: { Authorization: `Bearer ${token}` }
      })
      return response.data
    } catch (error) {
      return rejectWithValue(error.response?.data?.message || 'Failed to create service request')
    }
  }
)

const serviceRequestSlice = createSlice({
  name: 'serviceRequests',
  initialState,
  reducers: {
    setCurrentRequest: (state, action) => {
      state.currentRequest = action.payload
    },
    updateRequest: (state, action) => {
      const index = state.requests.findIndex(r => r.id === action.payload.id)
      if (index !== -1) {
        state.requests[index] = action.payload
      }
      if (state.currentRequest?.id === action.payload.id) {
        state.currentRequest = action.payload
      }
    },
    clearError: (state) => {
      state.error = null
    },
    clearRequests: (state) => {
      state.requests = []
      state.currentRequest = null
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch service requests
      .addCase(fetchServiceRequests.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(fetchServiceRequests.fulfilled, (state, action) => {
        state.isLoading = false
        state.requests = action.payload
      })
      .addCase(fetchServiceRequests.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload
      })
      // Create service request
      .addCase(createServiceRequest.pending, (state) => {
        state.isLoading = true
        state.error = null
      })
      .addCase(createServiceRequest.fulfilled, (state, action) => {
        state.isLoading = false
        state.requests.unshift(action.payload)
        state.currentRequest = action.payload
      })
      .addCase(createServiceRequest.rejected, (state, action) => {
        state.isLoading = false
        state.error = action.payload
      })
  },
})

export const { 
  setCurrentRequest, 
  updateRequest, 
  clearError, 
  clearRequests 
} = serviceRequestSlice.actions
export default serviceRequestSlice.reducer
