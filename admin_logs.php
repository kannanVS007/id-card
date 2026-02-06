<?php
require_once 'auth_check.php';
requireAdmin();

$where = "WHERE 1=1";
$params = [];

if (!empty($_GET['user_id'])) {
    $where .= " AND g.user_id = ?";
    $params[] = $_GET['user_id'];
}
if (!empty($_GET['mode'])) {
    $where .= " AND g.mode = ?";
    $params[] = $_GET['mode'];
}
if (!empty($_GET['academic_year'])) {
    $where .= " AND g.academic_year = ?";
    $params[] = $_GET['academic_year'];
}

$stmt = $pdo->prepare("SELECT g.*, u.username, u.email FROM id_generations g JOIN users u ON g.user_id = u.id $where ORDER BY g.timestamp DESC");
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get users for filter
$users = $pdo->query("SELECT id, username FROM users WHERE role = 'user' ORDER BY username")->fetchAll();
// Get distinct years for filter
$years = $pdo->query("SELECT DISTINCT academic_year FROM id_generations WHERE academic_year IS NOT NULL ORDER BY academic_year DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generation Logs | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        
        /* Mobile menu toggle */
        .mobile-menu-btn {
            display: none;
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                position: fixed;
                z-index: 50;
                height: 100vh;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 40;
            }
            
            .overlay.active {
                display: block;
            }
        }
        
        /* Filter toggle animation */
        .filter-panel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        
        .filter-panel.active {
            max-height: 500px;
        }
        
        /* Stats card animation */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card {
            animation: slideUp 0.4s ease-out;
        }
    </style>
