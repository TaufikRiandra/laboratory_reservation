<?php
// include/sidebar.php
// File ini berisi komponen sidebar yang dapat digunakan di berbagai halaman

// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'config/database.php';

// Fungsi untuk mendapatkan informasi pengguna dari database
function getUserInfo($userId, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        // Di aplikasi nyata, Anda mungkin ingin mencatat error ini
        return null;
    }
}

// Fungsi helper untuk membuat inisial dari nama
function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper($word[0]);
    }
    // Kembalikan maksimal 2 karakter inisial
    return substr($initials, 0, 2);
}

// Ambil informasi pengguna yang sedang login
 $userInfo = null;
if (isset($_SESSION['user_id'])) {
    $userInfo = getUserInfo($_SESSION['user_id'], $pdo);
}
?>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <div class="logo-icon">
            <?php if ($userInfo && !empty($userInfo['foto'])): ?>
                <img src="uploads/foto/<?php echo htmlspecialchars($userInfo['foto']); ?>" alt="Profile" class="user-photo">
            <?php elseif ($userInfo): ?>
                <div class="user-initials"><?php echo htmlspecialchars(getInitials($userInfo['nama_lengkap'])); ?></div>
            <?php else: ?>
                <i class="fas fa-flask"></i>
            <?php endif; ?>
        </div>
        <div class="logo-text"><?php echo $userInfo ? htmlspecialchars($userInfo['nama_lengkap']) : 'Manager'; ?></div>
        <div class="logo-subtitle"><?php echo $userInfo ? htmlspecialchars(ucfirst($userInfo['role'])) : 'Laboratorium'; ?></div>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" data-page="profile">
                <span class="nav-icon"><i class="fas fa-user"></i></span>
                Profile
            </a>
        </li>
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" data-page="dashboard">
                <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
                Status Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="labs.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'labs.php' ? 'active' : ''; ?>" data-page="labs">
                <span class="nav-icon"><i class="fas fa-th-large"></i></span>
                Daftar Lab
            </a>
        </li>
        <li class="nav-item">
            <a href="booking.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : ''; ?>" data-page="booking">
                <span class="nav-icon"><i class="fas fa-calendar-plus"></i></span>
                Booking
            </a>
        </li>
        <li class="nav-item">
            <a href="history.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>" data-page="history">
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

<style>
/* User photo styles */
.user-photo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.user-initials {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}
</style>