# API Configuration for Mobile App

## ✅ API Status

The API is **working correctly**. Tested endpoints:

```bash
# Public endpoint (works)
curl https://serviceman.sekimbi.com/api/categories
# Returns: {"data":[...], "success": true}
```

## 📍 API Base URL

**Production:** `https://serviceman.sekimbi.com/api`

**Important Notes:**
- `/api` alone returns 404 (expected - it's not an endpoint)
- Use specific endpoints like `/api/categories`, `/api/auth/login`, etc.
- The mobile app is already configured correctly

## 🔧 Mobile App Configuration

The mobile app (`resources/js/services/api.js`) automatically uses:

- **Web (Browser):** `/api` (relative URL)
- **Mobile (Capacitor):** `https://serviceman.sekimbi.com/api` (absolute URL)

This is handled automatically based on the platform.

## 🧪 Testing API Endpoints

### Public Endpoints (No Auth Required):
```bash
# Categories
GET https://serviceman.sekimbi.com/api/categories

# Category Details
GET https://serviceman.sekimbi.com/api/categories/{id}

# Servicemen by Category
GET https://serviceman.sekimbi.com/api/categories/{id}/servicemen

# Public Serviceman Profile
GET https://serviceman.sekimbi.com/api/servicemen/{userId}
```

### Protected Endpoints (Require Auth Token):
```bash
# Login
POST https://serviceman.sekimbi.com/api/auth/login
Body: {"email": "...", "password": "..."}

# Get Current User
GET https://serviceman.sekimbi.com/api/auth/me
Headers: Authorization: Bearer {token}

# Service Requests
GET https://serviceman.sekimbi.com/api/service-requests
Headers: Authorization: Bearer {token}
```

## 🔐 Authentication

The app uses **JWT (JSON Web Tokens)** for authentication:

1. Login returns `access_token`
2. Token is stored in `localStorage`
3. Token is sent as `Authorization: Bearer {token}` header

## 🐛 Troubleshooting

### "Not Found" Error on `/api`

**This is expected!** `/api` is not an endpoint. Use specific endpoints:
- ✅ `/api/categories`
- ✅ `/api/auth/login`
- ❌ `/api` (no route defined)

### API Not Connecting from Mobile

1. **Check API URL:**
   - File: `resources/js/services/api.js`
   - Ensure base URL is correct: `https://serviceman.sekimbi.com/api`

2. **Check CORS:**
   - File: `config/cors.php`
   - Should include `capacitor://localhost` (already configured)

3. **Test in Browser First:**
   ```bash
   curl -I https://serviceman.sekimbi.com/api/categories
   # Should return: HTTP/2 200
   ```

4. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Authentication Issues

1. **Token Not Being Sent:**
   - Check `localStorage.getItem('token')` in app
   - Verify token is stored after login

2. **Token Expired:**
   - Token expires after default JWT expiration time
   - User needs to login again

3. **CORS Issues:**
   - Ensure `config/cors.php` allows Capacitor origins
   - Already configured with `capacitor://localhost`

## 📝 Environment Variables

If you need to change the API URL, you can set it in:

1. **`.env` file:**
   ```env
   API_URL=https://serviceman.sekimbi.com
   ```

2. **Update `api.js`:**
   ```javascript
   const getApiBaseURL = () => {
       if (Capacitor.isNativePlatform()) {
           return import.meta.env.VITE_API_URL || 'https://serviceman.sekimbi.com/api';
       }
       return '/api';
   };
   ```

## ✅ Verification Checklist

- [x] API endpoint works: `https://serviceman.sekimbi.com/api/categories`
- [x] Mobile app configured with correct base URL
- [x] CORS allows Capacitor origins
- [x] Authentication uses JWT tokens
- [x] Token stored in localStorage
- [x] API interceptors handle errors

---

**The API is properly configured!** The mobile app will work once built and deployed.