</head>
<body class="flex bg-slate-50 min-h-screen">
    <!-- Mobile Menu Overlay -->
    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>
    
    <!-- Sidebar -->
    <div class="sidebar w-64 bg-slate-900 text-white flex flex-col">
        <div class="p-6 text-2xl font-bold border-b border-slate-800 text-blue-400">Admin Panel</div>
        <nav class="flex-1 p-4 space-y-2 mt-4">
            <a href="admin_dashboard.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">Dashboard</a>
            <a href="admin_users.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">User Management</a>
            <a href="admin_logs.php" class="block p-3 bg-blue-600 rounded-xl font-semibold">Generation Logs</a>
            <a href="index.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">Main System</a>
            <div class="pt-4 border-t border-slate-800 mt-4">
                <a href="logout.php" class="block p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition">Logout</a>
            </div>
        </nav>
    </div>

    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Mobile Header -->
        <div class="md:hidden bg-white border-b border-slate-200 p-4 flex items-center justify-between sticky top-0 z-30">
            <button class="mobile-menu-btn text-slate-700" onclick="toggleMenu()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-lg font-bold text-slate-800">Generation Logs</h1>
            <button class="text-slate-700" onclick="toggleFilters()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 p-4 md:p-8 overflow-y-auto">
            <!-- Desktop Header -->
            <header class="mb-6 md:mb-8 hidden md:block">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Generation Logs</h1>
                        <p class="text-slate-500">Track and filter all ID card generation activity</p>
                    </div>
                    <button onclick="toggleFilters()" class="md:hidden bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filters
                    </button>
                </div>
            </header>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 md:mb-8">
                <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 text-white p-4 md:p-6 rounded-2xl shadow-lg" style="animation-delay: 0s;">
                    <div class="text-2xl md:text-3xl font-bold"><?= count($logs) ?></div>
                    <div class="text-xs md:text-sm opacity-90 mt-1">Total Logs</div>
                </div>
                <div class="stat-card bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-4 md:p-6 rounded-2xl shadow-lg" style="animation-delay: 0.1s;">
                    <div class="text-2xl md:text-3xl font-bold"><?= array_sum(array_column($logs, 'total_cards')) ?></div>
                    <div class="text-xs md:text-sm opacity-90 mt-1">Cards Generated</div>
                </div>
                <div class="stat-card bg-gradient-to-br from-teal-500 to-teal-600 text-white p-4 md:p-6 rounded-2xl shadow-lg" style="animation-delay: 0.2s;">
                    <div class="text-2xl md:text-3xl font-bold"><?= count(array_filter($logs, fn($l) => $l['mode'] === 'manual')) ?></div>
                    <div class="text-xs md:text-sm opacity-90 mt-1">Manual Mode</div>
                </div>
                <div class="stat-card bg-gradient-to-br from-purple-500 to-purple-600 text-white p-4 md:p-6 rounded-2xl shadow-lg" style="animation-delay: 0.3s;">
                    <div class="text-2xl md:text-3xl font-bold"><?= count(array_filter($logs, fn($l) => $l['mode'] === 'bulk')) ?></div>
                    <div class="text-xs md:text-sm opacity-90 mt-1">Bulk Mode</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-6 md:mb-8 overflow-hidden">
                <button onclick="toggleFilters()" class="w-full md:hidden p-4 flex items-center justify-between text-slate-700 font-semibold">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter Logs
                    </span>
                    <svg class="w-5 h-5 transform transition-transform" id="filterIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <div class="filter-panel md:!max-h-none p-4 md:p-6" id="filterPanel">
                    <form action="admin_logs.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">School (User)</label>
                            <select name="user_id" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="">All Schools</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($_GET['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= $u['username'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Mode</label>
                            <select name="mode" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="">All Modes</option>
                                <option value="manual" <?= ($_GET['mode'] ?? '') == 'manual' ? 'selected' : '' ?>>Manual</option>
                                <option value="bulk" <?= ($_GET['mode'] ?? '') == 'bulk' ? 'selected' : '' ?>>Bulk</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Academic Year</label>
                            <select name="academic_year" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                                <option value="">All Years</option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?= $y['academic_year'] ?>" <?= ($_GET['academic_year'] ?? '') == $y['academic_year'] ? 'selected' : '' ?>><?= $y['academic_year'] ?></option>
                                <?php endforeach; ?>
                                <option value="2025-26">2025-26 (Default)</option>
                            </select>
                        </div>
                        <div class="flex flex-col justify-end">
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2.5 rounded-xl hover:bg-blue-700 transition">Apply</button>
                                <a href="admin_logs.php" class="bg-slate-100 text-slate-600 font-bold py-2.5 px-4 rounded-xl hover:bg-slate-200 transition flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mobile Cards View -->
            <div class="md:hidden space-y-4">
                <?php foreach ($logs as $l): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="font-bold text-slate-800"><?= $l['username'] ?></div>
                            <div class="text-xs text-slate-500 break-all"><?= $l['email'] ?></div>
                        </div>
                        <span class="px-2 py-1 rounded-md text-xs font-bold uppercase ml-2 <?= $l['mode'] === 'bulk' ? 'bg-indigo-100 text-indigo-700' : 'bg-teal-100 text-teal-700' ?>">
                            <?= $l['mode'] ?>
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-slate-50 p-3 rounded-xl">
                            <div class="text-xs text-slate-500 mb-1">Design ID</div>
                            <div class="font-mono text-sm font-bold text-slate-800">#<?= $l['design_id'] ?></div>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-xl">
                            <div class="text-xs text-blue-600 mb-1">Total Cards</div>
                            <div class="text-xl font-bold text-blue-700"><?= $l['total_cards'] ?></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-100">
                        <span class="font-medium"><?= $l['academic_year'] ?: 'N/A' ?></span>
                        <span><?= date('M d, Y • H:i', strtotime($l['timestamp'])) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($logs)): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-slate-400 font-medium">No logs found matching your criteria.</p>
                    <a href="admin_logs.php" class="inline-block mt-4 text-blue-600 font-semibold hover:underline">Clear filters</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">User / School</th>
                                <th class="px-6 py-4">Mode</th>
                                <th class="px-6 py-4">Design ID</th>
                                <th class="px-6 py-4">Total Cards</th>
                                <th class="px-6 py-4">Academic Year</th>
                                <th class="px-6 py-4">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($logs as $l): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800"><?= $l['username'] ?></div>
                                    <div class="text-xs text-slate-500"><?= $l['email'] ?></div>
                                </td>
                                <td class="px-6 py-4 capitalize">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase <?= $l['mode'] === 'bulk' ? 'bg-indigo-100 text-indigo-700' : 'bg-teal-100 text-teal-700' ?>">
                                        <?= $l['mode'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded-lg">
                                        #<?= $l['design_id'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                            </svg>
                                        </div>
                                        <span class="text-xl font-bold text-slate-800"><?= $l['total_cards'] ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-slate-700 font-medium"><?= $l['academic_year'] ?: 'N/A' ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    <div><?= date('M d, Y', strtotime($l['timestamp'])) ?></div>
                                    <div class="text-xs text-slate-400"><?= date('H:i:s', strtotime($l['timestamp'])) ?></div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-slate-400 font-medium text-lg">No logs found matching your criteria.</p>
                                    <a href="admin_logs.php" class="inline-block mt-4 text-blue-600 font-semibold hover:underline">Clear all filters</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        function toggleFilters() {
            const filterPanel = document.getElementById('filterPanel');
            const filterIcon = document.getElementById('filterIcon');
            filterPanel.classList.toggle('active');
            if (filterIcon) {
                filterIcon.style.transform = filterPanel.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0)';
            }
        }
        
        // Close menu when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                !menuBtn.contains(event.target) &&
                sidebar.classList.contains('active')) {
                toggleMenu();
            }
        });
        
        // Auto-open filters on desktop
        if (window.innerWidth >= 768) {
            document.getElementById('filterPanel').classList.add('active');
        }
    </script>
</body>
</html>