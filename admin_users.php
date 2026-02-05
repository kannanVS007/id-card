<?php
require_once 'config.php';
require_once 'auth_check.php';

requireAdmin();

// ===============================
// Handle User Actions
// ===============================
if ($pdo && isset($_GET['action'], $_GET['id'])) {

    $userId = (int) $_GET['id'];
    $action = $_GET['action'];

   switch ($action) {

    case 'approve':
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Approved user ID {$userId}");
        break;

    case 'reject':
        $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Rejected user ID {$userId}");
        break;

    case 'disable': // FIXED
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Deactivated user ID {$userId}");
        break;

    case 'enable': // FIXED
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Reactivated user ID {$userId}");
        break;

    case 'delete': // ✅ MISSING PART
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Deleted user ID {$userId}");
        break;
}


    header('Location: admin_users.php');
    exit;
}

// ===============================
// Fetch All Users
// ===============================
$users = [];

if ($pdo) {
    $stmt = $pdo->query(
        "SELECT id, username, email, role, status, created_at
         FROM users
         ORDER BY created_at DESC"
    );
    $users = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | <?= PROJECT_NAME ?></title>
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
            <a href="admin_users.php" class="block p-3 bg-blue-600 rounded-xl font-semibold">User Management</a>
            <a href="admin_logs.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">Generation Logs</a>
            <a href="index.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">Main System</a>
            <div class="pt-4 border-t border-slate-800 mt-4">
                <a href="logout.php" class="block p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition">Logout</a>
            </div>
        </nav>
    </div>

    <div class="flex-1 p-8 overflow-y-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800">User Management</h1>
            <p class="text-slate-500">Approve new schools or manage existing accounts</p>
        </header>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4">User Details</th>
                        <th class="px-6 py-4">Role</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Registered</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800"><?= $u['username'] ?></div>
                            <div class="text-sm text-slate-500"><?= $u['email'] ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-md text-xs font-bold uppercase <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($u['status'] === 'pending'): ?>
                                <span class="px-2 py-1 rounded-md text-xs font-bold uppercase bg-amber-100 text-amber-700">Pending</span>
                            <?php elseif ($u['status'] === 'active'): ?>
                                <span class="px-2 py-1 rounded-md text-xs font-bold uppercase bg-emerald-100 text-emerald-700">Active</span>
                            <?php else: ?>
                                <span class="px-2 py-1 rounded-md text-xs font-bold uppercase bg-red-100 text-red-700">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <?php if ($u['status'] === 'pending'): ?>
                                <a href="?action=approve&id=<?= $u['id'] ?>" class="text-emerald-600 font-bold hover:underline">Approve</a>
                            <?php elseif ($u['status'] === 'active'): ?>
                                <a href="?action=disable&id=<?= $u['id'] ?>" class="text-amber-600 font-bold hover:underline">Disable</a>
                            <?php else: ?>
                                <a href="?action=enable&id=<?= $u['id'] ?>" class="text-emerald-600 font-bold hover:underline">Enable</a>
                            <?php endif; ?>
                            
                            <?php if ($u['role'] !== 'admin'): ?>
                                <a href="?action=delete&id=<?= $u['id'] ?>" class="text-red-600 font-bold hover:underline" onclick="return confirm('Really delete?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
