<?php
// history.php
require_once 'config/database.php';
requireLogin();

// Get user's booking history
$stmt = $pdo->prepare("SELECT b.*, l.nama_lab, l.gedung, l.kapasitas 
                       FROM bookings b 
                       JOIN labs l ON b.lab_id = l.id 
                       WHERE b.user_id = ? 
                       ORDER BY b.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Labor Reservation</title>
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
        
        .booking-list {
            display: grid;
            gap: 20px;
        }
        
        .booking-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .booking-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .booking-title {
            flex: 1;
        }
        
        .booking-title h3 {
            color: #333;
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .booking-title p {
            color: #999;
            font-size: 14px;
        }
        
        .booking-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .booking-badge.menunggu {
            background: #fff3e0;
            color: #ff9800;
        }
        
        .booking-badge.disetujui {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .booking-badge.ditolak {
            background: #ffebee;
            color: #f44336;
        }
        
        .booking-badge.selesai {
            background: #e3f2fd;
            color: #2196f3;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 14px;
        }
        
        .detail-icon {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .booking-reason {
            background: #f5f7fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .booking-reason label {
            display: block;
            color: #999;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .booking-reason p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .empty-state-icon {
            font-size: 72px;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
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
                <a href="booking.php" class="nav-link">
                    <span class="nav-icon">📝</span>
                    Booking
                </a>
            </li>
            <li class="nav-item">
                <a href="history.php" class="nav-link active">
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
            <h1>Riwayat Peminjaman</h1>
            <p>Lihat riwayat peminjaman dan status persetujuan</p>
        </div>
        
        <?php if (empty($bookings)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>Belum Ada Riwayat</h3>
                <p>Anda belum memiliki riwayat peminjaman laboratorium.</p>
                <a href="booking.php" class="btn-primary">Buat Peminjaman</a>
            </div>
        <?php else: ?>
            <div class="booking-list">
                <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-title">
                            <h3><?= htmlspecialchars($booking['nama_lab']) ?></h3>
                            <p><?= htmlspecialchars($booking['gedung']) ?> - ID Peminjaman: <?= $booking['id'] ?></p>
                        </div>
                        <span class="booking-badge <?= $booking['status'] ?>">
                            <?= ucfirst($booking['status']) ?>
                        </span>
                    </div>
                    
                    <div class="booking-details">
                        <div class="detail-item">
                            <span class="detail-icon">📅</span>
                            <span><?= date('d M Y', strtotime($booking['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($booking['tanggal_selesai'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-icon">⏰</span>
                            <span><?= date('H:i', strtotime($booking['waktu_mulai'])) ?> - <?= date('H:i', strtotime($booking['waktu_selesai'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-icon">👥</span>
                            <span>Kapasitas: <?= $booking['kapasitas'] ?> orang</span>
                        </div>
                        <?php if ($booking['jumlah_komputer'] > 0): ?>
                        <div class="detail-item">
                            <span class="detail-icon">🖥️</span>
                            <span><?= $booking['jumlah_komputer'] ?> Komputer</span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <span class="detail-icon">📋</span>
                            <span><?= htmlspecialchars($booking['keperluan']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-icon">🕒</span>
                            <span>Dibuat: <?= date('d M Y H:i', strtotime($booking['created_at'])) ?></span>
                        </div>
                    </div>
                    
                    <?php if ($booking['alasan']): ?>
                    <div class="booking-reason">
                        <label>Alasan Peminjaman:</label>
                        <p><?= htmlspecialchars($booking['alasan']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>