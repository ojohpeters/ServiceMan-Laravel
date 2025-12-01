<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Simple Login Test</h1>
    <form id="loginForm">
        <input type="email" id="email" value="admin@serviceman.com" placeholder="Email">
        <input type="password" id="password" value="AdminPass123!" placeholder="Password">
        <button type="submit">Login</button>
    </form>
    <div id="result"></div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const result = document.getElementById('result');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            result.innerHTML = 'Logging in...<br>';
            
            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                result.innerHTML += `Status: ${response.status}<br>`;
                result.innerHTML += `Response: ${JSON.stringify(data, null, 2)}<br>`;
                
                if (response.ok && data.access_token) {
                    localStorage.setItem('auth_token', data.access_token);
                    result.innerHTML += '<br>✅ Login successful!<br>';
                    result.innerHTML += '<a href="/dashboard">Go to Dashboard</a>';
                }
                
            } catch (error) {
                result.innerHTML += `Error: ${error.message}<br>`;
            }
        });
    </script>
</body>
</html>
