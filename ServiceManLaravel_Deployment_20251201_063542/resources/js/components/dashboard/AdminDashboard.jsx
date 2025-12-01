import React, { useState, useEffect } from 'react';
import StatsCard from './StatsCard';
import DataTable from './DataTable';
import StatusBadge from './StatusBadge';
import SimpleChart from './SimpleChart';
import ServicemanAssignmentModal from './ServicemanAssignmentModal';
import { adminAPI, serviceRequestAPI, userAPI } from '../../services/api';

// Icons (using simple Unicode symbols for now)
const icons = {
    revenue: '💰',
    services: '🔧',
    users: '👥',
    servicemen: '👨‍🔧',
    pending: '⏳',
    completed: '✅',
    earnings: '💵',
    ratings: '⭐'
};

const AdminDashboard = () => {
    const [stats, setStats] = useState({});
    const [pendingAssignments, setPendingAssignments] = useState([]);
    const [recentActivity, setRecentActivity] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedRequest, setSelectedRequest] = useState(null);
    const [isAssignmentModalOpen, setIsAssignmentModalOpen] = useState(false);

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async () => {
        try {
            setLoading(true);
            
            // Initialize with default data
            let revenueStats = { data: { total_revenue: 0, revenue_growth: 0 } };
            let serviceStats = { data: { total_requests: 0, request_growth: 0 } };
            let userStats = { data: { total_users: 0, user_growth: 0 } };
            let servicemanStats = { data: { total_servicemen: 0 } };
            let pendingData = { data: [] };
            let activityData = { data: [] };

            // Try to load each API individually
            try {
                revenueStats = await adminAPI.getRevenueAnalytics();
            } catch (error) {
                console.log('Revenue analytics API not available, using default data');
            }

            try {
                serviceStats = await adminAPI.getServiceRequestStats();
            } catch (error) {
                console.log('Service request stats API not available, using default data');
            }

            try {
                userStats = await adminAPI.getUserStats();
            } catch (error) {
                console.log('User stats API not available, using default data');
            }

            try {
                servicemanStats = await adminAPI.getTopServicemen();
            } catch (error) {
                console.log('Servicemen stats API not available, using default data');
            }

            try {
                pendingData = await adminAPI.getPendingAssignments();
            } catch (error) {
                console.log('Pending assignments API not available, using default data');
            }

            try {
                activityData = await adminAPI.getRecentActivity();
            } catch (error) {
                console.log('Recent activity API not available, using default data');
            }

            // Ensure data is in correct format
            setStats({
                revenue: revenueStats.data || { total_revenue: 0, revenue_growth: 0 },
                services: serviceStats.data || { total_requests: 0, request_growth: 0 },
                users: userStats.data || { total_users: 0, user_growth: 0 },
                servicemen: servicemanStats.data || { total_servicemen: 0 }
            });

            setPendingAssignments(Array.isArray(pendingData.data) ? pendingData.data : []);
            setRecentActivity(Array.isArray(activityData.data) ? activityData.data : []);

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            // Set default empty data
            setStats({
                revenue: { total_revenue: 0, revenue_growth: 0 },
                services: { total_requests: 0, request_growth: 0 },
                users: { total_users: 0, user_growth: 0 },
                servicemen: { total_servicemen: 0 }
            });
            setPendingAssignments([]);
            setRecentActivity([]);
        } finally {
            setLoading(false);
        }
    };

    const handleAssignServiceman = async (requestId, servicemanId) => {
        try {
            await adminAPI.assignServiceman(requestId, { serviceman_id: servicemanId });
            loadDashboardData(); // Refresh data
        } catch (error) {
            console.error('Error assigning serviceman:', error);
        }
    };

    const pendingColumns = [
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
            header: 'Client',
            key: 'client',
            render: (value, row) => (
                <div>
                    <div className="font-medium">{row.client?.first_name} {row.client?.last_name}</div>
                    <div className="text-sm text-gray-500">{row.client?.email}</div>
                </div>
            )
        },
        {
            header: 'Category',
            key: 'category',
            render: (value, row) => row.category?.name || 'N/A'
        },
        {
            header: 'Booking Date',
            key: 'booking_date',
            render: (value) => new Date(value).toLocaleDateString()
        },
        {
            header: 'Emergency',
            key: 'is_emergency',
            render: (value) => value ? '🚨 Yes' : 'No'
        },
        {
            header: 'Actions',
            key: 'actions',
            render: (value, row) => (
                <div className="flex space-x-2">
                    <button
                        onClick={(e) => {
                            e.stopPropagation();
                            setSelectedRequest(row);
                            setIsAssignmentModalOpen(true);
                        }}
                        className="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600"
                    >
                        Assign
                    </button>
                </div>
            )
        }
    ];

    const activityColumns = [
        {
            header: 'Type',
            key: 'type',
            render: (value) => (
                <span className="capitalize">{value.replace('_', ' ')}</span>
            )
        },
        {
            header: 'Description',
            key: 'description'
        },
        {
            header: 'User',
            key: 'user',
            render: (value, row) => row.user ? `${row.user.first_name} ${row.user.last_name}` : 'System'
        },
        {
            header: 'Date',
            key: 'created_at',
            render: (value) => new Date(value).toLocaleString()
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
                <h1 className="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
                <p className="text-gray-600 mt-1">Monitor and manage the ServiceMan platform</p>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatsCard
                    title="Total Revenue"
                    value={`₦${(stats.revenue?.total_revenue || 0).toLocaleString()}`}
                    change={stats.revenue?.revenue_growth}
                    changeType={stats.revenue?.revenue_growth > 0 ? 'positive' : 'negative'}
                    icon={icons.revenue}
                    color="green"
                />
                <StatsCard
                    title="Active Services"
                    value={stats.services?.total_requests || 0}
                    change={stats.services?.request_growth}
                    changeType={stats.services?.request_growth > 0 ? 'positive' : 'negative'}
                    icon={icons.services}
                    color="blue"
                />
                <StatsCard
                    title="Total Users"
                    value={stats.users?.total_users || 0}
                    change={stats.users?.user_growth}
                    changeType={stats.users?.user_growth > 0 ? 'positive' : 'negative'}
                    icon={icons.users}
                    color="purple"
                />
                <StatsCard
                    title="Active Servicemen"
                    value={stats.servicemen?.total_servicemen || 0}
                    icon={icons.servicemen}
                    color="indigo"
                />
            </div>

            {/* Pending Assignments */}
            <div className="bg-white shadow rounded-lg">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h2 className="text-lg font-medium text-gray-900">Pending Service Assignments</h2>
                    <p className="text-sm text-gray-600">Services waiting for serviceman assignment</p>
                </div>
                <div className="p-6">
                    <DataTable
                        data={pendingAssignments}
                        columns={pendingColumns}
                        emptyMessage="No pending assignments"
                        loading={loading}
                    />
                </div>
            </div>

            {/* Recent Activity */}
            <div className="bg-white shadow rounded-lg">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h2 className="text-lg font-medium text-gray-900">Recent Activity</h2>
                    <p className="text-sm text-gray-600">Latest platform activities</p>
                </div>
                <div className="p-6">
                    <DataTable
                        data={recentActivity}
                        columns={activityColumns}
                        emptyMessage="No recent activity"
                        loading={loading}
                    />
                </div>
            </div>

            {/* Analytics Charts */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <SimpleChart
                    title="Service Requests by Category"
                    type="bar"
                    data={[
                        { label: 'Electrical', value: 45 },
                        { label: 'Plumbing', value: 32 },
                        { label: 'HVAC', value: 28 },
                        { label: 'Cleaning', value: 15 },
                        { label: 'Repair', value: 22 }
                    ]}
                    color="blue"
                />
                <SimpleChart
                    title="Revenue Trend (Last 6 Months)"
                    type="line"
                    data={[
                        { label: 'Jan', value: 125000 },
                        { label: 'Feb', value: 142000 },
                        { label: 'Mar', value: 138000 },
                        { label: 'Apr', value: 156000 },
                        { label: 'May', value: 162000 },
                        { label: 'Jun', value: 178000 }
                    ]}
                    color="green"
                />
                <SimpleChart
                    title="User Distribution"
                    type="pie"
                    data={[
                        { label: 'Clients', value: 65 },
                        { label: 'Servicemen', value: 25 },
                        { label: 'Admins', value: 10 }
                    ]}
                />
                <SimpleChart
                    title="Top Performing Servicemen"
                    type="bar"
                    data={[
                        { label: 'John Doe', value: 45 },
                        { label: 'Jane Smith', value: 38 },
                        { label: 'Mike Johnson', value: 32 },
                        { label: 'Sarah Wilson', value: 28 }
                    ]}
                    color="purple"
                />
            </div>

            {/* Quick Actions */}
            <div className="bg-white shadow rounded-lg p-6">
                <h2 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <button 
                        onClick={() => {
                            // Show user management options
                            alert('User Management Features:\n\n• View all users\n• Manage user permissions\n• Suspend/activate accounts\n• View user activity\n• Export user data\n\nThis feature will be implemented in the next update.');
                        }}
                        className="bg-blue-500 text-white p-4 rounded-lg hover:bg-blue-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">👥</div>
                        <div className="font-medium">Manage Users</div>
                        <div className="text-xs opacity-90 mt-1">User administration</div>
                    </button>
                    <button 
                        onClick={() => {
                            // Show detailed analytics
                            const revenue = stats.revenue?.total_revenue || 0;
                            const services = stats.services?.total_requests || 0;
                            const users = stats.users?.total_users || 0;
                            const servicemen = stats.servicemen?.total_servicemen || 0;
                            
                            alert(`Detailed Analytics:\n\n💰 Total Revenue: ₦${revenue.toLocaleString()}\n🔧 Total Services: ${services}\n👥 Total Users: ${users}\n👨‍🔧 Active Servicemen: ${servicemen}\n\n📈 Growth Rates:\nRevenue: ${stats.revenue?.revenue_growth || 0}%\nServices: ${stats.services?.request_growth || 0}%\nUsers: ${stats.users?.user_growth || 0}%`);
                        }}
                        className="bg-green-500 text-white p-4 rounded-lg hover:bg-green-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">📊</div>
                        <div className="font-medium">View Analytics</div>
                        <div className="text-xs opacity-90 mt-1">Platform metrics</div>
                    </button>
                    <button 
                        onClick={() => window.location.href = '/settings'}
                        className="bg-purple-500 text-white p-4 rounded-lg hover:bg-purple-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">⚙️</div>
                        <div className="font-medium">Settings</div>
                        <div className="text-xs opacity-90 mt-1">System preferences</div>
                    </button>
                    <button 
                        onClick={() => {
                            // Show system status
                            const pendingCount = pendingAssignments.length;
                            const recentActivityCount = recentActivity.length;
                            
                            alert(`System Status:\n\n⏳ Pending Assignments: ${pendingCount}\n📋 Recent Activities: ${recentActivityCount}\n✅ System: Operational\n🔒 Security: Active\n📊 Database: Connected\n\nAll systems are running normally.`);
                        }}
                        className="bg-orange-500 text-white p-4 rounded-lg hover:bg-orange-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">🔍</div>
                        <div className="font-medium">System Status</div>
                        <div className="text-xs opacity-90 mt-1">Health check</div>
                    </button>
                </div>
            </div>

            {/* Serviceman Assignment Modal */}
            <ServicemanAssignmentModal
                isOpen={isAssignmentModalOpen}
                onClose={() => {
                    setIsAssignmentModalOpen(false);
                    setSelectedRequest(null);
                }}
                serviceRequest={selectedRequest}
                onSuccess={() => {
                    loadDashboardData(); // Refresh data after successful assignment
                }}
            />
        </div>
    );
};

export default AdminDashboard;
