<?php
// pages/booking.php
require_once '../config/database.php';

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
<div class="header">
    <h1>Form Peminjaman Labor</h1>
    <p>Isi formulir untuk meminjam ruangan laboratorium</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="booking-container">
    <div class="preview-card">
        <h3 style="color: var(--text-color); margin-bottom: 15px;">
            <i class="fas fa-eye"></i> Preview Ruangan
        </h3>
        <div class="lab-preview-image">
            <i class="fas fa-door-open"></i>
        </div>
        <?php if ($selected_lab): ?>
            <h4 style="color: var(--text-color); margin-bottom: 5px;"><?= htmlspecialchars($selected_lab['nama_lab']) ?></h4>
            <p style="color: var(--text-light); font-size: 14px; margin-bottom: 15px;"><?= htmlspecialchars($selected_lab['gedung']) ?></p>
            <div style="padding: 15px; background: var(--bg-color); border-radius: 8px;">
                <p style="color: #666; font-size: 13px; margin-bottom: 8px;">
                    <i class="fas fa-users"></i> <strong>Kapasitas:</strong> <?= $selected_lab['kapasitas'] ?> orang
                </p>
                <p style="color: #666; font-size: 13px; margin-bottom: 8px;">
                    <i class="fas fa-desktop"></i> <strong>Fasilitas:</strong> <?= htmlspecialchars($selected_lab['fasilitas']) ?>
                </p>
                <p style="color: #666; font-size: 13px;">
                    <i class="fas fa-map-marker-alt"></i> <strong>Lokasi:</strong> <?= htmlspecialchars($selected_lab['gedung']) ?>, Level 1
                </p>
            </div>
        <?php else: ?>
            <p style="color: var(--text-light); font-size: 14px;">Pilih laboratorium dari daftar di bawah</p>
        <?php endif; ?>
        
        <div style="margin-top: 20px; padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 8px;">
            <h4 style="color: var(--primary-color); margin-bottom: 10px; font-size: 14px;">
                <i class="fas fa-info-circle"></i> Informasi Peminjaman
            </h4>
            <ul style="color: #666; font-size: 12px; padding-left: 20px; line-height: 1.6;">
                <li>Maksimal peminjaman 8 jam per hari</li>
                <li>Harap datang 15 menit sebelum waktu peminjaman</li>
                <li>Form peminjaman akan diproses maksimal 2x24 jam</li>
                <li>Pastikan untuk mengisi data dengan benar</li>
            </ul>
        </div>
    </div>
    
    <div class="form-card">
        <div class="form-progress">
            <div class="progress-step active" id="step1">
                <span>Informasi Peminjam</span>
            </div>
            <div class="progress-step" id="step2">
                <span>Pilih Ruangan</span>
            </div>
            <div class="progress-step" id="step3">
                <span>Tanggal & Waktu</span>
            </div>
            <div class="progress-step" id="step4">
                <span>Alasan</span>
            </div>
        </div>
        
        <form method="POST" id="bookingForm">
            <!-- Section 1: Informasi Peminjam -->
            <div class="form-section active" id="section1">
                <div class="section-title">
                    <i class="fas fa-user"></i> Informasi Peminjam
                </div>
                
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
            </div>
            
            <!-- Section 2: Pilih Ruangan -->
            <div class="form-section" id="section2">
                <div class="section-title">
                    <i class="fas fa-building"></i> Pilih Ruangan Labor
                </div>
                
                <div class="lab-selection">
                    <?php foreach ($available_labs as $lab): ?>
                    <label class="lab-option">
                        <input type="radio" name="lab_id" value="<?= $lab['id'] ?>" 
                            <?= ($selected_lab && $selected_lab['id'] == $lab['id']) ? 'checked' : '' ?> required>
                        <div class="lab-option-info">
                            <h4><?= htmlspecialchars($lab['nama_lab']) ?></h4>
                            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($lab['gedung']) ?> - <i class="fas fa-users"></i> Kapasitas: <?= $lab['kapasitas'] ?> orang</p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Section 3: Tanggal & Waktu -->
            <div class="form-section" id="section3">
                <div class="section-title">
                    <i class="fas fa-calendar-alt"></i> Tanggal Peminjaman
                </div>
                
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
                
                <div class="time-inputs">
                    <div class="form-group">
                        <label>Waktu Mulai</label>
                        <input type="time" name="waktu_mulai" value="08:00" required>
                    </div>
                    <div class="form-group">
                        <label>Waktu Selesai</label>
                        <input type="time" name="waktu_selesai" value="16:00" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Jumlah Komputer yang Dibutuhkan <span class="tooltip"><i class="fas fa-question-circle"></i>
                        <span class="tooltiptext">Isi jika Anda memerlukan komputer tertentu untuk kegiatan Anda</span>
                    </span></label>
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
            </div>
            
            <!-- Section 4: Alasan -->
            <div class="form-section" id="section4">
                <div class="section-title">
                    <i class="fas fa-edit"></i> Alasan Peminjaman
                </div>
                
                <div class="info-box">
                    <p><i class="fas fa-info-circle"></i> Jelaskan alasan dan keperluan peminjaman ruang laboratorium ini secara detail.</p>
                </div>
                
                <div class="form-group">
                    <label>Alasan</label>
                    <textarea name="alasan" id="alasan" placeholder="Jelaskan alasan atau keperluan peminjaman Anda..." required></textarea>
                    <div class="character-count"><span id="charCount">0</span> / 500 karakter</div>
                </div>
            </div>
            
            <div class="form-navigation">
                <button type="button" class="btn-nav btn-prev" id="prevBtn" style="display: none;">
                    <i class="fas fa-arrow-left"></i> Sebelumnya
                </button>
                <button type="button" class="btn-nav btn-next" id="nextBtn">
                    Selanjutnya <i class="fas fa-arrow-right"></i>
                </button>
                <button type="submit" class="btn-submit" id="submitBtn" style="display: none;">
                    <i class="fas fa-paper-plane"></i> Kirim Peminjaman
                </button>
            </div>
            
            <p style="text-align: center; margin-top: 15px; color: var(--text-light); font-size: 13px;">
                <i class="fas fa-info-circle"></i> Harap tunggu verifikasi dari Admin dan/atau operator. Lampirkan jika perlu.
            </p>
        </form>
    </div>
