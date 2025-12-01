import React, { useState, useEffect } from 'react';
import StatsCard from './StatsCard';
import DataTable from './DataTable';
import StatusBadge from './StatusBadge';
import { serviceRequestAPI, userAPI, notificationAPI } from '../../services/api';

const ServicemanDashboard = () => {
    const [serviceRequests, setServiceRequests] = useState([]);
    const [notifications, setNotifications] = useState([]);
    const [profile, setProfile] = useState({});
    const [stats, setStats] = useState({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async () => {
        try {
            setLoading(true);
            
            // Initialize with empty data
            let requestsData = { data: [] };
            let notificationsData = { data: [] };
            let profileData = { data: {} };

            // Try to load service requests
            try {
                requestsData = await serviceRequestAPI.index();
            } catch (error) {
                console.log('Service requests API not available, using empty data');
            }

            // Try to load notifications
            try {
                notificationsData = await notificationAPI.index();
            } catch (error) {
                console.log('Notifications API not available, using empty data');
            }

            // Try to load profile
            try {
                profileData = await userAPI.getServicemanProfile();
            } catch (error) {
                console.log('Profile API not available, using empty data');
            }

            // Ensure data is in correct format
            const serviceRequestsArray = Array.isArray(requestsData.data) ? requestsData.data : [];
            const notificationsArray = Array.isArray(notificationsData.data) ? notificationsData.data : [];
            const profileObject = profileData.data || {};

            setServiceRequests(serviceRequestsArray);
            setNotifications(notificationsArray);
            setProfile(profileObject);

            // Calculate stats safely
            const totalJobs = serviceRequestsArray.length;
            const completedJobs = serviceRequestsArray.filter(r => r.status === 'COMPLETED').length;
            const inProgressJobs = serviceRequestsArray.filter(r => ['ASSIGNED_TO_SERVICEMAN', 'IN_PROGRESS'].includes(r.status)).length;
            const totalEarnings = serviceRequestsArray.filter(r => r.status === 'COMPLETED').reduce((sum, r) => sum + (parseFloat(r.serviceman_estimated_cost) || 0), 0);

            setStats({
                totalJobs,
                completedJobs,
                inProgressJobs,
                totalEarnings: totalEarnings || 0,
                rating: profileObject.rating || 0,
                totalJobsCompleted: profileObject.total_jobs_completed || 0
            });

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            // Set default empty data
            setServiceRequests([]);
            setNotifications([]);
            setProfile({});
            setStats({
                totalJobs: 0,
                completedJobs: 0,
                inProgressJobs: 0,
                totalEarnings: 0,
                rating: 0,
                totalJobsCompleted: 0
            });
        } finally {
            setLoading(false);
        }
    };

    const handleRequestClick = (request) => {
        // TODO: Navigate to service request details
        console.log('View request:', request);
    };

    const handleSubmitEstimate = async (requestId, estimate) => {
        try {
            await serviceRequestAPI.submitEstimate(requestId, { estimated_cost: estimate });
            loadDashboardData(); // Refresh data
        } catch (error) {
            console.error('Error submitting estimate:', error);
        }
    };

    const handleMarkComplete = async (requestId) => {
        try {
            await serviceRequestAPI.markComplete(requestId, {});
            loadDashboardData(); // Refresh data
        } catch (error) {
            console.error('Error marking complete:', error);
        }
    };

    const toggleAvailability = async () => {
        try {
            const newAvailability = !profile.is_available;
            await userAPI.updateServicemanProfile({ is_available: newAvailability });
            setProfile(prev => ({ ...prev, is_available: newAvailability }));
        } catch (error) {
            console.error('Error updating availability:', error);
        }
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
            header: 'Estimated Cost',
            key: 'serviceman_estimated_cost',
            render: (value, row) => (
                <div>
                    {value ? `₦${parseFloat(value).toLocaleString()}` : 'Not set'}
                    {row.is_emergency && <div className="text-xs text-red-600">Emergency</div>}
                </div>
            )
        },
        {
            header: 'Actions',
            key: 'actions',
            render: (value, row) => (
                <div className="flex space-x-2">
                    {row.status === 'ASSIGNED_TO_SERVICEMAN' && (
                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                const estimate = prompt('Enter estimated cost:');
                                if (estimate) {
                                    handleSubmitEstimate(row.id, estimate);
                                }
                            }}
                            className="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600"
                        >
                            Submit Estimate
                        </button>
                    )}
                    {row.status === 'IN_PROGRESS' && (
                        <button
                            onClick={(e) => {
                                e.stopPropagation();
                                if (confirm('Mark this service as complete?')) {
                                    handleMarkComplete(row.id);
                                }
                            }}
                            className="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600"
                        >
                            Mark Complete
                        </button>
                    )}
                </div>
            )
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
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">Serviceman Dashboard</h1>
                        <p className="text-gray-600 mt-1">Manage your jobs and track your performance</p>
                    </div>
                    <div className="flex items-center space-x-4">
                        <div className="text-right">
                            <div className="text-sm text-gray-500">Availability Status</div>
                            <div className={`font-medium ${profile.is_available ? 'text-green-600' : 'text-red-600'}`}>
                                {profile.is_available ? 'Available' : 'Busy'}
                            </div>
                        </div>
                        <button
                            onClick={toggleAvailability}
                            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                                profile.is_available 
                                    ? 'bg-red-500 text-white hover:bg-red-600' 
                                    : 'bg-green-500 text-white hover:bg-green-600'
                            }`}
                        >
                            {profile.is_available ? 'Set Busy' : 'Set Available'}
                        </button>
                    </div>
                </div>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <StatsCard
                    title="Total Jobs"
                    value={stats.totalJobs}
                    icon="🔧"
                    color="blue"
                />
                <StatsCard
                    title="Completed"
                    value={stats.completedJobs}
                    icon="✅"
                    color="green"
                />
                <StatsCard
                    title="In Progress"
                    value={stats.inProgressJobs}
                    icon="⏳"
                    color="yellow"
                />
                <StatsCard
                    title="Total Earnings"
                    value={`₦${stats.totalEarnings.toLocaleString()}`}
                    icon="💰"
                    color="purple"
                />
                <StatsCard
                    title="Rating"
                    value={`${stats.rating}/5.0`}
                    icon="⭐"
                    color="indigo"
                />
            </div>

            {/* Profile Summary */}
            <div className="bg-white shadow rounded-lg p-6">
                <h2 className="text-lg font-medium text-gray-900 mb-4">Profile Summary</h2>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <div className="text-sm text-gray-500">Experience</div>
                        <div className="font-medium">{profile.years_of_experience || 'N/A'}</div>
                    </div>
                    <div>
                        <div className="text-sm text-gray-500">Total Jobs Completed</div>
                        <div className="font-medium">{stats.totalJobsCompleted}</div>
                    </div>
                    <div>
                        <div className="text-sm text-gray-500">Phone Number</div>
                        <div className="font-medium">{profile.phone_number || 'N/A'}</div>
                    </div>
                </div>
                {profile.bio && (
                    <div className="mt-4">
                        <div className="text-sm text-gray-500">Bio</div>
                        <div className="font-medium">{profile.bio}</div>
                    </div>
                )}
            </div>

            {/* Assigned Jobs */}
            <div className="bg-white shadow rounded-lg">
                <div className="px-6 py-4 border-b border-gray-200">
                    <h2 className="text-lg font-medium text-gray-900">My Assigned Jobs</h2>
                    <p className="text-sm text-gray-600">Service requests assigned to you</p>
                </div>
                <div className="p-6">
                    <DataTable
                        data={serviceRequests}
                        columns={serviceRequestColumns}
                        onRowClick={handleRequestClick}
                        emptyMessage="No jobs assigned yet"
                        loading={loading}
                    />
                </div>
            </div>

            {/* Notifications */}
            <div className="bg-white shadow rounded-lg">
                <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h2 className="text-lg font-medium text-gray-900">Notifications</h2>
                        <p className="text-sm text-gray-600">Stay updated with your job assignments</p>
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
                        onClick={() => window.location.href = '/settings'}
                        className="bg-blue-500 text-white p-4 rounded-lg hover:bg-blue-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">📝</div>
                        <div className="font-medium">Update Profile</div>
                        <div className="text-xs opacity-90 mt-1">Edit your profile</div>
                    </button>
                    <button 
                        onClick={() => {
                            // Show analytics in an alert for now
                            const totalJobs = stats.totalJobs || 0;
                            const completedJobs = stats.completedJobs || 0;
                            const completionRate = totalJobs > 0 ? Math.round((completedJobs / totalJobs) * 100) : 0;
                            const earnings = stats.totalEarnings || 0;
                            
                            alert(`Analytics Summary:\n\nTotal Jobs: ${totalJobs}\nCompleted: ${completedJobs}\nCompletion Rate: ${completionRate}%\nTotal Earnings: ₦${earnings.toLocaleString()}\nRating: ${stats.rating || 'N/A'}/5.0`);
                        }}
                        className="bg-green-500 text-white p-4 rounded-lg hover:bg-green-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">📊</div>
                        <div className="font-medium">View Analytics</div>
                        <div className="text-xs opacity-90 mt-1">Performance metrics</div>
                    </button>
                    <button 
                        onClick={() => window.location.href = '/settings'}
                        className="bg-purple-500 text-white p-4 rounded-lg hover:bg-purple-600 transition-colors flex flex-col items-center"
                    >
                        <div className="text-2xl mb-2">⚙️</div>
                        <div className="font-medium">Settings</div>
                        <div className="text-xs opacity-90 mt-1">Account preferences</div>
                    </button>
                </div>
            </div>
        </div>
    );
};

export default ServicemanDashboard;
