import React, { useState, useEffect } from 'react';
import StatsCard from './StatsCard';
import DataTable from './DataTable';
import StatusBadge from './StatusBadge';
import ServiceRequestModal from './ServiceRequestModal';
import { serviceRequestAPI, paymentAPI, ratingAPI, notificationAPI } from '../../services/api';

const ClientDashboard = () => {
    const [serviceRequests, setServiceRequests] = useState([]);
    const [payments, setPayments] = useState([]);
    const [notifications, setNotifications] = useState([]);
    const [stats, setStats] = useState({});
    const [loading, setLoading] = useState(true);
    const [isServiceModalOpen, setIsServiceModalOpen] = useState(false);

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async () => {
        try {
            setLoading(true);
            
            // Initialize with empty data
            let requestsData = { data: [] };
            let paymentsData = { data: [] };
            let notificationsData = { data: [] };

            // Try to load service requests
            try {
                requestsData = await serviceRequestAPI.index();
            } catch (error) {
                console.log('Service requests API not available, using empty data');
            }

            // Try to load payment history
            try {
                paymentsData = await paymentAPI.getPaymentHistory();
            } catch (error) {
                console.log('Payment history API not available, using empty data');
            }

            // Try to load notifications
            try {
                notificationsData = await notificationAPI.index();
            } catch (error) {
                console.log('Notifications API not available, using empty data');
            }

            // Ensure data is an array
            const serviceRequestsArray = Array.isArray(requestsData.data) ? requestsData.data : [];
            const paymentsArray = Array.isArray(paymentsData.data) ? paymentsData.data : [];
            const notificationsArray = Array.isArray(notificationsData.data) ? notificationsData.data : [];

            setServiceRequests(serviceRequestsArray);
            setPayments(paymentsArray);
            setNotifications(notificationsArray);

            // Calculate stats safely
            const totalRequests = serviceRequestsArray.length;
            const completedRequests = serviceRequestsArray.filter(r => r.status === 'COMPLETED').length;
            const pendingRequests = serviceRequestsArray.filter(r => ['PENDING_ADMIN_ASSIGNMENT', 'ASSIGNED_TO_SERVICEMAN', 'IN_PROGRESS'].includes(r.status)).length;
            const totalSpent = paymentsArray.filter(p => p.status === 'SUCCESSFUL').reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);

            setStats({
                totalRequests,
                completedRequests,
                pendingRequests,
                totalSpent: totalSpent || 0
            });

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            // Set default empty data
            setServiceRequests([]);
            setPayments([]);
            setNotifications([]);
            setStats({
                totalRequests: 0,
                completedRequests: 0,
                pendingRequests: 0,
                totalSpent: 0
            });
        } finally {
            setLoading(false);
        }
    };

    const handleRequestClick = (request) => {
        // TODO: Navigate to service request details
        console.log('View request:', request);
    };

    const handlePaymentClick = (payment) => {
        // TODO: Navigate to payment details
        console.log('View payment:', payment);
    };

    const serviceRequestColumns = [
        {
            header: 'Service Request',
            key: 'id',
            render: (value, row) => (
                <div>
                    <div className="font-medium">#{value}</div>
                    <div className="text-sm text-gray-500">{row.service_description}</div>
                </div>
            )
        },
        {
            header: 'Category',
            key: 'category',
            render: (value, row) => row.category?.name || 'N/A'
        },
        {
            header: 'Serviceman',
            key: 'serviceman',
            render: (value, row) => (
                row.serviceman ? 
                <div>
                    <div className="font-medium">{row.serviceman.first_name} {row.serviceman.last_name}</div>
                    <div className="text-sm text-gray-500">{row.serviceman.email}</div>
                </div> : 
                <span className="text-gray-500">Not assigned</span>
            )
        },
        {
            header: 'Status',
            key: 'status',
            render: (value) => <StatusBadge status={value} />
        },
        {
            header: 'Booking Date',
            key: 'booking_date',
            render: (value) => new Date(value).toLocaleDateString()
        },
        {
            header: 'Cost',
            key: 'final_cost',
            render: (value, row) => (
                <div>
                    {value ? `₦${parseFloat(value).toLocaleString()}` : 'TBD'}
                    {row.is_emergency && <div className="text-xs text-red-600">Emergency</div>}
                </div>
            )
        }
    ];

    const paymentColumns = [
        {
            header: 'Payment',
            key: 'id',
            render: (value, row) => (
                <div>
                    <div className="font-medium">#{value}</div>
                    <div className="text-sm text-gray-500">{row.payment_type.replace('_', ' ')}</div>
                </div>
            )
        },
        {
            header: 'Service Request',
            key: 'service_request_id',
            render: (value) => `#${value}`
        },
        {
            header: 'Amount',
            key: 'amount',
            render: (value) => `₦${parseFloat(value).toLocaleString()}`
        },
        {
            header: 'Status',
            key: 'status',
            render: (value) => <StatusBadge status={value} />
        },
        {
            header: 'Date',
            key: 'created_at',
            render: (value) => new Date(value).toLocaleDateString()
        }
    ];

    const notificationColumns = [
        {
            header: 'Notification',
            key: 'title',
            render: (value, row) => (
                <div>
                    <div className="font-medium">{value}</div>
                    <div className="text-sm text-gray-500">{row.message}</div>
                </div>
            )
        },
        {
            header: 'Type',
            key: 'notification_type',
            render: (value) => (
                <span className="capitalize">{value.replace('_', ' ')}</span>
            )
        },
        {
            header: 'Date',
            key: 'created_at',
            render: (value) => new Date(value).toLocaleDateString()
        },
        {
            header: 'Status',
            key: 'is_read',
            render: (value) => (
                <span className={value ? 'text-green-600' : 'text-red-600'}>
                    {value ? 'Read' : 'Unread'}
                </span>
            )
        }
    ];

    if (loading) {
        return (
            <div className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {[...Array(4)].map((_, i) => (
                        <div key={i} className="bg-white rounded-lg shadow-md p-6 animate-pulse">
                            <div className="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div className="h-8 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="bg-white shadow rounded-lg p-6">
                <h1 className="text-2xl font-bold text-gray-900">My Dashboard</h1>
                <p className="text-gray-600 mt-1">Manage your service requests and track your activities</p>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatsCard
                    title="Total Requests"
                    value={stats.totalRequests}
                    icon="🔧"
                    color="blue"
                />
                <StatsCard
                    title="Completed"
                    value={stats.completedRequests}
                    icon="✅"
                    color="green"
                />
                <StatsCard
                    title="In Progress"
                    value={stats.pendingRequests}
                    icon="⏳"
                    color="yellow"
                />
                <StatsCard
                    title="Total Spent"
                    value={`₦${stats.totalSpent.toLocaleString()}`}
                    icon="💰"
                    color="purple"
                />
            </div>

            {/* Service Requests */}
            <div className="bg-white shadow rounded-lg">
                <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h2 className="text-lg font-medium text-gray-900">My Service Requests</h2>
                        <p className="text-sm text-gray-600">Track your service requests and their status</p>
                    </div>
                    <button 
                        onClick={() => setIsServiceModalOpen(true)}
                        className="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        Request Service
                    </button>
                </div>
                <div className="p-6">
                    <DataTable
                        data={serviceRequests}
                        columns={serviceRequestColumns}
                        onRowClick={handleRequestClick}
                        emptyMessage="No service requests yet"
                        loading={loading}
                    />
                </div>
            </div>

            {/* Recent Payments */}
            <div className="bg-white shadow rounded-lg">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h2 className="text-lg font-medium text-gray-900">Recent Payments</h2>
                    <p className="text-sm text-gray-600">Your payment history</p>
                </div>
                <div className="p-6">
                    <DataTable
                        data={payments.slice(0, 10)} // Show only recent 10
                        columns={paymentColumns}
                        onRowClick={handlePaymentClick}
                        emptyMessage="No payments yet"
                        loading={loading}
                    />
                </div>
            </div>

            {/* Notifications */}
            <div className="bg-white shadow rounded-lg">
                <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h2 className="text-lg font-medium text-gray-900">Notifications</h2>
                        <p className="text-sm text-gray-600">Stay updated with your service requests</p>
                    </div>
                    <button 
                        onClick={() => {
                            // TODO: Mark all as read
                            console.log('Mark all notifications as read');
                        }}
                        className="text-blue-500 hover:text-blue-600 text-sm"
                    >
                        Mark all as read
                    </button>
                </div>
                <div className="p-6">
                    <DataTable
                        data={notifications.slice(0, 5)} // Show only recent 5
                        columns={notificationColumns}
                        emptyMessage="No notifications"
                        loading={loading}
                    />
                </div>
            </div>

            {/* Quick Actions */}
            <div className="bg-white shadow rounded-lg p-6">
                <h2 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button 
                        onClick={() => setIsServiceModalOpen(true)}
                        className="bg-blue-500 text-white p-4 rounded-lg hover:bg-blue-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">🔧</div>
                        <div className="font-medium">Request Service</div>
                        <div className="text-xs opacity-90 mt-1">Book a new service</div>
                    </button>
                    <button 
                        onClick={() => {
                            // Find completed requests that haven't been rated
                            const completedRequests = serviceRequests.filter(r => r.status === 'COMPLETED' && !r.rating);
                            if (completedRequests.length > 0) {
                                alert(`You have ${completedRequests.length} completed service(s) that can be rated. Click on a service request to rate it.`);
                            } else {
                                alert('No completed services available for rating.');
                            }
                        }}
                        className="bg-green-500 text-white p-4 rounded-lg hover:bg-green-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">⭐</div>
                        <div className="font-medium">Rate Service</div>
                        <div className="text-xs opacity-90 mt-1">Rate completed services</div>
                    </button>
                    <button 
                        onClick={() => window.location.href = '/settings'}
                        className="bg-purple-500 text-white p-4 rounded-lg hover:bg-purple-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">👤</div>
                        <div className="font-medium">Update Profile</div>
                        <div className="text-xs opacity-90 mt-1">Manage your profile</div>
                    </button>
                </div>
            </div>

            {/* Service Request Modal */}
            <ServiceRequestModal
                isOpen={isServiceModalOpen}
                onClose={() => setIsServiceModalOpen(false)}
                onSuccess={() => {
                    loadDashboardData(); // Refresh data after successful creation
                }}
            />
        </div>
    );
};

export default ClientDashboard;
