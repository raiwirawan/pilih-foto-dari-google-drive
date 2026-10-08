<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Studio</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f0f0f1; }
        .login-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 320px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: .5rem; }
        input[type="text"], input[type="password"] { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: .75rem; background: #2271b1; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #135e96; }
        .error { color: #d63638; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login Studio</h2>
        <div id="error-msg" class="error"></div>
        <form id="login-form">
            <div class="form-group">
                <label>Username</label>
                <input type="text" id="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const u = document.getElementById('username').value;
            const p = document.getElementById('password').value;
            try {
                const res = await fetch('<?php echo home_url('/'.PF_BASE_SLUG.'/api/login'); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-PF-CSRF': '1' },
                    body: JSON.stringify({username: u, password: p})
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    document.getElementById('error-msg').innerText = data.error || 'Login gagal';
                }
            } catch (err) {
                document.getElementById('error-msg').innerText = 'Terjadi kesalahan jaringan';
            }
        });
    </script>
</body>
</html>
