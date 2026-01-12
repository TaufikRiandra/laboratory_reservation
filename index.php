<?php
// index.php
require_once 'config/database.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labor Reservation</title>
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
            transition: transform 0.3s ease;
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
            transition: margin-left 0.3s ease;
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
        
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(102, 126, 234, 0.2);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
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
        
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 101;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body>
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
    </button>
    
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <div class="loading">
            <div class="loading-spinner"></div>
        </div>
    </div>
    
    <div class="notification" id="notification"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            mobileMenuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Page navigation
            const navLinks = document.querySelectorAll('.nav-link[data-page]');
            const notification = document.getElementById('notification');
            
            // Function to show notification
            function showNotification(message, type) {
                notification.textContent = message;
                notification.className = 'notification ' + type;
                notification.classList.add('show');
                
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            }
            
            // Function to load page content
            function loadPage(page) {
                // Show loading
                mainContent.innerHTML = '<div class="loading"><div class="loading-spinner"></div></div>';
                
                // Update active nav link
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('data-page') === page) {
                        link.classList.add('active');
                    }
                });
                
                // Fetch page content
                fetch(`pages/${page}.php`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Page not found');
                        }
                        return response.text();
                    })
                    .then(html => {
                        mainContent.innerHTML = html;
                        
                        // Execute any scripts in the loaded content
                        const scripts = mainContent.querySelectorAll('script');
                        scripts.forEach(script => {
                            const newScript = document.createElement('script');
                            Array.from(script.attributes).forEach(attr => {
                                newScript.setAttribute(attr.name, attr.value);
                            });
                            newScript.textContent = script.textContent;
                            script.parentNode.replaceChild(newScript, script);
                        });
                        
                        // Update page title
                        document.title = getPageTitle(page);
                        
                        // Close mobile menu if open
                        if (window.innerWidth <= 768) {
                            sidebar.classList.remove('active');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading page:', error);
                        mainContent.innerHTML = `
                            <div class="header">
                                <h1>Error</h1>
                                <p>Halaman tidak ditemukan</p>
                            </div>
                            <div style="text-align: center; padding: 50px;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: var(--error-color); margin-bottom: 20px;"></i>
                                <h3>Halaman tidak ditemukan</h3>
                                <p>Terjadi kesalahan saat memuat halaman. Silakan coba lagi.</p>
                            </div>
                        `;
                    });
            }
            
            // Function to get page title
            function getPageTitle(page) {
                const titles = {
                    'dashboard': 'Status Dashboard - Labor Reservation',
                    'profile': 'Profil - Labor Reservation',
                    'labs': 'Labor Tersedia - Labor Reservation',
                    'booking': 'Form Peminjaman - Labor Reservation',
                    'history': 'Riwayat Peminjaman - Labor Reservation'
                };
                return titles[page] || 'Labor Reservation';
            }
            
            // Add click event to nav links
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    loadPage(page);
                    
                    // Update URL without page reload
                    history.pushState({page}, '', this.getAttribute('href'));
                });
            });
            
            // Handle browser back/forward buttons
            window.addEventListener('popstate', function(e) {
                if (e.state && e.state.page) {
                    loadPage(e.state.page);
                }
            });
            
            // Load initial page (dashboard)
            const initialPage = 'dashboard';
            loadPage(initialPage);
            history.replaceState({page: initialPage}, '', 'dashboard.php');
        });
    </script>
</body>
</html>