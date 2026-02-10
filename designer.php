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
    <title>Visual ID Designer | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --accent: #f59e0b;
        }
        
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #f8fafc;
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
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(245, 158, 11, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .content-wrapper {
            position: relative;
            z-index: 1;
        }
        
        /* Glass Cards */
        .glass-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(30, 41, 59, 0.3) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
        }
        
        /* Canvas Container */
        .canvas-container { 
            margin: 0 auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 3px solid rgba(99, 102, 241, 0.3);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }
        
        /* Tab Buttons */
        .tab-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .tab-btn.active { 
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
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
            background: rgba(99, 102, 241, 0.1);
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
            background: rgba(15, 23, 42, 0.7);
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            color: white;
            transition: all 0.3s ease;
        }
        
        .input-luxury:focus {
            border-color: var(--primary);
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            outline: none;
        }
        
        /* Premium Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
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
        
        /* File Input Styling */
        input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            cursor: pointer;
            font-weight: 600;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }
        
        input[type="file"]::file-selector-button:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3) 0%, rgba(139, 92, 246, 0.3) 100%);
            border-color: rgba(99, 102, 241, 0.5);
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
        
        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-in {
            animation: slideIn 0.5s ease-out;
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            top: 6rem;
            right: 2rem;
            z-index: 1000;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes logo-load {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-logo-load {
            animation: logo-load 0.7s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <!-- Mobile Overlay -->
        <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
        
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-slate-900/80 backdrop-blur-md border-b border-white/5 px-4 sm:px-6 lg:px-8 py-4 shadow-xl">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <!-- Mobile Menu Button -->
                        <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg bg-white/5 hover:bg-white/10 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                                Visual ID Designer
                            </h1>
                            <p class="text-xs text-slate-400 font-medium hidden sm:block">Professional Card Design Studio</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-4">
                        <a href="dashboard.php" class="hidden sm:flex items-center gap-2 text-slate-400 hover:text-white font-medium transition px-3 sm:px-4 py-2 rounded-xl hover:bg-white/10 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back
                        </a>
                        <button id="saveBtn" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 sm:px-6 lg:px-8 py-2 sm:py-3 rounded-xl font-bold hover:shadow-2xl transition transform hover:scale-105 shadow-lg text-sm">
                            <span class="hidden sm:inline">💾 Save Layout</span>
                            <span class="sm:hidden">💾 Save</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-6 lg:gap-8 py-6 lg:py-8">
            <!-- Sidebar Controls -->
            <aside class="sidebar-panel w-80 lg:w-96 space-y-6 pb-20 lg:pb-6" id="sidebar">
                <!-- Add Elements -->
                <div class="glass-card p-5 sm:p-6 rounded-2xl">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center border border-indigo-500/30">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-white">Add Elements</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Logo -->
                        <button onclick="addField('logo')" class="element-btn flex flex-col items-center justify-center p-4 bg-amber-500/10 rounded-xl border-2 border-amber-500/20 hover:border-amber-500/50">
                            <svg class="w-7 h-7 mb-2 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            <span class="text-xs font-bold text-amber-300">School Logo</span>
                        </button>

                        <!-- Photo -->
                        <button onclick="addField('photo')" class="element-btn flex flex-col items-center justify-center p-4 bg-blue-500/10 rounded-xl border-2 border-blue-500/20 hover:border-blue-500/50">
                            <svg class="w-7 h-7 mb-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-xs font-bold text-blue-300">Student Photo</span>
                        </button>

                        <!-- School Name -->
                        <button onclick="addField('text', 'SCHOOL NAME', 'school_name')" class="element-btn flex flex-col items-center justify-center p-4 bg-purple-500/10 rounded-xl border-2 border-purple-500/20 hover:border-purple-500/50">
                            <svg class="w-7 h-7 mb-2 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-xs font-bold text-purple-300">School Name</span>
                        </button>

                        <!-- Student Name -->
                        <button onclick="addField('text', 'Name:', 'name')" class="element-btn flex flex-col items-center justify-center p-4 bg-green-500/10 rounded-xl border-2 border-green-500/20 hover:border-green-500/50">
                            <span class="text-2xl mb-1">👤</span>
                            <span class="text-xs font-bold text-green-300">Name</span>
                        </button>

                        <!-- Class -->
                        <button onclick="addField('text', 'Class:', 'class')" class="element-btn flex flex-col items-center justify-center p-4 bg-cyan-500/10 rounded-xl border-2 border-cyan-500/20 hover:border-cyan-500/50">
                            <span class="text-2xl mb-1">📚</span>
                            <span class="text-xs font-bold text-cyan-300">Class</span>
                        </button>

                        <!-- D.O.B -->
                        <button onclick="addField('text', 'D.O.B:', 'dob')" class="element-btn flex flex-col items-center justify-center p-4 bg-pink-500/10 rounded-xl border-2 border-pink-500/20 hover:border-pink-500/50">
                            <span class="text-2xl mb-1">🎂</span>
                            <span class="text-xs font-bold text-pink-300">D.O.B</span>
                        </button>

                        <!-- Blood Group -->
                        <button onclick="addField('text', 'Blood:', 'blood')" class="element-btn flex flex-col items-center justify-center p-4 bg-red-500/10 rounded-xl border-2 border-red-500/20 hover:border-red-500/50">
                            <span class="text-2xl mb-1">🩸</span>
                            <span class="text-xs font-bold text-red-300">Blood</span>
                        </button>

                        <!-- Parent Name -->
                        <button onclick="addField('text', 'Parent:', 'parent')" class="element-btn flex flex-col items-center justify-center p-4 bg-violet-500/10 rounded-xl border-2 border-violet-500/20 hover:border-violet-500/50">
                            <span class="text-2xl mb-1">👨‍👩‍👧</span>
                            <span class="text-xs font-bold text-violet-300">Parent</span>
                        </button>

                        <!-- Contact -->
                        <button onclick="addField('text', 'Contact:', 'contact')" class="element-btn flex flex-col items-center justify-center p-4 bg-teal-500/10 rounded-xl border-2 border-teal-500/20 hover:border-teal-500/50">
                            <span class="text-2xl mb-1">📞</span>
                            <span class="text-xs font-bold text-teal-300">Contact</span>
                        </button>

                        <!-- Address -->
                        <button onclick="addField('text', 'Address:', 'address')" class="element-btn flex flex-col items-center justify-center p-4 bg-orange-500/10 rounded-xl border-2 border-orange-500/20 hover:border-orange-500/50 col-span-2">
                            <span class="text-2xl mb-1">🏠</span>
                            <span class="text-xs font-bold text-orange-300">Address</span>
                        </button>

                        <!-- Custom Field -->
                        <button onclick="addCustomInputField()" class="element-btn flex flex-col items-center justify-center p-4 bg-slate-500/10 rounded-xl border-2 border-slate-500/20 hover:border-slate-500/50 col-span-2">
                            <span class="text-2xl mb-1">➕</span>
                            <span class="text-xs font-bold text-slate-300">Custom Field</span>
                        </button>
                    </div>
                </div>

                <!-- Background Upload -->
                <div class="glass-card p-5 sm:p-6 rounded-2xl">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 bg-amber-500/20 rounded-xl flex items-center justify-center border border-amber-500/30">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-white">Card Background</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-300 mb-2">Front Side</label>
                            <input type="file" id="bgFrontInput" accept="image/*" class="block w-full text-sm text-slate-400 input-luxury rounded-xl p-3 cursor-pointer border-2 border-dashed border-indigo-500/20 hover:border-indigo-500/40 transition" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-300 mb-2">Back Side</label>
                            <input type="file" id="bgBackInput" accept="image/*" class="block w-full text-sm text-slate-400 input-luxury rounded-xl p-3 cursor-pointer border-2 border-dashed border-indigo-500/20 hover:border-indigo-500/40 transition" />
                        </div>
                    </div>
                </div>

                <!-- Properties Panel -->
                <div id="propPanel" class="glass-card p-5 sm:p-6 rounded-2xl hidden">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 bg-purple-500/20 rounded-xl flex items-center justify-center border border-purple-500/30">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-bold text-white">Properties</h3>
                    </div>
                    <div class="space-y-5">
                        <div id="textProps">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Font Size</label>
                            <input type="range" id="fontSize" min="8" max="72" class="w-full h-2 bg-indigo-500/20 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                            <div class="text-sm font-semibold text-indigo-400 mt-2" id="fontSizeDisplay">16px</div>
                        </div>
                        <div id="colorProps">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Text Color</label>
                            <input type="color" id="fontColor" class="w-full h-12 rounded-xl cursor-pointer border-2 border-indigo-500/20">
                        </div>
                        <div id="photoShapeControl" class="hidden">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Photo Shape</label>
                            <select id="photoShape" class="w-full p-3 input-luxury rounded-xl font-medium cursor-pointer">
                                <option value="rect">Rectangle</option>
                                <option value="rounded">Rounded Corners</option>
                                <option value="circle">Circle</option>
                            </select>
                        </div>
                        <div id="logoShapeControl" class="hidden">
                            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2">Logo Shape</label>
                            <select id="logoShape" class="w-full p-3 input-luxury rounded-xl font-medium cursor-pointer">
                                <option value="rect">Rectangle</option>
                                <option value="rounded">Rounded Corners</option>
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
                    <button onclick="switchTab('back')" id="tabBack" class="tab-btn px-6 sm:px-8 py-3 font-bold rounded-xl text-slate-400 text-sm sm:text-base">
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
                    <h4 class="font-bold text-white mb-4 flex items-center gap-2 text-base sm:text-lg">
                        <span class="text-xl sm:text-2xl">💡</span>
                        Quick Tips
                    </h4>
                    <ul class="space-y-2 text-xs sm:text-sm text-slate-300">
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-400 font-black mt-0.5">•</span>
                            <span><strong class="text-white">Drag & Drop:</strong> Move elements anywhere</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-400 font-black mt-0.5">•</span>
                            <span><strong class="text-white">Resize:</strong> Use corner handles (Shift = proportional)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-400 font-black mt-0.5">•</span>
                            <span><strong class="text-white">Arrow Keys:</strong> Fine-tune positioning</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-indigo-400 font-black mt-0.5">•</span>
                            <span><strong class="text-white">Delete Key:</strong> Remove selected element</span>
                        </li>
                    </ul>
                </div>
            </main>
        </div>
    </div>

    <script>
        const canvas = new fabric.Canvas('idCanvas', {
            backgroundColor: '#1e293b'
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
                        fill: item.fill || '#ffffff',
                        fontFamily: 'Inter',
                        fontWeight: item.fontWeight || '600',
                        id: item.id,
                        dataField: item.dataField
                    });
                } else if (item.type === 'photo') {
                    obj = new fabric.Rect({
                        left: item.left,
                        top: item.top,
                        width: item.width || 100,
                        height: item.height || 120,
                        fill: '#3b82f6',
                        rx: item.rx || 24,
                        ry: item.ry || 24,
                        stroke: '#60a5fa',
                        strokeWidth: 3,
                        id: item.id,
                        dataField: 'photo'
                    });
                } else if (item.type === 'logo') {
                    obj = new fabric.Rect({
                        left: item.left,
                        top: item.top,
                        width: item.width || 80,
                        height: item.height || 80,
                        fill: '#f59e0b',
                        rx: item.rx || 12,
                        ry: item.ry || 12,
                        stroke: '#fbbf24',
                        strokeWidth: 2,
                        id: item.id,
                        dataField: 'logo'
                    });
                }
                if (obj) {
                    canvas.add(obj);
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
                canvas.setBackgroundColor('#1e293b', canvas.renderAll.bind(canvas));
            }
        }

        function switchTab(tab) {
            saveCurrentState();
            currentTab = tab;
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.add('text-slate-400');
            });
            const activeBtn = document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1));
            activeBtn.classList.add('active');
            activeBtn.classList.remove('text-slate-400');
            initCanvas();
        }

        function saveCurrentState() {
            layouts[currentTab] = canvas.getObjects()
                .filter(obj => obj.id && obj.dataField)
                .map(obj => ({
                    id: obj.id,
                    dataField: obj.dataField,
                    type: obj.type === 'i-text' ? 'text' : obj.dataField,
                    left: obj.left,
                    top: obj.top,
                    width: obj.width * (obj.scaleX || 1),
                    height: obj.height * (obj.scaleY || 1),
                    text: obj.text,
                    fontSize: obj.fontSize,
                    fill: obj.fill,
                    fontWeight: obj.fontWeight,
                    rx: obj.rx,
                    ry: obj.ry
                }));
        }

        function addField(type, text = 'Text', dataField = 'custom') {
            let obj;
            const id = dataField + '_' + Date.now();
            
            if (type === 'text') {
                obj = new fabric.IText(text, {
                    left: 50,
                    top: 100,
                    fontSize: 14,
                    fill: '#ffffff',
                    fontFamily: 'Inter',
                    fontWeight: '600',
                    id: id,
                    dataField: dataField
                });
            } else if (type === 'photo') {
                obj = new fabric.Rect({
                    left: 90,
                    top: 80,
                    width: 150,
                    height: 180,
                    fill: '#3b82f6',
                    rx: 24,
                    ry: 24,
                    stroke: '#60a5fa',
                    strokeWidth: 3,
                    id: 'photo_' + Date.now(),
                    dataField: 'photo'
                });
            } else if (type === 'logo') {
                obj = new fabric.Rect({
                    left: 120,
                    top: 20,
                    width: 80,
                    height: 80,
                    fill: '#f59e0b',
                    rx: 12,
                    ry: 12,
                    stroke: '#fbbf24',
                    strokeWidth: 2,
                    id: 'logo_' + Date.now(),
                    dataField: 'logo'
                });
            }
            
            if (obj) {
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
                document.getElementById('colorProps').style.display = 'block';
                document.getElementById('fontSize').value = active.fontSize;
                document.getElementById('fontSizeDisplay').textContent = active.fontSize + 'px';
                document.getElementById('fontColor').value = active.fill;
                document.getElementById('photoShapeControl').classList.add('hidden');
                document.getElementById('logoShapeControl').classList.add('hidden');
            } else if (active.dataField === 'photo') {
                document.getElementById('textProps').style.display = 'none';
                document.getElementById('colorProps').style.display = 'none';
                document.getElementById('photoShapeControl').classList.remove('hidden');
                document.getElementById('logoShapeControl').classList.add('hidden');
                
                if (active.rx === 0) {
                    document.getElementById('photoShape').value = 'rect';
                } else if (active.rx >= active.width / 2) {
                    document.getElementById('photoShape').value = 'circle';
                } else {
                    document.getElementById('photoShape').value = 'rounded';
                }
            } else if (active.dataField === 'logo') {
                document.getElementById('textProps').style.display = 'none';
                document.getElementById('colorProps').style.display = 'none';
                document.getElementById('photoShapeControl').classList.add('hidden');
                document.getElementById('logoShapeControl').classList.remove('hidden');
                
                if (active.rx === 0) {
                    document.getElementById('logoShape').value = 'rect';
                } else if (active.rx >= active.width / 2) {
                    document.getElementById('logoShape').value = 'circle';
                } else {
                    document.getElementById('logoShape').value = 'rounded';
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

        document.getElementById('fontColor').oninput = function() {
            const active = canvas.getActiveObject();
            if (active && active.type === 'i-text') {
                active.set('fill', this.value);
                canvas.renderAll();
            }
        };

        document.getElementById('photoShape').onchange = function() {
            const active = canvas.getActiveObject();
            if (active && active.type === 'rect') {
                if (this.value === 'circle') {
                    const radius = Math.min(active.width, active.height) / 2;
                    active.set({rx: radius, ry: radius});
                } else if (this.value === 'rounded') {
                    active.set({rx: 24, ry: 24});
                } else {
                    active.set({rx: 0, ry: 0});
                }
                canvas.renderAll();
            }
        };

        document.getElementById('logoShape').onchange = function() {
            const active = canvas.getActiveObject();
            if (active && active.type === 'rect') {
                if (this.value === 'circle') {
                    const radius = Math.min(active.width, active.height) / 2;
                    active.set({rx: radius, ry: radius});
                } else if (this.value === 'rounded') {
                    active.set({rx: 12, ry: 12});
                } else {
                    active.set({rx: 0, ry: 0});
                }
                canvas.renderAll();
            }
        };

        // File Uploads
        document.getElementById('bgFrontInput').onchange = e => uploadBg(e, 'front');
        document.getElementById('bgBackInput').onchange = e => uploadBg(e, 'back');

        function uploadBg(e, side) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('background', file);
            formData.append('side', side);

            fetch('upload_background.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    backgrounds[side] = data.path;
                    if (currentTab === side) loadBackground();
                    showToast('✅ Background uploaded successfully!', 'success');
                } else {
                    showToast('❌ Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('❌ Upload failed', 'error');
                console.error(err);
            });
        }

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
            toast.className = `toast glass-card px-6 py-4 rounded-xl font-bold ${type === 'success' ? 'border-l-4 border-green-500' : 'border-l-4 border-red-500'}`;
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
                canvas.remove(active);
                document.getElementById('propPanel').classList.add('hidden');
            }
            
            canvas.renderAll();
        });

        // Initialize
        initCanvas();
    </script>
</body>
</html>