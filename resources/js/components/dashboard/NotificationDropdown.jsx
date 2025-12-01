import React, { useState, useEffect } from 'react';
import { notificationAPI } from '../../services/api';
import StatusBadge from './StatusBadge';

const NotificationDropdown = ({ isOpen, onClose }) => {
    const [notifications, setNotifications] = useState([]);
    const [loading, setLoading] = useState(false);
    const [unreadCount, setUnreadCount] = useState(0);

    useEffect(() => {
        if (isOpen) {
            loadNotifications();
        }
    }, [isOpen]);

    useEffect(() => {
        // Load unread count when component mounts
        loadUnreadCount();
    }, []);

    const loadNotifications = async () => {
        try {
            setLoading(true);
            const response = await notificationAPI.index();
            setNotifications(response.data || []);
        } catch (error) {
            console.error('Error loading notifications:', error);
        } finally {
            setLoading(false);
        }
    };

    const loadUnreadCount = async () => {
        try {
            const response = await notificationAPI.getUnreadCount();
            setUnreadCount(response.data.count || 0);
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    };

    const markAsRead = async (notificationId) => {
        try {
            await notificationAPI.markAsRead(notificationId);
            setNotifications(prev => 
                prev.map(notif => 
                    notif.id === notificationId 
                        ? { ...notif, is_read: true }
                        : notif
                )
            );
            setUnreadCount(prev => Math.max(0, prev - 1));
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await notificationAPI.markAllAsRead();
            setNotifications(prev => 
                prev.map(notif => ({ ...notif, is_read: true }))
            );
            setUnreadCount(0);
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    };

    const deleteNotification = async (notificationId) => {
        try {
            await notificationAPI.destroy(notificationId);
            setNotifications(prev => 
                prev.filter(notif => notif.id !== notificationId)
            );
            setUnreadCount(prev => Math.max(0, prev - 1));
        } catch (error) {
            console.error('Error deleting notification:', error);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <div className="p-4 border-b border-gray-200">
                <div className="flex justify-between items-center">
                    <h3 className="text-lg font-medium text-gray-900">Notifications</h3>
                    <div className="flex space-x-2">
                        <button
                            onClick={markAllAsRead}
                            className="text-blue-500 hover:text-blue-600 text-sm"
                            disabled={unreadCount === 0}
                        >
                            Mark all read
                        </button>
                        <button
                            onClick={onClose}
                            className="text-gray-400 hover:text-gray-600"
                        >
                            ✕
                        </button>
                    </div>
                </div>
                {unreadCount > 0 && (
                    <p className="text-sm text-gray-600 mt-1">
                        {unreadCount} unread notification{unreadCount !== 1 ? 's' : ''}
                    </p>
                )}
            </div>

            <div className="max-h-96 overflow-y-auto">
                {loading ? (
                    <div className="p-4 text-center">
                        <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mx-auto"></div>
                        <p className="text-sm text-gray-500 mt-2">Loading...</p>
                    </div>
                ) : notifications.length === 0 ? (
                    <div className="p-4 text-center text-gray-500">
                        <p>No notifications</p>
                    </div>
                ) : (
                    notifications.slice(0, 10).map((notification) => (
                        <div
                            key={notification.id}
                            className={`p-4 border-b border-gray-100 hover:bg-gray-50 ${
                                !notification.is_read ? 'bg-blue-50' : ''
                            }`}
                        >
                            <div className="flex justify-between items-start">
                                <div className="flex-1">
                                    <div className="flex items-center space-x-2">
                                        <h4 className="text-sm font-medium text-gray-900">
                                            {notification.title}
                                        </h4>
                                        {!notification.is_read && (
                                            <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        )}
                                    </div>
                                    <p className="text-sm text-gray-600 mt-1">
                                        {notification.message}
                                    </p>
                                    <div className="flex items-center justify-between mt-2">
                                        <span className="text-xs text-gray-500">
                                            {new Date(notification.created_at).toLocaleDateString()}
                                        </span>
                                        <span className="text-xs text-gray-500 capitalize">
                                            {notification.notification_type?.replace('_', ' ')}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex space-x-1 ml-2">
                                    {!notification.is_read && (
                                        <button
                                            onClick={() => markAsRead(notification.id)}
                                            className="text-blue-500 hover:text-blue-600 text-xs"
                                            title="Mark as read"
                                        >
                                            ✓
                                        </button>
                                    )}
                                    <button
                                        onClick={() => deleteNotification(notification.id)}
                                        className="text-red-500 hover:text-red-600 text-xs"
                                        title="Delete"
                                    >
                                        🗑
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>

            {notifications.length > 10 && (
                <div className="p-4 border-t border-gray-200 text-center">
                    <button className="text-blue-500 hover:text-blue-600 text-sm">
                        View all notifications
                    </button>
                </div>
            )}
        </div>
    );
};

export default NotificationDropdown;
