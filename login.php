<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === AUTH_USER && $password === AUTH_PASS) {
        $_SESSION['authenticated'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .input-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }
        .input-glass:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>
<body>
    <!-- Background Blobs -->
    <div class="absolute top-0 -left-4 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
    <div class="absolute top-0 -right-4 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-teal-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>

    <div class="relative w-full max-w-md px-6">
        <div class="glass rounded-3xl p-8">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-2xl mb-4 shadow-lg shadow-blue-500/30">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-4.514A11.042 11.042 0 0010 7.036V5a2 2 0 012-2h0a2 2 0 012 2v2.036c.742.069 1.467.19 2.16.357m-9.571 3.514L4.3 15M12 11c0-3.517 1.009-6.799 2.753-9.571m3.44 4.514L20.3 11m-8 0L7.4 15m4.6-4L16.6 15"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Welcome Back</h2>
                <p class="text-slate-400 mt-2">Sign in to manage ID cards</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 text-sm p-4 rounded-xl mb-6 text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                    <input type="text" name="username" required 
                           class="w-full input-glass rounded-xl px-4 py-3 text-sm" 
                           placeholder="Enter your username">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full input-glass rounded-xl px-4 py-3 text-sm" 
                           placeholder="••••••••">
                </div>
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-blue-600/20">
                    Sign In
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-white/10 text-center">
                <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">
                    System Protected & Secure
                </p>
            </div>
        </div>
    </div>
</body>
</html>
