import React, { useState, useEffect } from 'react'
import { useSelector } from 'react-redux'
import { serviceRequestAPI, paymentAPI } from '../../services/api'

const BookingModal = ({ 
  isOpen, 
  onClose, 
  serviceman, 
  categoryId, 
  onSuccess 
}) => {
  const { user } = useSelector((state) => state.auth)
  const [loading, setLoading] = useState(false)
  const [step, setStep] = useState(1) // 1: Details, 2: Payment, 3: Confirmation
  const [bookingData, setBookingData] = useState({
    service_description: '',
    preferred_date: '',
    preferred_time: '',
    is_emergency: false,
    client_address: user?.address || '',
    client_phone: user?.phone_number || ''
  })

  // Calculate booking fee based on emergency status and date
  const calculateBookingFee = () => {
    if (!bookingData.preferred_date) return 0
    
    const bookingDate = new Date(bookingData.preferred_date)
    const today = new Date()
    const daysDifference = Math.ceil((bookingDate - today) / (1000 * 60 * 60 * 24))
    
    // Auto-detect emergency if within 2 days
    if (daysDifference <= 2 && !bookingData.is_emergency) {
      setBookingData(prev => ({ ...prev, is_emergency: true }))
    }
    
    return bookingData.is_emergency ? 5000 : 2000
  }

  const bookingFee = calculateBookingFee()

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target
    setBookingData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }))
  }

  const handleSubmitDetails = async (e) => {
    e.preventDefault()
    setLoading(true)
    
    try {
      // Create service request
      const serviceRequestData = {
        ...bookingData,
        category_id: parseInt(categoryId),
        serviceman_id: serviceman.id,
        preferred_date: new Date(bookingData.preferred_date + 'T' + bookingData.preferred_time).toISOString(),
        booking_fee: bookingFee
      }

      const response = await serviceRequestAPI.store(serviceRequestData)
      
      if (response.data) {
        setStep(2) // Move to payment step
        // Store request ID for payment
        setBookingData(prev => ({ ...prev, requestId: response.data.id }))
      }
    } catch (error) {
      console.error('Error creating service request:', error)
      alert('Failed to create service request. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  const handlePayment = async () => {
    setLoading(true)
    
    try {
      // Initialize payment with Paystack
      const paymentData = {
        amount: bookingFee * 100, // Convert to kobo
        email: user.email,
        service_request_id: bookingData.requestId,
        payment_type: 'booking_fee'
      }

      const response = await paymentAPI.initialize(paymentData)
      
      if (response.data.authorization_url) {
        // Redirect to Paystack
        window.location.href = response.data.authorization_url
      }
    } catch (error) {
      console.error('Error initializing payment:', error)
      alert('Failed to initialize payment. Please try again.')
    } finally {
      setLoading(false)
    }
  }

  const handleClose = () => {
    setStep(1)
    setBookingData({
      service_description: '',
      preferred_date: '',
      preferred_time: '',
      is_emergency: false,
      client_address: user?.address || '',
      client_phone: user?.phone_number || ''
    })
    onClose()
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div className="p-6">
          {/* Header */}
          <div className="flex justify-between items-center mb-6">
            <h3 className="text-lg font-semibold text-gray-900">
              {step === 1 && 'Book Service'}
              {step === 2 && 'Payment'}
              {step === 3 && 'Confirmation'}
            </h3>
            <button
              onClick={handleClose}
              className="text-gray-400 hover:text-gray-600"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          {/* Serviceman Info */}
          <div className="bg-gray-50 rounded-lg p-4 mb-6">
            <div className="flex items-center">
              <div className="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                <span className="text-gray-600 font-bold text-lg">
                  {serviceman.user?.first_name?.[0] || 'U'}
                </span>
              </div>
              <div className="ml-4">
                <h4 className="font-medium text-gray-900">
                  {serviceman.user?.first_name} {serviceman.user?.last_name}
                </h4>
                <p className="text-sm text-gray-500">
                  {serviceman.experience_years} years experience
                </p>
                {serviceman.hourly_rate && (
                  <p className="text-sm font-medium text-blue-600">
                    ₦{serviceman.hourly_rate.toLocaleString()}/hr
                  </p>
                )}
              </div>
            </div>
          </div>

          {/* Step 1: Service Details */}
          {step === 1 && (
            <form onSubmit={handleSubmitDetails} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Service Description *
                </label>
                <textarea
                  required
                  name="service_description"
                  value={bookingData.service_description}
                  onChange={handleInputChange}
                  rows={3}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Describe the service you need..."
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Preferred Date *
                  </label>
                  <input
                    required
                    type="date"
                    name="preferred_date"
                    value={bookingData.preferred_date}
                    onChange={handleInputChange}
                    min={new Date().toISOString().split('T')[0]}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Preferred Time *
                  </label>
                  <input
                    required
                    type="time"
                    name="preferred_time"
                    value={bookingData.preferred_time}
                    onChange={handleInputChange}
                    className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Service Address *
                </label>
                <input
                  required
                  type="text"
                  name="client_address"
                  value={bookingData.client_address}
                  onChange={handleInputChange}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter service location address"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Contact Phone *
                </label>
                <input
                  required
                  type="tel"
                  name="client_phone"
                  value={bookingData.client_phone}
                  onChange={handleInputChange}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Your phone number"
                />
              </div>

              <div className="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                <div className="flex items-center">
                  <input
                    type="checkbox"
                    name="is_emergency"
                    checked={bookingData.is_emergency}
                    onChange={handleInputChange}
                    className="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                  />
                  <label className="ml-2 block text-sm text-gray-900">
                    This is an emergency service
                  </label>
                </div>
                <span className="text-sm font-medium text-red-600">
                  {bookingData.is_emergency ? 'Emergency' : 'Normal'}
                </span>
              </div>

              {/* Booking Fee Display */}
              <div className="bg-blue-50 rounded-lg p-4">
                <div className="flex justify-between items-center">
                  <span className="text-sm font-medium text-gray-700">Booking Fee:</span>
                  <span className="text-lg font-bold text-blue-600">
                    ₦{bookingFee.toLocaleString()}
                  </span>
                </div>
                <p className="text-xs text-gray-500 mt-1">
                  {bookingData.is_emergency ? 'Emergency service fee' : 'Standard booking fee'}
                </p>
              </div>

              <div className="flex space-x-3 pt-4">
                <button
                  type="button"
                  onClick={handleClose}
                  className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={loading}
                  className="flex-1 px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {loading ? 'Creating...' : 'Continue to Payment'}
                </button>
              </div>
            </form>
          )}

          {/* Step 2: Payment */}
          {step === 2 && (
            <div className="space-y-4">
              <div className="text-center">
                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <span className="text-blue-600 text-2xl">💳</span>
                </div>
                <h4 className="text-lg font-medium text-gray-900 mb-2">Complete Your Payment</h4>
                <p className="text-sm text-gray-500 mb-4">
                  Pay the booking fee to confirm your service request
                </p>
              </div>

              <div className="bg-gray-50 rounded-lg p-4">
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Service:</span>
                    <span className="text-sm font-medium">{bookingData.service_description}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Date:</span>
                    <span className="text-sm font-medium">
                      {new Date(bookingData.preferred_date).toLocaleDateString()} at {bookingData.preferred_time}
                    </span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-gray-600">Booking Fee:</span>
                    <span className="text-sm font-medium">₦{bookingFee.toLocaleString()}</span>
                  </div>
                  <div className="border-t pt-2">
                    <div className="flex justify-between">
                      <span className="font-medium">Total:</span>
                      <span className="font-bold text-lg">₦{bookingFee.toLocaleString()}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div className="flex space-x-3">
                <button
                  type="button"
                  onClick={() => setStep(1)}
                  className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Back
                </button>
                <button
                  onClick={handlePayment}
                  disabled={loading}
                  className="flex-1 px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {loading ? 'Processing...' : 'Pay with Paystack'}
                </button>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default BookingModal
