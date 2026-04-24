<?php
// RentDress - Entry Point
// Redirect to dist folder (Vue build output) or frontend dev server

// If dist folder exists (production), serve from there
if (file_exists(__DIR__ . '/dist/index.html')) {
    header('Location: /RentDress/dist/');
    exit;
}

// Otherwise show development message
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentDress - Development</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Prompt', sans-serif;
        }
        .card {
            background: #fff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .gold { color: #D4AF37; }
        .btn-gold {
            background: linear-gradient(135deg, #D4AF37, #B8962D);
            border: none;
            color: #1a1a1a;
            font-weight: 600;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="card p-5 text-center" style="max-width: 500px;">
        <h1 class="gold mb-3">RentDress</h1>
        <h5 class="mb-4">Development Mode</h5>

        <div class="mb-4">
            <p class="mb-2">Frontend (Vue.js)</p>
            <a href="http://localhost:5173" class="btn btn-gold btn-lg" target="_blank">
                Open Dev Server (port 5173)
            </a>
        </div>

        <hr>

        <div class="mb-3">
            <p class="text-muted small mb-2">API Endpoints</p>
            <code class="d-block mb-1">/RentDress/api/products/read.php</code>
            <code class="d-block mb-1">/RentDress/api/users/login.php</code>
            <code class="d-block">/RentDress/api/bookings/read.php</code>
        </div>

        <hr>

        <div class="text-muted small">
            <p class="mb-1">To build for production:</p>
            <code>cd frontend && npm run build</code>
        </div>
    </div>
</body>
</html>
