<!DOCTYPE html>
<html>
<head>
    <title>Simple Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .form-group { margin: 10px 0; }
        input { padding: 8px; width: 200px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <h1>ServiceMan - Simple Login</h1>
    
    <form id="loginForm">
        <div class="form-group">
            <label>Email:</label><br>
            <input type="email" id="email" value="admin@serviceman.com" required>
        </div>
        
        <div class="form-group">
            <label>Password:</label><br>
            <input type="password" id="password" value="AdminPass123!" required>
        </div>
        
        <button type="submit">Login</button>
    </form>
    
    <div class="result" id="result">
        <p>Ready to test login...</p>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const result = document.getElementById('result');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            result.innerHTML = '<p>Attempting login...</p>';
            
            try {
                console.log('Sending login request...');
                
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        email: email, 
                        password: password 
                    })
                });
                
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);
                
                if (response.ok && data.access_token) {
                    localStorage.setItem('auth_token', data.access_token);
                    result.innerHTML = `
                        <p style="color: green;">✅ Login successful!</p>
                        <p>Token: ${data.access_token.substring(0, 20)}...</p>
                        <p>User: ${data.user.username} (${data.user.user_type})</p>
                        <p><a href="/simple-dashboard" style="background: green; color: white; padding: 10px; text-decoration: none;">Go to Simple Dashboard</a></p>
                    `;
                } else {
                    result.innerHTML = `
                        <p style="color: red;">❌ Login failed</p>
                        <p>Status: ${response.status}</p>
                        <p>Error: ${data.message || data.error || 'Unknown error'}</p>
                    `;
                }
                
            } catch (error) {
                console.error('Login error:', error);
                result.innerHTML = `<p style="color: red;">❌ Network error: ${error.message}</p>`;
            }
        });
    </script>
</body>
</html>