</div>

<style>
    .booking-container {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 25px;
    }
    
    .preview-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        height: fit-content;
        transition: all 0.3s ease;
        animation: slideUp 0.6s ease-out;
        position: sticky;
        top: 30px;
    }
    
    .lab-preview-image {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 72px;
        margin-bottom: 15px;
        position: relative;
        overflow: hidden;
    }
    
    .lab-preview-image::after {
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
    
    .preview-card:hover .lab-preview-image::after {
        animation: shine 0.5s ease-in-out;
    }
    
    @keyframes shine {
        0% { transform: rotate(45deg) translateY(-100%); }
        100% { transform: rotate(45deg) translateY(100%); }
    }
    
    .form-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        animation: slideUp 0.8s ease-out;
    }
    
    .section-title {
        color: var(--text-color);
        font-size: 18px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--border-color);
        position: relative;
        transition: color 0.3s ease;
    }
    
    .section-title:hover {
        color: var(--primary-color);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
        position: relative;
    }
    
    .form-group label {
        display: block;
        color: var(--text-color);
        font-size: 14px;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
        border: 2px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .lab-option::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 0;
        background: rgba(102, 126, 234, 0.05);
        transition: width 0.3s ease;
    }
    
    .lab-option:hover::before {
        width: 100%;
    }
    
    .lab-option:hover {
        border-color: var(--primary-color);
        transform: translateX(5px);
    }
    
    .lab-option input {
        margin-right: 15px;
        width: auto;
    }
    
    .lab-option-info h4 {
        color: var(--text-color);
        margin-bottom: 3px;
        transition: color 0.3s ease;
    }
    
    .lab-option:hover .lab-option-info h4 {
        color: var(--primary-color);
    }
    
    .lab-option-info p {
        color: var(--text-light);
        font-size: 13px;
    }
    
    .btn-submit {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-submit::before {
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
    
    .btn-submit:active::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(102, 126, 234, 0.3);
    }
    
    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        position: relative;
        animation: slideDown 0.5s ease-out;
    }
    
    @keyframes slideDown {
        from { 
            opacity: 0;
            transform: translateY(-20px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-success {
        background: rgba(76, 175, 80, 0.1);
        color: var(--success-color);
        border: 1px solid var(--success-color);
    }
    
    .alert-error {
        background: rgba(244, 67, 54, 0.1);
        color: var(--error-color);
        border: 1px solid var(--error-color);
    }
    
    .info-box {
        background: rgba(33, 150, 243, 0.1);
        border-left: 4px solid var(--info-color);
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    
    .info-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transform: translateX(-100%);
        animation: shimmer 3s infinite;
    }
    
    @keyframes shimmer {
        100% {
            transform: translateX(100%);
        }
    }
    
    .info-box p {
        color: var(--info-color);
        font-size: 13px;
        line-height: 1.6;
        position: relative;
        z-index: 1;
    }
    
    .form-progress {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        position: relative;
    }
    
    .form-progress::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--border-color);
        z-index: 1;
    }
    
    .progress-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 25%;
    }
    
    .progress-step::before {
        content: '';
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: white;
        border: 2px solid var(--border-color);
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }
    
    .progress-step.active::before {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .progress-step.completed::before {
        background: var(--success-color);
        border-color: var(--success-color);
    }
    
    .progress-step span {
        font-size: 12px;
        color: var(--text-light);
        text-align: center;
    }
    
    .progress-step.active span {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .progress-step.completed span {
        color: var(--success-color);
    }
    
    .form-section {
        display: none;
        animation: fadeIn 0.5s ease-in-out;
    }
    
    .form-section.active {
        display: block;
    }
    
    .form-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    
    .btn-nav {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-prev {
        background: var(--border-color);
        color: var(--text-color);
    }
    
    .btn-prev:hover {
        background: #d0d0d0;
    }
    
    .btn-next {
        background: var(--primary-color);
        color: white;
    }
    
    .btn-next:hover {
        background: var(--secondary-color);
    }
    
    .character-count {
        position: absolute;
        right: 10px;
        bottom: 10px;
        font-size: 12px;
        color: var(--text-light);
    }
    
    .time-inputs {
        display: flex;
        gap: 15px;
    }
    
    .time-inputs .form-group {
        flex: 1;
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
    
    @media (max-width: 992px) {
        .booking-container {
            grid-template-columns: 1fr;
        }
        
        .preview-card {
            position: static;
        }
    }
    
    @media (max-width: 576px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .time-inputs {
            flex-direction: column;
            gap: 0;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form navigation
    const sections = document.querySelectorAll('.form-section');
    const steps = document.querySelectorAll('.progress-step');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const bookingForm = document.getElementById('bookingForm');
    const alasanTextarea = document.getElementById('alasan');
    const charCount = document.getElementById('charCount');
    
    let currentSection = 0;
    
    // Character count for textarea
    if (alasanTextarea && charCount) {
        alasanTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            if (count > 500) {
                this.value = this.value.substring(0, 500);
                charCount.textContent = 500;
            }
        });
    }
    
    // Form validation
    function validateSection(sectionIndex) {
        const currentSectionElement = sections[sectionIndex];
        const requiredFields = currentSectionElement.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.style.borderColor = 'var(--error-color)';
                
                // Add shake animation
                field.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    field.style.animation = '';
                }, 500);
            } else {
                field.style.borderColor = 'var(--border-color)';
            }
        });
        
        return isValid;
    }
    
    // Update form navigation
    function updateFormNavigation() {
        // Hide all sections
        sections.forEach(section => section.classList.remove('active'));
        steps.forEach(step => step.classList.remove('active', 'completed'));
        
        // Show current section
        sections[currentSection].classList.add('active');
        
        // Update progress steps
        for (let i = 0; i <= currentSection; i++) {
            if (i < currentSection) {
                steps[i].classList.add('completed');
            } else {
                steps[i].classList.add('active');
            }
        }
        
        // Update navigation buttons
        prevBtn.style.display = currentSection === 0 ? 'none' : 'block';
        nextBtn.style.display = currentSection === sections.length - 1 ? 'none' : 'block';
        submitBtn.style.display = currentSection === sections.length - 1 ? 'block' : 'none';
    }
    
    // Next button click
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (validateSection(currentSection)) {
                if (currentSection < sections.length - 1) {
                    currentSection++;
                    updateFormNavigation();
                }
            }
        });
    }
    
    // Previous button click
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentSection > 0) {
                currentSection--;
                updateFormNavigation();
            }
        });
    }
    
    // Form submission
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            if (!validateSection(currentSection)) {
                e.preventDefault();
            }
        });
    }
    
    // Lab selection update preview
    const labOptions = document.querySelectorAll('input[name="lab_id"]');
    
    labOptions.forEach(option => {
        option.addEventListener('change', function() {
            if (this.checked) {
                // Update preview with selected lab details
                // This would typically be done with AJAX, but for simplicity we'll just show a message
                const previewCard = document.querySelector('.preview-card');
                previewCard.style.animation = 'none';
                setTimeout(() => {
                    previewCard.style.animation = 'pulse 0.5s ease';
                }, 10);
            }
        });
    });
    
    // Date validation
    const startDateInput = document.querySelector('input[name="tanggal_mulai"]');
    const endDateInput = document.querySelector('input[name="tanggal_selesai"]');
    
    if (startDateInput && endDateInput) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;
        endDateInput.min = today;
        
        // Update end date minimum when start date changes
        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value;
            
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });
    }
    
    // Add shake animation for invalid fields
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
});
</script>