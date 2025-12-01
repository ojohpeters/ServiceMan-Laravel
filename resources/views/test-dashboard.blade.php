<!DOCTYPE html>
<html>
<head>
    <title>Test Dashboard</title>
</head>
<body>
    <h1>Simple Dashboard Test</h1>
    <p>If you can see this page, the dashboard route is working!</p>
    
    <div id="userInfo"></div>
    <button onclick="loadUserInfo()">Load User Info</button>
    
    <script>
        async function loadUserInfo() {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                document.getElementById('userInfo').innerHTML = 'No token found';
                return;
            }
            
            try {
                const response = await fetch('/api/auth/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                document.getElementById('userInfo').innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
            } catch (error) {
                document.getElementById('userInfo').innerHTML = `Error: ${error.message}`;
            }
        }
    </script>
</body>
</html>
