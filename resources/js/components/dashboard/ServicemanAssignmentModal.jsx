import React, { useState, useEffect } from 'react';
import { adminAPI, categoryAPI } from '../../services/api';

const ServicemanAssignmentModal = ({ isOpen, onClose, serviceRequest, onSuccess }) => {
    const [servicemen, setServicemen] = useState([]);
    const [selectedServiceman, setSelectedServiceman] = useState('');
    const [loading, setLoading] = useState(false);
    const [assigning, setAssigning] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        if (isOpen && serviceRequest?.category_id) {
            loadServicemenByCategory(serviceRequest.category_id);
        }
    }, [isOpen, serviceRequest]);

    const loadServicemenByCategory = async (categoryId) => {
        try {
            setLoading(true);
            const response = await categoryAPI.getServicemenByCategory(categoryId);
            setServicemen(response.data || []);
        } catch (error) {
            console.error('Error loading servicemen:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleAssign = async (e) => {
        e.preventDefault();
        
        if (!selectedServiceman) {
            setErrors({ serviceman: 'Please select a serviceman' });
            return;
        }

        try {
            setAssigning(true);
            await adminAPI.assignServiceman(serviceRequest.id, { 
                serviceman_id: selectedServiceman 
            });
            onSuccess && onSuccess();
            onClose();
            resetForm();
        } catch (error) {
            console.error('Error assigning serviceman:', error);
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
        } finally {
            setAssigning(false);
        }
    };

    const resetForm = () => {
        setSelectedServiceman('');
        setErrors({});
    };

    const handleClose = () => {
        resetForm();
        onClose();
    };

    if (!isOpen || !serviceRequest) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4">
                <div className="p-6">
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-2xl font-bold text-gray-900">Assign Serviceman</h2>
                        <button
                            onClick={handleClose}
                            className="text-gray-400 hover:text-gray-600"
                        >
                            ✕
                        </button>
                    </div>

                    {/* Service Request Info */}
                    <div className="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 className="font-medium text-gray-900 mb-2">Service Request Details</h3>
                        <div className="space-y-1 text-sm text-gray-600">
                            <p><strong>Request ID:</strong> #{serviceRequest.id}</p>
                            <p><strong>Category:</strong> {serviceRequest.category?.name}</p>
                            <p><strong>Description:</strong> {serviceRequest.service_description}</p>
                            <p><strong>Client:</strong> {serviceRequest.client?.first_name} {serviceRequest.client?.last_name}</p>
                            <p><strong>Address:</strong> {serviceRequest.client_address}</p>
                            <p><strong>Booking Date:</strong> {new Date(serviceRequest.booking_date).toLocaleDateString()}</p>
                            {serviceRequest.is_emergency && (
                                <p className="text-red-600 font-medium">🚨 Emergency Request</p>
                            )}
                        </div>
                    </div>

                    <form onSubmit={handleAssign}>
                        {/* Serviceman Selection */}
                        <div className="mb-6">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Select Serviceman *
                            </label>
                            {loading ? (
                                <div className="flex items-center justify-center py-4">
                                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                                    <span className="ml-2 text-gray-600">Loading servicemen...</span>
                                </div>
                            ) : servicemen.length === 0 ? (
                                <div className="text-center py-4 text-gray-500">
                                    No available servicemen for this category
                                </div>
                            ) : (
                                <div className="space-y-2 max-h-60 overflow-y-auto">
                                    {servicemen.map((serviceman) => (
                                        <label
                                            key={serviceman.id}
                                            className={`flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 ${
                                                selectedServiceman === serviceman.id.toString() 
                                                    ? 'border-blue-500 bg-blue-50' 
                                                    : 'border-gray-200'
                                            }`}
                                        >
                                            <input
                                                type="radio"
                                                name="serviceman"
                                                value={serviceman.id}
                                                checked={selectedServiceman === serviceman.id.toString()}
                                                onChange={(e) => setSelectedServiceman(e.target.value)}
                                                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                            />
                                            <div className="ml-3 flex-1">
                                                <div className="flex justify-between items-start">
                                                    <div>
                                                        <p className="text-sm font-medium text-gray-900">
                                                            {serviceman.first_name} {serviceman.last_name}
                                                        </p>
                                                        <p className="text-sm text-gray-600">
                                                            {serviceman.email}
                                                        </p>
                                                        {serviceman.servicemanProfile && (
                                                            <div className="flex items-center space-x-4 mt-1">
                                                                <span className="text-xs text-gray-500">
                                                                    ⭐ {serviceman.servicemanProfile.rating || 'N/A'}
                                                                </span>
                                                                <span className="text-xs text-gray-500">
                                                                    📞 {serviceman.servicemanProfile.phone_number}
                                                                </span>
                                                                <span className={`text-xs px-2 py-1 rounded-full ${
                                                                    serviceman.servicemanProfile.is_available 
                                                                        ? 'bg-green-100 text-green-800' 
                                                                        : 'bg-red-100 text-red-800'
                                                                }`}>
                                                                    {serviceman.servicemanProfile.is_available ? 'Available' : 'Busy'}
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    ))}
                                </div>
                            )}
                            {errors.serviceman && (
                                <p className="text-red-500 text-sm mt-1">{errors.serviceman}</p>
                            )}
                        </div>

                        {/* Form Actions */}
                        <div className="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <button
                                type="button"
                                onClick={handleClose}
                                className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                                disabled={assigning}
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={assigning || !selectedServiceman}
                                className="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {assigning ? 'Assigning...' : 'Assign Serviceman'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default ServicemanAssignmentModal;
