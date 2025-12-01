import React, { useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { useSelector } from 'react-redux'
import BookingModal from '../modals/BookingModal'

export default function CategoryDetailPage() {
  const { id } = useParams()
  const { user, isAuthenticated } = useSelector((state) => state.auth)
  const [selectedServiceman, setSelectedServiceman] = useState(null)
  const [isBookingModalOpen, setIsBookingModalOpen] = useState(false)

  const { data: category, isLoading: categoryLoading, error: categoryError } = useQuery({
    queryKey: ['category', id],
    queryFn: async () => {
      const response = await axios.get(`/api/categories/${id}`)
      return response.data
    },
    enabled: !!id
  })

  const { data: servicemen = [], isLoading: servicemenLoading } = useQuery({
    queryKey: ['servicemen', id],
    queryFn: async () => {
      const response = await axios.get(`/api/categories/${id}/servicemen`)
      return response.data
    },
    enabled: !!id
  })

  const handleBookService = (serviceman) => {
    if (!isAuthenticated) {
      alert('Please login to book a service')
      return
    }
    setSelectedServiceman(serviceman)
    setIsBookingModalOpen(true)
  }

  const handleBookingSuccess = () => {
    setIsBookingModalOpen(false)
    setSelectedServiceman(null)
    // Optionally refresh the page or show success message
  }

  if (categoryLoading || servicemenLoading) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="animate-pulse">
          <div className="h-8 bg-gray-200 rounded w-1/3 mb-4"></div>
          <div className="h-4 bg-gray-200 rounded w-2/3 mb-8"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="bg-gray-200 rounded-lg h-48"></div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  if (categoryError) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-gray-900 mb-4">Category Not Found</h2>
          <p className="text-gray-600 mb-4">The category you're looking for doesn't exist.</p>
          <Link
            to="/categories"
            className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
          >
            Back to Categories
          </Link>
        </div>
      </div>
    )
  }

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Breadcrumb */}
      <nav className="flex mb-8" aria-label="Breadcrumb">
        <ol className="inline-flex items-center space-x-1 md:space-x-3">
          <li className="inline-flex items-center">
            <Link to="/" className="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
              <svg className="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
              </svg>
              Home
            </Link>
          </li>
          <li>
            <div className="flex items-center">
              <svg className="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd"></path>
              </svg>
              <Link to="/categories" className="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">
                Categories
              </Link>
            </div>
          </li>
          <li aria-current="page">
            <div className="flex items-center">
              <svg className="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd"></path>
              </svg>
              <span className="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                {category?.name}
              </span>
            </div>
          </li>
        </ol>
      </nav>

      {/* Category Header */}
      <div className="text-center mb-12">
        <div className="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <span className="text-blue-600 font-bold text-2xl">📋</span>
        </div>
        <h1 className="text-4xl font-bold text-gray-900 mb-4">
          {category?.name}
        </h1>
        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
          {category?.description}
        </p>
      </div>

      {/* Available Servicemen */}
      <div className="mb-8">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">
          Available Servicemen ({servicemen.length})
        </h2>
        
        {servicemen.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {servicemen.map((serviceman) => (
              <div
                key={serviceman.id}
                className="bg-white rounded-lg shadow-md hover:shadow-lg transition-all duration-200 p-6 border border-gray-200 hover:border-blue-300"
              >
                <div className="flex items-center mb-4">
                  <div className="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                    <span className="text-gray-600 font-bold text-lg">
                      {serviceman.user?.first_name?.[0] || 'U'}
                    </span>
                  </div>
                  <div className="ml-4">
                    <h3 className="text-lg font-semibold text-gray-900">
                      {serviceman.user?.first_name} {serviceman.user?.last_name}
                    </h3>
                    <p className="text-sm text-gray-500">
                      {serviceman.experience_years} years experience
                    </p>
                  </div>
                </div>
                
                {serviceman.bio && (
                  <p className="text-gray-600 text-sm leading-relaxed mb-4">
                    {serviceman.bio.length > 100 
                      ? `${serviceman.bio.substring(0, 100)}...` 
                      : serviceman.bio
                    }
                  </p>
                )}
                
                <div className="flex items-center justify-between">
                  <div className="text-sm text-gray-500">
                    {serviceman.is_available ? (
                      <span className="text-green-600 font-medium">Available</span>
                    ) : (
                      <span className="text-red-600 font-medium">Busy</span>
                    )}
                  </div>
                  {serviceman.hourly_rate && (
                    <div className="text-sm font-medium text-gray-900">
                      ₦{serviceman.hourly_rate.toLocaleString()}/hr
                    </div>
                  )}
                </div>
                
                <div className="flex space-x-2 mt-4">
                  <button className="flex-1 bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                    View Profile
                  </button>
                  {serviceman.is_available && (
                    <button 
                      onClick={() => handleBookService(serviceman)}
                      className="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors"
                    >
                      Book Service
                    </button>
                  )}
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="text-center py-12 bg-gray-50 rounded-lg">
            <div className="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-gray-400 text-2xl">🔧</span>
            </div>
            <h3 className="text-lg font-medium text-gray-900 mb-2">No servicemen available</h3>
            <p className="text-gray-600">There are currently no servicemen available in this category.</p>
          </div>
        )}
      </div>

      {/* Booking Modal */}
      <BookingModal
        isOpen={isBookingModalOpen}
        onClose={() => setIsBookingModalOpen(false)}
        serviceman={selectedServiceman}
        categoryId={id}
        onSuccess={handleBookingSuccess}
      />
    </div>
  )
}

