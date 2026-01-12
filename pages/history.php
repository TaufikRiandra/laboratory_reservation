<?php
// pages/history.php
require_once '../config/database.php';

// Get user's booking history
 $stmt = $pdo->prepare("SELECT b.*, l.nama_lab, l.gedung, l.kapasitas 
                       FROM bookings b 
                       JOIN labs l ON b.lab_id = l.id 
                       WHERE b.user_id = ? 
                       ORDER BY b.created_at DESC");
 $stmt->execute([$_SESSION['user_id']]);
 $bookings = $stmt->fetchAll();
?>
<div class="header">
    <h1>Riwayat Peminjaman</h1>
    <p>Lihat riwayat peminjaman dan status persetujuan</p>
</div>

<div class="stats-container">
    <?php
    // Count bookings by status
    $menunggu = 0;
    $disetujui = 0;
    $ditolak = 0;
    $selesai = 0;
    
    foreach ($bookings as $booking) {
        switch ($booking['status']) {
            case 'menunggu':
                $menunggu++;
                break;
            case 'disetujui':
                $disetujui++;
                break;
            case 'ditolak':
                $ditolak++;
                break;
            case 'selesai':
                $selesai++;
                break;
        }
    }
    ?>
    
    <div class="stat-card menunggu">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?= $menunggu ?></h3>
            <p>Menunggu Persetujuan</p>
        </div>
    </div>
    
    <div class="stat-card disetujui">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?= $disetujui ?></h3>
            <p>Disetujui</p>
        </div>
    </div>
    
    <div class="stat-card ditolak">
        <div class="stat-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?= $ditolak ?></h3>
            <p>Ditolak</p>
        </div>
    </div>
    
    <div class="stat-card selesai">
        <div class="stat-icon">
            <i class="fas fa-flag-checkered"></i>
        </div>
        <div class="stat-info">
            <h3><?= $selesai ?></h3>
            <p>Selesai</p>
        </div>
    </div>
</div>

<div class="filter-container">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Cari berdasarkan nama lab atau gedung...">
    </div>
    
    <div class="filter-buttons">
        <button class="filter-btn active" data-filter="all">
            <i class="fas fa-th"></i> Semua
        </button>
        <button class="filter-btn" data-filter="menunggu">
            <i class="fas fa-clock"></i> Menunggu
        </button>
        <button class="filter-btn" data-filter="disetujui">
            <i class="fas fa-check-circle"></i> Disetujui
        </button>
        <button class="filter-btn" data-filter="ditolak">
            <i class="fas fa-times-circle"></i> Ditolak
        </button>
        <button class="filter-btn" data-filter="selesai">
            <i class="fas fa-flag-checkered"></i> Selesai
        </button>
    </div>
    
    <button class="export-btn" id="exportBtn">
        <i class="fas fa-download"></i> Export
    </button>
</div>

<?php if (empty($bookings)): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <h3>Belum Ada Riwayat</h3>
        <p>Anda belum memiliki riwayat peminjaman laboratorium.</p>
        <a href="booking.php" class="btn-primary">
            <i class="fas fa-plus"></i> Buat Peminjaman
        </a>
    </div>
<?php else: ?>
    <div class="booking-list" id="bookingList">
        <?php foreach ($bookings as $booking): ?>
        <div class="booking-card <?= $booking['status'] ?>" data-status="<?= $booking['status'] ?>" data-lab="<?= htmlspecialchars($booking['nama_lab']) ?>" data-building="<?= htmlspecialchars($booking['gedung']) ?>">
            <div class="booking-header">
                <div class="booking-title">
                    <h3><?= htmlspecialchars($booking['nama_lab']) ?></h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($booking['gedung']) ?> - ID Peminjaman: #<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></p>
                </div>
                <span class="booking-badge <?= $booking['status'] ?> tooltip">
                    <?= ucfirst($booking['status']) ?>
                    <span class="tooltiptext">
                        <?php
                        switch ($booking['status']) {
                            case 'menunggu':
                                echo 'Peminjaman Anda sedang menunggu persetujuan dari admin';
                                break;
                            case 'disetujui':
                                echo 'Peminjaman Anda telah disetujui';
                                break;
                            case 'ditolak':
                                echo 'Peminjaman Anda ditolak. Silakan hubungi admin untuk informasi lebih lanjut';
                                break;
                            case 'selesai':
                                echo 'Peminjaman Anda telah selesai';
                                break;
                        }
                        ?>
                    </span>
                </span>
            </div>
            
            <div class="booking-details">
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-calendar-alt"></i></span>
                    <span><?= date('d M Y', strtotime($booking['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($booking['tanggal_selesai'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-clock"></i></span>
                    <span><?= date('H:i', strtotime($booking['waktu_mulai'])) ?> - <?= date('H:i', strtotime($booking['waktu_selesai'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-users"></i></span>
                    <span>Kapasitas: <?= $booking['kapasitas'] ?> orang</span>
                </div>
                <?php if ($booking['jumlah_komputer'] > 0): ?>
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-desktop"></i></span>
                    <span><?= $booking['jumlah_komputer'] ?> Komputer</span>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-tag"></i></span>
                    <span><?= htmlspecialchars($booking['keperluan']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-history"></i></span>
                    <span>Dibuat: <?= date('d M Y H:i', strtotime($booking['created_at'])) ?></span>
                </div>
            </div>
            
            <?php if ($booking['alasan']): ?>
            <div class="booking-reason">
                <label><i class="fas fa-comment-alt"></i> Alasan Peminjaman:</label>
                <p><?= htmlspecialchars($booking['alasan']) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="no-results" id="noResults" style="display: none;">
        <i class="fas fa-search"></i>
        <h3>Tidak ada riwayat yang ditemukan</h3>
        <p>Coba ubah kata kunci pencarian atau filter yang digunakan</p>
    </div>
    
    <div class="pagination">
        <a href="#" class="page-btn" id="prevPage">
            <i class="fas fa-chevron-left"></i>
        </a>
        <a href="#" class="page-btn active">1</a>
        <a href="#" class="page-btn">2</a>
        <a href="#" class="page-btn">3</a>
        <a href="#" class="page-btn" id="nextPage">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
<?php endif; ?>

<style>
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
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
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 15px;
    }
    
    .stat-card.menunggu .stat-icon {
        background: rgba(255, 152, 0, 0.1);
        color: var(--warning-color);
    }
    
    .stat-card.disetujui .stat-icon {
        background: rgba(76, 175, 80, 0.1);
        color: var(--success-color);
    }
    
    .stat-card.ditolak .stat-icon {
        background: rgba(244, 67, 54, 0.1);
        color: var(--error-color);
    }
    
    .stat-card.selesai .stat-icon {
        background: rgba(33, 150, 243, 0.1);
        color: var(--info-color);
    }
    
    .stat-info h3 {
        font-size: 24px;
        color: var(--text-color);
        margin-bottom: 5px;
    }
    
    .stat-info p {
        font-size: 14px;
        color: var(--text-light);
    }
    
    .filter-container {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .search-box {
        position: relative;
        flex: 1;
        min-width: 250px;
    }
    
    .search-box input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .search-box input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
    }
    
    .filter-buttons {
        display: flex;
        gap: 10px;
    }
    
    .filter-btn {
        padding: 8px 15px;
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    
    .filter-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .export-btn {
        padding: 8px 15px;
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .export-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    
    .booking-list {
        display: grid;
        gap: 20px;
    }
    
    .booking-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        animation: slideUp 0.6s ease-out;
        position: relative;
        overflow: hidden;
    }
    
    .booking-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: var(--border-color);
        transition: all 0.3s ease;
    }
    
    .booking-card.menunggu::before {
        background: var(--warning-color);
    }
    
    .booking-card.disetujui::before {
        background: var(--success-color);
    }
    
    .booking-card.ditolak::before {
        background: var(--error-color);
    }
    
    .booking-card.selesai::before {
        background: var(--info-color);
    }
    
    .booking-card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-5px);
    }
    
    .booking-card:hover::before {
        width: 8px;
    }
    
    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--bg-color);
    }
    
    .booking-title {
        flex: 1;
    }
    
    .booking-title h3 {
        color: var(--text-color);
        font-size: 20px;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }
    
    .booking-card:hover .booking-title h3 {
        color: var(--primary-color);
    }
    
    .booking-title p {
        color: var(--text-light);
        font-size: 14px;
    }
    
    .booking-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .booking-badge:hover {
        transform: scale(1.05);
    }
    
    .booking-badge.menunggu {
        background: rgba(255, 152, 0, 0.1);
        color: var(--warning-color);
    }
    
    .booking-badge.disetujui {
        background: rgba(76, 175, 80, 0.1);
        color: var(--success-color);
    }
    
    .booking-badge.ditolak {
        background: rgba(244, 67, 54, 0.1);
        color: var(--error-color);
    }
    
    .booking-badge.selesai {
        background: rgba(33, 150, 243, 0.1);
        color: var(--info-color);
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
        transition: all 0.3s ease;
    }
    
    .detail-item:hover {
        color: var(--primary-color);
        transform: translateX(5px);
    }
    
    .detail-icon {
        margin-right: 10px;
        font-size: 18px;
        color: var(--primary-color);
    }
    
    .booking-reason {
        background: var(--bg-color);
        padding: 15px;
        border-radius: 8px;
        margin-top: 15px;
        transition: all 0.3s ease;
    }
    
    .booking-reason:hover {
        background: #eef1f5;
    }
    
    .booking-reason label {
        display: block;
        color: var(--text-light);
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
        box-shadow: var(--card-shadow);
        animation: slideUp 0.6s ease-out;
    }
    
    .empty-state-icon {
        font-size: 72px;
        margin-bottom: 20px;
        color: var(--border-color);
    }
    
    .empty-state h3 {
        color: var(--text-color);
        margin-bottom: 10px;
    }
    
    .empty-state p {
        color: var(--text-light);
        margin-bottom: 20px;
    }
    
    .btn-primary {
        display: inline-block;
        padding: 12px 24px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-primary::before {
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
    
    .btn-primary:active::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(102, 126, 234, 0.3);
    }
    
    .no-results {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px;
        color: var(--text-light);
        background: white;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        animation: slideUp 0.6s ease-out;
    }
    
    .no-results i {
        font-size: 48px;
        margin-bottom: 15px;
        color: var(--border-color);
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 30px;
        gap: 5px;
    }
    
    .page-btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-color);
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .page-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    
    .page-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const bookingCards = document.querySelectorAll('.booking-card');
    const noResults = document.getElementById('noResults');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter cards
            const filter = this.getAttribute('data-filter');
            let visibleCount = 0;
            
            bookingCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-status') === filter) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            if (noResults) {
                noResults.style.display = visibleCount > 0 ? 'none' : 'block';
            }
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;
            
            bookingCards.forEach(card => {
                const labName = card.getAttribute('data-lab').toLowerCase();
                const building = card.getAttribute('data-building').toLowerCase();
                
                if (labName.includes(searchTerm) || building.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            if (noResults) {
                noResults.style.display = visibleCount > 0 ? 'none' : 'block';
            }
        });
    }
    
    // Export functionality
    const exportBtn = document.getElementById('exportBtn');
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // Create a simple notification
            const notification = document.getElementById('notification');
            notification.textContent = 'Data riwayat berhasil diekspor!';
            notification.className = 'notification success';
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
            
            // In a real application, you would implement actual export functionality here
            console.log('Export functionality would be implemented here');
        });
    }
    
    // Add click event to booking cards
    bookingCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on a button or link
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            
            const labName = this.querySelector('.booking-title h3').textContent;
            const bookingId = this.querySelector('.booking-title p').textContent.split(': ')[1];
            
            // Create a simple notification
            const notification = document.getElementById('notification');
            notification.textContent = `Detail untuk ${labName} (${bookingId}) akan segera tersedia`;
            notification.className = 'notification info';
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        });
    });
});
</script>