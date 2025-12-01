import React, { useState, useEffect } from 'react';
import { useSelector } from 'react-redux';
import AdminDashboard from '../dashboard/AdminDashboard';
import ClientDashboard from '../dashboard/ClientDashboard';
import ServicemanDashboard from '../dashboard/ServicemanDashboard';
import { authAPI } from '../../services/api';

const DashboardPage = () => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const authState = useSelector(state => state.auth);

    useEffect(() => {
        loadUser();
    }, []);

    const loadUser = async () => {
        try {
            const response = await authAPI.me();
            setUser(response.data);
        } catch (error) {
            console.error('Error loading user:', error);
            // Redirect to login if not authenticated
            window.location.href = '/login';
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gray-100 flex items-center justify-center">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                    <p className="mt-4 text-gray-600">Loading dashboard...</p>
                </div>
            </div>
        );
    }

    if (!user) {
        return (
            <div className="min-h-screen bg-gray-100 flex items-center justify-center">
                <div className="text-center">
                    <p className="text-red-600">Unable to load user data</p>
                    <button 
                        onClick={() => window.location.href = '/login'}
                        className="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                    >
                        Go to Login
                    </button>
                </div>
            </div>
        );
    }

    // Render appropriate dashboard based on user type
    const renderDashboard = () => {
        switch (user.user_type) {
            case 'ADMIN':
                return <AdminDashboard />;
            case 'CLIENT':
                return <ClientDashboard />;
            case 'SERVICEMAN':
                return <ServicemanDashboard />;
            default:
                return (
                    <div className="bg-white shadow rounded-lg p-6">
                        <h1 className="text-2xl font-bold text-gray-900">Welcome!</h1>
                        <p className="text-gray-600 mt-2">
                            Your account type is not recognized. Please contact support.
                        </p>
                    </div>
                );
        }
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <div className="container mx-auto px-4 py-8">
                {renderDashboard()}
            </div>
        </div>
    );
};

export default DashboardPage;