import React, { useState, useEffect } from 'react'
import { adminAPI, categoryAPI } from '../../services/api'

const AdminAssignmentModal = ({ 
  isOpen, 
  onClose, 
  serviceRequest, 
  onSuccess 
}) => {
  const [availableServicemen, setAvailableServicemen] = useState([])
  const [selectedServiceman, setSelectedServiceman] = useState('')
  const [selectedBackup, setSelectedBackup] = useState('')
  const [loading, setLoading] = useState(false)
  const [loadingServicemen, setLoadingServicemen] = useState(false)

  useEffect(() => {
    if (isOpen && serviceRequest) {
      loadAvailableServicemen()
    }
  }, [isOpen, serviceRequest])

  const loadAvailableServicemen = async () => {
    try {
      setLoadingServicemen(true)
      const response = await categoryAPI.getServicemenByCategory(serviceRequest.category_id)
      const servicemen = response.data.filter(s => s.is_available)
      setAvailableServicemen(servicemen)
    } catch (error) {
      console.error('Error loading servicemen:', error)
    } finally {
      setLoadingServicemen(false)
    }
  }

  const handleAssign = async (e) => {
    e.preventDefault()
    if (!selectedServiceman) {
      alert('Please select a serviceman')
      return
    }

    try {
      setLoading(true)
      await adminAPI.assignServiceman(serviceRequest.id, {
        serviceman_id: selectedServiceman,
        backup_serviceman_id: selectedBackup || null
      })

      alert('Serviceman assigned successfully!')
      onSuccess()
      onClose()
    } catch (error) {
      console.error('Error assigning serviceman:', error)
      alert('Failed to assign serviceman. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  const handleEmergencyToggle = async () => {
    try {
      await adminAPI.updateFinalCost(serviceRequest.id, {
        is_emergency: !serviceRequest.is_emergency
      })
      // Refresh the service request data
      onSuccess()
    } catch (error) {
      console.error('Error updating emergency status:', error)
      alert('Failed to update emergency status')
    }
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div className="p-6">
          {/* Header */}
          <div className="flex justify-between items-center mb-6">
            <div>
              <h3 className="text-lg font-semibold text-gray-900">
                Assign Serviceman - Request #{serviceRequest?.id}
              </h3>
              <p className="text-sm text-gray-500">
                {serviceRequest?.service_description}
              </p>
            </div>
            <button
              onClick={onClose}
              className="text-gray-400 hover:text-gray-600"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          {/* Service Request Details */}
          <div className="bg-gray-50 rounded-lg p-4 mb-6">
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span className="font-medium text-gray-700">Client:</span>
                <p className="text-gray-900">
                  {serviceRequest?.client?.first_name} {serviceRequest?.client?.last_name}
                </p>
              </div>
              <div>
                <span className="font-medium text-gray-700">Phone:</span>
                <p className="text-gray-900">{serviceRequest?.client_phone}</p>
              </div>
              <div>
                <span className="font-medium text-gray-700">Address:</span>
                <p className="text-gray-900">{serviceRequest?.client_address}</p>
              </div>
              <div>
                <span className="font-medium text-gray-700">Date:</span>
                <p className="text-gray-900">
                  {new Date(serviceRequest?.preferred_date).toLocaleDateString()} at {serviceRequest?.preferred_time}
                </p>
              </div>
              <div className="col-span-2">
                <span className="font-medium text-gray-700">Status:</span>
                <span className={`ml-2 px-2 py-1 rounded-full text-xs font-medium ${
                  serviceRequest?.status === 'PENDING_ADMIN_ASSIGNMENT' ? 'bg-yellow-100 text-yellow-800' :
                  serviceRequest?.status === 'ASSIGNED_TO_SERVICEMAN' ? 'bg-blue-100 text-blue-800' :
                  'bg-gray-100 text-gray-800'
                }`}>
                  {serviceRequest?.status?.replace('_', ' ')}
                </span>
              </div>
            </div>

            {/* Emergency Toggle */}
            <div className="mt-4 flex items-center justify-between p-3 bg-red-50 rounded-lg">
              <div>
                <span className="font-medium text-gray-700">Emergency Service:</span>
                <p className="text-sm text-gray-500">Mark this as an emergency service</p>
              </div>
              <label className="relative inline-flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  checked={serviceRequest?.is_emergency || false}
                  onChange={handleEmergencyToggle}
                  className="sr-only peer"
                />
                <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
              </label>
            </div>
          </div>

          {/* Assignment Form */}
          <form onSubmit={handleAssign} className="space-y-6">
            {/* Primary Serviceman Selection */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Select Primary Serviceman *
              </label>
              {loadingServicemen ? (
                <div className="flex items-center justify-center py-8">
                  <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
              ) : (
                <select
                  value={selectedServiceman}
                  onChange={(e) => setSelectedServiceman(e.target.value)}
                  required
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Choose a serviceman...</option>
                  {availableServicemen.map((serviceman) => (
                    <option key={serviceman.id} value={serviceman.id}>
                      {serviceman.user?.first_name} {serviceman.user?.last_name} 
                      ({serviceman.experience_years} years, ₦{serviceman.hourly_rate}/hr)
                    </option>
                  ))}
                </select>
              )}
              <p className="text-xs text-gray-500 mt-1">
                Available servicemen for this category: {availableServicemen.length}
              </p>
            </div>

            {/* Backup Serviceman Selection */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Select Backup Serviceman (Optional)
              </label>
              <select
                value={selectedBackup}
                onChange={(e) => setSelectedBackup(e.target.value)}
                className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">No backup serviceman</option>
                {availableServicemen
                  .filter(s => s.id !== selectedServiceman)
                  .map((serviceman) => (
                    <option key={serviceman.id} value={serviceman.id}>
                      {serviceman.user?.first_name} {serviceman.user?.last_name} 
                      ({serviceman.experience_years} years, ₦{serviceman.hourly_rate}/hr)
                    </option>
                  ))}
              </select>
              <p className="text-xs text-gray-500 mt-1">
                Backup serviceman will be notified if primary serviceman declines
              </p>
            </div>

            {/* Selected Serviceman Details */}
            {selectedServiceman && (
              <div className="bg-blue-50 rounded-lg p-4">
                <h4 className="font-medium text-gray-900 mb-2">Selected Serviceman Details</h4>
                {(() => {
                  const serviceman = availableServicemen.find(s => s.id === selectedServiceman)
                  return serviceman ? (
                    <div className="text-sm text-gray-700">
                      <p><strong>Name:</strong> {serviceman.user?.first_name} {serviceman.user?.last_name}</p>
                      <p><strong>Experience:</strong> {serviceman.experience_years} years</p>
                      <p><strong>Hourly Rate:</strong> ₦{serviceman.hourly_rate?.toLocaleString()}</p>
                      <p><strong>Skills:</strong> {serviceman.skills || 'Not specified'}</p>
                      {serviceman.bio && (
                        <p><strong>Bio:</strong> {serviceman.bio}</p>
                      )}
                    </div>
                  ) : null
                })()}
              </div>
            )}

            {/* Action Buttons */}
            <div className="flex space-x-3 pt-4">
              <button
                type="button"
                onClick={onClose}
                className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancel
              </button>
              <button
                type="submit"
                disabled={loading || !selectedServiceman}
                className="flex-1 px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {loading ? 'Assigning...' : 'Assign Serviceman'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}

export default AdminAssignmentModal
