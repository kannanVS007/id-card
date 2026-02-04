<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= PROJECT_NAME ?> | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --accent: #f59e0b;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.1) 0px, transparent 50%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
        }
        .input-premium {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.2s ease;
        }
        .input-premium:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.39);
        }
        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.23);
        }
        .btn-accent {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 14px 0 rgba(245, 158, 11, 0.39);
        }
        .nav-blur {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
</head>
<body class="pb-12">

    <!-- NAVBAR -->
    <nav class="nav-blur sticky top-0 z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5L12 4l-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight"><?= PROJECT_NAME ?></h1>
                    <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest">Premium Management System</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-slate-400 hidden sm:block">Welcome, <b><?= AUTH_USER ?></b></span>
                <a href="logout.php" class="text-xs font-semibold bg-white/5 hover:bg-white/10 px-4 py-2 rounded-lg border border-white/10 transition">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 mt-10">
        
        <!-- HERO HEADER -->
        <div class="mb-12 text-center sm:text-left">
            <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-4">
                Redefining <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-teal-400">ID Generation</span>
            </h2>
            <p class="text-slate-400 text-lg max-w-2xl">
                A professional-grade suite for creating stunning, print-ready identification cards with ease.
            </p>
        </div>

        <div class="grid lg:grid-cols-12 gap-8">
            
            <!-- LEFT COLUMN: BULK UPLOAD -->
            <div class="lg:col-span-5">
                <div class="glass-card rounded-[2rem] p-8 h-full">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-3 bg-blue-500/10 rounded-2xl">
                            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Bulk Processing</h3>
                            <p class="text-sm text-slate-400">Generate multiple cards from Excel</p>
                        </div>
                    </div>

                    <form action="process_bulk.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div class="group">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Excel/CSV Data File</label>
                            <div class="relative">
                                <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" required
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="input-premium rounded-2xl p-4 flex items-center gap-3 group-hover:border-blue-500/50">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="text-sm text-slate-400">Select .xlsx or .csv file</span>
                                </div>
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Photos Archive (ZIP)</label>
                            <div class="relative">
                                <input type="file" name="photos_zip" accept=".zip" required
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="input-premium rounded-2xl p-4 flex items-center gap-3 group-hover:border-blue-500/50">
                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    <span class="text-sm text-slate-400">Upload .zip with student photos</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full btn-primary text-white font-bold py-4 rounded-2xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                            Start Bulk Generation
                        </button>
                    </form>

    <div class="mt-8 p-6 bg-white/5 rounded-2xl border border-white/5">
                        <h4 class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-3">Format Guide (Required)</h4>
                        <ul class="text-[13px] text-slate-400 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold">•</span>
                                <b>Format:</b> Must be <code class="text-slate-200">.CSV</code> (Comma Separated Values)
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold">•</span>
                                <b>Required Headers:</b> <code class="text-slate-200">name</code>, <code class="text-slate-200">grade</code>, <code class="text-slate-200">dob</code>, <code class="text-slate-200">fname</code>, <code class="text-slate-200">mob</code>, <code class="text-slate-200">add</code>, <code class="text-slate-200">bg</code>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-blue-500 font-bold">•</span>
                                <b>Photos:</b> ZIP file where filenames match the <code class="text-slate-200">mob</code> column (e.g., 9880838396.jpg)
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: SINGLE ENTRY -->
            <div class="lg:col-span-7">
                <div class="glass-card rounded-[2rem] p-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="p-3 bg-amber-500/10 rounded-2xl">
                            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Manual Entry</h3>
                            <p class="text-sm text-slate-400">Generate a card for an individual student</p>
                        </div>
                    </div>

                    <form action="process_single.php" method="POST" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2 group">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Student Photo</label>
                            <input type="file" name="photo" accept="image/*" required
                                   class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Student Full Name</label>
                            <input type="text" name="student_name" placeholder="John Doe" required
                                   class="w-full input-premium rounded-xl p-3 text-sm uppercase">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Parent Name</label>
                            <input type="text" name="parent_name" placeholder="Richard Roe" required
                                   class="w-full input-premium rounded-xl p-3 text-sm uppercase">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Date of Birth</label>
                            <input type="date" name="dob" required
                                   class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Blood Group</label>
                            <input type="text" name="blood_group" placeholder="B+ve" required
                                   class="w-full input-premium rounded-xl p-3 text-sm uppercase">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Contact Number</label>
                            <input type="text" name="phone" placeholder="+91 00000 00000" required
                                   class="w-full input-premium rounded-xl p-3 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Academic Class</label>
                            <select name="student_class" required
                                    class="w-full input-premium rounded-xl p-3 text-sm">
                                <option value="" class="bg-slate-800">Select Class</option>
                                <option value="TODD CARE" class="bg-slate-800">Todd Care</option>
                                <option value="NURSERY" class="bg-slate-800">Nursery</option>
                                <option value="JR. KG" class="bg-slate-800">Jr. KG</option>
                                <option value="SR. KG" class="bg-slate-800">Sr. KG</option>
                                <option value="DAY CARE" class="bg-slate-800">Day Care</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Residential Address</label>
                            <textarea name="address" placeholder="Full residential address here..." rows="2" required
                                      class="w-full input-premium rounded-xl p-3 text-sm"></textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full btn-accent text-white font-bold py-4 rounded-2xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                                Generate Individual Card
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- LOGO UPLOAD SECTION -->
        <div class="mt-8">
            <div class="glass-card rounded-3xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-500/10 rounded-xl">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-300">Customize school identification</p>
                    </div>
                    <form action="upload_logo.php" method="POST" enctype="multipart/form-data" class="flex gap-3">
                        <input type="file" name="logo" accept="image/*" required class="hidden" id="logo-input">
                        <label for="logo-input" class="cursor-pointer text-xs font-semibold bg-white/5 hover:bg-white/10 px-4 py-2 rounded-lg border border-white/10 transition">
                            Choose Brand Logo
                        </label>
                        <button type="submit" class="text-xs font-bold bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-lg transition-all shadow-lg shadow-emerald-500/20">
                            Update Brand
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</body>
</html>