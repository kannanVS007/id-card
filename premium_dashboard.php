<?php 
require_once 'auth_check.php'; 

if (!isset($_SESSION['is_premium']) || !$_SESSION['is_premium']) {
    header("Location: dashboard.php?error=premium_required");
    exit;
}

// Fetch custom layout to check if designed
$layout = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM custom_layouts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $layout = $stmt->fetch();
} catch (PDOException $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium ID Dashboard | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fdf2f8; /* Light pink/purple bg for premium feel */
            min-height: 100vh;
        }
        .premium-card {
            background: white;
            border: 1px solid #fbcfe8;
            transition: all 0.3s ease;
        }
        .premium-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .gradient-text {
            background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-premium { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); }
        @keyframes logo-load {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-logo-load {
            animation: logo-load 0.7s ease-out forwards;
        }
    </style>
</head>
<body class="pb-12">
    <!-- NAVBAR -->
    <nav class="sticky top-0 z-50 px-6 py-4 bg-white/80 backdrop-blur-md border-b border-pink-100 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-24 h-24 flex items-center justify-center transition-transform duration-300 hover:scale-110 cursor-pointer">
    <img 
        src="assets/images/trishul-logo.png"
        alt="Trishul Logo"
        class="w-24 h-24 object-contain"
    >
</div>

                <div>
                    <h1 class="text-xl font-black text-gray-800">Premium Designer</h1>
                    <p class="text-[10px] text-pink-500 font-bold uppercase tracking-widest">Advanced Layout Mode</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="text-xs font-bold text-gray-500 hover:text-pink-600">Standard Mode</a>
                <a href="designer.php" class="text-xs font-bold btn-premium text-white px-4 py-2 rounded-lg shadow-lg">Design Editor</a>
                <a href="logout.php" class="text-xs font-bold text-red-500">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 mt-10">
        <?php if (!$layout): ?>
            <div class="bg-amber-50 border-2 border-amber-200 p-6 rounded-3xl mb-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-4xl">🎨</span>
                    <div>
                        <h3 class="font-bold text-amber-900">No Layout Designed Yet</h3>
                        <p class="text-sm text-amber-700">You need to design your ID card layout first before you can generate cards in Premium Mode.</p>
                    </div>
                </div>
                <a href="designer.php" class="bg-amber-500 text-white px-6 py-3 rounded-2xl font-bold hover:bg-amber-600 transition shadow-lg">Open Designer</a>
            </div>
        <?php endif; ?>

        <div class="mb-12">
            <h2 class="text-4xl font-extrabold text-gray-900 mb-2">
                Premium <span class="gradient-text">Generation</span>
            </h2>
            <p class="text-gray-500 text-lg">Process ID cards using your custom visual templates.</p>
        </div>

        <div class="grid lg:grid-cols-12 gap-8 <?= !$layout ? 'opacity-50 pointer-events-none' : '' ?>">
            <!-- BULK UPLOAD -->
            <div class="lg:col-span-5">
                <div class="premium-card rounded-[2rem] p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center gap-2">
                        <span class="p-2 bg-pink-50 rounded-lg text-pink-500">📦</span>
                        Premium Bulk Processing
                    </h3>

                    <form action="process_bulk_custom.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-2">School Logo</label>
                            <input type="file" name="school_logo" accept="image/*" class="w-full border-2 border-pink-50 rounded-xl p-3 text-sm focus:border-pink-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-2">CSV Data File</label>
                            <input type="file" name="excel_file" accept=".csv" required class="w-full border-2 border-pink-50 rounded-xl p-3 text-sm focus:border-pink-300 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-2">Photos (ZIP)</label>
                            <input type="file" name="photos_zip" accept=".zip" required class="w-full border-2 border-pink-50 rounded-xl p-3 text-sm focus:border-pink-300 outline-none">
                        </div>
                        <button type="submit" class="w-full btn-premium text-white font-bold py-4 rounded-2xl transition-all shadow-xl shadow-pink-200">Generate Custom IDs</button>
                    </form>
                </div>
            </div>

            <!-- SINGLE ENTRY -->
            <div class="lg:col-span-7">
                <div class="premium-card rounded-[2rem] p-8 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center gap-2">
                        <span class="p-2 bg-purple-50 rounded-lg text-purple-500">👤</span>
                        Premium Manual Entry
                    </h3>

                    <form action="process_single_custom.php" method="POST" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-bold text-gray-600 mb-2">School Logo</label>
                            <input type="file" name="school_logo" accept="image/*" class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm focus:border-purple-300 outline-none">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-bold text-gray-600 mb-2">Student Photo</label>
                            <input type="file" name="photo" accept="image/*" required class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm focus:border-purple-300 outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <input type="text" name="school_name" placeholder="School Name" class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm uppercase">
                        </div>
                        <input type="text" name="student_name" placeholder="Full Name" required class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm uppercase">
                        <input type="text" name="parent_name" placeholder="Parent Name" required class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm uppercase">
                        <input type="date" name="dob" required class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm">
                        <input type="text" name="blood_group" placeholder="Blood Group" required class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm uppercase">
                        <input type="text" name="contact" placeholder="Contact/Phone" required class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm">
                        <input type="text" name="student_class" placeholder="Class/Grade" class="w-full border-2 border-purple-50 rounded-xl p-3 text-sm uppercase">
                        <textarea name="address" placeholder="Residential Address" rows="2" required class="sm:col-span-2 w-full border-2 border-purple-50 rounded-xl p-3 text-sm"></textarea>
                        <button type="submit" class="sm:col-span-2 w-full btn-premium text-white font-bold py-4 rounded-2xl shadow-xl shadow-purple-200">Generate Custom Card</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
