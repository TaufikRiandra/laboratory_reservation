<?php
// pages/labs.php
require_once '../config/database.php';

// Get all available labs
 $stmt = $pdo->query("SELECT * FROM labs ORDER BY status = 'tersedia' DESC, nama_lab ASC");
 $labs = $stmt->fetchAll();
?>
<div class="header">
    <h1>Labor yang tersedia</h1>
    <p>Temukan labor yang sesuai untuk kebutuhan Anda</p>
</div>

<div class="filter-container">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Cari labor berdasarkan nama atau gedung...">
    </div>
    
    <div class="filter-buttons">
        <button class="filter-btn active" data-filter="all">
            <i class="fas fa-th"></i> Semua
        </button>
        <button class="filter-btn" data-filter="tersedia">
            <i class="fas fa-check-circle"></i> Tersedia
        </button>
        <button class="filter-btn" data-filter="digunakan">
            <i class="fas fa-clock"></i> Digunakan
        </button>
    </div>
    
    <div class="view-toggle">
        <button class="view-btn active" id="gridView">
            <i class="fas fa-th"></i>
        </button>
        <button class="view-btn" id="listView">
            <i class="fas fa-list"></i>
        </button>
    </div>
</div>

<div class="lab-grid" id="labGrid">
    <?php foreach ($labs as $lab): ?>
    <div class="lab-card" data-status="<?= $lab['status'] ?>" data-name="<?= htmlspecialchars($lab['nama_lab']) ?>" data-building="<?= htmlspecialchars($lab['gedung']) ?>">
        <div class="lab-image">
            <i class="fas fa-door-open"></i>
            <span class="lab-badge <?= $lab['status'] ?>">
                <?= ucfirst($lab['status']) ?>
            </span>
        </div>
        <div class="lab-content">
            <div class="lab-title"><?= htmlspecialchars($lab['nama_lab']) ?></div>
            <div class="lab-subtitle"><?= htmlspecialchars($lab['gedung']) ?></div>
            
            <div class="lab-details">
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-users"></i></span>
                    <span>Kapasitas: <?= $lab['kapasitas'] ?> orang</span>
                </div>
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-clock"></i></span>
                    <span>Durasi: 8 jam maksimal</span>
                </div>
                <div class="detail-item">
                    <span class="detail-icon"><i class="fas fa-desktop"></i></span>
                    <span><?= htmlspecialchars($lab['fasilitas'] ?? 'Komputer, AC, Proyektor') ?></span>
                </div>
            </div>
        </div>
        <div class="lab-footer">
            <span class="lab-location">
                <i class="fas fa-map-marker-alt"></i> 
                <?= htmlspecialchars($lab['gedung']) ?>, Level 1
            </span>
            <?php if ($lab['status'] === 'tersedia'): ?>
                <a href="booking.php?lab_id=<?= $lab['id'] ?>" class="btn-book tooltip">
                    <i class="fas fa-calendar-plus"></i> Tersedia
                    <span class="tooltiptext">Klik untuk memesan labor ini</span>
                </a>
            <?php elseif ($lab['status'] === 'digunakan'): ?>
                <button class="btn-book tooltip" disabled>
                    <i class="fas fa-clock"></i> Digunakan
                    <span class="tooltiptext">Labor ini sedang digunakan</span>
                </button>
            <?php elseif ($lab['status'] === 'ditolak'): ?>
                <button class="btn-book tooltip" disabled style="background: var(--error-color);">
                    <i class="fas fa-times-circle"></i> Tidak Tersedia
                    <span class="tooltiptext">Labor ini tidak tersedia untuk sementara</span>
                </button>
            <?php else: ?>
                <button class="btn-book tooltip" disabled style="background: var(--warning-color);">
                    <i class="fas fa-tools"></i> Maintenance
                    <span class="tooltiptext">Labor ini sedang dalam perawatan</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="no-results" id="noResults" style="display: none;">
    <i class="fas fa-search"></i>
    <h3>Tidak ada labor yang ditemukan</h3>
    <p>Coba ubah kata kunci pencarian atau filter yang digunakan</p>
