<?php
// register.php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lengkap']);
    $nim = trim($_POST['nim']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($nama) || empty($nim) || empty($email) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Check if NIM or email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nim = ? OR email = ?");
        $stmt->execute([$nim, $email]);
        
        if ($stmt->fetch()) {
            $error = 'NIM atau Email sudah terdaftar!';
        } else {
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, nim, email, password, role) VALUES (?, ?, ?, ?, 'mahasiswa')");
            if ($stmt->execute([$nama, $nim, $email, md5($password)])) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}

if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Labor Reservation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .icon-wrapper svg {
            width: 40px;
            height: 40px;
            color: white;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #333;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .input-icon {
            margin-right: 5px;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #f093fb;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .footer-text a {
            color: #f5576c;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer-text a:hover {
            text-decoration: underline;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #fcc;
        }
        
        .success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #cfc;
        }
        
        .help-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #333;
            color: white;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-wrapper">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
        </div>
        
        <h1>Daftar Akun</h1>
        <p class="subtitle">Buat akun baru untuk meminjam laboratorium</p>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>
                    <span class="input-icon">👤</span>
                    Nama Lengkap
                </label>
                <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
            </div>
            
            <div class="form-group">
                <label>
                    <span class="input-icon">🎓</span>
                    Nomor Induk Mahasiswa (NIM)
                </label>
                <input type="text" name="nim" placeholder="Masukkan NIM" required>
            </div>
            
            <div class="form-group">
                <label>
                    <span class="input-icon">📧</span>
                    Email
                </label>
                <input type="email" name="email" placeholder="Masukkan email" required>
            </div>
            
            <div class="form-group">
                <label>
                    <span class="input-icon">🔒</span>
                    Password
                </label>
                <input type="password" name="password" placeholder="Buat password" required>
            </div>
            
            <div class="form-group">
                <label>
                    <span class="input-icon">🔒</span>
                    Konfirmasi Password
                </label>
                <input type="password" name="confirm_password" placeholder="Konfirmasi password" required>
            </div>
            
            <button type="submit" class="btn">Daftar</button>
        </form>
        
        <div class="footer-text">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>
    
    <button class="help-btn">?</button>
</body>
</html>