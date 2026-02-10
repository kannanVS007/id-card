<?php
// ==========================================
// ADMIN DASHBOARD - SYSTEM OVERVIEW
// ==========================================
require_once 'auth_check.php';
requireAdmin();

// Initialize stats array with defaults
$stats = [
    'total_users' => 0,
    'pending_users' => 0,
    'active_users' => 0,
    'total_generations' => 0
];

try {
    // Optimization: Combined user status counts into a single query
    $userStatsStmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
        FROM users
    ");
    $userRes = $userStatsStmt->fetch();
    
    if ($userRes) {
        $stats['total_users']   = (int)$userRes['total'];
        $stats['pending_users'] = (int)$userRes['pending'];
        $stats['active_users']  = (int)$userRes['active'];
    }

    // Total Generations
    $genCountStmt = $pdo->query("SELECT COUNT(*) FROM id_generations");
    $stats['total_generations'] = (int)$genCountStmt->fetchColumn();

    // Recent Generations - Limit to 5 for dashboard performance
    $recentGenStmt = $pdo->query("
        SELECT g.*, u.username 
        FROM id_generations g 
        JOIN users u ON g.user_id = u.id 
        ORDER BY g.timestamp DESC 
        LIMIT 5
    ");
    $recent_generations = $recentGenStmt->fetchAll();

    // Recent Activity / Logins
    $recentActStmt = $pdo->query("
        SELECT a.*, u.username 
        FROM activity_logs a 
        JOIN users u ON a.user_id = u.id 
        ORDER BY a.timestamp DESC 
        LIMIT 5
    ");
    $recent_activity = $recentActStmt->fetchAll();

} catch (PDOException $e) {
    // Graceful error handling for missing tables or connection issues
    $recent_generations = [];
    $recent_activity = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?= htmlspecialchars(PROJECT_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --sidebar-bg: rgba(15, 23, 42, 0.95);
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #f8fafc;
            min-height: 100vh;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(16, 185, 129, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .content-wrapper {
            position: relative;
            z-index: 1;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: var(--sidebar-bg);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.3);
        }
        
        .sidebar-link {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-link:hover {
            background: rgba(99, 102, 241, 0.1);
            border-left-color: #6366f1;
            transform: translateX(4px);
        }
        
        .sidebar-link.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.2) 0%, rgba(99, 102, 241, 0.05) 100%);
            border-left-color: #6366f1;
            color: #a5b4fc;
        }
        
        /* Premium Glass Card */
        .glass-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(30, 41, 59, 0.3) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-card:hover {
            transform: translateY(-6px);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        }
        
        /* Stat Cards */
        .stat-card {
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--card-color), transparent);
        }
        
        /* Table Styles */
        .data-table {
            background: rgba(15, 23, 42, 0.4);
        }
        
        .table-row {
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .table-row:hover {
            background: rgba(99, 102, 241, 0.08);
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu-btn {
            display: none;
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                z-index: 50;
                transition: left 0.3s ease;
            }
            
            .sidebar.open {
                left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            
            .overlay.show {
                display: block;
            }
        }
        
        /* Responsive Tables */
        @media (max-width: 768px) {
            .data-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
        
        /* Animations */
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .animate-slide-in {
            animation: slideInRight 0.5s ease-out;
        }
        
        /* Badge Styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .badge-primary { background: rgba(99, 102, 241, 0.2); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.3); }
        .badge-success { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }
        .badge-warning { background: rgba(245, 158, 11, 0.2); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-info { background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); }
        @keyframes logo-load {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-logo-load {
            animation: logo-load 0.7s ease-out forwards;
        }
    </style>
</head>
<body class="flex">
    <div class="content-wrapper w-full flex">
        <!-- Mobile Overlay -->
        <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar w-72 flex flex-col" id="sidebar">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-white/5">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-14 h-14 flex items-center justify-center transition-transform duration-300 ease-out hover:scale-105 animate-logo-load">
    <img 
        src="assets/images/trishul-logo.png"
        alt="Trishul Logo"
        class="w-14 h-14 object-contain"
    >
</div>

                    <div>
                        <h2 class="text-xl font-bold bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">Admin Panel</h2>
                        <p class="text-xs text-indigo-400 font-medium">System Control</p>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="admin_dashboard.php" class="sidebar-link active flex items-center gap-3 p-3 rounded-xl font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="admin_users.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    User Management
                </a>
                <a href="admin_logs.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Generation Logs
                </a>
                <a href="dashboard.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Main System
                </a>
                
                <div class="pt-4 border-t border-white/10 mt-4">
                    <a href="logout.php" class="sidebar-link flex items-center gap-3 p-3 rounded-xl font-medium text-red-400 hover:bg-red-500/10 hover:border-red-500/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <!-- Top Bar -->
            <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-md border-b border-white/5 px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <!-- Mobile Menu Button -->
                        <button class="mobile-menu-btn p-2 rounded-lg bg-white/5 hover:bg-white/10 transition lg:hidden" onclick="toggleSidebar()">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-white">System Overview</h1>
                            <p class="text-sm text-slate-400 mt-1">Welcome back, <span class="text-indigo-400 font-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span></p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <a href="dashboard.php" class="hidden sm:flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2.5 rounded-xl font-medium border border-white/10 hover:border-white/20 transition backdrop-blur-sm text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Generate Cards
                        </a>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="p-4 sm:p-6 lg:p-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 animate-slide-in">
                    <!-- Total Users -->
                    <div class="glass-card stat-card p-6 rounded-2xl" style="--card-color: #6366f1;">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-indigo-500/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <span class="badge badge-primary">Total</span>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-2"><?= (int)$stats['total_users'] ?></h3>
                        <p class="text-slate-400 text-sm font-medium">Registered Users</p>
                    </div>
                    
                    <!-- Pending Users -->
                    <div class="glass-card stat-card p-6 rounded-2xl" style="--card-color: #f59e0b;">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="badge badge-warning">Pending</span>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-2"><?= (int)$stats['pending_users'] ?></h3>
                        <p class="text-slate-400 text-sm font-medium">Awaiting Approval</p>
                    </div>
                    
                    <!-- Active Users -->
                    <div class="glass-card stat-card p-6 rounded-2xl" style="--card-color: #10b981;">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="badge badge-success">Active</span>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-2"><?= (int)$stats['active_users'] ?></h3>
                        <p class="text-slate-400 text-sm font-medium">Active Accounts</p>
                    </div>
                    
                    <!-- Total Generations -->
                    <div class="glass-card stat-card p-6 rounded-2xl" style="--card-color: #3b82f6;">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                </svg>
                            </div>
                            <span class="badge badge-info">Cards</span>
                        </div>
                        <h3 class="text-4xl font-bold text-white mb-2"><?= (int)$stats['total_generations'] ?></h3>
                        <p class="text-slate-400 text-sm font-medium">ID Cards Generated</p>
                    </div>
                </div>

                <!-- Data Tables Grid -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 sm:gap-8">
                    <!-- Recent Generations -->
                    <div class="glass-card rounded-2xl overflow-hidden">
                        <div class="p-6 border-b border-white/5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div>
                                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Recent Generations
                                </h2>
                                <p class="text-xs text-slate-400 mt-1">Latest ID card creations</p>
                            </div>
                            <a href="admin_logs.php" class="text-indigo-400 hover:text-indigo-300 font-semibold text-sm flex items-center gap-1 transition">
                                View All
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left data-table">
                                <thead class="bg-slate-800/50 text-slate-400 text-xs uppercase font-semibold">
                                    <tr>
                                        <th class="px-6 py-4">User</th>
                                        <th class="px-6 py-4">Mode</th>
                                        <th class="px-6 py-4">Cards</th>
                                        <th class="px-6 py-4">Design</th>
                                        <th class="px-6 py-4">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_generations)): ?>
                                        <?php foreach ($recent_generations as $g): ?>
                                        <tr class="table-row">
                                            <td class="px-6 py-4">
                                                <span class="font-medium text-white"><?= htmlspecialchars($g['username']) ?></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="badge badge-primary capitalize"><?= htmlspecialchars($g['mode']) ?></span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-300 font-semibold"><?= (int)$g['total_cards'] ?></td>
                                            <td class="px-6 py-4">
                                                <span class="badge badge-info">D-<?= htmlspecialchars($g['design_id']) ?></span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-400 text-sm"><?= htmlspecialchars(date('M d, H:i', strtotime($g['timestamp']))) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center gap-3">
                                                    <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                    </svg>
                                                    <p class="text-slate-500 italic">No recent generations found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Login History -->
                    <div class="glass-card rounded-2xl overflow-hidden">
                        <div class="p-6 border-b border-white/5">
                            <div>
                                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Login History
                                </h2>
                                <p class="text-xs text-slate-400 mt-1">Recent user activity</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left data-table">
                                <thead class="bg-slate-800/50 text-slate-400 text-xs uppercase font-semibold">
                                    <tr>
                                        <th class="px-6 py-4">User</th>
                                        <th class="px-6 py-4">Action</th>
                                        <th class="px-6 py-4">IP Address</th>
                                        <th class="px-6 py-4">Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_activity)): ?>
                                        <?php foreach ($recent_activity as $a): ?>
                                        <tr class="table-row">
                                            <td class="px-6 py-4">
                                                <span class="font-medium text-white"><?= htmlspecialchars($a['username']) ?></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-slate-300"><?= htmlspecialchars($a['action']) ?></span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-slate-400 font-mono text-xs"><?= htmlspecialchars($a['ip_address']) ?></span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-400 text-sm"><?= htmlspecialchars(date('M d, H:i', strtotime($a['timestamp']))) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center gap-3">
                                                    <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                    </svg>
                                                    <p class="text-slate-500 italic">No recent activity logged</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }
        
        // Close sidebar when clicking outside on mobile
        document.getElementById('overlay').addEventListener('click', function() {
            toggleSidebar();
        });
    </script>
</body>
</html>