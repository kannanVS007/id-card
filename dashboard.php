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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --surface: rgba(30, 41, 59, 0.4);
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #f8fafc;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(245, 158, 11, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(139, 92, 246, 0.08) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        
        .content-wrapper {
            position: relative;
            z-index: 1;
        }
        
        /* Premium Glass Cards */
        .glass-premium {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(30, 41, 59, 0.3) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass-premium:hover {
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-6px);
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        
        /* Premium Inputs */
        .input-luxury {
            background: rgba(15, 23, 42, 0.7);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            color: white;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .input-luxury:focus {
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            outline: none;
        }
        
        .input-luxury::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }
        
        /* Premium Buttons */
        .btn-premium {
            position: relative;
            overflow: hidden;
            font-weight: 600;
            letter-spacing: 0.025em;
            transition: all 0.3s ease;
        }
        
        .btn-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-premium:hover::before {
            left: 100%;
        }
        
        .btn-primary-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #6366f1 100%);
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }
        
        .btn-accent-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 50%, #f59e0b 100%);
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(99, 102, 241, 0.4);
        }
        
        /* Icon Containers */
        .icon-box {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.1) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
        }
        
        .icon-box-accent {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(239, 68, 68, 0.1) 100%);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        
        /* Navbar Glass Effect */
        .navbar-glass {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        }
        
        /* Badge Styles */
        .badge-premium {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border: 1px solid rgba(99, 102, 241, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* File Upload Styling */
        input[type="file"] {
            cursor: pointer;
        }
        
        input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }
        
        input[type="file"]::file-selector-button:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3) 0%, rgba(139, 92, 246, 0.3) 100%);
            border-color: rgba(99, 102, 241, 0.5);
        }
        
        /* Responsive Typography */
        @media (max-width: 640px) {
            .hero-title {
                font-size: 2rem;
                line-height: 1.2;
            }
        }
        
        /* Premium Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.5);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #8b5cf6 0%, #6366f1 100%);
        }
        
        @keyframes logo-load {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-logo-load {
            animation: logo-load 0.7s ease-out forwards;
        }
        .shadow-glow {
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.1);
        }
        .shadow-glow:hover {
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- PREMIUM NAVBAR -->
        <nav class="navbar-glass sticky top-0 z-50 px-4 sm:px-6 lg:px-8 py-4">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <!-- Logo Section -->
                    <div class="flex items-center gap-3">
                       <div class="w-14 h-14 flex items-center justify-center transition-transform duration-300 ease-out hover:scale-105 animate-logo-load">
    <img 
        src="assets/images/trishul-logo.png"
        alt="Trishul Logo"
        class="w-14 h-14 object-contain"
    >
</div>

                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold tracking-tight bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent"><?= PROJECT_NAME ?></h1>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                                <p class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider">Design #<?= $design_id ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Buttons -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
                        <?php if (isAdmin()): ?>
                            <a href="admin_dashboard.php" class="text-xs font-semibold bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2.5 rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 transition-all hover:scale-105">
                                Admin Panel
                            </a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['is_premium']) && $_SESSION['is_premium']): ?>
                            <a href="premium_dashboard.php" class="text-xs font-semibold bg-gradient-to-r from-teal-600 to-emerald-600 text-white px-4 py-2.5 rounded-xl shadow-lg shadow-teal-600/30 hover:shadow-teal-600/50 transition-all hover:scale-105">
                                Premium Designer
                            </a>
                        <?php endif; ?>
                        <a href="design_showcase.php" class="text-xs font-semibold bg-white/10 hover:bg-white/20 text-white px-4 py-2.5 rounded-xl border border-white/10 hover:border-white/20 transition-all backdrop-blur-sm">
                            Change Design
                        </a>
                        <a href="logout.php" class="text-xs font-semibold bg-red-500/10 hover:bg-red-500/20 text-red-400 px-4 py-2.5 rounded-xl border border-red-500/20 hover:border-red-500/30 transition-all">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <!-- Hero Section -->
            <div class="mb-10 sm:mb-16">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6">
                    <div class="flex-1">
                        <div class="inline-block mb-4">
                            <span class="bg-gradient-to-r from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 px-4 py-2 rounded-full text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                                ID Card Generator
                            </span>
                        </div>
                        <h2 class="hero-title text-4xl sm:text-5xl lg:text-6xl font-black text-white mb-4 sm:mb-6 leading-tight">
                            Create Premium
                            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">
                                ID Cards Instantly
                            </span>
                        </h2>
                        <p class="text-slate-400 text-base sm:text-lg max-w-2xl leading-relaxed">
                            Professional student ID cards with your selected design template. Single or bulk generation available.
                        </p>
                    </div>
                    
                    <div class="hidden lg:block">
                        <div class="badge-premium p-6 rounded-2xl">
                            <p class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest mb-2">Active Template</p>
                            <p class="text-white font-bold text-2xl">Design #<?= $design_id ?></p>
                            <div class="mt-3 flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-green-400 animate-pulse"></div>
                                <span class="text-xs text-green-300 font-medium">Ready to Generate</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="grid lg:grid-cols-12 gap-6 sm:gap-8 mb-8">
                <!-- BULK UPLOAD CARD -->
                <div class="lg:col-span-5">
                    <div class="glass-premium rounded-3xl p-6 sm:p-8 h-full">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="icon-box p-4 rounded-2xl">
                                <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl sm:text-2xl font-bold text-white">Bulk Processing</h3>
                                <p class="text-xs text-slate-400 mt-1">Upload CSV & Photos</p>
                            </div>
                        </div>

                        <form action="process_bulk.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-3">School Logo (Optional)</label>
                                <input type="file" name="school_logo" accept="image/*" class="w-full input-luxury rounded-xl p-3.5 text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Academic Year</label>
                                <input type="text" name="academic_year" value="2025-26" class="w-full input-luxury rounded-xl p-3.5 text-sm font-medium">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-3">CSV Data File *</label>
                                <input type="file" name="excel_file" accept=".csv" required class="w-full input-luxury rounded-xl p-3.5 text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Photos ZIP Archive *</label>
                                <input type="file" name="photos_zip" accept=".zip" required class="w-full input-luxury rounded-xl p-3.5 text-sm">
                            </div>
                            
                            <button type="submit" class="w-full btn-premium btn-primary-gradient text-white font-bold py-4 rounded-xl shadow-xl shadow-indigo-500/30 hover:shadow-indigo-500/50 mt-6">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Start Bulk Generation
                                </span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- MANUAL ENTRY CARD -->
                <div class="lg:col-span-7">
                    <div class="glass-premium rounded-3xl p-6 sm:p-8 h-full">
                        <div class="flex items-center gap-4 mb-8">
                            <div class="icon-box-accent p-4 rounded-2xl">
                                <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl sm:text-2xl font-bold text-white">Manual Entry</h3>
                                <p class="text-xs text-slate-400 mt-1">Single Student Card</p>
                            </div>
                        </div>

                        <form action="process_single.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">School Logo</label>
                                <input type="file" name="school_logo" accept="image/*" class="w-full input-luxury rounded-xl p-3.5 text-sm">
                            </div>
                            
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Academic Year</label>
                                <input type="text" name="academic_year" value="2025-26" class="w-full input-luxury rounded-xl p-3.5 text-sm font-medium">
                            </div>
                            
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Student Photo *</label>
                                <input type="file" name="photo" accept="image/*" required class="w-full input-luxury rounded-xl p-3.5 text-sm">
                            </div>
                            
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Full Name *</label>
                                <input type="text" name="student_name" placeholder="STUDENT NAME" required class="w-full input-luxury rounded-xl p-3.5 text-sm uppercase font-medium">
                            </div>
                            
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Parent Name *</label>
                                <input type="text" name="parent_name" placeholder="PARENT NAME" required class="w-full input-luxury rounded-xl p-3.5 text-sm uppercase font-medium">
                            </div>
                            
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Date of Birth *</label>
                                <input type="date" name="dob" required class="w-full input-luxury rounded-xl p-3.5 text-sm">
                            </div>
                            
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Blood Group *</label>
                                <input type="text" name="blood_group" placeholder="A+" required class="w-full input-luxury rounded-xl p-3.5 text-sm uppercase font-medium">
                            </div>
                            
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Phone Number *</label>
                                <input type="text" name="phone" placeholder="+91 XXXXX XXXXX" required class="w-full input-luxury rounded-xl p-3.5 text-sm">
                            </div>
                            
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Class *</label>
                                <select name="student_class" required class="w-full input-luxury rounded-xl p-3.5 text-sm">
                                    <option value="">Select Class</option>
                                    <option value="NURSERY">Nursery</option>
                                    <option value="JR. KG">Jr. KG</option>
                                    <option value="SR. KG">Sr. KG</option>
                                </select>
                            </div>
                            
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-slate-300 mb-3">Address *</label>
                                <textarea name="address" placeholder="Enter complete address..." rows="2" required class="w-full input-luxury rounded-xl p-3.5 text-sm resize-none"></textarea>
                            </div>
                            
                            <button type="submit" class="sm:col-span-2 w-full btn-premium btn-accent-gradient text-white font-bold py-4 rounded-xl shadow-xl shadow-amber-500/30 hover:shadow-amber-500/50 mt-2">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Generate Single Card
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- FORMAT GUIDE -->
            <div class="info-box glass-premium rounded-2xl p-6 sm:p-8 border-l-4 border-indigo-500">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-indigo-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <h4 class="text-lg sm:text-xl font-bold text-white mb-4">📋 File Format Requirements</h4>
                        
                        <div class="grid sm:grid-cols-2 gap-4 mb-5">
                            <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4">
                                <p class="text-sm font-bold text-indigo-300 mb-2">CSV File Structure</p>
                                <p class="text-xs text-slate-300 leading-relaxed">
                                    Columns: student_name, parent_name, dob (DD/MM/YYYY), blood_group, phone, student_class, address, photo_filename
                                </p>
                            </div>
                            
                            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4">
                                <p class="text-sm font-bold text-amber-300 mb-2">Photos ZIP Archive</p>
                                <p class="text-xs text-slate-300 leading-relaxed">
                                    JPG/PNG format, filenames must match CSV entries, no subfolders allowed
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 inline-block">
                            <a href="sample_data.csv" download class="flex items-center gap-2 text-green-300 hover:text-green-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="font-semibold text-sm">Download Sample Files & Templates</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>