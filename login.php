<?php
require_once 'config.php';

$error = '';
$info = ''; // ✅ ADDED

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'All fields are required';
    } else {

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            if ($user['status'] !== 'active') {
                if ($user['status'] === 'pending') {
                    // Timed Auto-Approval Logic
                    $regTime = strtotime($user['created_at']);
                    $now = time();
                    $diffMinutes = ($now - $regTime) / 60;

                    if ($diffMinutes >= AUTO_APPROVAL_MINUTES) {
                        // Auto-Approve now
                        $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$user['id']]);
                        
                        
                        // Proceed to success
                        $_SESSION['user_id']  = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role']     = $user['role'];
                        $_SESSION['email']    = $user['email'];
                        $_SESSION['is_premium'] = (bool)$user['is_premium'];
                        logActivity($user['id'], 'User Login (Timed Auto-Approve)');
                        header('Location: index.php');
                        exit;
                    } else {
                        $info = "Your account is under verification and will be automatically activated within 5 minutes. Please try again shortly.";
                    }
                } else {
                    $error = 'Your account has been disabled by admin.';
                }
            } else {
                // Login success
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['email']    = $user['email'];
                $_SESSION['is_premium'] = (bool)$user['is_premium'];


                logActivity($user['id'], 'User Login');

                header('Location: index.php');
                exit;
            }

        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | <?= PROJECT_NAME ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Outfit', sans-serif;
    background: linear-gradient(135deg,#0f172a,#1e293b);
}
.glass {
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(12px);
}
</style>
</head>

<body class="min-h-screen flex items-center justify-center">

<div class="w-full max-w-md p-6">
    <div class="glass rounded-2xl p-8 shadow-xl">

        <h2 class="text-3xl font-bold text-white text-center mb-2">
            <?= PROJECT_NAME ?>
        </h2>
        <p class="text-slate-400 text-center mb-8">
            Login to continue
        </p>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500 text-red-400 text-sm p-3 rounded-xl mb-4 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($info): ?>
            <div class="bg-blue-500/10 border border-blue-500 text-blue-400 text-sm p-3 rounded-xl mb-4 text-center">
                <?= htmlspecialchars($info) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="text-slate-300 text-sm">Username</label>
                <input type="text" name="username" required
                       class="w-full mt-1 px-4 py-3 rounded-xl bg-white/10 text-white outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="text-slate-300 text-sm">Password</label>
                <input type="password" name="password" required
                       class="w-full mt-1 px-4 py-3 rounded-xl bg-white/10 text-white outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
                Sign In
            </button>
        </form>

        <!-- ✅ REGISTER LINK (NEW USER) -->
        <div class="mt-6 text-center">
            <p class="text-slate-400 text-sm">
                New user?
                <a href="register.php" class="text-blue-500 hover:underline font-semibold">
                    Create an account
                </a>
            </p>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            Admin support: <?= ADMIN_EMAIL ?>
        </p>

    </div>
</div>

</body>
</html>
