<?php
require_once 'auth_check.php';

if (!isset($_SESSION['is_premium']) || !$_SESSION['is_premium']) {
    header("Location: index.php?error=premium_required");
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
        }
        .canvas-container { 
            margin: 0 auto; 
            box-shadow: 0 20px 60px -12px rgba(0, 0, 0, 0.3);
            border: 3px solid #333;
            position: relative;
        }
        .tab-btn.active { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
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
            background: rgba(102, 126, 234, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }
        .element-btn:hover::before {
            width: 300px;
            height: 300px;
        }
        .element-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-preview {
            position: relative;
            background-size: cover;
            background-position: center;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }
        .input-field-style {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .input-field-style:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .photo-placeholder {
            background: white;
            border: 3px solid #f59e0b;
            border-radius: 24px;
            overflow: hidden;
        }
        .logo-placeholder {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white/80 backdrop-blur-lg border-b border-purple-100 px-8 py-4 mb-8 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 gradient-bg rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-lg">
                    V
                </div>
                <div>
                    <h1 class="text-2xl font-black bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        Visual ID Designer
                    </h1>
                    <p class="text-xs text-gray-500 font-medium">Design beautiful student ID cards</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-gray-600 font-semibold hover:text-purple-600 transition px-4 py-2 rounded-xl hover:bg-purple-50">
                    ← Back to Generator
                </a>
                <button id="saveBtn" class="gradient-bg text-white px-8 py-3 rounded-2xl font-bold hover:shadow-2xl transition transform hover:scale-105 shadow-lg">
                    💾 Save Layout
                </button>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-8 flex gap-8 pb-12">
        <!-- Sidebar Controls -->
        <div class="w-96 space-y-6">
            <!-- Add Elements -->
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-purple-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-800">Add Elements</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <!-- Logo Upload -->
                    <button onclick="addField('logo')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border-2 border-amber-200 hover:border-amber-400">
                        <svg class="w-8 h-8 mb-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        <span class="text-sm font-bold text-amber-900">School Logo</span>
                    </button>

                    <!-- Photo Upload -->
                    <button onclick="addField('photo')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border-2 border-blue-200 hover:border-blue-400">
                        <svg class="w-8 h-8 mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm font-bold text-blue-900">Student Photo</span>
                    </button>

                    <!-- School Name -->
                    <button onclick="addField('text', 'SCHOOL NAME', 'school_name')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl border-2 border-purple-200 hover:border-purple-400">
                        <svg class="w-8 h-8 mb-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="text-sm font-bold text-purple-900">School Name</span>
                    </button>

                    <!-- Student Name -->
                    <button onclick="addField('text', 'Name:', 'name')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border-2 border-green-200 hover:border-green-400">
                        <svg class="w-8 h-8 mb-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-sm font-bold text-green-900">Name</span>
                    </button>

                    <!-- Class -->
                    <button onclick="addField('text', 'Class:', 'class')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-cyan-50 to-blue-50 rounded-2xl border-2 border-cyan-200 hover:border-cyan-400">
                        <span class="text-2xl font-black mb-1 text-cyan-700">📚</span>
                        <span class="text-sm font-bold text-cyan-900">Class</span>
                    </button>

                    <!-- D.O.B -->
                    <button onclick="addField('text', 'D.O.B:', 'dob')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-pink-50 to-rose-50 rounded-2xl border-2 border-pink-200 hover:border-pink-400">
                        <span class="text-2xl font-black mb-1 text-pink-700">🎂</span>
                        <span class="text-sm font-bold text-pink-900">D.O.B</span>
                    </button>

                    <!-- Blood Group -->
                    <button onclick="addField('text', 'Blood group:', 'blood')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-red-50 to-rose-50 rounded-2xl border-2 border-red-200 hover:border-red-400">
                        <span class="text-2xl font-black mb-1 text-red-700">🩸</span>
                        <span class="text-sm font-bold text-red-900">Blood Group</span>
                    </button>

                    <!-- Parent Name -->
                    <button onclick="addField('text', 'Parent\'s Name', 'parent')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl border-2 border-violet-200 hover:border-violet-400">
                        <span class="text-2xl font-black mb-1 text-violet-700">👨‍👩‍👧</span>
                        <span class="text-sm font-bold text-violet-900">Parent Name</span>
                    </button>

                    <!-- Contact No -->
                    <button onclick="addField('text', 'Contact No', 'contact')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-teal-50 to-cyan-50 rounded-2xl border-2 border-teal-200 hover:border-teal-400">
                        <span class="text-2xl font-black mb-1 text-teal-700">📞</span>
                        <span class="text-sm font-bold text-teal-900">Contact</span>
                    </button>

                    <!-- Address -->
                    <button onclick="addField('text', 'Address', 'address')" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl border-2 border-orange-200 hover:border-orange-400 col-span-2">
                        <span class="text-2xl font-black mb-1 text-orange-700">🏠</span>
                        <span class="text-sm font-bold text-orange-900">Address</span>
                    </button>

                    <!-- Custom Input Field -->
                    <button onclick="addCustomInputField()" class="element-btn flex flex-col items-center justify-center p-5 bg-gradient-to-br from-gray-50 to-slate-50 rounded-2xl border-2 border-gray-300 hover:border-gray-500 col-span-2">
                        <span class="text-2xl font-black mb-1 text-gray-700">➕</span>
                        <span class="text-sm font-bold text-gray-900">Custom Input Field</span>
                    </button>
                </div>
            </div>

            <!-- Background Upload -->
            <div class="bg-white p-6 rounded-3xl shadow-xl border border-purple-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-800">Card Background</h3>
                </div>
                <div class="space-y-4">
                    <div class="relative">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Front Side Background</label>
                        <input type="file" id="bgFrontInput" accept="image/*" class="block w-full text-sm text-gray-600
                            file:mr-4 file:py-3 file:px-6
                            file:rounded-2xl file:border-0
                            file:text-sm file:font-bold
                            file:bg-gradient-to-r file:from-violet-500 file:to-purple-500
                            file:text-white hover:file:from-violet-600 hover:file:to-purple-600
                            file:cursor-pointer file:transition file:shadow-lg
                            cursor-pointer border-2 border-dashed border-purple-200 rounded-2xl p-4 hover:border-purple-400 transition" />
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Back Side Background</label>
                        <input type="file" id="bgBackInput" accept="image/*" class="block w-full text-sm text-gray-600
                            file:mr-4 file:py-3 file:px-6
                            file:rounded-2xl file:border-0
                            file:text-sm file:font-bold
                            file:bg-gradient-to-r file:from-violet-500 file:to-purple-500
                            file:text-white hover:file:from-violet-600 hover:file:to-purple-600
                            file:cursor-pointer file:transition file:shadow-lg
                            cursor-pointer border-2 border-dashed border-purple-200 rounded-2xl p-4 hover:border-purple-400 transition" />
                    </div>
                </div>
            </div>

            <!-- Properties Panel -->
            <div id="propPanel" class="bg-white p-6 rounded-3xl shadow-xl border border-purple-100 hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 gradient-bg rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-gray-800">Properties</h3>
                </div>
                <div class="space-y-5">
                    <div id="textProps">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-wider block mb-2">Font Size</label>
                        <input type="range" id="fontSize" min="8" max="72" class="w-full h-2 bg-purple-100 rounded-lg appearance-none cursor-pointer accent-purple-600">
                        <div class="text-sm font-semibold text-purple-600 mt-1" id="fontSizeDisplay">16px</div>
                    </div>
                    <div id="colorProps">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-wider block mb-2">Text Color</label>
                        <input type="color" id="fontColor" class="w-full h-12 rounded-xl cursor-pointer border-2 border-purple-200">
                    </div>
                    <div id="photoShapeControl" class="hidden">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-wider block mb-2">Photo Shape</label>
                        <select id="photoShape" class="w-full p-3 bg-purple-50 border-2 border-purple-200 rounded-xl font-semibold text-gray-700 cursor-pointer focus:outline-none focus:border-purple-500">
                            <option value="rect">Rectangle</option>
                            <option value="rounded">Rounded Corners</option>
                            <option value="circle">Circle</option>
                        </select>
                    </div>
                    <div id="logoShapeControl" class="hidden">
                        <label class="text-xs font-black text-gray-500 uppercase tracking-wider block mb-2">Logo Shape</label>
                        <select id="logoShape" class="w-full p-3 bg-purple-50 border-2 border-purple-200 rounded-xl font-semibold text-gray-700 cursor-pointer focus:outline-none focus:border-purple-500">
                            <option value="rect">Rectangle</option>
                            <option value="rounded">Rounded Corners</option>
                            <option value="circle">Circle</option>
                        </select>
                    </div>
                    <button onclick="removeSelected()" class="w-full py-3 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-2xl font-bold hover:from-red-600 hover:to-pink-600 transition shadow-lg transform hover:scale-105">
                        🗑️ Remove Element
                    </button>
                </div>
            </div>
        </div>

        <!-- Designer Canvas -->
        <div class="flex-1 flex flex-col items-center">
            <!-- Tab Switcher -->
            <div class="flex gap-4 mb-8 bg-white p-2 rounded-3xl shadow-xl border border-purple-100">
                <button onclick="switchTab('front')" id="tabFront" class="tab-btn px-8 py-3 font-bold rounded-2xl transition-all duration-300">
                    🎨 Front Side
                </button>
                <button onclick="switchTab('back')" id="tabBack" class="tab-btn px-8 py-3 font-bold rounded-2xl transition-all duration-300 text-gray-600">
                    🎨 Back Side
                </button>
            </div>

            <!-- Canvas -->
            <div class="canvas-wrapper relative bg-white rounded-lg overflow-hidden">
                <canvas id="idCanvas" width="325" height="500"></canvas>
            </div>
            
            <!-- Tips -->
            <div class="mt-8 bg-white/80 backdrop-blur-sm p-6 rounded-3xl border border-purple-100 shadow-lg max-w-2xl">
                <h4 class="font-black text-gray-800 mb-3 flex items-center gap-2">
                    <span class="text-2xl">💡</span>
                    Pro Tips
                </h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start gap-2">
                        <span class="text-purple-500 font-black">•</span>
                        <span><strong>Drag & Drop:</strong> Move elements anywhere on the card</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-purple-500 font-black">•</span>
                        <span><strong>Resize:</strong> Use corner handles to resize | Hold Shift for proportional scaling</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-purple-500 font-black">•</span>
                        <span><strong>Arrow Keys:</strong> Fine-tune positioning pixel by pixel</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-purple-500 font-black">•</span>
                        <span><strong>Delete Key:</strong> Quick remove selected element</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-purple-500 font-black">•</span>
                        <span><strong>Background:</strong> Upload full card design images for best results</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        const canvas = new fabric.Canvas('idCanvas', {
            backgroundColor: '#f8fafc'
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
                        fill: item.fill || '#1e293b',
                        fontFamily: 'Poppins',
                        fontWeight: item.fontWeight || '600',
                        id: item.id,
                        dataField: item.dataField
                    });
                } else if (item.type === 'photo') {
                    // Student Photo with rounded corners (matching image style)
                    obj = new fabric.Rect({
                        left: item.left,
                        top: item.top,
                        width: item.width || 100,
                        height: item.height || 120,
                        fill: '#e0e7ff',
                        rx: item.rx || 24,
                        ry: item.ry || 24,
                        stroke: '#f59e0b',
                        strokeWidth: 3,
                        id: item.id,
                        dataField: 'photo'
                    });
                } else if (item.type === 'logo') {
                    // Logo placeholder
                    obj = new fabric.Rect({
                        left: item.left,
                        top: item.top,
                        width: item.width || 80,
                        height: item.height || 80,
                        fill: '#fef3c7',
                        rx: item.rx || 12,
                        ry: item.ry || 12,
                        stroke: '#f59e0b',
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
                    // Properly fit the background to cover the entire canvas
                    const canvasAspect = canvas.width / canvas.height;
                    const imgAspect = img.width / img.height;
                    
                    let scale, left = 0, top = 0;
                    
                    if (canvasAspect > imgAspect) {
                        // Canvas is wider - scale to width
                        scale = canvas.width / img.width;
                        top = -(img.height * scale - canvas.height) / 2;
                    } else {
                        // Canvas is taller - scale to height
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
                canvas.setBackgroundColor('#f8fafc', canvas.renderAll.bind(canvas));
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
            layouts[currentTab] = canvas.getObjects()
                .filter(obj => obj.id && obj.dataField) // Only save our custom fields
                .map(obj => {
                    const data = {
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
                    };
                    return data;
                });
        }

        function addField(type, text = 'Text', dataField = 'custom') {
            let obj;
            const id = dataField + '_' + Date.now();
            
            if (type === 'text') {
                obj = new fabric.IText(text, {
                    left: 50,
                    top: 100,
                    fontSize: 14,
                    fill: '#1e293b',
                    fontFamily: 'Poppins',
                    fontWeight: '600',
                    id: id,
                    dataField: dataField
                });
            } else if (type === 'photo') {
                // Student photo with rounded corners (matching the image style)
                obj = new fabric.Rect({
                    left: 90,
                    top: 80,
                    width: 150,
                    height: 180,
                    fill: '#e0e7ff',
                    rx: 24,  // Rounded corners like in the image
                    ry: 24,
                    stroke: '#f59e0b',
                    strokeWidth: 3,
                    id: 'photo_' + Date.now(),
                    dataField: 'photo'
                });
            } else if (type === 'logo') {
                // School logo placeholder
                obj = new fabric.Rect({
                    left: 120,
                    top: 20,
                    width: 80,
                    height: 80,
                    fill: '#fef3c7',
                    rx: 12,
                    ry: 12,
                    stroke: '#f59e0b',
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
            const fieldName = prompt('Enter field label (e.g., "Emergency Contact", "Roll No", etc.):');
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
                
                // Set current shape
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
                
                // Set current shape
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
                    active.set('rx', radius);
                    active.set('ry', radius);
                } else if (this.value === 'rounded') {
                    active.set('rx', 24);
                    active.set('ry', 24);
                } else {
                    active.set('rx', 0);
                    active.set('ry', 0);
                }
                canvas.renderAll();
            }
        };

        document.getElementById('logoShape').onchange = function() {
            const active = canvas.getActiveObject();
            if (active && active.type === 'rect') {
                if (this.value === 'circle') {
                    const radius = Math.min(active.width, active.height) / 2;
                    active.set('rx', radius);
                    active.set('ry', radius);
                } else if (this.value === 'rounded') {
                    active.set('rx', 12);
                    active.set('ry', 12);
                } else {
                    active.set('rx', 0);
                    active.set('ry', 0);
                }
                canvas.renderAll();
            }
        };

        // File Uploads for Background
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
                    
                    // Show success message
                    const msg = document.createElement('div');
                    msg.className = 'fixed top-24 right-8 bg-green-500 text-white px-6 py-3 rounded-2xl shadow-lg font-bold z-50';
                    msg.textContent = '✅ Background uploaded successfully!';
                    document.body.appendChild(msg);
                    setTimeout(() => msg.remove(), 3000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                alert('Upload failed. Please try again.');
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
                    // Show success message
                    const msg = document.createElement('div');
                    msg.className = 'fixed top-24 right-8 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-8 py-4 rounded-2xl shadow-2xl font-bold z-50 transform transition-all';
                    msg.innerHTML = '✅ Layout saved successfully!';
                    document.body.appendChild(msg);
                    setTimeout(() => {
                        msg.style.transform = 'translateX(400px)';
                        setTimeout(() => msg.remove(), 300);
                    }, 2500);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                alert('Save failed. Please try again.');
                console.error(err);
            });
        };

        // Keyboard support
        window.addEventListener('keydown', e => {
            const active = canvas.getActiveObject();
            if (!active) return;

            // Prevent default for arrow keys to avoid page scroll
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }

            const step = e.shiftKey ? 10 : 2; // Shift for larger steps
            
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

        // Add smooth animations on load
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.5s';
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>