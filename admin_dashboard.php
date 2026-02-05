<?php
// ==========================================
// ADMIN DASHBOARD - SYSTEM OVERVIEW
// ==========================================
require_once 'auth_check.php';
requireAdmin();

// Initialize stats array with defaults // UPDATED
$stats = [
    'total_users' => 0,
    'pending_users' => 0,
    'active_users' => 0,
    'total_generations' => 0
];

try {
    // Optimization: Combined user status counts into a single query // UPDATED
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
    // Graceful error handling for missing tables or connection issues // UPDATED
    $recent_generations = [];
    $recent_activity = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?= htmlspecialchars(PROJECT_NAME) ?></title> <!-- UPDATED -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .stat-card { transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="flex bg-slate-50 min-h-screen">

    <!-- Sidebar -->
    <div class="w-64 bg-slate-900 text-white flex flex-col">
        <div class="p-6 text-2xl font-bold border-b border-slate-800 text-blue-400">
            Admin Panel
        </div>
        <nav class="flex-1 p-4 space-y-2 mt-4">
            <a href="admin_dashboard.php" class="block p-3 bg-blue-600 rounded-xl font-semibold">Dashboard</a>
            <a href="admin_users.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">User Management</a>
            <a href="admin_logs.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">Generation Logs</a>
            <a href="index.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">Main System</a>
            <div class="pt-4 border-t border-slate-800 mt-4">
                <a href="logout.php" class="block p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition">Logout</a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-8 overflow-y-auto">
        <header class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">System Overview</h1>
                <p class="text-slate-500">Welcome back, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></p> <!-- UPDATED -->
            </div>
            <div class="flex gap-4">
                <a href="index.php" class="bg-white border text-slate-700 px-4 py-2 rounded-xl font-medium shadow-sm hover:shadow-md transition">← Generate Cards</a>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm font-medium uppercase tracking-wider mb-2">Total Users</p>
                <h3 class="text-4xl font-bold text-slate-800"><?= (int)$stats['total_users'] ?></h3>
            </div>
            <div class="stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm font-medium uppercase tracking-wider mb-2">Pending</p>
                <h3 class="text-4xl font-bold text-amber-600"><?= (int)$stats['pending_users'] ?></h3>
            </div>
            <div class="stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm font-medium uppercase tracking-wider mb-2">Active</p>
                <h3 class="text-4xl font-bold text-emerald-600"><?= (int)$stats['active_users'] ?></h3>
            </div>
            <div class="stat-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm font-medium uppercase tracking-wider mb-2">ID Cards Built</p>
                <h3 class="text-4xl font-bold text-blue-600"><?= (int)$stats['total_generations'] ?></h3>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Recent Generations -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-800">Recent Generations</h2>
                    <a href="admin_logs.php" class="text-blue-600 font-semibold text-sm hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Mode</th>
                                <th class="px-6 py-4">Cards</th>
                                <th class="px-6 py-4">Design</th>
                                <th class="px-6 py-4">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (!empty($recent_generations)): ?> <!-- UPDATED -->
                                <?php foreach ($recent_generations as $g): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($g['username']) ?></td> <!-- UPDATED -->
                                    <td class="px-6 py-4 capitalize"><?= htmlspecialchars($g['mode']) ?></td> <!-- UPDATED -->
                                    <td class="px-6 py-4"><?= (int)$g['total_cards'] ?></td>
                                    <td class="px-6 py-4">D-<?= htmlspecialchars($g['design_id']) ?></td> <!-- UPDATED -->
                                    <td class="px-6 py-4 text-slate-500 text-sm"><?= htmlspecialchars(date('M d, H:i', strtotime($g['timestamp']))) ?></td> <!-- UPDATED -->
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400 italic">No recent generations found.</td> <!-- UPDATED -->
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity/Logins -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-800">Login History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">User</th>
                                <th class="px-6 py-4">Action</th>
                                <th class="px-6 py-4">IP Address</th>
                                <th class="px-6 py-4">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (!empty($recent_activity)): ?> <!-- UPDATED -->
                                <?php foreach ($recent_activity as $a): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($a['username']) ?></td> <!-- UPDATED -->
                                    <td class="px-6 py-4"><?= htmlspecialchars($a['action']) ?></td> <!-- UPDATED -->
                                    <td class="px-6 py-4 text-slate-500"><?= htmlspecialchars($a['ip_address']) ?></td> <!-- UPDATED -->
                                    <td class="px-6 py-4 text-slate-500 text-sm"><?= htmlspecialchars(date('M d, H:i', strtotime($a['timestamp']))) ?></td> <!-- UPDATED -->
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-400 italic">No recent activity logged.</td> <!-- UPDATED -->
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
