<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $contact_number = trim($_POST['contact_number'] ?? '');
    $door_street    = trim($_POST['door_street'] ?? '');
    $area           = trim($_POST['area'] ?? '');
    $city_town      = trim($_POST['city_town'] ?? '');
    $state          = trim($_POST['state'] ?? '');
    $pincode        = trim($_POST['pincode'] ?? '');

    if (
        $username === '' || $email === '' || $password === '' || $confirm === '' ||
        $contact_number === '' || $door_street === '' || $area === '' ||
        $city_town === '' || $state === '' || $pincode === ''
    ) {
        $error = 'All fields are required';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    }
    elseif ($password !== $confirm) {
        $error = 'Passwords do not match';
    }
    else {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $email_parts = explode('@', $email);
            $domain = end($email_parts);

            $status = 'pending';
            if (in_array($domain, TRUSTED_DOMAINS)) {
                $status = 'active';
            }

            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, role, status, contact_number, door_street, area, city_town, state, pincode)
                 VALUES (?, ?, ?, 'user', ?, ?, ?, ?, ?, ?, ?)"
            );

            if ($stmt->execute([
                $username, $email, $hashed_password, $status,
                $contact_number, $door_street, $area, $city_town, $state, $pincode
            ])) {

                if ($status === 'active') {
                    $success = 'Registration successful! Your institutional account is active. You can login now.';
                } else {
                    $success = 'Registration successful! Your account is under verification and will be automatically activated within 5 minutes.';
                }
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | <?= PROJECT_NAME ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Outfit', sans-serif;
    background: linear-gradient(135deg,#0f172a,#1e293b);
}
.glass { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); }
.input-glass { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color:white; }
@keyframes register-logo {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}
.animate-register-logo {
    animation: register-logo 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
</style>
</head>

<body class="min-h-screen flex items-center justify-center">

<div class="w-full max-w-md p-6">
<div class="glass rounded-3xl p-8 shadow-xl">

<div class="flex flex-col items-center mb-8 animate-register-logo">
    <div class="w-24 h-24 flex items-center justify-center transition-transform duration-300 hover:scale-110 cursor-pointer">
    <img 
        src="assets/images/trishul-logo.png"
        alt="Trishul Logo"
        class="w-24 h-24 object-contain"
    >
</div>

    <h2 class="text-3xl font-bold text-white text-center italic mt-4">Create Account</h2>
</div>

<?php if ($error): ?>
<div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-xl mb-4 text-center">
<?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="bg-emerald-500/10 border border-emerald-500 text-emerald-400 p-4 rounded-xl mb-4 text-center">
<?= $success ?>
<div class="mt-4">
<a href="login.php" class="bg-emerald-600 text-white px-5 py-2 rounded-lg">Login Now</a>
</div>
</div>
<?php else: ?>

<form method="POST" class="space-y-4">

<input type="text" name="username" required class="w-full input-glass px-4 py-3 rounded-xl" placeholder="Username">
<input type="email" name="email" required class="w-full input-glass px-4 py-3 rounded-xl" placeholder="School Email">

<!-- PASSWORD WITH TOGGLE -->
<div class="relative">
<input type="password" name="password" id="reg_password" required
class="w-full input-glass px-4 py-3 pr-12 rounded-xl" placeholder="Password">
<button type="button" onclick="togglePassword('reg_password', 'icon_password')"
class="absolute right-4 top-3 text-slate-400">
<svg id="icon_password" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
</svg>
</button>
</div>

<!-- CONFIRM PASSWORD WITH TOGGLE -->
<div class="relative">
<input type="password" name="confirm_password" id="reg_confirm" required
class="w-full input-glass px-4 py-3 pr-12 rounded-xl" placeholder="Confirm Password">
<button type="button" onclick="togglePassword('reg_confirm', 'icon_confirm')"
class="absolute right-4 top-3 text-slate-400">
<svg id="icon_confirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
</svg>
</button>
</div>

<input type="text" name="contact_number" required class="w-full input-glass px-4 py-3 rounded-xl" placeholder="Contact Number">
<input type="text" name="door_street" required class="w-full input-glass px-4 py-3 rounded-xl" placeholder="Door No & Street">

<div class="grid grid-cols-2 gap-3">
<input type="text" name="area" required class="input-glass px-4 py-3 rounded-xl" placeholder="Area">
<input type="text" name="city_town" required class="input-glass px-4 py-3 rounded-xl" placeholder="City">
</div>

<div class="grid grid-cols-2 gap-3">
<input type="text" name="state" required class="input-glass px-4 py-3 rounded-xl" placeholder="State">
<input type="text" name="pincode" required class="input-glass px-4 py-3 rounded-xl" placeholder="Pincode">
</div>

<button class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold">Register</button>
</form>
<?php endif; ?>

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