<?php
// profile.php
require_once 'config/database.php';
requireLogin();

 $message = '';
 $error = '';

// Get user data
 $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
 $stmt->execute([$_SESSION['user_id']]);
 $user = $stmt->fetch();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (md5($old_password) !== $user['password']) {
        $error = 'Password lama salah!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru tidak cocok!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([md5($new_password), $_SESSION['user_id']])) {
            $message = 'Password berhasil diubah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Labor Reservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #4caf50;
            --error-color: #f44336;
            --warning-color: #ff9800;
            --info-color: #2196f3;
            --text-color: #333;
            --text-light: #999;
            --border-color: #e0e0e0;
            --bg-color: #f5f7fa;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
            --hover-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: var(--bg-color);
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: white;
            border-right: 1px solid var(--border-color);
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            z-index: 100;
            animation: slideInLeft 0.5s ease-out;
        }
        
        @keyframes slideInLeft {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            margin-bottom: 10px;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .logo-text {
            font-weight: 600;
            color: var(--text-color);
            font-size: 18px;
        }
        
        .logo-subtitle {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 4px;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin: 5px 15px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #666;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transition: width 0.3s ease;
            z-index: -1;
        }
        
        .nav-link:hover::before {
            width: 100%;
        }
        
        .nav-link:hover {
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .nav-icon {
            margin-right: 10px;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }
        
        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 15px;
            right: 15px;
        }
        
        .logout-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--error-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .logout-link:hover {
            background: #fee;
            transform: translateX(5px);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
            animation: fadeIn 0.8s ease-in-out;
        }
        
        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }
        
        .header h1 {
            color: var(--text-color);
            margin-bottom: 5px;
            position: relative;
        }
        
        .header p {
            color: var(--text-light);
            font-size: 14px;
        }
        
        .profile-container {
            max-width: 800px;
        }
        
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .profile-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-5px);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
            margin-right: 20px;
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .avatar::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            transition: all 0.5s ease;
        }
        
        .avatar:hover::after {
            animation: shine 0.5s ease-in-out;
        }
        
        @keyframes shine {
            0% { transform: rotate(45deg) translateY(-100%); }
            100% { transform: rotate(45deg) translateY(100%); }
        }
        
        .avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
        }
        
        .profile-info h2 {
            color: var(--text-color);
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }
        
        .profile-info:hover h2 {
            color: var(--primary-color);
        }
        
        .role-badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .role-badge:hover {
            background: var(--info-color);
            color: white;
            transform: scale(1.05);
        }
        
        .info-grid {
            display: grid;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: var(--bg-color);
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .info-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .info-item:hover::before {
            transform: scaleY(1);
        }
        
        .info-item:hover {
            background: white;
            box-shadow: var(--card-shadow);
            transform: translateX(5px);
        }
        
        .info-icon {
            width: 45px;
            height: 45px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
            color: var(--primary-color);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .info-item:hover .info-icon {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            transform: rotate(10deg) scale(1.1);
        }
        
        .info-content label {
            display: block;
            color: var(--text-light);
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .info-content p {
            color: var(--text-color);
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: var(--text-color);
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: var(--text-light);
            transition: all 0.3s ease;
        }
        
        .toggle-password:hover {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.2);
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }
        
        .btn:active::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: var(--border-color);
            color: var(--text-color);
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            position: relative;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from { 
                opacity: 0;
                transform: translateY(-20px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }
        
        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }
        
        .alert-info {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
            border: 1px solid var(--info-color);
        }
        
        .alert-warning {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
            border: 1px solid var(--warning-color);
        }
        
        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .btn-primary.loading .loading-spinner {
            display: inline-block;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 5px;
            background: var(--border-color);
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .password-strength-weak {
            width: 33%;
            background-color: var(--error-color);
        }
        
        .password-strength-medium {
            width: 66%;
            background-color: var(--warning-color);
        }
        
        .password-strength-strong {
            width: 100%;
            background-color: var(--success-color);
        }
        
        .tooltip {
            position: relative;
        }
        
        .tooltip-text {
            visibility: hidden;
            width: 200px;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }
        
        .tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-flask"></i>
            </div>
            <div class="logo-text">Manager</div>
            <div class="logo-subtitle">Laboratorium</div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="profile.php" class="nav-link active">
                    <span class="nav-icon"><i class="fas fa-user"></i></span>
                    Profile
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
                    Status Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="labs.php" class="nav-link">
                    <span class="nav-icon"><i class="fas fa-th-large"></i></span>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="booking.php" class="nav-link">
                    <span class="nav-icon"><i class="fas fa-calendar-plus"></i></span>
                    Booking
                </a>
            </li>
            <li class="nav-item">
                <a href="history.php" class="nav-link">
                    <span class="nav-icon"><i class="fas fa-history"></i></span>
                    History
                </a>
            </li>
        </ul>
        
        <div class="logout-btn">
            <a href="logout.php" class="logout-link">
                <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                Logout
            </a>
        </div>
    </div>
    
    <div class="main-content">
        <div class="header">
            <h1>Profil</h1>
            <p>Kelola data akun dan ubah kata sandi</p>
        </div>
        
        <div class="profile-container">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-card">
                <div class="profile-header">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($user['nama_lengkap']) ?></h2>
                        <span class="role-badge"><?= ucfirst($user['role']) ?></span>
                        <p style="color: #999; font-size: 14px; margin-top: 5px;">
                            <i class="far fa-calendar"></i> Sejak <?= date('d M Y', strtotime($user['created_at'])) ?>
                        </p>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <label>ID / NIM</label>
                            <p><?= htmlspecialchars($user['nim']) ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-content">
                            <label>Nama Lengkap</label>
                            <p><?= htmlspecialchars($user['nama_lengkap']) ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <label>Alamat Email</label>
                            <p><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-card">
                <h3 style="color: var(--text-color); margin-bottom: 20px;">
                    <i class="fas fa-lock"></i> Ubah Kata Sandi
                </h3>
                <p style="color: var(--text-light); font-size: 14px; margin-bottom: 20px;">
                    Pastikan akun Anda aman dengan menggunakan kata sandi yang kuat dan sulit ditebak.
                </p>
                
                <form method="POST" id="passwordForm">
                    <div class="form-group">
                        <label>Kata Sandi Lama</label>
                        <div class="input-wrapper">
                            <input type="password" name="old_password" id="old_password" placeholder="Masukkan kata sandi lama" required>
                            <button type="button" class="toggle-password" data-target="old_password">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Kata Sandi Baru</label>
                        <div class="input-wrapper tooltip">
                            <input type="password" name="new_password" id="new_password" placeholder="Masukkan kata sandi baru" required>
                            <button type="button" class="toggle-password" data-target="new_password">
                                <i class="far fa-eye"></i>
                            </button>
                            <span class="tooltip-text">Gunakan kombinasi huruf besar, kecil, angka, dan simbol untuk keamanan maksimal</span>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmasi Kata Sandi Baru</label>
                        <div class="input-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="Konfirmasi kata sandi baru" required>
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="change_password" class="btn btn-primary" id="changePasswordBtn">
                            <i class="fas fa-sync-alt"></i> Perbarui Kata Sandi
                            <span class="loading-spinner"></span>
                        </button>
                        <button type="button" class="btn btn-secondary" id="cancelBtn">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const toggleButtons = document.querySelectorAll('.toggle-password');
            
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Password strength checker
            const newPasswordInput = document.getElementById('new_password');
            const passwordStrengthBar = document.getElementById('passwordStrengthBar');
            
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Check password strength
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[$@#&!]+/)) strength++;
                
                // Update strength bar
                passwordStrengthBar.className = 'password-strength-bar';
                
                if (password.length > 0) {
                    if (strength <= 2) {
                        passwordStrengthBar.classList.add('password-strength-weak');
                    } else if (strength <= 4) {
                        passwordStrengthBar.classList.add('password-strength-medium');
                    } else {
                        passwordStrengthBar.classList.add('password-strength-strong');
                    }
                }
            });
            
            // Form submission with loading state
            const passwordForm = document.getElementById('passwordForm');
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            
            passwordForm.addEventListener('submit', function(e) {
                // Validate passwords match
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    
                    // Create error alert
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-error';
                    errorAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i> Password baru tidak cocok!';
                    
                    // Insert at the beginning of the form
                    passwordForm.insertBefore(errorAlert, passwordForm.firstChild);
                    
                    // Remove after 5 seconds
                    setTimeout(() => {
                        errorAlert.remove();
                    }, 5000);
                    
                    return;
                }
                
                // Show loading state
                changePasswordBtn.classList.add('loading');
                changePasswordBtn.disabled = true;
            });
            
            // Cancel button
            const cancelBtn = document.getElementById('cancelBtn');
            
            cancelBtn.addEventListener('click', function() {
                passwordForm.reset();
                passwordStrengthBar.className = 'password-strength-bar';
            });
            
            // Add animation to profile cards on scroll
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'slideUp 0.6s ease-out';
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.profile-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>