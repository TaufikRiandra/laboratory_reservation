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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f7fa;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: white;
            border-right: 1px solid #e0e0e0;
            padding: 20px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .logo-text {
            font-weight: 600;
            color: #333;
        }
        
        .logo-subtitle {
            font-size: 12px;
            color: #999;
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
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            background: #f5f7fa;
            color: #667eea;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .nav-icon {
            margin-right: 10px;
            font-size: 18px;
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
            color: #e74c3c;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .logout-link:hover {
            background: #fee;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #999;
            font-size: 14px;
        }
        
        .profile-container {
            max-width: 800px;
        }
        
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            margin-right: 20px;
        }
        
        .profile-info h2 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e3f2fd;
            color: #2196f3;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .info-grid {
            display: grid;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f5f7fa;
            border-radius: 8px;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .info-content label {
            display: block;
            color: #999;
            font-size: 12px;
            margin-bottom: 3px;
        }
        
        .info-content p {
            color: #333;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #333;
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
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
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
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #666;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #4caf50;
            border: 1px solid #4caf50;
        }
        
        .alert-error {
            background: #ffebee;
            color: #f44336;
            border: 1px solid #f44336;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">⚡</div>
            <div class="logo-text">Manager</div>
            <div class="logo-subtitle">Laboratorium</div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="profile.php" class="nav-link active">
                    <span class="nav-icon">👤</span>
                    Profile
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    Status Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="labs.php" class="nav-link">
                    <span class="nav-icon">📋</span>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="booking.php" class="nav-link">
                    <span class="nav-icon">📝</span>
                    Booking
                </a>
            </li>
            <li class="nav-item">
                <a href="history.php" class="nav-link">
                    <span class="nav-icon">📜</span>
                    History
                </a>
            </li>
        </ul>
        
        <div class="logout-btn">
            <a href="logout.php" class="logout-link">
                <span class="nav-icon">🚪</span>
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
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="profile-card">
                <div class="profile-header">
                    <div class="avatar">👤</div>
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($user['nama_lengkap']) ?></h2>
                        <span class="role-badge"><?= ucfirst($user['role']) ?></span>
                        <p style="color: #999; font-size: 14px; margin-top: 5px;">
                            Sejak <?= date('d M Y', strtotime($user['created_at'])) ?>
                        </p>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon">📱</div>
                        <div class="info-content">
                            <label>ID / NIM</label>
                            <p><?= htmlspecialchars($user['nim']) ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">👤</div>
                        <div class="info-content">
                            <label>Nama Lengkap</label>
                            <p><?= htmlspecialchars($user['nama_lengkap']) ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">📧</div>
                        <div class="info-content">
                            <label>Alamat Email</label>
                            <p><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="profile-card">
                <h3 style="color: #333; margin-bottom: 20px;">🔐 Ubah Kata Sandi</h3>
                <p style="color: #999; font-size: 14px; margin-bottom: 20px;">
                    Pastikan akun Anda aman dengan menggunakan kata sandi yang kuat dan sulit ditebak.
                </p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Kata Sandi Lama</label>
                        <div class="input-wrapper">
                            <input type="password" name="old_password" placeholder="Masukkan kata sandi lama" required>
                            <button type="button" class="toggle-password">👁</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Kata Sandi Baru</label>
                        <div class="input-wrapper">
                            <input type="password" name="new_password" placeholder="Masukkan kata sandi baru" required>
                            <button type="button" class="toggle-password">👁</button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmasi Kata Sandi Baru</label>
                        <div class="input-wrapper">
                            <input type="password" name="confirm_password" placeholder="Konfirmasi kata sandi baru" required>
                            <button type="button" class="toggle-password">👁</button>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            Perbarui Kata Sandi
                        </button>
                        <button type="button" class="btn btn-secondary">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>