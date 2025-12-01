import { configureStore } from '@reduxjs/toolkit'
import authSlice from './slices/authSlice'
import notificationSlice from './slices/notificationSlice'
import serviceRequestSlice from './slices/serviceRequestSlice'

export const store = configureStore({
  reducer: {
    auth: authSlice,
    notifications: notificationSlice,
    serviceRequests: serviceRequestSlice,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: ['persist/PERSIST', 'persist/REHYDRATE'],
      },
    }),
})

export default store
