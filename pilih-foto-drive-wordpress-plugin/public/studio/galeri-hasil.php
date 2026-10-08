<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Edit - Studio</title>
</head>
<body>
    <h1>Hasil Edit Klien</h1>
    <button onclick="logout()">Logout</button>
    <div id="app">Membangun UI hasil baru...</div>
    <script>
        async function logout() {
            await fetch('<?php echo home_url('/'.PF_BASE_SLUG.'/api/logout'); ?>', { method: 'POST', headers: { 'X-PF-CSRF': '1' } });
            window.location.reload();
        }
    </script>
</body>
</html>
