<?php
// pages/dashboard.php
require_once '../config/database.php';

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

<style>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh button functionality
    const refreshBtn = document.getElementById('refreshBtn');
    const notification = document.getElementById('notification');
    
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Add spinning animation
            this.classList.add('spinning');
            
            // Simulate data refresh
            setTimeout(() => {
                this.classList.remove('spinning');
                showNotification('Data berhasil diperbarui!', 'success');
            }, 1500);
        });
    }
    
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
    if (progressFill) {
        const targetWidth = progressFill.style.width;
        progressFill.style.width = '0';
        
        setTimeout(() => {
            progressFill.style.width = targetWidth;
        }, 300);
    }
    
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
});
</script>