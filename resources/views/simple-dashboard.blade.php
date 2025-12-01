<!DOCTYPE html>
<html>
<head>
    <title>Simple Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { border-bottom: 1px solid #dee2e6; padding-bottom: 20px; margin-bottom: 20px; }
        .user-info { background: #e9ecef; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .success { color: green; }
        .error { color: red; }
        button { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 ServiceMan Dashboard</h1>
            <p>Welcome to your dashboard!</p>
        </div>
        
        <div id="status">
            <p>Loading user information...</p>
        </div>
        
        <div class="user-info" id="userInfo" style="display: none;">
            <h3>User Information</h3>
            <div id="userDetails"></div>
        </div>
        
        <div>
            <button onclick="loadUserInfo()">Refresh User Info</button>
            <button onclick="testApi()">Test API</button>
            <button onclick="logout()">Logout</button>
        </div>
        
        <div id="apiResult" style="margin-top: 20px;"></div>
    </div>

    <script>
        async function loadUserInfo() {
            const status = document.getElementById('status');
            const userInfo = document.getElementById('userInfo');
            const userDetails = document.getElementById('userDetails');
            
            const token = localStorage.getItem('auth_token');
            
            if (!token) {
                status.innerHTML = '<p class="error">❌ No authentication token found. Please login first.</p>';
                return;
            }
            
            try {
                status.innerHTML = '<p>Loading user information...</p>';
                
                const response = await fetch('/api/auth/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const user = await response.json();
                    status.innerHTML = '<p class="success">✅ Successfully loaded user information!</p>';
                    userInfo.style.display = 'block';
                    userDetails.innerHTML = `
                        <p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>
                        <p><strong>Username:</strong> ${user.username}</p>
                        <p><strong>Email:</strong> ${user.email}</p>
                        <p><strong>User Type:</strong> ${user.user_type}</p>
                        <p><strong>Email Verified:</strong> ${user.is_email_verified ? '✅ Yes' : '❌ No'}</p>
                        <p><strong>Created:</strong> ${new Date(user.created_at).toLocaleString()}</p>
                    `;
                } else {
                    const error = await response.json();
                    status.innerHTML = `<p class="error">❌ Failed to load user info: ${error.message || 'Unknown error'}</p>`;
                }
                
            } catch (error) {
                status.innerHTML = `<p class="error">❌ Network error: ${error.message}</p>`;
            }
        }
        
        async function testApi() {
            const result = document.getElementById('apiResult');
            const token = localStorage.getItem('auth_token');
            
            if (!token) {
                result.innerHTML = '<p class="error">No token available</p>';
                return;
            }
            
            try {
                const response = await fetch('/api/categories', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    result.innerHTML = `<p class="success">✅ API Test successful! Found ${data.data ? data.data.length : 0} categories.</p>`;
                } else {
                    result.innerHTML = `<p class="error">❌ API Test failed: ${response.status}</p>`;
                }
            } catch (error) {
                result.innerHTML = `<p class="error">❌ API Test error: ${error.message}</p>`;
            }
        }
        
        function logout() {
            localStorage.removeItem('auth_token');
            window.location.href = '/simple-login';
        }
        
        // Auto-load user info on page load
        window.addEventListener('load', loadUserInfo);
    </script>
</body>
</html>
