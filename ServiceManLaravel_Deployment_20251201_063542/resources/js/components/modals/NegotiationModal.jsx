import React, { useState, useEffect, useRef } from 'react'
import { useSelector, useDispatch } from 'react-redux'
import { negotiationAPI } from '../../services/api'

const NegotiationModal = ({ 
  isOpen, 
  onClose, 
  serviceRequest, 
  userRole 
}) => {
  const dispatch = useDispatch()
  const { user } = useSelector((state) => state.auth)
  const [negotiations, setNegotiations] = useState([])
  const [loading, setLoading] = useState(false)
  const [sending, setSending] = useState(false)
  const [newMessage, setNewMessage] = useState('')
  const [newPrice, setNewPrice] = useState('')
  const messagesEndRef = useRef(null)

  useEffect(() => {
    if (isOpen && serviceRequest) {
      loadNegotiations()
    }
  }, [isOpen, serviceRequest])

  useEffect(() => {
    scrollToBottom()
  }, [negotiations])

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }

  const loadNegotiations = async () => {
    try {
      setLoading(true)
      const response = await negotiationAPI.index()
      const requestNegotiations = response.data.filter(
        n => n.service_request_id === serviceRequest.id
      )
      setNegotiations(requestNegotiations)
    } catch (error) {
      console.error('Error loading negotiations:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleSendMessage = async (e) => {
    e.preventDefault()
    if (!newMessage.trim()) return

    try {
      setSending(true)
      const response = await negotiationAPI.store({
        service_request_id: serviceRequest.id,
        message: newMessage,
        type: 'message'
      })

      setNegotiations(prev => [...prev, response.data])
      setNewMessage('')
    } catch (error) {
      console.error('Error sending message:', error)
      alert('Failed to send message. Please try again.')
    } finally {
      setSending(false)
    }
  }

  const handleCounterOffer = async (e) => {
    e.preventDefault()
    if (!newPrice || isNaN(newPrice)) return

    try {
      setSending(true)
      const response = await negotiationAPI.counter(negotiations[0]?.id, {
        new_price: parseFloat(newPrice),
        message: `New price proposal: ₦${parseFloat(newPrice).toLocaleString()}`
      })

      setNegotiations(prev => [...prev, response.data])
      setNewPrice('')
    } catch (error) {
      console.error('Error making counter offer:', error)
      alert('Failed to make counter offer. Please try again.')
    } finally {
      setSending(false)
    }
  }

  const handleAccept = async () => {
    if (!confirm('Are you sure you want to accept this price?')) return

    try {
      setSending(true)
      await negotiationAPI.accept(negotiations[0]?.id)
      loadNegotiations() // Refresh to get updated status
    } catch (error) {
      console.error('Error accepting negotiation:', error)
      alert('Failed to accept negotiation. Please try again.')
    } finally {
      setSending(false)
    }
  }

  const handleReject = async () => {
    if (!confirm('Are you sure you want to reject this negotiation?')) return

    try {
      setSending(true)
      await negotiationAPI.reject(negotiations[0]?.id)
      loadNegotiations() // Refresh to get updated status
    } catch (error) {
      console.error('Error rejecting negotiation:', error)
      alert('Failed to reject negotiation. Please try again.')
    } finally {
      setSending(false)
    }
  }

  const formatTimestamp = (timestamp) => {
    return new Date(timestamp).toLocaleString()
  }

  const canNegotiate = () => {
    if (!negotiations.length) return true
    const latest = negotiations[negotiations.length - 1]
    return latest.status === 'pending' || latest.status === 'countered'
  }

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] flex flex-col">
        {/* Header */}
        <div className="p-6 border-b border-gray-200">
          <div className="flex justify-between items-center">
            <div>
              <h3 className="text-lg font-semibold text-gray-900">
                Negotiation - Request #{serviceRequest?.id}
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
        </div>

        {/* Messages */}
        <div className="flex-1 overflow-y-auto p-6">
          {loading ? (
            <div className="flex justify-center items-center h-32">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
          ) : negotiations.length === 0 ? (
            <div className="text-center text-gray-500 py-8">
              No negotiations yet. Start a conversation below.
            </div>
          ) : (
            <div className="space-y-4">
              {negotiations.map((negotiation) => (
                <div
                  key={negotiation.id}
                  className={`flex ${negotiation.user_id === user.id ? 'justify-end' : 'justify-start'}`}
                >
                  <div
                    className={`max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                      negotiation.user_id === user.id
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-200 text-gray-900'
                    }`}
                  >
                    <div className="text-sm">{negotiation.message}</div>
                    <div className={`text-xs mt-1 ${
                      negotiation.user_id === user.id ? 'text-blue-100' : 'text-gray-500'
                    }`}>
                      {formatTimestamp(negotiation.created_at)}
                    </div>
                    {negotiation.status && (
                      <div className={`text-xs mt-1 font-medium ${
                        negotiation.status === 'accepted' ? 'text-green-300' :
                        negotiation.status === 'rejected' ? 'text-red-300' :
                        'text-yellow-300'
                      }`}>
                        Status: {negotiation.status}
                      </div>
                    )}
                  </div>
                </div>
              ))}
              <div ref={messagesEndRef} />
            </div>
          )}
        </div>

        {/* Input Area */}
        <div className="p-6 border-t border-gray-200">
          {canNegotiate() && (
            <div className="space-y-4">
              {/* Price Counter Offer (for clients) */}
              {userRole === 'CLIENT' && (
                <form onSubmit={handleCounterOffer} className="flex space-x-2">
                  <input
                    type="number"
                    value={newPrice}
                    onChange={(e) => setNewPrice(e.target.value)}
                    placeholder="Enter your price offer"
                    className="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                  <button
                    type="submit"
                    disabled={sending || !newPrice}
                    className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {sending ? 'Sending...' : 'Counter'}
                  </button>
                </form>
              )}

              {/* Message Input */}
              <form onSubmit={handleSendMessage} className="flex space-x-2">
                <input
                  type="text"
                  value={newMessage}
                  onChange={(e) => setNewMessage(e.target.value)}
                  placeholder="Type a message..."
                  className="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                />
                <button
                  type="submit"
                  disabled={sending || !newMessage.trim()}
                  className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {sending ? 'Sending...' : 'Send'}
                </button>
              </form>

              {/* Action Buttons */}
              {negotiations.length > 0 && (
                <div className="flex space-x-2">
                  <button
                    onClick={handleAccept}
                    disabled={sending}
                    className="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Accept
                  </button>
                  <button
                    onClick={handleReject}
                    disabled={sending}
                    className="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Reject
                  </button>
                </div>
              )}
            </div>
          )}

          {!canNegotiate() && (
            <div className="text-center text-gray-500">
              This negotiation is closed.
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default NegotiationModal
