import { store } from '../store'
import { handleRealTimeNotification } from '../store/slices/notificationSlice'

class NotificationService {
  constructor() {
    this.socket = null
    this.reconnectAttempts = 0
    this.maxReconnectAttempts = 5
    this.reconnectDelay = 1000
  }

  connect() {
    // For now, we'll use polling as fallback since WebSocket setup requires backend support
    // In production, replace with actual WebSocket connection
    this.startPolling()
  }

  startPolling() {
    // Poll for notifications every 30 seconds
    this.pollInterval = setInterval(() => {
      this.pollNotifications()
    }, 30000)

    // Initial poll
    this.pollNotifications()
  }

  async pollNotifications() {
    try {
      const { isAuthenticated } = store.getState().auth
      if (!isAuthenticated) return

      const response = await fetch('/api/notifications', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Accept': 'application/json'
        }
      })

      if (response.ok) {
        const data = await response.json()
        // Check for new notifications and update store
        this.handleNotificationUpdate(data.data || [])
      }
    } catch (error) {
      console.log('Error polling notifications:', error)
    }
  }

  handleNotificationUpdate(notifications) {
    const { notifications: currentNotifications } = store.getState().notifications
    
    // Find new notifications
    const newNotifications = notifications.filter(newNotif => 
      !currentNotifications.some(currentNotif => currentNotif.id === newNotif.id)
    )

    // Add new notifications to store
    newNotifications.forEach(notification => {
      store.dispatch(handleRealTimeNotification(notification))
    })
  }

  // WebSocket implementation (for when backend supports it)
  connectWebSocket() {
    const token = localStorage.getItem('token')
    if (!token) return

    // This would be the actual WebSocket connection
    // this.socket = new WebSocket(`ws://localhost:8000/ws/notifications?token=${token}`)
    
    this.socket.onopen = () => {
      console.log('WebSocket connected')
      this.reconnectAttempts = 0
    }

    this.socket.onmessage = (event) => {
      try {
        const notification = JSON.parse(event.data)
        store.dispatch(handleRealTimeNotification(notification))
      } catch (error) {
        console.error('Error parsing notification:', error)
      }
    }

    this.socket.onclose = () => {
      console.log('WebSocket disconnected')
      this.attemptReconnect()
    }

    this.socket.onerror = (error) => {
      console.error('WebSocket error:', error)
    }
  }

  attemptReconnect() {
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++
      setTimeout(() => {
        this.connectWebSocket()
      }, this.reconnectDelay * this.reconnectAttempts)
    }
  }

  disconnect() {
    if (this.socket) {
      this.socket.close()
      this.socket = null
    }
    
    if (this.pollInterval) {
      clearInterval(this.pollInterval)
      this.pollInterval = null
    }
  }

  // Send notification (for testing or admin use)
  sendNotification(userId, notification) {
    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
      this.socket.send(JSON.stringify({
        user_id: userId,
        notification: notification
      }))
    }
  }
}

// Create singleton instance
const notificationService = new NotificationService()

export default notificationService
