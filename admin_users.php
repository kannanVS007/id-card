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

    case 'disable':
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Deactivated user ID {$userId}");
        break;

    case 'enable':
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Reactivated user ID {$userId}");
        break;

    case 'delete':
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Deleted user ID {$userId}");
        break;

    case 'toggle_premium':
        $stmt = $pdo->prepare("UPDATE users SET is_premium = NOT is_premium WHERE id = ?");
        $stmt->execute([$userId]);
        logActivity($_SESSION['user_id'], "Toggled premium status for user ID {$userId}");
        
        if ($userId === $_SESSION['user_id']) {
            $_SESSION['is_premium'] = !$_SESSION['is_premium'];
        }
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
        "SELECT id, username, email, role, status, is_premium, created_at, 
                contact_number, door_street, area, city_town, state, pincode
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
    <style>
        body { font-family: 'Outfit', sans-serif; }
        
        /* Premium Toggle Switch Styling */
        .premium-switch {
            position: relative;
            display: inline-block;
            width: 56px;
            height: 28px;
        }
        
        .premium-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .premium-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
            transition: 0.4s;
            border-radius: 28px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .premium-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        input:checked + .premium-slider {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
            box-shadow: 0 0 12px rgba(251, 191, 36, 0.4);
        }
        
        input:checked + .premium-slider:before {
            transform: translateX(28px);
            background: linear-gradient(135deg, #fef3c7, #ffffff);
        }
        
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
            <a href="admin_users.php" class="block p-3 bg-blue-600 rounded-xl font-semibold">User Management</a>
            <a href="admin_logs.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">Generation Logs</a>
            <a href="dashboard.php" class="block p-3 hover:bg-slate-800 rounded-xl transition">Main System</a>
            <div class="pt-4 border-t border-slate-800 mt-4">
                <a href="logout.php" class="block p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition">Logout</a>
            </div>
        </nav>
    </div>

    <div class="flex-1 flex flex-col min-h-screen">
        <!-- Mobile Header -->
        <div class="md:hidden bg-white border-b border-slate-200 p-4 flex items-center justify-between">
            <button class="mobile-menu-btn text-slate-700" onclick="toggleMenu()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-lg font-bold text-slate-800">User Management</h1>
            <div class="w-6"></div>
        </div>

        <div class="flex-1 p-4 md:p-8 overflow-y-auto">
            <header class="mb-6 md:mb-8 hidden md:block">
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800">User Management</h1>
                <p class="text-slate-500">Approve new schools or manage existing accounts</p>
            </header>

            <!-- Mobile Cards View -->
            <div class="md:hidden space-y-4">
                <?php foreach ($users as $u): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="font-bold text-slate-800"><?= $u['username'] ?></div>
                            <div class="text-sm text-slate-500 break-all"><?= $u['email'] ?></div>
                        </div>
                        <span class="px-2 py-1 rounded-md text-xs font-bold uppercase ml-2 <?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                            <?= $u['role'] ?>
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-3 mb-3">
                        <?php if ($u['status'] === 'pending'): ?>
                            <span class="px-2 py-1 rounded-md text-xs font-bold uppercase bg-amber-100 text-amber-700">Pending</span>
                        <?php elseif ($u['status'] === 'active'): ?>
                            <span class="px-2 py-1 rounded-md text-xs font-bold uppercase bg-emerald-100 text-emerald-700">Active</span>
                        <?php else: ?>
                            <span class="px-2 py-1 rounded-md text-xs font-bold uppercase bg-red-100 text-red-700">Inactive</span>
                        <?php endif; ?>
                        
                        <span class="text-xs text-slate-500"><?= date('M d, Y', strtotime($u['created_at'])) ?></span>
                    </div>

                    <!-- Premium Toggle -->
                    <div class="flex items-center justify-between mb-3 p-3 rounded-xl bg-gradient-to-r <?= $u['is_premium'] ? 'from-amber-50 to-yellow-50 border border-amber-200' : 'from-slate-50 to-gray-50 border border-slate-200' ?>">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl"><?= $u['is_premium'] ? '👑' : '⭐' ?></span>
                            <span class="text-sm font-semibold <?= $u['is_premium'] ? 'text-amber-700' : 'text-slate-600' ?>">
                                <?= $u['is_premium'] ? 'Premium Member' : 'Standard Member' ?>
                            </span>
                        </div>
                        <label class="premium-switch">
                            <input type="checkbox" <?= $u['is_premium'] ? 'checked' : '' ?> 
                                   onchange="window.location.href='?action=toggle_premium&id=<?= $u['id'] ?>'">
                            <span class="premium-slider"></span>
                        </label>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 mb-3">
                        <?php if ($u['status'] === 'pending'): ?>
                            <a href="?action=approve&id=<?= $u['id'] ?>" class="flex-1 text-center px-3 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-bold hover:bg-emerald-200 transition">Approve</a>
                        <?php elseif ($u['status'] === 'active'): ?>
                            <a href="?action=disable&id=<?= $u['id'] ?>" class="flex-1 text-center px-3 py-2 bg-amber-100 text-amber-700 rounded-lg text-sm font-bold hover:bg-amber-200 transition">Disable</a>
                        <?php else: ?>
                            <a href="?action=enable&id=<?= $u['id'] ?>" class="flex-1 text-center px-3 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-bold hover:bg-emerald-200 transition">Enable</a>
                        <?php endif; ?>
                        
                        <?php if ($u['role'] !== 'admin'): ?>
                            <a href="?action=delete&id=<?= $u['id'] ?>" class="flex-1 text-center px-3 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-bold hover:bg-red-200 transition" onclick="return confirm('Really delete?')">Delete</a>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile Details Toggle -->
                    <button onclick="toggleMobileDetails('mob-details-<?= $u['id'] ?>', this)" 
                            class="w-full py-2 flex items-center justify-center gap-2 text-slate-500 font-semibold border-t border-slate-100 mt-2 pt-3 hover:text-blue-500 transition">
                        <span>View Details</span>
                        <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Mobile Details Body -->
                    <div id="mob-details-<?= $u['id'] ?>" class="hidden mt-4 space-y-4 pt-4 border-t border-slate-50">
                        <div class="bg-slate-50 p-4 rounded-xl space-y-3">
                            <div>
                                <div class="text-[10px] font-bold uppercase text-slate-400">Contact Number</div>
                                <div class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($u['contact_number'] ?: 'N/A') ?></div>
                            </div>
                            <div class="pt-2 border-t border-slate-200">
                                <div class="text-[10px] font-bold uppercase text-slate-400">Address</div>
                                <div class="text-sm font-semibold text-slate-700">
                                    <?= htmlspecialchars($u['door_street'] ?: 'N/A') ?>,<br>
                                    <?= htmlspecialchars($u['area'] ?: 'N/A') ?>,<br>
                                    <?= htmlspecialchars($u['city_town'] ?: 'N/A') ?>,<br>
                                    <?= htmlspecialchars($u['state']) ?> - <?= htmlspecialchars($u['pincode']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">User Details</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Premium</th>
                                <th class="px-6 py-4">Registered</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                                <th class="px-6 py-4 text-center">Details</th>
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
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3 p-2 rounded-lg <?= $u['is_premium'] ? 'bg-gradient-to-r from-amber-50 to-yellow-50' : 'bg-slate-50' ?>">
                                        <span class="text-xl"><?= $u['is_premium'] ? '👑' : '⭐' ?></span>
                                        <label class="premium-switch" title="Toggle Premium">
                                            <input type="checkbox" <?= $u['is_premium'] ? 'checked' : '' ?> 
                                                   onchange="window.location.href='?action=toggle_premium&id=<?= $u['id'] ?>'">
                                            <span class="premium-slider"></span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if ($u['status'] === 'pending'): ?>
                                            <a href="?action=approve&id=<?= $u['id'] ?>" class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold hover:bg-emerald-200 transition">Approve</a>
                                        <?php elseif ($u['status'] === 'active'): ?>
                                            <a href="?action=disable&id=<?= $u['id'] ?>" class="px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold hover:bg-amber-200 transition">Disable</a>
                                        <?php else: ?>
                                            <a href="?action=enable&id=<?= $u['id'] ?>" class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold hover:bg-emerald-200 transition">Enable</a>
                                        <?php endif; ?>
                                        
                                        <?php if ($u['role'] !== 'admin'): ?>
                                            <a href="?action=delete&id=<?= $u['id'] ?>" class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-xs font-bold hover:bg-red-200 transition" onclick="return confirm('Really delete?')">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button onclick="toggleDetails('details-<?= $u['id'] ?>', this)" 
                                            class="text-blue-500 hover:text-blue-700 transition transform duration-200">
                                        <svg class="w-6 h-6 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <!-- Expanded Details Row -->
                            <tr id="details-<?= $u['id'] ?>" class="hidden bg-slate-50/50">
                                <td colspan="7" class="px-8 py-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white p-6 rounded-2xl border border-slate-100 glass shadow-sm">
                                        <div>
                                            <h4 class="text-xs font-bold uppercase text-slate-400 mb-4 tracking-wider">Contact Information</h4>
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-400">Contact Number</div>
                                                    <div class="font-semibold text-slate-700"><?= htmlspecialchars($u['contact_number'] ?: 'N/A') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold uppercase text-slate-400 mb-4 tracking-wider">Address Information</h4>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <div class="text-xs text-slate-400">Door No & Street</div>
                                                    <div class="font-semibold text-slate-700"><?= htmlspecialchars($u['door_street'] ?: 'N/A') ?></div>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-400">Area</div>
                                                    <div class="font-semibold text-slate-700"><?= htmlspecialchars($u['area'] ?: 'N/A') ?></div>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-400">City / Town</div>
                                                    <div class="font-semibold text-slate-700"><?= htmlspecialchars($u['city_town'] ?: 'N/A') ?></div>
                                                </div>
                                                <div>
                                                    <div class="text-xs text-slate-400">State & Pincode</div>
                                                    <div class="font-semibold text-slate-700"><?= htmlspecialchars($u['state']) ?> - <?= htmlspecialchars($u['pincode']) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
        
        let lastExpandedId = null;
        let lastMobExpandedId = null;

        function toggleDetails(id, btn) {
            const row = document.getElementById(id);
            const isHidden = row.classList.contains('hidden');
            
            if (lastExpandedId && lastExpandedId !== id) {
                const lastRow = document.getElementById(lastExpandedId);
                if (lastRow) {
                    lastRow.classList.add('hidden');
                    // Reset rotation for all detail buttons
                    document.querySelectorAll('button[onclick^="toggleDetails"]').forEach(b => b.classList.remove('rotate-180'));
                }
            }

            if (isHidden) {
                row.classList.remove('hidden');
                btn.classList.add('rotate-180');
                lastExpandedId = id;
            } else {
                row.classList.add('hidden');
                btn.classList.remove('rotate-180');
                lastExpandedId = null;
            }
        }

        function toggleMobileDetails(id, btn) {
            const body = document.getElementById(id);
            const isHidden = body.classList.contains('hidden');
            const svg = btn.querySelector('svg');
            const span = btn.querySelector('span');

            if (lastMobExpandedId && lastMobExpandedId !== id) {
                const lastBody = document.getElementById(lastMobExpandedId);
                if (lastBody) {
                    lastBody.classList.add('hidden');
                    // Reset all other mobile detail buttons
                    document.querySelectorAll('button[onclick^="toggleMobileDetails"]').forEach(mb => {
                        mb.querySelector('svg').classList.remove('rotate-180');
                        mb.querySelector('span').textContent = 'View Details';
                    });
                }
            }

            if (isHidden) {
                body.classList.remove('hidden');
                svg.classList.add('rotate-180');
                span.textContent = 'Hide Details';
                lastMobExpandedId = id;
            } else {
                body.classList.add('hidden');
                svg.classList.remove('rotate-180');
                span.textContent = 'View Details';
                lastMobExpandedId = null;
            }
        }

        // Close menu when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                menuBtn && !menuBtn.contains(event.target) &&
                sidebar.classList.contains('active')) {
                toggleMenu();
            }
        });
    </script>
</body>
</html>