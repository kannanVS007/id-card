<?php
require_once 'auth_check.php';

if (!isset($_SESSION['is_premium']) || !$_SESSION['is_premium']) {
    header("Location: dashboard.php?error=premium_required");
    exit;
}

// Fetch existing layout if any
$layout = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM custom_layouts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $layout = $stmt->fetch();
} catch (PDOException $e) {
    // Silent fail
}

$layout_json = $layout ? $layout['layout_json'] : '{}';
$bg_front = $layout ? $layout['background_front'] : '';
$bg_back = $layout ? $layout['background_back'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium ID Designer | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ec4899;
            --primary-dark: #db2777;
            --accent: #f472b6;
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #fdf2f8 100%);
            color: #1f2937;
            min-height: 100vh;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(236, 72, 153, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(219, 39, 119, 0.06) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .content-wrapper {
            position: relative;
            z-index: 1;
        }
        
        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(236, 72, 153, 0.15);
            box-shadow: 0 8px 32px rgba(236, 72, 153, 0.15);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            border-color: rgba(236, 72, 153, 0.25);
        }
        
        /* Canvas Container */
        .canvas-container { 
            margin: 0 auto;
            box-shadow: 0 20px 60px rgba(236, 72, 153, 0.25);
            border: 3px solid rgba(236, 72, 153, 0.3);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
            background: white;
        }
        
        /* Tab Buttons */
        .tab-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .tab-btn.active { 
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(236, 72, 153, 0.4);
        }
        
        /* Element Buttons */
        .element-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .element-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(236, 72, 153, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }
        
        .element-btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .element-btn:hover {
            transform: translateY(-4px);
            border-color: currentColor !important;
        }
        
        /* Input Fields */
        .input-luxury {
            background: rgba(255, 255, 255, 0.9);
            border: 1.5px solid rgba(236, 72, 153, 0.2);
            color: #1f2937;
            transition: all 0.3s ease;
        }
        
        .input-luxury:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.15);
            outline: none;
        }
        
        /* Premium Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(236, 72, 153, 0.05);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #ec4899 0%, #db2777 100%);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #db2777 0%, #ec4899 100%);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Cropper Container */
        .crop-container {
            position: relative;
            max-width: 100%;
            max-height: 60vh;
            margin: 0 auto;
        }
        
        .crop-container img {
            max-width: 100%;
            display: block;
        }
        
        /* Responsive Sidebar */
        @media (max-width: 1024px) {
            .sidebar-panel {
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                z-index: 100;
                transition: left 0.3s ease;
                overflow-y: auto;
                width: 90%;
                max-width: 400px;
            }
            
            .sidebar-panel.open {
                left: 0;
            }
            
            .overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.7);
                z-index: 99;
                backdrop-filter: blur(4px);
            }
            
            .overlay.show {
                display: block;
            }
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            top: 6rem;
            right: 2rem;
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes logo-load {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-logo-load {
            animation: logo-load 0.7s ease-out forwards;
        }
        
        /* Image Library */
        .image-thumb {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .image-thumb:hover {
            transform: scale(1.05);
            border-color: #ec4899;
            box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
        }
        
        .image-thumb.selected {
            border-color: #ec4899;
            box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.2);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
</head>
<body>
    <div class="content-wrapper">
        <!-- Mobile Overlay -->
        <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
        
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-pink-200 px-4 sm:px-6 lg:px-8 py-4 shadow-xl">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <!-- Mobile Menu Button -->
                        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg bg-pink-50 hover:bg-pink-100 transition">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        
                        <div class="w-14 h-14 flex items-center justify-center transition-transform duration-300 ease-out hover:scale-105 animate-logo-load">
                            <img 
                                src="assets/images/trishul-logo.png"
                                alt="Trishul Logo"
                                class="w-14 h-14 object-contain"
                            >
                        </div>

                        <div>
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-pink-500 to-rose-500 bg-clip-text text-transparent">
                                Premium ID Designer
                            </h1>
                            <p class="text-xs text-gray-600 font-medium hidden sm:block">Professional Card Design Studio ✨</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <a href="dashboard.php" class="hidden sm:flex items-center gap-2 text-gray-600 hover:text-gray-900 font-medium transition px-3 sm:px-4 py-2 rounded-xl hover:bg-pink-50 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back
                        </a>
                        <button id="saveBtn" class="bg-gradient-to-r from-pink-500 to-rose-500 text-white px-4 sm:px-6 lg:px-8 py-2 sm:py-3 rounded-xl font-bold hover:shadow-2xl transition transform hover:scale-105 shadow-lg text-sm">
                            <span class="hidden sm:inline">💾 Save Layout</span>
                            <span class="sm:hidden">💾 Save</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-6 lg:gap-8 py-6 lg:py-8">
            <!-- Sidebar Controls -->
            <aside class="sidebar-panel w-80 lg:w-96 space-y-6 pb-20 lg:pb-6 glass-card p-4 rounded-2xl" id="sidebar">
                <!-- Add Elements -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center border border-pink-200">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Add Elements</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Logo Upload -->
                        <button onclick="openImageLibrary('logo')" class="element-btn flex flex-col items-center justify-center p-4 bg-amber-50 rounded-xl border-2 border-amber-200 hover:border-amber-400">
                            <svg class="w-7 h-7 mb-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            <span class="text-xs font-bold text-amber-700">School Logo</span>
                        </button>

                        <!-- Photo Upload -->
                        <button onclick="openImageLibrary('photo')" class="element-btn flex flex-col items-center justify-center p-4 bg-blue-50 rounded-xl border-2 border-blue-200 hover:border-blue-400">
                            <svg class="w-7 h-7 mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-xs font-bold text-blue-700">Student Photo</span>
                        </button>

                        <!-- Background -->
                        <button onclick="openBackgroundLibrary()" class="element-btn flex flex-col items-center justify-center p-4 bg-purple-50 rounded-xl border-2 border-purple-200 hover:border-purple-400">
                            <svg class="w-7 h-7 mb-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-xs font-bold text-purple-700">Background</span>
                        </button>

                        <!-- School Name -->
                        <button onclick="addField('text', 'SCHOOL NAME', 'school_name')" class="element-btn flex flex-col items-center justify-center p-4 bg-pink-50 rounded-xl border-2 border-pink-200 hover:border-pink-400">
                            <svg class="w-7 h-7 mb-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-xs font-bold text-pink-700">School Name</span>
                        </button>

                        <!-- Student Name -->
                        <button onclick="addField('text', 'Name:', 'name')" class="element-btn flex flex-col items-center justify-center p-4 bg-green-50 rounded-xl border-2 border-green-200 hover:border-green-400">
                            <span class="text-2xl mb-1">👤</span>
                            <span class="text-xs font-bold text-green-700">Name</span>
                        </button>

                        <!-- Class -->
                        <button onclick="addField('text', 'Class:', 'class')" class="element-btn flex flex-col items-center justify-center p-4 bg-cyan-50 rounded-xl border-2 border-cyan-200 hover:border-cyan-400">
                            <span class="text-2xl mb-1">📚</span>
                            <span class="text-xs font-bold text-cyan-700">Class</span>
                        </button>

                        <!-- D.O.B -->
                        <button onclick="addField('text', 'D.O.B:', 'dob')" class="element-btn flex flex-col items-center justify-center p-4 bg-violet-50 rounded-xl border-2 border-violet-200 hover:border-violet-400">
                            <span class="text-2xl mb-1">🎂</span>
                            <span class="text-xs font-bold text-violet-700">D.O.B</span>
                        </button>

                        <!-- Blood Group -->
                        <button onclick="addField('text', 'Blood:', 'blood')" class="element-btn flex flex-col items-center justify-center p-4 bg-red-50 rounded-xl border-2 border-red-200 hover:border-red-400">
                            <span class="text-2xl mb-1">🩸</span>
                            <span class="text-xs font-bold text-red-700">Blood</span>
                        </button>

                        <!-- Parent Name -->
                        <button onclick="addField('text', 'Parent:', 'parent')" class="element-btn flex flex-col items-center justify-center p-4 bg-indigo-50 rounded-xl border-2 border-indigo-200 hover:border-indigo-400">
                            <span class="text-2xl mb-1">👨‍👩‍👧</span>
                            <span class="text-xs font-bold text-indigo-700">Parent</span>
                        </button>

                        <!-- Contact -->
                        <button onclick="addField('text', 'Contact:', 'contact')" class="element-btn flex flex-col items-center justify-center p-4 bg-teal-50 rounded-xl border-2 border-teal-200 hover:border-teal-400">
                            <span class="text-2xl mb-1">📞</span>
                            <span class="text-xs font-bold text-teal-700">Contact</span>
                        </button>

                        <!-- Address -->
                        <button onclick="addField('text', 'Address:', 'address')" class="element-btn flex flex-col items-center justify-center p-4 bg-orange-50 rounded-xl border-2 border-orange-200 hover:border-orange-400 col-span-2">
                            <span class="text-2xl mb-1">🏠</span>
                            <span class="text-xs font-bold text-orange-700">Address</span>
                        </button>

                        <!-- Custom Field -->
                        <button onclick="addCustomInputField()" class="element-btn flex flex-col items-center justify-center p-4 bg-gray-50 rounded-xl border-2 border-gray-200 hover:border-gray-400 col-span-2">
                            <span class="text-2xl mb-1">➕</span>
                            <span class="text-xs font-bold text-gray-700">Custom Field</span>
                        </button>
                    </div>
                </div>

                <!-- Properties Panel -->
                <div id="propPanel" class="space-y-4 hidden">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center border border-rose-200">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Properties</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div id="textProps">
                            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block mb-2">Font Size</label>
                            <input type="range" id="fontSize" min="8" max="72" class="w-full h-2 bg-pink-200 rounded-lg appearance-none cursor-pointer accent-pink-500">
                            <div class="text-sm font-semibold text-pink-600 mt-2" id="fontSizeDisplay">16px</div>
                        </div>
                        
                        <div id="fontWeightControl">
                            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block mb-2">Font Weight</label>
                            <select id="fontWeight" class="w-full p-3 input-luxury rounded-xl font-medium cursor-pointer">
                                <option value="300">Light</option>
                                <option value="400">Regular</option>
                                <option value="500">Medium</option>
                                <option value="600" selected>Semi Bold</option>
                                <option value="700">Bold</option>
                                <option value="800">Extra Bold</option>
                                <option value="900">Black</option>
                            </select>
                        </div>
                        
                        <div id="colorProps">
                            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block mb-2">Text Color</label>
                            <input type="color" id="fontColor" class="w-full h-12 rounded-xl cursor-pointer border-2 border-pink-200">
                        </div>
                        
                        <div id="imageProps" class="hidden">
                            <button onclick="cropImage()" class="w-full py-3 bg-gradient-to-r from-pink-500 to-rose-500 text-white rounded-xl font-bold hover:from-pink-600 hover:to-rose-600 transition shadow-lg">
                                ✂️ Crop / Resize
                            </button>
                        </div>
                        
                        <div id="filterProps" class="hidden">
                            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block mb-2">Image Filters</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="applyFilter('none')" class="p-2 bg-gray-100 rounded-lg text-xs font-bold hover:bg-pink-100">Original</button>
                                <button onclick="applyFilter('grayscale')" class="p-2 bg-gray-100 rounded-lg text-xs font-bold hover:bg-pink-100">B&W</button>
                                <button onclick="applyFilter('sepia')" class="p-2 bg-gray-100 rounded-lg text-xs font-bold hover:bg-pink-100">Sepia</button>
                                <button onclick="applyFilter('brightness')" class="p-2 bg-gray-100 rounded-lg text-xs font-bold hover:bg-pink-100">Bright</button>
                            </div>
                        </div>
                        
                        <div id="shapeControl" class="hidden">
                            <label class="text-xs font-bold text-gray-600 uppercase tracking-wider block mb-2">Shape</label>
                            <select id="imageShape" class="w-full p-3 input-luxury rounded-xl font-medium cursor-pointer">
                                <option value="rect">Rectangle</option>
                                <option value="rounded">Rounded</option>
                                <option value="circle">Circle</option>
                            </select>
                        </div>
                        
                        <button onclick="removeSelected()" class="w-full py-3 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-xl font-bold hover:from-red-600 hover:to-pink-600 transition shadow-lg transform hover:scale-105">
                            🗑️ Remove Element
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Designer Canvas -->
            <main class="flex-1 flex flex-col items-center">
                <!-- Tab Switcher -->
                <div class="flex gap-3 sm:gap-4 mb-6 sm:mb-8 glass-card p-2 rounded-2xl">
                    <button onclick="switchTab('front')" id="tabFront" class="tab-btn px-6 sm:px-8 py-3 font-bold rounded-xl text-sm sm:text-base">
                        <span class="hidden sm:inline">🎨 Front Side</span>
                        <span class="sm:hidden">Front</span>
                    </button>
                    <button onclick="switchTab('back')" id="tabBack" class="tab-btn px-6 sm:px-8 py-3 font-bold rounded-xl text-gray-600 text-sm sm:text-base">
                        <span class="hidden sm:inline">🎨 Back Side</span>
                        <span class="sm:hidden">Back</span>
                    </button>
                </div>

                <!-- Canvas -->
                <div class="canvas-wrapper relative overflow-hidden mb-8">
                    <canvas id="idCanvas" width="325" height="500"></canvas>
                </div>
                
                <!-- Tips -->
                <div class="glass-card p-5 sm:p-6 rounded-2xl max-w-2xl w-full">
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-base sm:text-lg">
                        <span class="text-xl sm:text-2xl">💡</span>
                        Quick Tips
                    </h4>
                    <ul class="space-y-2 text-xs sm:text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <span class="text-pink-500 font-black mt-0.5">•</span>
                            <span><strong class="text-gray-900">Drag & Drop:</strong> Move elements anywhere</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-pink-500 font-black mt-0.5">•</span>
                            <span><strong class="text-gray-900">Resize:</strong> Use corner handles (Shift = proportional)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-pink-500 font-black mt-0.5">•</span>
                            <span><strong class="text-gray-900">Upload & Crop:</strong> Click on images to crop/resize professionally</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-pink-500 font-black mt-0.5">•</span>
                            <span><strong class="text-gray-900">Delete Key:</strong> Remove selected element</span>
                        </li>
                    </ul>
                </div>
            </main>
        </div>
    </div>

    <!-- Image Library Modal -->
    <div id="imageLibraryModal" class="modal">
        <div class="modal-content glass-card p-6 rounded-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Select or Upload Image</h3>
                <button onclick="closeImageLibrary()" class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center hover:bg-pink-200 transition">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <label class="block w-full p-6 border-2 border-dashed border-pink-300 rounded-xl text-center cursor-pointer hover:border-pink-500 hover:bg-pink-50 transition">
                    <input type="file" id="imageUpload" accept="image/*" class="hidden" onchange="handleImageUpload(event)">
                    <svg class="w-12 h-12 mx-auto mb-3 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-pink-600 font-bold mb-1">Click to upload new image</p>
                    <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 10MB</p>
                </label>
            </div>
            
            <div id="uploadedImagesGrid" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                <!-- Dynamically loaded images will appear here -->
            </div>
        </div>
    </div>

    <!-- Crop Modal -->
    <div id="cropModal" class="modal">
        <div class="modal-content glass-card p-6 rounded-2xl max-w-4xl w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Crop & Resize Image</h3>
                <button onclick="closeCropModal()" class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center hover:bg-pink-200 transition">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="crop-container mb-6">
                <img id="cropImage" src="" alt="Crop" style="max-width: 100%;">
            </div>
            
            <div class="flex gap-3 justify-end">
                <button onclick="closeCropModal()" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button onclick="applyCrop()" class="px-6 py-3 bg-gradient-to-r from-pink-500 to-rose-500 text-white rounded-xl font-bold hover:from-pink-600 hover:to-rose-600 transition shadow-lg">
                    ✅ Apply Crop
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        const canvas = new fabric.Canvas('idCanvas', {
            backgroundColor: '#ffffff'
        });
        
        let currentTab = 'front';
        const layouts = {
            front: [],
            back: []
        };
        let backgrounds = {
            front: '<?= $bg_front ?>',
            back: '<?= $bg_back ?>'
        };
        
        let currentImageType = null;
        let cropper = null;
        let cropTargetObject = null;
        let uploadedImages = {
            logo: [],
            photo: [],
            background: []
        };

        const savedLayout = <?= $layout_json ?>;
        if (savedLayout.front) layouts.front = savedLayout.front;
        if (savedLayout.back) layouts.back = savedLayout.back;

        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        }

        // Initialize Canvas
        function initCanvas() {
            canvas.clear();
            loadBackground();
            
            const currentItems = layouts[currentTab];
            currentItems.forEach(item => {
                let obj;
                if (item.type === 'text') {
                    obj = new fabric.IText(item.text, {
                        left: item.left,
                        top: item.top,
                        fontSize: item.fontSize || 14,
                        fill: item.fill || '#000000',
                        fontFamily: 'Inter',
                        fontWeight: item.fontWeight || '600',
                        id: item.id,
                        dataField: item.dataField
                    });
                } else if (item.type === 'image') {
                    fabric.Image.fromURL(item.src, function(img) {
                        img.set({
                            left: item.left,
                            top: item.top,
                            scaleX: item.scaleX || 1,
                            scaleY: item.scaleY || 1,
                            id: item.id,
                            dataField: item.dataField
                        });
                        
                        if (item.clipPath) {
                            const shape = item.clipPath.shape;
                            const clipPath = createClipPath(img, shape);
                            img.clipPath = clipPath;
                        }
                        
                        if (item.filters) {
                            applyFiltersToImage(img, item.filters);
                        }
                        
                        canvas.add(img);
                        canvas.renderAll();
                    }, { crossOrigin: 'anonymous' });
                }
            });
            canvas.renderAll();
        }

        function loadBackground() {
            const bgUrl = backgrounds[currentTab];
            if (bgUrl) {
                fabric.Image.fromURL(bgUrl, function(img) {
                    const canvasAspect = canvas.width / canvas.height;
                    const imgAspect = img.width / img.height;
                    
                    let scale, left = 0, top = 0;
                    
                    if (canvasAspect > imgAspect) {
                        scale = canvas.width / img.width;
                        top = -(img.height * scale - canvas.height) / 2;
                    } else {
                        scale = canvas.height / img.height;
                        left = -(img.width * scale - canvas.width) / 2;
                    }
                    
                    canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                        scaleX: scale,
                        scaleY: scale,
                        left: left,
                        top: top
                    });
                }, { crossOrigin: 'anonymous' });
            } else {
                canvas.setBackgroundColor('#ffffff', canvas.renderAll.bind(canvas));
            }
        }

        function switchTab(tab) {
            saveCurrentState();
            currentTab = tab;
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.add('text-gray-600');
            });
            const activeBtn = document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1));
            activeBtn.classList.add('active');
            activeBtn.classList.remove('text-gray-600');
            initCanvas();
        }

        function saveCurrentState() {
            layouts[currentTab] = canvas.getObjects().map(obj => {
                const data = {
                    id: obj.id,
                    dataField: obj.dataField,
                    left: obj.left,
                    top: obj.top
                };
                
                if (obj.type === 'i-text') {
                    data.type = 'text';
                    data.text = obj.text;
                    data.fontSize = obj.fontSize;
                    data.fill = obj.fill;
                    data.fontWeight = obj.fontWeight;
                } else if (obj.type === 'image') {
                    data.type = 'image';
                    data.src = obj.getSrc();
                    data.scaleX = obj.scaleX;
                    data.scaleY = obj.scaleY;
                    
                    if (obj.clipPath) {
                        data.clipPath = { shape: obj.clipPath.shape };
                    }
                    
                    if (obj.filters && obj.filters.length > 0) {
                        data.filters = obj.filters.map(f => f.type);
                    }
                }
                
                return data;
            });
        }

        function addField(type, text = 'Text', dataField = 'custom') {
            const id = dataField + '_' + Date.now();
            
            if (type === 'text') {
                const obj = new fabric.IText(text, {
                    left: 50,
                    top: 100,
                    fontSize: 14,
                    fill: '#000000',
                    fontFamily: 'Inter',
                    fontWeight: '600',
                    id: id,
                    dataField: dataField
                });
                canvas.add(obj);
                canvas.setActiveObject(obj);
                canvas.renderAll();
            }
        }

        function addCustomInputField() {
            const fieldName = prompt('Enter field label (e.g., "Emergency Contact", "Roll No"):');
            if (fieldName && fieldName.trim()) {
                addField('text', fieldName.trim() + ':', 'custom');
            }
        }

        function removeSelected() {
            const active = canvas.getActiveObject();
            if (active) {
                canvas.remove(active);
                document.getElementById('propPanel').classList.add('hidden');
                canvas.renderAll();
            }
        }

        // Image Library Functions
        function openImageLibrary(type) {
            currentImageType = type;
            document.getElementById('imageLibraryModal').classList.add('show');
            loadUploadedImages(type);
        }

        function closeImageLibrary() {
            document.getElementById('imageLibraryModal').classList.remove('show');
            currentImageType = null;
        }

        function openBackgroundLibrary() {
            currentImageType = 'background';
            document.getElementById('imageLibraryModal').classList.add('show');
            loadUploadedImages('background');
        }

        function loadUploadedImages(type) {
            const grid = document.getElementById('uploadedImagesGrid');
            grid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">Loading...</p>';
            
            fetch(`get_images.php?type=${type}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.images.length > 0) {
                        grid.innerHTML = '';
                        data.images.forEach(img => {
                            const div = document.createElement('div');
                            div.className = 'image-thumb rounded-xl overflow-hidden aspect-square';
                            div.innerHTML = `<img src="${img}" class="w-full h-full object-cover cursor-pointer" onclick="selectImage('${img}')">`;
                            grid.appendChild(div);
                        });
                    } else {
                        grid.innerHTML = '<p class="col-span-full text-center text-gray-500 py-8">No images uploaded yet. Upload your first image!</p>';
                    }
                })
                .catch(err => {
                    grid.innerHTML = '<p class="col-span-full text-center text-red-500 py-8">Error loading images</p>';
                });
        }

        function handleImageUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('image', file);
            formData.append('type', currentImageType);
            
            showToast('⏳ Uploading image...', 'info');
            
            fetch('upload_image.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ Image uploaded successfully!', 'success');
                    loadUploadedImages(currentImageType);
                } else {
                    showToast('❌ Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('❌ Upload failed', 'error');
            });
            
            e.target.value = '';
        }

        function selectImage(src) {
            if (currentImageType === 'background') {
                backgrounds[currentTab] = src;
                loadBackground();
                closeImageLibrary();
                showToast('✅ Background applied!', 'success');
            } else {
                const id = currentImageType + '_' + Date.now();
                fabric.Image.fromURL(src, function(img) {
                    img.set({
                        left: 50,
                        top: 50,
                        scaleX: 0.3,
                        scaleY: 0.3,
                        id: id,
                        dataField: currentImageType
                    });
                    
                    // Apply default rounded corners
                    const clipPath = createClipPath(img, 'rounded');
                    img.clipPath = clipPath;
                    
                    canvas.add(img);
                    canvas.setActiveObject(img);
                    canvas.renderAll();
                }, { crossOrigin: 'anonymous' });
                
                closeImageLibrary();
                showToast('✅ Image added to canvas!', 'success');
            }
        }

        // Crop Functions
        function cropImage() {
            const active = canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            
            cropTargetObject = active;
            
            const cropImg = document.getElementById('cropImage');
            cropImg.src = active.getSrc();
            
            document.getElementById('cropModal').classList.add('show');
            
            setTimeout(() => {
                if (cropper) {
                    cropper.destroy();
                }
                
                cropper = new Cropper(cropImg, {
                    aspectRatio: NaN,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false
                });
            }, 100);
        }

        function closeCropModal() {
            document.getElementById('cropModal').classList.remove('show');
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            cropTargetObject = null;
        }

        function applyCrop() {
            if (!cropper || !cropTargetObject) return;
            
            const croppedCanvas = cropper.getCroppedCanvas();
            const croppedDataUrl = croppedCanvas.toDataURL();
            
            fabric.Image.fromURL(croppedDataUrl, function(img) {
                img.set({
                    left: cropTargetObject.left,
                    top: cropTargetObject.top,
                    scaleX: cropTargetObject.scaleX,
                    scaleY: cropTargetObject.scaleY,
                    id: cropTargetObject.id,
                    dataField: cropTargetObject.dataField
                });
                
                if (cropTargetObject.clipPath) {
                    img.clipPath = cropTargetObject.clipPath;
                }
                
                if (cropTargetObject.filters) {
                    img.filters = cropTargetObject.filters;
                    img.applyFilters();
                }
                
                canvas.remove(cropTargetObject);
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
                
                closeCropModal();
                showToast('✅ Crop applied successfully!', 'success');
            });
        }

        // Filter Functions
        function applyFilter(filterType) {
            const active = canvas.getActiveObject();
            if (!active || active.type !== 'image') return;
            
            active.filters = [];
            
            switch(filterType) {
                case 'grayscale':
                    active.filters.push(new fabric.Image.filters.Grayscale());
                    break;
                case 'sepia':
                    active.filters.push(new fabric.Image.filters.Sepia());
                    break;
                case 'brightness':
                    active.filters.push(new fabric.Image.filters.Brightness({ brightness: 0.2 }));
                    break;
            }
            
            active.applyFilters();
            canvas.renderAll();
            showToast('✅ Filter applied!', 'success');
        }

        function applyFiltersToImage(img, filterTypes) {
            img.filters = [];
            filterTypes.forEach(type => {
                switch(type) {
                    case 'Grayscale':
                        img.filters.push(new fabric.Image.filters.Grayscale());
                        break;
                    case 'Sepia':
                        img.filters.push(new fabric.Image.filters.Sepia());
                        break;
                    case 'Brightness':
                        img.filters.push(new fabric.Image.filters.Brightness({ brightness: 0.2 }));
                        break;
                }
            });
            img.applyFilters();
        }

        // Shape Functions
        function createClipPath(img, shape) {
            const width = img.width;
            const height = img.height;
            let clipPath;
            
            switch(shape) {
                case 'circle':
                    const radius = Math.min(width, height) / 2;
                    clipPath = new fabric.Circle({
                        radius: radius,
                        originX: 'center',
                        originY: 'center',
                        left: width / 2,
                        top: height / 2
                    });
                    clipPath.shape = 'circle';
                    break;
                    
                case 'rounded':
                    clipPath = new fabric.Rect({
                        width: width,
                        height: height,
                        rx: 20,
                        ry: 20,
                        originX: 'center',
                        originY: 'center',
                        left: width / 2,
                        top: height / 2
                    });
                    clipPath.shape = 'rounded';
                    break;
                    
                default: // rect
                    clipPath = new fabric.Rect({
                        width: width,
                        height: height,
                        originX: 'center',
                        originY: 'center',
                        left: width / 2,
                        top: height / 2
                    });
                    clipPath.shape = 'rect';
                    break;
            }
            
            return clipPath;
        }

        // Selection Handling
        canvas.on('selection:created', showProps);
        canvas.on('selection:updated', showProps);
        canvas.on('selection:cleared', () => {
            document.getElementById('propPanel').classList.add('hidden');
        });

        function showProps() {
            const active = canvas.getActiveObject();
            if (!active) return;

            document.getElementById('propPanel').classList.remove('hidden');
            
            if (active.type === 'i-text') {
                document.getElementById('textProps').style.display = 'block';
                document.getElementById('fontWeightControl').style.display = 'block';
                document.getElementById('colorProps').style.display = 'block';
                document.getElementById('imageProps').classList.add('hidden');
                document.getElementById('filterProps').classList.add('hidden');
                document.getElementById('shapeControl').classList.add('hidden');
                
                document.getElementById('fontSize').value = active.fontSize;
                document.getElementById('fontSizeDisplay').textContent = active.fontSize + 'px';
                document.getElementById('fontColor').value = active.fill;
                document.getElementById('fontWeight').value = active.fontWeight || '600';
            } else if (active.type === 'image') {
                document.getElementById('textProps').style.display = 'none';
                document.getElementById('fontWeightControl').style.display = 'none';
                document.getElementById('colorProps').style.display = 'none';
                document.getElementById('imageProps').classList.remove('hidden');
                document.getElementById('filterProps').classList.remove('hidden');
                document.getElementById('shapeControl').classList.remove('hidden');
                
                if (active.clipPath && active.clipPath.shape) {
                    document.getElementById('imageShape').value = active.clipPath.shape;
                } else {
                    document.getElementById('imageShape').value = 'rect';
                }
            }
        }

        // Property Updates
        document.getElementById('fontSize').oninput = function() {
            const active = canvas.getActiveObject();
            if (active && active.type === 'i-text') {
                active.set('fontSize', parseInt(this.value));
                document.getElementById('fontSizeDisplay').textContent = this.value + 'px';
                canvas.renderAll();
            }
        };

        document.getElementById('fontWeight').onchange = function() {
            const active = canvas.getActiveObject();
            if (active && active.type === 'i-text') {
                active.set('fontWeight', this.value);
                canvas.renderAll();
            }
        };

        document.getElementById('fontColor').oninput = function() {
            const active = canvas.getActiveObject();
            if (active && active.type === 'i-text') {
                active.set('fill', this.value);
                canvas.renderAll();
            }
        };

        document.getElementById('imageShape').onchange = function() {
            const active = canvas.getActiveObject();
            if (active && active.type === 'image') {
                const clipPath = createClipPath(active, this.value);
                active.clipPath = clipPath;
                canvas.renderAll();
            }
        };

        // Save Layout
        document.getElementById('saveBtn').onclick = function() {
            saveCurrentState();
            const data = {
                layout: layouts,
                background_front: backgrounds.front,
                background_back: backgrounds.back
            };

            fetch('save_layout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ Layout saved successfully!', 'success');
                } else {
                    showToast('❌ Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('❌ Save failed', 'error');
                console.error(err);
            });
        };

        // Toast Notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'border-green-500' : type === 'error' ? 'border-red-500' : 'border-blue-500';
            toast.className = `toast glass-card px-6 py-4 rounded-xl font-bold border-l-4 ${bgColor}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
        }

        // Keyboard Support
        window.addEventListener('keydown', e => {
            const active = canvas.getActiveObject();
            if (!active) return;

            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }

            const step = e.shiftKey ? 10 : 2;
            
            if (e.key === 'ArrowUp') active.top -= step;
            if (e.key === 'ArrowDown') active.top += step;
            if (e.key === 'ArrowLeft') active.left -= step;
            if (e.key === 'ArrowRight') active.left += step;
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    canvas.remove(active);
                    document.getElementById('propPanel').classList.add('hidden');
                }
            }
            
            canvas.renderAll();
        });

        // Initialize
        initCanvas();
    </script>
</body>
</html>