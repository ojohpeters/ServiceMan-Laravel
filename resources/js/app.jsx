import React from 'react'
import { createRoot } from 'react-dom/client'
import { Provider } from 'react-redux'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { BrowserRouter, HashRouter } from 'react-router-dom'
import { Capacitor } from '@capacitor/core'
import { store } from './store'
import App from './components/App'
import './bootstrap'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
      staleTime: 5 * 60 * 1000, // 5 minutes
    },
  },
})

// Use HashRouter for Capacitor (mobile app), BrowserRouter for web
const Router = Capacitor.isNativePlatform() ? HashRouter : BrowserRouter

// Check if we're in a Laravel view context or Capacitor
const appElement = document.getElementById('app')

if (appElement) {
  const root = createRoot(appElement)
  root.render(
    <Provider store={store}>
      <QueryClientProvider client={queryClient}>
        <Router>
          <App />
        </Router>
      </QueryClientProvider>
    </Provider>
  )
}
