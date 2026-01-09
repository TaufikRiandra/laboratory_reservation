<?php
// booking.php
require_once 'config/database.php';
requireLogin();

$message = '';
$error = '';
$selected_lab = null;

// Get lab if lab_id is provided
if (isset($_GET['lab_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM labs WHERE id = ? AND status = 'tersedia'");
    $stmt->execute([$_GET['lab_id']]);
    $selected_lab = $stmt->fetch();
}

// Get all available labs
$stmt = $pdo->query("SELECT * FROM labs WHERE status = 'tersedia' ORDER BY nama_lab ASC");
$available_labs = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_id = $_POST['lab_id'];
    $nim = $_POST['nim'];
    $nama = $_POST['nama_lengkap'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $waktu_mulai = $_POST['waktu_mulai'] ?? '08:00';
    $waktu_selesai = $_POST['waktu_selesai'] ?? '16:00';
    $jumlah_komputer = $_POST['jumlah_komputer'] ?? 0;
    $keperluan = $_POST['keperluan'];
    $alasan = $_POST['alasan'];
    
    if (empty($lab_id) || empty($nim) || empty($nama) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
        $error = 'Semua field wajib diisi!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, lab_id, nim, nama_lengkap, tanggal_mulai, tanggal_selesai, waktu_mulai, waktu_selesai, jumlah_komputer, keperluan, alasan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu')");
        
        if ($stmt->execute([$_SESSION['user_id'], $lab_id, $nim, $nama, $tanggal_mulai, $tanggal_selesai, $waktu_mulai, $waktu_selesai, $jumlah_komputer, $keperluan, $alasan])) {
            $message = 'Peminjaman berhasil diajukan! Menunggu persetujuan.';
        } else {
            $error = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman - Labor Reservation</title>
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
            overflow-y: auto;
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
        
        .booking-container {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 25px;
        }
        
        .preview-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            height: fit-content;
        }
        
        .lab-preview-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 72px;
            margin-bottom: 15px;
        }
        
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .section-title {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .lab-selection {
            display: grid;
            gap: 10px;
        }
        
        .lab-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .lab-option:hover {
            border-color: #667eea;
            background: #f5f7fa;
        }
        
        .lab-option input {
            margin-right: 15px;
        }
        
        .lab-option-info h4 {
            color: #333;
            margin-bottom: 3px;
        }
        
        .lab-option-info p {
            color: #999;
            font-size: 13px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
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
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            color: #1976d2;
            font-size: 13px;
            line-height: 1.6;
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
                <a href="profile.php" class="nav-link">
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
                <a href="booking.php" class="nav-link active">
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
            <h1>Form Peminjaman Labor</h1>
            <p>Isi formulir untuk meminjam ruangan laboratorium</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="booking-container">
            <div class="preview-card">
                <h3 style="color: #333; margin-bottom: 15px;">Preview Ruangan</h3>
                <div class="lab-preview-image">🏢</div>
                <?php if ($selected_lab): ?>
                    <h4 style="color: #333; margin-bottom: 5px;"><?= htmlspecialchars($selected_lab['nama_lab']) ?></h4>
                    <p style="color: #999; font-size: 14px; margin-bottom: 15px;"><?= htmlspecialchars($selected_lab['gedung']) ?></p>
                    <div style="padding: 15px; background: #f5f7fa; border-radius: 8px;">
                        <p style="color: #666; font-size: 13px; margin-bottom: 8px;">
                            <strong>Kapasitas:</strong> <?= $selected_lab['kapasitas'] ?> orang
                        </p>
                        <p style="color: #666; font-size: 13px; margin-bottom: 8px;">
                            <strong>Fasilitas:</strong> <?= htmlspecialchars($selected_lab['fasilitas']) ?>
                        </p>
                        <p style="color: #666; font-size: 13px;">
                            <strong>Lokasi:</strong> <?= htmlspecialchars($selected_lab['gedung']) ?>, Level 1
                        </p>
                    </div>
                <?php else: ?>
                    <p style="color: #999; font-size: 14px;">Pilih laboratorium dari daftar di bawah</p>
                <?php endif; ?>
            </div>
            
            <div class="form-card">
                <form method="POST">
                    <div class="section-title">📋 Informasi Peminjam</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>ID / NIM</label>
                            <input type="text" name="nim" value="<?= htmlspecialchars($_SESSION['nim']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($_SESSION['nama']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="section-title" style="margin-top: 30px;">🏢 Pilih Ruangan Labor</div>
                    
                    <div class="lab-selection">
                        <?php foreach ($available_labs as $lab): ?>
                        <label class="lab-option">
                            <input type="radio" name="lab_id" value="<?= $lab['id'] ?>" 
                                <?= ($selected_lab && $selected_lab['id'] == $lab['id']) ? 'checked' : '' ?> required>
                            <div class="lab-option-info">
                                <h4><?= htmlspecialchars($lab['nama_lab']) ?></h4>
                                <p><?= htmlspecialchars($lab['gedung']) ?> - Kapasitas: <?= $lab['kapasitas'] ?> orang</p>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="section-title" style="margin-top: 30px;">📅 Tanggal Peminjaman</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" required>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Jumlah Komputer yang Dibutuhkan (Optional)</label>
                        <input type="number" name="jumlah_komputer" min="0" placeholder="Masukkan jumlah">
                    </div>
                    
                    <div class="form-group">
                        <label>Mode Peminjaman</label>
                        <select name="keperluan" required>
                            <option value="">Pilih mode peminjaman</option>
                            <option value="Muda Meeting">Muda Meeting</option>
                            <option value="Belanja Observasi">Belanja Observasi</option>
                        </select>
                    </div>
                    
                    <div class="section-title" style="margin-top: 30px;">📝 Alasan Peminjaman</div>
                    
                    <div class="info-box">
                        <p>Jelaskan alasan dan keperluan peminjaman ruang laboratorium ini secara detail.</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Alasan</label>
                        <textarea name="alasan" placeholder="Jelaskan alasan atau keperluan peminjaman Anda..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">Kirim Peminjaman</button>
                    
                    <p style="text-align: center; margin-top: 15px; color: #999; font-size: 13px;">
                        Harap tunggu verifikasi dari Admin dan/atau operator. Lampirkan jika perlu.
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>