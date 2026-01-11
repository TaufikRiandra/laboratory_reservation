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
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
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
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .stat-info h3 {
            color: var(--text-color);
            font-size: 32px;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }
        
        .stat-card:hover .stat-info h3 {
            color: var(--primary-color);
        }
        
        .stat-info p {
            color: var(--text-light);
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
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: rotate(10deg) scale(1.1);
        }
        
        .stat-card.tersedia .stat-icon {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }
        
        .stat-card.digunakan .stat-icon {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
        }
        
        .stat-card.ditolak .stat-icon {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
        }
        
        .stat-card.maintenance .stat-icon {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
        }
        
        .section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            transition: all 0.3s ease;
            animation: slideUp 0.8s ease-out;
        }
        
        .section:hover {
            box-shadow: var(--hover-shadow);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            color: var(--text-color);
            font-size: 18px;
            flex: 1;
            transition: color 0.3s ease;
        }
        
        .section:hover .section-header h2 {
            color: var(--primary-color);
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transition: width 1s ease;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transform: translateX(-100%);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            100% {
                transform: translateX(100%);
            }
        }
        
        .lab-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .lab-card {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: slideUp 1s ease-out;
        }
        
        .lab-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .lab-image {
            width: 100%;
            height: 150px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            position: relative;
            overflow: hidden;
        }
        
        .lab-image::after {
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
        
        .lab-card:hover .lab-image::after {
            animation: shine 0.5s ease-in-out;
        }
        
        @keyframes shine {
            0% { transform: rotate(45deg) translateY(-100%); }
            100% { transform: rotate(45deg) translateY(100%); }
        }
        
        .lab-content {
            padding: 15px;
        }
        
        .lab-title {
            color: var(--text-color);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }
        
        .lab-card:hover .lab-title {
            color: var(--primary-color);
        }
        
        .lab-subtitle {
            color: var(--text-light);
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
            transition: all 0.3s ease;
        }
        
        .badge:hover {
            transform: scale(1.05);
        }
        
        .badge.tersedia {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }
        
        .badge.digunakan {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
        }
        
        .badge.ditolak {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
        }
        
        .badge.maintenance {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
        }
        
        .tooltip {
            position: relative;
            display: inline-block;
        }
        
        .tooltip .tooltiptext {
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
        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            box-shadow: var(--card-shadow);
            transform: translateX(120%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: var(--success-color);
        }
        
        .notification.error {
            background: var(--error-color);
        }
        
        .notification.info {
            background: var(--info-color);
        }
        
        .notification.warning {
            background: var(--warning-color);
        }
        
        .refresh-btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .refresh-btn i {
            transition: transform 0.5s ease;
        }
        
        .refresh-btn.spinning i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            100% { transform: rotate(360deg); }
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
                <a href="profile.php" class="nav-link">
                    <span class="nav-icon"><i class="fas fa-user"></i></span>
                    Profile
                </a>
            </li>
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
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
            <h1>Status Laboratorium</h1>
            <p>Kelola semua labor dan reservasi secara real-time</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card tersedia">
                <div class="stat-info">
                    <h3><?= $stats['menunggu'] ?></h3>
                    <p>Menunggu</p>
                </div>
                <div class="stat-icon tooltip">
                    <i class="fas fa-clock"></i>
                    <span class="tooltiptext">Reservasi yang menunggu persetujuan</span>
                </div>
            </div>
            
            <div class="stat-card digunakan">
                <div class="stat-info">
                    <h3><?= $stats['disetujui'] ?></h3>
                    <p>Disetujui</p>
                </div>
                <div class="stat-icon tooltip">
                    <i class="fas fa-check-circle"></i>
                    <span class="tooltiptext">Reservasi yang telah disetujui</span>
                </div>
            </div>
            
            <div class="stat-card ditolak">
                <div class="stat-info">
                    <h3><?= $stats['ditolak'] ?></h3>
                    <p>Ditolak</p>
                </div>
                <div class="stat-icon tooltip">
                    <i class="fas fa-times-circle"></i>
                    <span class="tooltiptext">Reservasi yang ditolak</span>
                </div>
            </div>
            
            <div class="stat-card maintenance">
                <div class="stat-info">
                    <h3><?= $stats['selesai'] ?></h3>
                    <p>Selesai</p>
                </div>
                <div class="stat-icon tooltip">
                    <i class="fas fa-flag-checkered"></i>
                    <span class="tooltiptext">Reservasi yang telah selesai</span>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-desktop"></i> Ketersediaan Komputer</h2>
                <button class="refresh-btn" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i>
                    Refresh
                </button>
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
                <h2><i class="fas fa-building"></i> Status Detail Ruangan</h2>
            </div>
            
            <div class="lab-grid">
                <?php foreach ($labs as $lab): ?>
                <div class="lab-card">
                    <div class="lab-image">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="lab-content">
                        <div class="lab-title"><?= htmlspecialchars($lab['nama_lab']) ?></div>
                        <div class="lab-subtitle"><?= htmlspecialchars($lab['gedung']) ?></div>
                        <div class="lab-info">
                            <span><i class="fas fa-users"></i> <?= $lab['kapasitas'] ?> orang</span>
                            <span><i class="fas fa-clock"></i> 8 jam</span>
                        </div>
                        <div class="lab-info" style="margin-bottom: 15px;">
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($lab['gedung']) ?>, Level 1</span>
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
    
    <div class="notification" id="notification"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh button functionality
            const refreshBtn = document.getElementById('refreshBtn');
            const notification = document.getElementById('notification');
            
            refreshBtn.addEventListener('click', function() {
                // Add spinning animation
                this.classList.add('spinning');
                
                // Simulate data refresh
                setTimeout(() => {
                    this.classList.remove('spinning');
                    showNotification('Data berhasil diperbarui!', 'success');
                }, 1500);
            });
            
            // Show notification function
            function showNotification(message, type) {
                notification.textContent = message;
                notification.className = 'notification ' + type;
                notification.classList.add('show');
                
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            }
            
            // Animate progress bar on page load
            const progressFill = document.querySelector('.progress-fill');
            const targetWidth = progressFill.style.width;
            progressFill.style.width = '0';
            
            setTimeout(() => {
                progressFill.style.width = targetWidth;
            }, 300);
            
            // Add click event to lab cards
            const labCards = document.querySelectorAll('.lab-card');
            labCards.forEach(card => {
                card.addEventListener('click', function() {
                    const labName = this.querySelector('.lab-title').textContent;
                    showNotification(`Detail untuk ${labName} akan segera tersedia`, 'info');
                });
            });
            
            // Add hover effect to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    const value = this.querySelector('h3').textContent;
                    if (value > 0) {
                        this.querySelector('h3').style.transform = 'scale(1.1)';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    this.querySelector('h3').style.transform = 'scale(1)';
                });
            });
            
            // Simulate real-time updates
            setInterval(() => {
                // Randomly update a stat
                const randomStat = Math.floor(Math.random() * 4) + 1;
                const statCard = document.querySelector(`.stats-grid .stat-card:nth-child(${randomStat})`);
                const statValue = statCard.querySelector('h3');
                const currentValue = parseInt(statValue.textContent);
                
                // Small random change
                const change = Math.random() > 0.5 ? 1 : 0;
                statValue.textContent = currentValue + change;
                
                // Add pulse animation
                statCard.style.animation = 'none';
                setTimeout(() => {
                    statCard.style.animation = 'pulse 0.5s ease';
                }, 10);
            }, 30000); // Update every 30 seconds
        });
    </script>
</body>
</html>