<?php
// login.php - Halaman Login
require_once 'config/database.php';

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim']);
    $password = $_POST['password'];
    
    if (!empty($nim) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nim = ?");
        $stmt->execute([$nim]);
        $user = $stmt->fetch();
        
        if ($user && md5($password) === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['nim'] = $user['nim'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: index.php");
            exit();
        } else {
            $error = 'NIM atau password salah!';
        }
    } else {
        $error = 'Semua field harus diisi!';
    }
}

// Redirect jika sudah login
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Labor Reservation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .logo-icon svg {
            width: 40px;
            height: 40px;
            stroke: white;
            fill: none;
        }

        .logo-circle img {
            width: 130px;
            height:130px;
        }
        
        h1 {
            text-align: center;
            color: #1a1a1a;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .subtitle {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 35px;
        }
        
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-label {
            display: flex;
            align-items: center;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .label-icon {
            width: 16px;
            height: 16px;
            margin-right: 6px;
        }
        
        .form-input {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            color: #1f2937;
            transition: all 0.3s ease;
            background: #f9fafb;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            color: #9ca3af;
            font-size: 14px;
        }
        
        .register-link {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        
        .register-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .help-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 56px;
            height: 56px;
            background: #1f2937;
            color: white;
            border-radius: 50%;
            border: none;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .help-button:hover {
            transform: scale(1.1);
            background: #374151;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }
            
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">

                <div class="logo-circle"><img src="logo unp.png" alt=""></div>

        </div>
        
        <h1>Login Mahasiswa</h1>
        <p class="subtitle">Labor Vokasi Universitas Negeri Padang</p>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">
                    <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Nomor Induk Mahasiswa (NIM)
                </label>
                <input 
                    type="text" 
                    name="nim" 
                    class="form-input" 
                    placeholder="Masukkan NIM Anda"
                    value="<?= isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : '' ?>"
                    required
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Password
                </label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="Masukkan password"
                    required
                >
            </div>
            
            <button type="submit" class="btn-login">
                Masuk
            </button>
        </form>
        
        <div class="divider">atau</div>
        
        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar di sini</a>
        </div>
    </div>
    
    <button class="help-button" onclick="alert('Hubungi admin untuk bantuan:\nEmail: admin@lab.com\nTelp: 0812-3456-7890')">
        ?
    </button>
</body>
</html>