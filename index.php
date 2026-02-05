<?php require_once 'auth_check.php'; ?>
<?php
// Default design in session
if (!isset($_SESSION['design_id'])) $_SESSION['design_id'] = 1;
$design_id = $_SESSION['design_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= PROJECT_NAME ?> | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #3b82f6; --primary-dark: #2563eb; --accent: #f59e0b; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            background-image: radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.1) 0px, transparent 50%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover { border-color: rgba(255, 255, 255, 0.2); transform: translateY(-4px); }
        .input-premium {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s ease;
        }
        .input-premium:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); outline: none; }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); }
        .btn-accent { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    </style>
</head>
<body class="pb-12">
    <!-- NAVBAR -->
    <nav class="sticky top-0 z-50 px-6 py-4 bg-slate-900/80 backdrop-blur-md border-b border-white/5">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5L12 4l-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight"><?= PROJECT_NAME ?></h1>
                    <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest">Active Design: #<?= $design_id ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <?php if (isAdmin()): ?>
                    <a href="admin_dashboard.php" class="text-xs font-bold bg-blue-600 text-white px-4 py-2 rounded-lg">Admin Panel</a>
                <?php endif; ?>
                <a href="design_showcase.php" class="text-xs font-bold bg-white/10 text-white px-4 py-2 rounded-lg border border-white/10">Change Design</a>
                <a href="logout.php" class="text-xs font-semibold bg-red-500/10 text-red-400 px-4 py-2 rounded-lg border border-red-500/20">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 mt-10">
        <div class="mb-12 text-center sm:text-left flex justify-between items-end">
            <div>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-4">
                    Ready to <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-teal-400">Generate</span>
                </h2>
                <p class="text-slate-400 text-lg max-w-2xl">Create professional ID cards with your selected design.</p>
            </div>
            <div class="hidden lg:block">
                 <div class="bg-blue-600/10 border border-blue-500/20 p-4 rounded-2xl">
                    <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest mb-1">Selected Design</p>
                    <p class="text-white font-bold">Design Style #<?= $design_id ?></p>
                 </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-12 gap-8">
            <!-- LEFT COLUMN: BULK UPLOAD -->
            <div class="lg:col-span-5">
                <div class="glass-card rounded-[2rem] p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-3 bg-blue-500/10 rounded-2xl">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Bulk Processing</h3>
                    </div>

                    <form action="process_bulk.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">School Logo (Optional)</label>
                            <input type="file" name="school_logo" accept="image/*" class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Academic Year</label>
                            <input type="text" name="academic_year" value="2025-26" class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">CSV Data File</label>
                            <input type="file" name="excel_file" accept=".csv" required class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Photos (ZIP)</label>
                            <input type="file" name="photos_zip" accept=".zip" required class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>
                        <button type="submit" class="w-full btn-primary text-white font-bold py-4 rounded-2xl transition-all">Start Bulk Generation</button>
                    </form>
                </div>
            </div>

            <!-- RIGHT COLUMN: SINGLE ENTRY -->
            <div class="lg:col-span-7">
                <div class="glass-card rounded-[2rem] p-8 h-full">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-3 bg-amber-500/10 rounded-2xl">
                            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Manual Entry</h3>
                    </div>

                    <form action="process_single.php" method="POST" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-slate-300 mb-2">School Logo</label>
                            <input type="file" name="school_logo" accept="image/*" class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Academic Year</label>
                            <input type="text" name="academic_year" value="2025-26" class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Student Photo</label>
                            <input type="file" name="photo" accept="image/*" required class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>
                        <input type="text" name="student_name" placeholder="Full Name" required class="w-full input-premium rounded-xl p-3 text-sm uppercase">
                        <input type="text" name="parent_name" placeholder="Parent Name" required class="w-full input-premium rounded-xl p-3 text-sm uppercase">
                        <input type="date" name="dob" required class="w-full input-premium rounded-xl p-3 text-sm">
                        <input type="text" name="blood_group" placeholder="Blood Group" required class="w-full input-premium rounded-xl p-3 text-sm uppercase">
                        <input type="text" name="phone" placeholder="Phone" required class="w-full input-premium rounded-xl p-3 text-sm">
                        <select name="student_class" required class="w-full input-premium rounded-xl p-3 text-sm bg-slate-800">
                            <option value="">Select Class</option>
                            <option value="NURSERY">Nursery</option><option value="JR. KG">Jr. KG</option><option value="SR. KG">Sr. KG</option>
                        </select>
                        <textarea name="address" placeholder="Address" rows="2" required class="sm:col-span-2 w-full input-premium rounded-xl p-3 text-sm"></textarea>
                        <button type="submit" class="sm:col-span-2 w-full btn-accent text-white font-bold py-4 rounded-2xl transition-all">Generate Card</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Just copy this single box after your heading -->
<div class="bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/30 rounded-xl p-4 mb-6">
    <p class="text-sm font-semibold text-white mb-3">📋 File Format Guide</p>
    
    <div class="text-lg text-slate-300 space-y-2">
        <p><span class="text-blue-400 font-semibold">CSV Columns:</span> student_name, parent_name, dob (DD/MM/YYYY), blood_group, phone, student_class, address, photo_filename</p>
        
        <p><span class="text-amber-400 font-semibold">Photos ZIP:</span> All photos in JPG/PNG, filenames must match CSV, no folders inside ZIP</p>
        
        <p class="text-green-300">💡 <a href="sample_data.csv" download class="underline hover:text-green-200">Download Sample Files</a></p>
    </div>
</div>
    </div>
</body>
</html>
