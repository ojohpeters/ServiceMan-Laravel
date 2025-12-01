import React, { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import { getCurrentUser } from '../../store/slices/authSlice'
import Header from './Header'
import Footer from './Footer'

export default function Layout({ children }) {
  const dispatch = useDispatch()
  const { user, isLoading } = useSelector((state) => state.auth)

  useEffect(() => {
    // Initialize auth state on app load
    const token = localStorage.getItem('auth_token')
    if (token && !user) {
      dispatch(getCurrentUser())
    }
  }, [dispatch, user])

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1">
        {children}
      </main>
      <Footer />
    </div>
  )
}
