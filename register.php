<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Basic validation
    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'All fields are required';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    }
    elseif ($password !== $confirm) {
        $error = 'Passwords do not match';
    }
    else {

        // Check existing user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = 'Username or email already exists';
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, role, status)
                 VALUES (?, ?, ?, 'user', 'pending')"
            );

            if ($stmt->execute([$username, $email, $hashed_password])) {

                $userId = $pdo->lastInsertId();

                // Log activity
                logActivity($userId, 'New User Registered (Pending Approval)');

                // Notify admin
                $subject = "New User Registration - Approval Required";
                $message = "
New user registered on " . PROJECT_NAME . "

Username: $username
Email: $email
Status: Pending Approval
Date: " . date('Y-m-d H:i:s') . "

Please login to admin panel to approve or reject.
";
                @mail(ADMIN_EMAIL, $subject, $message, "From: noreply@littlekrish.com");

                $success = 'Registration successful! Please wait for admin approval.';
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | <?= PROJECT_NAME ?></title>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Outfit', sans-serif;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.glass {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
}
.input-glass {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: white;
}
.input-glass:focus {
    background: rgba(255,255,255,0.1);
    border-color: #3b82f6;
    outline: none;
}
</style>
</head>

<body>

<div class="w-full max-w-md px-6">
    <div class="glass rounded-3xl p-8 shadow-xl">

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-white">Create Account</h2>
            <p class="text-slate-400 mt-2">Join the ID Card Platform</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-xl mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-500/10 border border-emerald-500 text-emerald-400 p-4 rounded-xl mb-6 text-center">
                <?= $success ?>
                <div class="mt-4">
                    <a href="login.php" class="bg-emerald-600 text-white px-5 py-2 rounded-lg inline-block">
                        Login Now
                    </a>
                </div>
            </div>
        <?php else: ?>

        <form method="POST" class="space-y-5">
            <input type="text" name="username" required
                   class="w-full input-glass px-4 py-3 rounded-xl"
                   placeholder="Username">

            <input type="email" name="email" required
                   class="w-full input-glass px-4 py-3 rounded-xl"
                   placeholder="School email">

            <input type="password" name="password" required
                   class="w-full input-glass px-4 py-3 rounded-xl"
                   placeholder="Password">

            <input type="password" name="confirm_password" required
                   class="w-full input-glass px-4 py-3 rounded-xl"
                   placeholder="Confirm password">

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition">
                Register
            </button>
        </form>

        <?php endif; ?>

        <div class="mt-8 pt-6 border-t border-white/10 text-center">
            <p class="text-slate-400">
                Already have an account?
                <a href="login.php" class="text-blue-500 hover:underline">Sign In</a>
            </p>
        </div>

    </div>
</div>

</body>
</html>
