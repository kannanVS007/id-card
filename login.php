<?php
require_once 'config.php';
$error = '';
$info = '';
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
                $info = "Your account is under verification. Please try again later.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
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
body { font-family: 'Outfit', sans-serif; background: linear-gradient(135deg,#0f172a,#1e293b); }
.glass { background: rgba(255,255,255,0.06); backdrop-filter: blur(12px); }
</style>
</head>
<body class="min-h-screen flex items-center justify-center">
<div class="w-full max-w-md p-6">
<div class="glass rounded-2xl p-8 shadow-xl">
<h2 class="text-3xl font-bold text-white text-center mb-6"><?= PROJECT_NAME ?></h2>
<?php if ($error): ?>
<div class="bg-red-500/10 border border-red-500 text-red-400 p-3 rounded-xl mb-4 text-center">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>
<?php if ($info): ?>
<div class="bg-blue-500/10 border border-blue-500 text-blue-400 p-3 rounded-xl mb-4 text-center">
<?= htmlspecialchars($info) ?>
</div>
<?php endif; ?>
<form method="POST" class="space-y-4">
<input type="text" name="username" required class="w-full px-4 py-3 rounded-xl bg-white/10 text-white" placeholder="Username">
<div class="relative">
<input type="password" name="password" id="login_password" required
class="w-full px-4 py-3 pr-12 rounded-xl bg-white/10 text-white" placeholder="Password">
<button type="button" onclick="togglePassword('login_password', 'icon_login')"
class="absolute right-4 top-3 text-slate-400">
<svg id="icon_login" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
</svg>
</button>
</div>
<button class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold">Sign In</button>
</form>
</div>
</div>
<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />';
    }
}
</script>
</body>
</html>