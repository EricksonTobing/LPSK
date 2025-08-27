<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/csrf.php';
require_once __DIR__ . '/../inc/helpers.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $ok = login(trim($_POST['username'] ?? ''), (string)($_POST['password'] ?? ''));
    if ($ok) redirect('dashboard.php');
    $error = 'Username atau password salah';
}
$title = 'Login - MEDAN MELINDUNGI';
require __DIR__ . '/../inc/layout_header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MEDAN MELINDUNGI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-red: #C6100D;
            --primary-blue: #241E4E;
            --accent-red: #E53E3E;
            --accent-blue: #3182CE;
            --light-bg: #F7FAFC;
            --dark-bg: #1A202C;
            --text-light: #2D3748;
            --text-dark: #E2E8F0;
        }
        
        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .login-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: flex;
            flex-direction: column;
        }
        
        @media (min-width: 768px) {
            .login-card {
                flex-direction: row;
                min-height: 550px;
            }
        }
        
        .login-hero {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-red) 100%);
            color: white;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        @media (min-width: 768px) {
            .login-hero {
                width: 45%;
            }
        }
        
        .login-hero::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .login-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .hero-logo {
            background: rgba(255, 255, 255, 0.15);
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .hero-logo img {
            width: 80px;
            height: auto;
        }
        
        .hero-title {
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
            z-index: 10;
        }
        
        .hero-subtitle {
            opacity: 0.9;
            margin-bottom: 2rem;
            position: relative;
            z-index: 10;
        }
        
        .hero-features {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
            position: relative;
            z-index: 10;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .feature-icon {
            background: white;
            color: var(--primary-blue);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .feature-text {
            font-size: 0.875rem;
        }
        
        .login-form-section {
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        @media (min-width: 768px) {
            .login-form-section {
                width: 55%;
            }
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }
        
        .form-subtitle {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .form-subtitle a {
            color: var(--primary-red);
            text-decoration: none;
            font-weight: 500;
        }
        
        .form-subtitle a:hover {
            text-decoration: underline;
        }
        
        .error-alert {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .error-icon {
            flex-shrink: 0;
        }
        
        .input-group {
            margin-bottom: 1.25rem;
        }
        
        .input-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(36, 30, 78, 0.1);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .remember-checkbox {
            width: 1rem;
            height: 1rem;
            border-radius: 0.25rem;
            border: 1px solid #cbd5e1;
            accent-color: var(--primary-blue);
        }
        
        .forgot-password {
            color: var(--primary-red);
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .login-button {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-red) 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .login-button:active {
            transform: translateY(0);
        }
        
        .copyright {
            text-align: center;
            margin-top: 2rem;
            color: #64748b;
            font-size: 0.75rem;
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .login-card {
                background: #1e293b;
            }
            
            .form-title {
                color: white;
            }
            
            .form-subtitle {
                color: #cbd5e1;
            }
            
            .input-label {
                color: #e2e8f0;
            }
            
            .form-input {
                background: #334155;
                border-color: #475569;
                color: white;
            }
            
            .form-input:focus {
                border-color: var(--primary-blue);
                box-shadow: 0 0 0 3px rgba(36, 30, 78, 0.3);
            }
            
            .input-icon {
                color: #94a3b8;
            }
            
            .error-alert {
                background-color: #7f1d1d;
                border-color: #b91c1c;
                color: #fecaca;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-content">
        <div class="login-card">
            <!-- Hero Section -->
            <div class="login-hero">
                <div class="hero-logo">
                    <img src="../inc/img/logo.png" alt="MEDAN MELINDUNGI Logo">
                </div>
                <h1 class="hero-title">MEDAN MELINDUNGI</h1>
                <p class="hero-subtitle">Sistem Informasi Terpadu untuk Layanan Publik</p>
                
                <div class="hero-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <p class="feature-text">Keamanan data terjamin dengan sistem enkripsi terbaru</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <p class="feature-text">Akses cepat dan responsif dari berbagai perangkat</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <p class="feature-text">Privasi dan perlindungan data pengguna terjamin</p>
                    </div>
                </div>
            </div>
            
            <!-- Login Form Section -->
            <div class="login-form-section">
                <div class="form-header">
                    <h2 class="form-title">Masuk ke Akun Anda</h2>
                    <p class="form-subtitle">
                        Atau <a href="#">hubungi administrator</a> untuk bantuan
                    </p>
                </div>
                
                <form method="post">
                    <?= csrf_field() ?>
                    <?php if ($error): ?>
                        <div class="error-alert">
                            <div class="error-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div><?= e($error) ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="input-group">
                        <label for="username" class="input-label">Username</label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <input id="username" name="username" type="text" autocomplete="username" required class="form-input" placeholder="Masukkan username">
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="password" class="input-label">Password</label>
                        <div class="input-wrapper">
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input" placeholder="Masukkan password">
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input id="remember" name="remember" type="checkbox" class="remember-checkbox">
                            <span>Ingat saya</span>
                        </label>
                        
                        <a href="#" class="forgot-password">Lupa password?</a>
                    </div>
                    
                    <button type="submit" class="login-button">
                        <i class="fas fa-sign-in-alt"></i>
                        Masuk
                    </button>
                </form>
                
                <div class="copyright">
                    © <?= date('Y') ?> MEDAN MELINDUNGI. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php require __DIR__ . '/../inc/layout_footer.php'; ?>