</div>

<style>
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
    
    .lab-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
    }
    
    .lab-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        animation: slideUp 0.6s ease-out;
        position: relative;
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
    
    .lab-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }
    
    .lab-image {
        width: 100%;
        height: 180px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 64px;
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
    
    .lab-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        z-index: 2;
        transition: all 0.3s ease;
    }
    
    .lab-badge.tersedia {
        background: var(--success-color);
        color: white;
    }
    
    .lab-badge.digunakan {
        background: var(--info-color);
        color: white;
    }
    
    .lab-badge.ditolak {
        background: var(--error-color);
        color: white;
    }
    
    .lab-badge.maintenance {
        background: var(--warning-color);
        color: white;
    }
    
    .lab-card:hover .lab-badge {
        transform: scale(1.05);
    }
    
    .lab-content {
        padding: 20px;
    }
    
    .lab-title {
        color: var(--text-color);
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }
    
    .lab-card:hover .lab-title {
        color: var(--primary-color);
    }
    
    .lab-subtitle {
        color: var(--text-light);
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
        transition: all 0.3s ease;
    }
    
    .detail-item:hover {
        color: var(--primary-color);
        transform: translateX(5px);
    }
    
    .detail-icon {
        margin-right: 10px;
        width: 20px;
        color: var(--primary-color);
    }
    
    .lab-footer {
        padding: 15px 20px;
        background: var(--bg-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .lab-location {
        color: #666;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .lab-location i {
        color: var(--primary-color);
    }
    
    .btn-book {
        padding: 8px 20px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-book::before {
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
    
    .btn-book:active::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-book:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }
    
    .btn-book:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
    }
    
    .btn-book:disabled:hover {
        transform: none;
        box-shadow: none;
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
    
    .no-results {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px;
        color: var(--text-light);
    }
    
    .no-results i {
        font-size: 48px;
        margin-bottom: 15px;
        color: var(--border-color);
    }
    
    .view-toggle {
        display: flex;
        gap: 5px;
    }
    
    .view-btn {
        padding: 8px 10px;
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .view-btn:first-child {
        border-radius: 6px 0 0 6px;
    }
    
    .view-btn:last-child {
        border-radius: 0 6px 6px 0;
    }
    
    .view-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .list-view .lab-grid {
        grid-template-columns: 1fr;
    }
    
    .list-view .lab-card {
        display: flex;
        height: auto;
    }
    
    .list-view .lab-image {
        width: 200px;
        height: auto;
        min-height: 150px;
    }
    
    .list-view .lab-content {
        flex: 1;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const labCards = document.querySelectorAll('.lab-card');
    const noResults = document.getElementById('noResults');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter cards
            const filter = this.getAttribute('data-filter');
            let visibleCount = 0;
            
            labCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-status') === filter) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            noResults.style.display = visibleCount > 0 ? 'none' : 'block';
            
            // Show notification
            if (filter === 'all') {
                showNotification('Menampilkan semua labor', 'info');
            } else {
                showNotification(`Menampilkan labor dengan status: ${filter}`, 'info');
            }
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;
            
            labCards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const building = card.getAttribute('data-building').toLowerCase();
                
                if (name.includes(searchTerm) || building.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            noResults.style.display = visibleCount > 0 ? 'none' : 'block';
        });
    }
    
    // View toggle functionality
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const mainContent = document.querySelector('.main-content');
    
    if (gridView && listView) {
        gridView.addEventListener('click', function() {
            gridView.classList.add('active');
            listView.classList.remove('active');
            mainContent.classList.remove('list-view');
            showNotification('Tampilan grid', 'info');
        });
        
        listView.addEventListener('click', function() {
            listView.classList.add('active');
            gridView.classList.remove('active');
            mainContent.classList.add('list-view');
            showNotification('Tampilan daftar', 'info');
        });
    }
    
    // Add click event to lab cards
    labCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on a button or link
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            
            const labName = this.querySelector('.lab-title').textContent;
            showNotification(`Detail untuk ${labName} akan segera tersedia`, 'info');
        });
    });
});
</script>