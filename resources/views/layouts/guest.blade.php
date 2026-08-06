<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        @isset($title)
            {{ trim((string) $title) }} - {{ config('app.name', 'AZRA System') }}
        @else
            {{ config('app.name', 'AZRA System') }}
        @endisset
    </title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/hospital-clean.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ time() }}">
</head>
<body class="login-body">
    <div class="login-page-wrapper">

        <!-- Animated background blobs -->
        <div class="login-bg-blobs">
            <div class="login-blob login-blob-1"></div>
            <div class="login-blob login-blob-2"></div>
            <div class="login-blob login-blob-3"></div>
        </div>

        <!-- Centered Login Card -->
        <div class="login-card">
            <!-- Left Panel — Form -->
            <div class="login-left-panel">
                <div class="login-form-wrapper">
                    {{ $slot }}
                </div>
            </div>

            <!-- Right Panel — Visual -->
            <div class="login-right-panel">
                <div class="login-visual">
                    <div class="login-logo-circle">
                        <img src="{{ asset('images/azra-logo.png') }}" alt="AZRA Logo" class="azra-logo">
                    </div>
                    <h1 class="login-visual-title">SISTEM MONITORING ASURANSI</h1>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
