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
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="flex bg-slate-50 min-h-screen">
    <!-- Sidebar -->
    <div class="w-64 bg-slate-900 text-white flex flex-col">
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

    <div class="flex-1 p-8 overflow-y-auto">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Generation Logs</h1>
                <p class="text-slate-500">Track and filter all ID card generation activity</p>
            </div>
        </header>

        <!-- Filters -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 mb-8">
            <form action="admin_logs.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">School (User)</label>
                    <select name="user_id" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Schools</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($_GET['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= $u['username'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Mode</label>
                    <select name="mode" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Modes</option>
                        <option value="manual" <?= ($_GET['mode'] ?? '') == 'manual' ? 'selected' : '' ?>>Manual</option>
                        <option value="bulk" <?= ($_GET['mode'] ?? '') == 'bulk' ? 'selected' : '' ?>>Bulk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Academic Year</label>
                    <select name="academic_year" class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">All Years</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= $y['academic_year'] ?>" <?= ($_GET['academic_year'] ?? '') == $y['academic_year'] ? 'selected' : '' ?>><?= $y['academic_year'] ?></option>
                        <?php endforeach; ?>
                        <option value="2025-26">2025-26 (Default)</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-2.5 rounded-xl hover:bg-blue-700 transition">Filter</button>
                    <a href="admin_logs.php" class="bg-slate-100 text-slate-600 font-bold py-2.5 px-4 rounded-xl hover:bg-slate-200 transition">Reset</a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4">User / School</th>
                        <th class="px-6 py-4">Mode</th>
                        <th class="px-6 py-4">Design ID</th>
                        <th class="px-6 py-4">Total Cards</th>
                        <th class="px-6 py-4">Year</th>
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
                            <span class="px-2 py-1 rounded-md text-xs font-bold <?= $l['mode'] === 'bulk' ? 'bg-indigo-100 text-indigo-700' : 'bg-teal-100 text-teal-700' ?>">
                                <?= $l['mode'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 font-mono text-sm">Design #<?= $l['design_id'] ?></td>
                        <td class="px-6 py-4 font-bold"><?= $l['total_cards'] ?></td>
                        <td class="px-6 py-4 text-slate-600"><?= $l['academic_year'] ?: 'N/A' ?></td>
                        <td class="px-6 py-4 text-sm text-slate-500"><?= date('M d, Y - H:i:s', strtotime($l['timestamp'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-400">No logs found matching your criteria.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
