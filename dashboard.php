<?php
// dashboard.php
require_once 'config/database.php';
requireLogin();

// Get statistics
$stmt = $pdo->prepare("SELECT 
    COUNT(CASE WHEN status = 'menunggu' THEN 1 END) as menunggu,
    COUNT(CASE WHEN status = 'disetujui' THEN 1 END) as disetujui,
    COUNT(CASE WHEN status = 'ditolak' THEN 1 END) as ditolak,
    COUNT(CASE WHEN status = 'selesai' THEN 1 END) as selesai
    FROM bookings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Get all labs with their status
$stmt = $pdo->query("SELECT * FROM labs");
$labs = $stmt->fetchAll();

// Get computer availability
$stmt = $pdo->query("SELECT 
    SUM(kapasitas) as total_komputer,
    SUM(CASE WHEN status = 'tersedia' THEN kapasitas ELSE 0 END) as tersedia
    FROM labs");
$komputer = $stmt->fetch();
$komputer_terpakai = $komputer['total_komputer'] - $komputer['tersedia'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Labor Reservation</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stat-info h3 {
            color: #333;
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #999;
            font-size: 14px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        
        .stat-card.tersedia .stat-icon {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .stat-card.digunakan .stat-icon {
            background: #e3f2fd;
            color: #2196f3;
        }
        
        .stat-card.ditolak .stat-icon {
            background: #ffebee;
            color: #f44336;
        }
        
        .stat-card.maintenance .stat-icon {
            background: #fff3e0;
            color: #ff9800;
        }
        
        .section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            color: #333;
            font-size: 18px;
            flex: 1;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
        }
        
        .lab-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .lab-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .lab-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .lab-image {
            width: 100%;
            height: 150px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
        
        .lab-content {
            padding: 15px;
        }
        
        .lab-title {
            color: #333;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .lab-subtitle {
            color: #999;
            font-size: 13px;
            margin-bottom: 10px;
        }
        
        .lab-info {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge.tersedia {
            background: #e8f5e9;
            color: #4caf50;
        }
        
        .badge.digunakan {
            background: #e3f2fd;
            color: #2196f3;
        }
        
        .badge.ditolak {
            background: #ffebee;
            color: #f44336;
        }
        
        .badge.maintenance {
            background: #fff3e0;
            color: #ff9800;
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
                <a href="dashboard.php" class="nav-link active">
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
            <h1>Status Laboratorium</h1>
            <p>Kelola semua labor dan reservasi secara real-time</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card tersedia">
                <div class="stat-info">
                    <h3><?= $stats['menunggu'] ?></h3>
                    <p>Menunggu</p>
                </div>
                <div class="stat-icon">✓</div>
            </div>
            
            <div class="stat-card digunakan">
                <div class="stat-info">
                    <h3><?= $stats['disetujui'] ?></h3>
                    <p>Disetujui</p>
                </div>
                <div class="stat-icon">⚙</div>
            </div>
            
            <div class="stat-card ditolak">
                <div class="stat-info">
                    <h3><?= $stats['ditolak'] ?></h3>
                    <p>Ditolak</p>
                </div>
                <div class="stat-icon">✕</div>
            </div>
            
            <div class="stat-card maintenance">
                <div class="stat-info">
                    <h3><?= $stats['selesai'] ?></h3>
                    <p>Selesai</p>
                </div>
                <div class="stat-icon">⚠</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2>🖥️ Ketersediaan Komputer</h2>
            </div>
            <p style="color: #666; margin-bottom: 10px;">
                Tracking jumlah komputer tersedia
            </p>
            <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                <span style="color: #666;">
                    <strong><?= $komputer_terpakai ?></strong> / <?= $komputer['total_komputer'] ?>
                </span>
                <span style="color: #999;"><?= round(($komputer_terpakai / $komputer['total_komputer']) * 100) ?>% Terpakai</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= ($komputer_terpakai / $komputer['total_komputer']) * 100 ?>%"></div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2>Status Detail Ruangan</h2>
            </div>
            
            <div class="lab-grid">
                <?php foreach ($labs as $lab): ?>
                <div class="lab-card">
                    <div class="lab-image">🏢</div>
                    <div class="lab-content">
                        <div class="lab-title"><?= htmlspecialchars($lab['nama_lab']) ?></div>
                        <div class="lab-subtitle"><?= htmlspecialchars($lab['gedung']) ?></div>
                        <div class="lab-info">
                            <span>👥 <?= $lab['kapasitas'] ?> orang</span>
                            <span>⏰ 8 jam</span>
                        </div>
                        <div class="lab-info" style="margin-bottom: 15px;">
                            <span>📍 <?= htmlspecialchars($lab['gedung']) ?>, Level 1</span>
                        </div>
                        <span class="badge <?= $lab['status'] ?>">
                            <?= ucfirst($lab['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>