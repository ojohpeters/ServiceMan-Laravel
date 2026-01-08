import axios from 'axios';
import { Capacitor } from '@capacitor/core';

// Determine API base URL based on environment
const getApiBaseURL = () => {
    // If running in Capacitor (mobile app)
    if (Capacitor.isNativePlatform()) {
        // Use your production Laravel API URL
        // Note: API endpoints work, but /api alone returns 404 - use specific endpoints like /api/categories
        return 'https://serviceman.sekimbi.com/api';
    }
    // If running in browser (web app)
    return '/api';
};

// Create axios instance with base configuration
const api = axios.create({
    baseURL: getApiBaseURL(),
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add auth token to requests
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle auth errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            // Only redirect if not in Capacitor (mobile app handles routing differently)
            if (typeof window !== 'undefined' && !window.Capacitor) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

// Auth API
export const authAPI = {
    login: (credentials) => api.post('/auth/login', credentials),
    register: (userData) => api.post('/auth/register', userData),
    me: () => api.get('/auth/me'),
    logout: () => api.post('/auth/logout'),
    refresh: () => api.post('/auth/refresh'),
    updateClientProfile: (data) => api.put('/users/client-profile', data),
    updateServicemanProfile: (data) => api.put('/users/serviceman-profile', data),
};

// User API
export const userAPI = {
    getCurrentUser: () => api.get('/users/me'),
    getClientProfile: () => api.get('/users/client-profile'),
    updateClientProfile: (data) => api.put('/users/client-profile', data),
    getServicemanProfile: () => api.get('/users/serviceman-profile'),
    updateServicemanProfile: (data) => api.put('/users/serviceman-profile', data),
    getPublicServicemanProfile: (userId) => api.get(`/users/servicemen/${userId}`),
};

// Service Request API
export const serviceRequestAPI = {
    index: (params = {}) => api.get('/service-requests', { params }),
    show: (id) => api.get(`/service-requests/${id}`),
    store: (data) => api.post('/service-requests', data),
    update: (id, data) => api.put(`/service-requests/${id}`, data),
    submitEstimate: (id, data) => api.post(`/service-requests/${id}/submit-estimate`, data),
    markComplete: (id, data) => api.post(`/service-requests/${id}/mark-complete`, data),
};

// Category API
export const categoryAPI = {
    index: () => api.get('/categories'),
    show: (id) => api.get(`/categories/${id}`),
    getServicemenByCategory: (id) => api.get(`/categories/${id}/servicemen`),
    store: (data) => api.post('/categories', data),
    update: (id, data) => api.put(`/categories/${id}`, data),
    destroy: (id) => api.delete(`/categories/${id}`),
};

// Payment API
export const paymentAPI = {
    initialize: (data) => api.post('/payments/initialize', data),
    verify: (data) => api.post('/payments/verify', data),
    getPaymentHistory: () => api.get('/payments/history'),
};

// Negotiation API
export const negotiationAPI = {
    index: () => api.get('/negotiations'),
    show: (id) => api.get(`/negotiations/${id}`),
    store: (data) => api.post('/negotiations', data),
    accept: (id) => api.post(`/negotiations/${id}/accept`),
    reject: (id) => api.post(`/negotiations/${id}/reject`),
    counter: (id, data) => api.post(`/negotiations/${id}/counter`, data),
};

// Notification API
export const notificationAPI = {
    index: () => api.get('/notifications'),
    show: (id) => api.get(`/notifications/${id}`),
    markAsRead: (id) => api.patch(`/notifications/${id}/read`),
    markAllAsRead: () => api.patch('/notifications/mark-all-read'),
    getUnreadCount: () => api.get('/notifications/unread-count'),
    destroy: (id) => api.delete(`/notifications/${id}`),
};

// Rating API
export const ratingAPI = {
    index: () => api.get('/ratings'),
    show: (id) => api.get(`/ratings/${id}`),
    store: (data) => api.post('/ratings', data),
    update: (id, data) => api.put(`/ratings/${id}`, data),
    destroy: (id) => api.delete(`/ratings/${id}`),
    getServicemanRatings: (servicemanId) => api.get(`/ratings/servicemen/${servicemanId}`),
};

// Admin API
export const adminAPI = {
    getPendingAssignments: () => api.get('/admin/pending-assignments'),
    assignServiceman: (id, data) => api.post(`/admin/service-requests/${id}/assign-serviceman`, data),
    getPricingReview: () => api.get('/admin/pricing-review'),
    updateFinalCost: (id, data) => api.put(`/admin/service-requests/${id}/final-cost`, data),
    getRevenueAnalytics: (params = {}) => api.get('/admin/analytics/revenue', { params }),
    getTopServicemen: (params = {}) => api.get('/admin/analytics/servicemen', { params }),
    getTopCategories: (params = {}) => api.get('/admin/analytics/categories', { params }),
    getServiceRequestStats: (params = {}) => api.get('/admin/analytics/service-requests', { params }),
    getUserStats: (params = {}) => api.get('/admin/analytics/users', { params }),
    getRecentActivity: (params = {}) => api.get('/admin/analytics/recent-activity', { params }),
};

export default api;
