import React from 'react'
import { Link } from 'react-router-dom'

export default function AboutPage() {
  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      {/* Hero Section */}
      <div className="text-center mb-16">
        <h1 className="text-4xl font-bold text-gray-900 mb-6">
          About ServiceMan
        </h1>
        <p className="text-xl text-gray-600 max-w-3xl mx-auto">
          Connecting skilled professionals with clients who need quality services. 
          We're building the future of service delivery in Nigeria.
        </p>
      </div>

      {/* Mission Section */}
      <div className="bg-blue-50 rounded-2xl p-8 mb-16">
        <div className="text-center mb-8">
          <h2 className="text-3xl font-bold text-gray-900 mb-4">Our Mission</h2>
          <p className="text-lg text-gray-700 max-w-2xl mx-auto">
            To create a seamless platform where skilled service providers can easily 
            connect with clients, delivering quality services while building trust 
            and transparency in every interaction.
          </p>
        </div>
      </div>

      {/* Features Grid */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
        <div className="text-center">
          <div className="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span className="text-blue-600 text-2xl">🔍</span>
          </div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">Easy Discovery</h3>
          <p className="text-gray-600">
            Find skilled professionals in your area with our comprehensive 
            category-based search system.
          </p>
        </div>

        <div className="text-center">
          <div className="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span className="text-green-600 text-2xl">⭐</span>
          </div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">Quality Assurance</h3>
          <p className="text-gray-600">
            All our servicemen are verified and rated by real clients, 
            ensuring you get the best quality service.
          </p>
        </div>

        <div className="text-center">
          <div className="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <span className="text-purple-600 text-2xl">💬</span>
          </div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">Direct Communication</h3>
          <p className="text-gray-600">
            Communicate directly with service providers, negotiate prices, 
            and discuss requirements in real-time.
          </p>
        </div>
      </div>

      {/* How It Works */}
      <div className="mb-16">
        <h2 className="text-3xl font-bold text-gray-900 text-center mb-12">How It Works</h2>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="text-center">
            <div className="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">
              1
            </div>
            <h3 className="text-lg font-semibold text-gray-900 mb-2">Browse Categories</h3>
            <p className="text-gray-600">
              Explore our wide range of service categories from electrical work 
              to cleaning services.
            </p>
          </div>

          <div className="text-center">
            <div className="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">
              2
            </div>
            <h3 className="text-lg font-semibold text-gray-900 mb-2">Choose Your Serviceman</h3>
            <p className="text-gray-600">
              Review profiles, ratings, and portfolios to select the perfect 
              professional for your needs.
            </p>
          </div>

          <div className="text-center">
            <div className="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">
              3
            </div>
            <h3 className="text-lg font-semibold text-gray-900 mb-2">Get Your Service Done</h3>
            <p className="text-gray-600">
              Book, communicate, and pay securely through our platform. 
              Rate and review after completion.
            </p>
          </div>
        </div>
      </div>

      {/* Categories */}
      <div className="mb-16">
        <h2 className="text-3xl font-bold text-gray-900 text-center mb-12">Our Service Categories</h2>
        
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
          {[
            { name: 'Electrical', icon: '⚡' },
            { name: 'Plumbing', icon: '🔧' },
            { name: 'HVAC', icon: '🌡️' },
            { name: 'Carpentry', icon: '🔨' },
            { name: 'Painting', icon: '🎨' },
            { name: 'Cleaning', icon: '🧽' },
            { name: 'Appliance Repair', icon: '🔌' },
            { name: 'Gardening', icon: '🌱' }
          ].map((category, index) => (
            <div key={index} className="text-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
              <div className="text-2xl mb-2">{category.icon}</div>
              <div className="font-medium text-gray-900">{category.name}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Stats */}
      <div className="bg-gray-900 rounded-2xl p-8 mb-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-center text-white">
          <div>
            <div className="text-3xl font-bold mb-2">500+</div>
            <div className="text-gray-300">Skilled Professionals</div>
          </div>
          <div>
            <div className="text-3xl font-bold mb-2">1,000+</div>
            <div className="text-gray-300">Happy Clients</div>
          </div>
          <div>
            <div className="text-3xl font-bold mb-2">8</div>
            <div className="text-gray-300">Service Categories</div>
          </div>
        </div>
      </div>

      {/* CTA Section */}
      <div className="text-center">
        <h2 className="text-3xl font-bold text-gray-900 mb-4">Ready to Get Started?</h2>
        <p className="text-lg text-gray-600 mb-8">
          Join thousands of satisfied clients and skilled professionals on our platform.
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link
            to="/register"
            className="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium"
          >
            Sign Up as Client
          </Link>
          <Link
            to="/register?type=serviceman"
            className="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors font-medium"
          >
            Join as Serviceman
          </Link>
        </div>
      </div>
    </div>
  )
}

