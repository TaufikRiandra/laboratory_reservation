<?php
// labs.php
require_once 'config/database.php';
requireLogin();

// Get all available labs
$stmt = $pdo->query("SELECT * FROM labs ORDER BY status = 'tersedia' DESC, nama_lab ASC");
$labs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labor Tersedia - Labor Reservation</title>
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
        
        .lab-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }
        
        .lab-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .lab-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        
        .lab-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 64px;
            position: relative;
        }
        
        .lab-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .lab-badge.tersedia {
            background: #4caf50;
            color: white;
        }
        
        .lab-badge.digunakan {
            background: #2196f3;
            color: white;
        }
        
        .lab-badge.ditolak {
            background: #f44336;
            color: white;
        }
        
        .lab-badge.maintenance {
            background: #ff9800;
            color: white;
        }
        
        .lab-content {
            padding: 20px;
        }
        
        .lab-title {
            color: #333;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .lab-subtitle {
            color: #999;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .lab-details {
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .detail-icon {
            margin-right: 10px;
            width: 20px;
        }
        
        .lab-footer {
            padding: 15px 20px;
            background: #f5f7fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .lab-location {
            color: #666;
            font-size: 13px;
        }
        
        .btn-book {
            padding: 8px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-book:hover {
            transform: translateY(-2px);
        }
        
        .btn-book:disabled {
            background: #ccc;
            cursor: not-allowed;
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
                <a href="labs.php" class="nav-link active">
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
            <h1>Labor yang tersedia</h1>
            <p>Temukan labor yang sesuai untuk kebutuhan Anda</p>
        </div>
        
        <div class="lab-grid">
            <?php foreach ($labs as $lab): ?>
            <div class="lab-card">
                <div class="lab-image">
                    🏢
                    <span class="lab-badge <?= $lab['status'] ?>">
                        <?= ucfirst($lab['status']) ?>
                    </span>
                </div>
                <div class="lab-content">
                    <div class="lab-title"><?= htmlspecialchars($lab['nama_lab']) ?></div>
                    <div class="lab-subtitle"><?= htmlspecialchars($lab['gedung']) ?></div>
                    
                    <div class="lab-details">
                        <div class="detail-item">
                            <span class="detail-icon">👥</span>
                            <span>Kapasitas: <?= $lab['kapasitas'] ?> orang</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-icon">⏰</span>
                            <span>Durasi: 8 jam maksimal</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-icon">🖥️</span>
                            <span><?= htmlspecialchars($lab['fasilitas'] ?? 'Komputer, AC, Proyektor') ?></span>
                        </div>
                    </div>
                </div>
                <div class="lab-footer">
                    <span class="lab-location">📍 <?= htmlspecialchars($lab['gedung']) ?>, Level 1</span>
                    <?php if ($lab['status'] === 'tersedia'): ?>
                        <a href="booking.php?lab_id=<?= $lab['id'] ?>" class="btn-book">Tersedia</a>
                    <?php elseif ($lab['status'] === 'digunakan'): ?>
                        <button class="btn-book" disabled>Digunakan</button>
                    <?php elseif ($lab['status'] === 'ditolak'): ?>
                        <button class="btn-book" disabled style="background: #f44336;">Tidak Tersedia</button>
                    <?php else: ?>
                        <button class="btn-book" disabled style="background: #ff9800;">Maintenance</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>