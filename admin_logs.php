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

// Calculate stats
$total_cards = array_sum(array_column($logs, 'total_cards'));
$manual_count = count(array_filter($logs, fn($l) => $l['mode'] === 'manual'));
$bulk_count = count(array_filter($logs, fn($l) => $l['mode'] === 'bulk'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generation Logs | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Gradient Background */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Glass Effect */
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Animated Stats Cards */
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Mobile Menu Toggle */
        .mobile-menu { display: none; }
        .mobile-menu.active { display: block; }
        
        /* Table Animations */
        tbody tr {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive Table */
        @media (max-width: 768px) {
            .responsive-table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .card-view {
                display: block !important;
            }
            
            .card-view tbody,
            .card-view tr {
                display: block;
            }
            
            .card-view thead {
                display: none;
            }
            
            .card-view tr {
                margin-bottom: 1rem;
                background: white;
                border-radius: 12px;
                padding: 1rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }
            
            .card-view td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0 !important;
                border: none !important;
            }
            
            .card-view td:before {
                content: attr(data-label);
                font-weight: 600;
                color: #64748b;
                font-size: 0.75rem;
                text-transform: uppercase;
            }
        }
        
        /* Premium Badge Effect */
        .premium-badge {
            position: relative;
            overflow: hidden;
        }
        .premium-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        .premium-badge:hover::before {
            left: 100%;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <!-- Mobile Header -->
    <div class="lg:hidden bg-white shadow-lg sticky top-0 z-50">
        <div class="flex items-center justify-between p-4">
            <div class="text-xl font-bold gradient-bg bg-clip-text text-transparent">Admin Panel</div>
            <button onclick="toggleMobileMenu()" class="p-2 rounded-lg hover:bg-slate-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
        
        <!-- Mobile Menu -->
        <nav class="mobile-menu bg-white border-t border-slate-200 p-4 space-y-2">
            <a href="admin_dashboard.php" class="block p-3 hover:bg-slate-100 rounded-xl transition">📊 Dashboard</a>
            <a href="admin_users.php" class="block p-3 hover:bg-slate-100 rounded-xl transition">👥 Users</a>
            <a href="admin_logs.php" class="block p-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold">📋 Logs</a>
            <a href="dashboard.php" class="block p-3 hover:bg-slate-100 rounded-xl transition">🏠 Main System</a>
            <a href="logout.php" class="block p-3 text-red-600 hover:bg-red-50 rounded-xl transition">🚪 Logout</a>
        </nav>
    </div>

    <div class="flex min-h-screen">
        <!-- Desktop Sidebar -->
        <div class="hidden lg:block w-72 bg-white shadow-2xl">
            <div class="sticky top-0">
                <div class="p-6 border-b border-slate-100">
                    <div class="gradient-bg bg-clip-text text-transparent text-2xl font-bold mb-2">Admin Panel</div>
                    <div class="text-xs text-slate-500 font-medium">ID Card Management System</div>
                </div>
                
                <nav class="p-4 space-y-2">
                    <a href="admin_dashboard.php" class="flex items-center gap-3 p-3 hover:bg-slate-50 rounded-xl transition group">
                        <span class="text-2xl">📊</span>
                        <span class="font-medium text-slate-700 group-hover:text-slate-900">Dashboard</span>
                    </a>
                    <a href="admin_users.php" class="flex items-center gap-3 p-3 hover:bg-slate-50 rounded-xl transition group">
                        <span class="text-2xl">👥</span>
                        <span class="font-medium text-slate-700 group-hover:text-slate-900">User Management</span>
                    </a>
                    <a href="admin_logs.php" class="flex items-center gap-3 p-3 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-200">
                        <span class="text-2xl">📋</span>
                        <span class="font-semibold text-white">Generation Logs</span>
                    </a>
                    <a href="dashboard.php" class="flex items-center gap-3 p-3 hover:bg-slate-50 rounded-xl transition group">
                        <span class="text-2xl">🏠</span>
                        <span class="font-medium text-slate-700 group-hover:text-slate-900">Main System</span>
                    </a>
                    
                    <div class="pt-4 mt-4 border-t border-slate-100">
                        <a href="logout.php" class="flex items-center gap-3 p-3 text-red-600 hover:bg-red-50 rounded-xl transition group">
                            <span class="text-2xl">🚪</span>
                            <span class="font-medium">Logout</span>
                        </a>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-7xl mx-auto p-4 lg:p-8">
                <!-- Header -->
                <header class="mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-2">Generation Logs</h1>
                            <p class="text-slate-600">Track and analyze all ID card generation activity</p>
                        </div>
                        <div class="flex gap-3">
                            <button onclick="exportLogs()" class="premium-badge px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition">
                                📥 Export Data
                            </button>
                        </div>
                    </div>
                </header>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                    <div class="stat-card glass rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-2xl">
                                📊
                            </div>
                            <span class="text-xs font-semibold text-blue-600 bg-blue-100 px-3 py-1 rounded-full">Total</span>
                        </div>
                        <div class="text-3xl font-bold text-slate-900 mb-1"><?= count($logs) ?></div>
                        <div class="text-sm text-slate-600">Total Generations</div>
                    </div>

                    <div class="stat-card glass rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-2xl">
                                🎫
                            </div>
                            <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-3 py-1 rounded-full">Cards</span>
                        </div>
                        <div class="text-3xl font-bold text-slate-900 mb-1"><?= number_format($total_cards) ?></div>
                        <div class="text-sm text-slate-600">Total Cards Generated</div>
                    </div>

                    <div class="stat-card glass rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center text-2xl">
                                ✍️
                            </div>
                            <span class="text-xs font-semibold text-teal-600 bg-teal-100 px-3 py-1 rounded-full">Manual</span>
                        </div>
                        <div class="text-3xl font-bold text-slate-900 mb-1"><?= $manual_count ?></div>
                        <div class="text-sm text-slate-600">Manual Entries</div>
                    </div>

                    <div class="stat-card glass rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center text-2xl">
                                📦
                            </div>
                            <span class="text-xs font-semibold text-indigo-600 bg-indigo-100 px-3 py-1 rounded-full">Bulk</span>
                        </div>
                        <div class="text-3xl font-bold text-slate-900 mb-1"><?= $bulk_count ?></div>
                        <div class="text-sm text-slate-600">Bulk Uploads</div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="glass rounded-2xl p-4 lg:p-6 shadow-lg mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-xl">🔍</span>
                        <h2 class="text-lg font-bold text-slate-800">Advanced Filters</h2>
                    </div>
                    
                    <form action="admin_logs.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">School / User</label>
                            <select name="user_id" class="w-full border-2 border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                                <option value="">All Schools</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($_GET['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= $u['username'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Generation Mode</label>
                            <select name="mode" class="w-full border-2 border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                                <option value="">All Modes</option>
                                <option value="manual" <?= ($_GET['mode'] ?? '') == 'manual' ? 'selected' : '' ?>>✍️ Manual</option>
                                <option value="bulk" <?= ($_GET['mode'] ?? '') == 'bulk' ? 'selected' : '' ?>>📦 Bulk</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Academic Year</label>
                            <select name="academic_year" class="w-full border-2 border-slate-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                                <option value="">All Years</option>
                                <?php foreach ($years as $y): ?>
                                    <option value="<?= $y['academic_year'] ?>" <?= ($_GET['academic_year'] ?? '') == $y['academic_year'] ? 'selected' : '' ?>><?= $y['academic_year'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="flex flex-col justify-end gap-2">
                            <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 rounded-xl hover:shadow-lg transition transform hover:scale-105">
                                Apply Filters
                            </button>
                            <a href="admin_logs.php" class="bg-slate-100 text-slate-700 font-bold py-3 rounded-xl hover:bg-slate-200 transition text-center">
                                Reset All
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Table -->
                <div class="glass rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-4 lg:p-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-blue-50">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <h2 class="text-xl font-bold text-slate-800">Activity Log</h2>
                            <div class="text-sm text-slate-600">
                                Showing <span class="font-bold text-slate-900"><?= count($logs) ?></span> results
                            </div>
                        </div>
                    </div>
                    
                    <div class="responsive-table overflow-x-auto">
                        <table class="w-full text-left card-view">
                            <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                                <tr>
                                    <th class="px-6 py-4">User / School</th>
                                    <th class="px-6 py-4">Mode</th>
                                    <th class="px-6 py-4">Design</th>
                                    <th class="px-6 py-4">Cards</th>
                                    <th class="px-6 py-4">Academic Year</th>
                                    <th class="px-6 py-4">Date & Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <?php foreach ($logs as $l): ?>
                                <tr class="hover:bg-blue-50/50 transition">
                                    <td class="px-6 py-4" data-label="User / School">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold text-sm">
                                                <?= strtoupper(substr($l['username'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900"><?= htmlspecialchars($l['username']) ?></div>
                                                <div class="text-xs text-slate-500"><?= htmlspecialchars($l['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4" data-label="Mode">
                                        <?php if ($l['mode'] === 'bulk'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-indigo-100 to-purple-100 text-indigo-700 border border-indigo-200">
                                                <span>📦</span> Bulk
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-teal-100 to-emerald-100 text-teal-700 border border-teal-200">
                                                <span>✍️</span> Manual
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4" data-label="Design">
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg">
                                            <span class="text-slate-500">🎨</span>
                                            <span class="font-mono text-sm font-semibold text-slate-700">Design #<?= $l['design_id'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4" data-label="Cards">
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                                                <?= $l['total_cards'] ?>
                                            </span>
                                            <span class="text-xs text-slate-500">cards</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4" data-label="Academic Year">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 rounded-lg text-sm font-semibold border border-amber-200">
                                            <span>📅</span> <?= $l['academic_year'] ?: 'N/A' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4" data-label="Date & Time">
                                        <div class="text-sm">
                                            <div class="font-semibold text-slate-700"><?= date('M d, Y', strtotime($l['timestamp'])) ?></div>
                                            <div class="text-xs text-slate-500"><?= date('h:i A', strtotime($l['timestamp'])) ?></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="text-6xl mb-4">📭</div>
                                        <div class="text-xl font-bold text-slate-400 mb-2">No logs found</div>
                                        <div class="text-sm text-slate-500">Try adjusting your filters or check back later</div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="mt-8 text-center text-sm text-slate-500">
                    <p>© <?= date('Y') ?> <?= PROJECT_NAME ?> - Admin Dashboard</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            document.querySelector('.mobile-menu').classList.toggle('active');
        }

        function exportLogs() {
            alert('Export functionality - CSV/Excel export will be implemented here');
            // You can implement actual export functionality here
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.querySelector('.mobile-menu');
            const button = event.target.closest('button[onclick="toggleMobileMenu()"]');
            
            if (!menu.contains(event.target) && !button && menu.classList.contains('active')) {
                menu.classList.remove('active');
            }
        });
    </script>
</body>
</html>