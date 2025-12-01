import React, { useState, useEffect } from 'react';
import { categoryAPI, serviceRequestAPI } from '../../services/api';

const ServiceRequestModal = ({ isOpen, onClose, onSuccess }) => {
    const [formData, setFormData] = useState({
        category_id: '',
        service_description: '',
        client_address: '',
        booking_date: '',
        is_emergency: false
    });
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    useEffect(() => {
        if (isOpen) {
            loadCategories();
            // Set default booking date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            setFormData(prev => ({
                ...prev,
                booking_date: tomorrow.toISOString().split('T')[0]
            }));
        }
    }, [isOpen]);

    const loadCategories = async () => {
        try {
            const response = await categoryAPI.index();
            setCategories(response.data || []);
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    };

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
        
        // Clear error when user starts typing
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };

    const validateForm = () => {
        const newErrors = {};

        if (!formData.category_id) {
            newErrors.category_id = 'Please select a category';
        }
        if (!formData.service_description.trim()) {
            newErrors.service_description = 'Please describe the service needed';
        }
        if (!formData.client_address.trim()) {
            newErrors.client_address = 'Please provide your address';
        }
        if (!formData.booking_date) {
            newErrors.booking_date = 'Please select a booking date';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }

        try {
            setLoading(true);
            await serviceRequestAPI.store(formData);
            onSuccess && onSuccess();
            onClose();
            resetForm();
        } catch (error) {
            console.error('Error creating service request:', error);
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
        } finally {
            setLoading(false);
        }
    };

    const resetForm = () => {
        setFormData({
            category_id: '',
            service_description: '',
            client_address: '',
            booking_date: '',
            is_emergency: false
        });
        setErrors({});
    };

    const handleClose = () => {
        resetForm();
        onClose();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div className="p-6">
                    <div className="flex justify-between items-center mb-6">
                        <h2 className="text-2xl font-bold text-gray-900">Request Service</h2>
                        <button
                            onClick={handleClose}
                            className="text-gray-400 hover:text-gray-600"
                        >
                            ✕
                        </button>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Category Selection */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Service Category *
                            </label>
                            <select
                                name="category_id"
                                value={formData.category_id}
                                onChange={handleInputChange}
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.category_id ? 'border-red-500' : 'border-gray-300'
                                }`}
                            >
                                <option value="">Select a category</option>
                                {categories.map(category => (
                                    <option key={category.id} value={category.id}>
                                        {category.name}
                                    </option>
                                ))}
                            </select>
                            {errors.category_id && (
                                <p className="text-red-500 text-sm mt-1">{errors.category_id}</p>
                            )}
                        </div>

                        {/* Service Description */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Service Description *
                            </label>
                            <textarea
                                name="service_description"
                                value={formData.service_description}
                                onChange={handleInputChange}
                                rows={4}
                                placeholder="Please describe the service you need in detail..."
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.service_description ? 'border-red-500' : 'border-gray-300'
                                }`}
                            />
                            {errors.service_description && (
                                <p className="text-red-500 text-sm mt-1">{errors.service_description}</p>
                            )}
                        </div>

                        {/* Client Address */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Service Address *
                            </label>
                            <textarea
                                name="client_address"
                                value={formData.client_address}
                                onChange={handleInputChange}
                                rows={3}
                                placeholder="Enter the address where the service will be performed..."
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.client_address ? 'border-red-500' : 'border-gray-300'
                                }`}
                            />
                            {errors.client_address && (
                                <p className="text-red-500 text-sm mt-1">{errors.client_address}</p>
                            )}
                        </div>

                        {/* Booking Date */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Preferred Date *
                            </label>
                            <input
                                type="date"
                                name="booking_date"
                                value={formData.booking_date}
                                onChange={handleInputChange}
                                min={new Date().toISOString().split('T')[0]}
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.booking_date ? 'border-red-500' : 'border-gray-300'
                                }`}
                            />
                            {errors.booking_date && (
                                <p className="text-red-500 text-sm mt-1">{errors.booking_date}</p>
                            )}
                        </div>

                        {/* Emergency Checkbox */}
                        <div className="flex items-center">
                            <input
                                type="checkbox"
                                name="is_emergency"
                                checked={formData.is_emergency}
                                onChange={handleInputChange}
                                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label className="ml-2 block text-sm text-gray-700">
                                This is an emergency service request
                            </label>
                        </div>

                        {/* Emergency Notice */}
                        {formData.is_emergency && (
                            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div className="flex">
                                    <div className="text-red-400 mr-3">⚠️</div>
                                    <div>
                                        <h3 className="text-sm font-medium text-red-800">
                                            Emergency Service Request
                                        </h3>
                                        <p className="text-sm text-red-700 mt-1">
                                            Emergency requests are prioritized and may incur additional fees.
                                            We'll do our best to assign a serviceman quickly.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Form Actions */}
                        <div className="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <button
                                type="button"
                                onClick={handleClose}
                                className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                                disabled={loading}
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={loading}
                                className="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {loading ? 'Creating...' : 'Create Request'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
};

export default ServiceRequestModal;
