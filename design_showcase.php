<?php
require_once 'auth_check.php';

// Default design
if (!isset($_SESSION['design_id'])) {
    $_SESSION['design_id'] = 1;
}

// Handle selection
if (isset($_GET['select'])) {
    $_SESSION['design_id'] = (int)$_GET['select'];
    header("Location: dashboard.php?msg=design_updated");
    exit;
}

$designs = [
    [
        'id' => 1,
        'name' => 'Classic Professional',
        'color' => 'bg-blue-700',
        'desc' => 'Traditional layout with weighted header and clean accents.',
        'preview_color' => '#1d4ed8'
    ],
    [
        'id' => 2,
        'name' => 'Modern Minimalist',
        'color' => 'bg-slate-900',
        'desc' => 'Sleek, dark theme with high contrast and sharp typography.',
        'preview_color' => '#0f172a'
    ],
    [
        'id' => 3,
        'name' => 'Playful Academy',
        'color' => 'bg-emerald-600',
        'desc' => 'Vibrant greens and rounded edges, perfect for primary schools.',
        'preview_color' => '#059669'
    ],
    [
        'id' => 4,
        'name' => 'Premium Gold',
        'color' => 'bg-amber-600',
        'desc' => 'Warm, prestigious look with golden accents and soft shadows.',
        'preview_color' => '#d97706'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select ID Design | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        @keyframes logo-load {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-logo-load {
            animation: logo-load 0.7s ease-out forwards;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-12">
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 px-8 py-4 mb-12 shadow-sm">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 flex items-center justify-center transition-transform duration-300 ease-out hover:scale-105 animate-logo-load">
    <img 
        src="assets/images/trishul-logo.png"
        alt="Trishul Logo"
        class="w-14 h-14 object-contain"
    >
</div>

                <h1 class="text-xl font-bold text-slate-800">Design Gallery</h1>
            </div>
            <div class="flex items-center gap-6">
                <a href="dashboard.php" class="text-slate-600 font-semibold hover:text-blue-600 transition">Back to Generator</a>
                <?php if(isAdmin()): ?>
                    <a href="admin_dashboard.php" class="bg-slate-900 text-white px-5 py-2 rounded-xl font-semibold hover:bg-slate-800 transition">Admin Panel</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-8">

        <div class="text-center mb-12">
            <h2 class="text-4xl font-black text-slate-900 mb-4 tracking-tight">Pick Your Design Style</h2>
            <p class="text-slate-500 max-w-2xl mx-auto text-lg">Different schools have different branding expectations. Select a layout that best represents your institution.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($designs as $design): ?>
            <div class="group relative bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 hover:shadow-2xl hover:border-blue-200 transition-all duration-500 overflow-hidden <?= $_SESSION['design_id'] == $design['id'] ? 'ring-4 ring-blue-500/20 border-blue-400' : '' ?>">
                <?php if ($_SESSION['design_id'] == $design['id']): ?>
                    <div class="absolute top-4 right-4 bg-blue-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest z-10">Current</div>
                <?php endif; ?>

                <!-- Preview Area -->
                <div class="relative aspect-[3/4] rounded-[2rem] mb-6 overflow-hidden shadow-inner group-hover:scale-[1.02] transition-transform duration-500">
                    <div class="absolute inset-0 <?= $design['color'] ?>"></div>
                    <!-- Preview Layouts -->
                    <?php if ($design['id'] == 1): ?>
                        <!-- DESIGN 1 — Classic Professional -->
                        <div class="absolute inset-0 flex flex-col items-center py-6 px-4">
                            <div class="w-full h-8 bg-white/10 rounded-md mb-6"></div>
                            <div class="w-20 h-20 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center mb-6">
                                <div class="w-14 h-14 rounded-full bg-white/10"></div>
                            </div>
                            <div class="h-2.5 w-28 bg-white/40 rounded-full mb-3"></div>
                            <div class="h-1.5 w-16 bg-white/20 rounded-full mb-auto"></div>
                            <div class="w-full h-5 bg-white/10 rounded-sm"></div>
                        </div>

                    <?php elseif ($design['id'] == 2): ?>
                        <!-- DESIGN 2 — Modern Minimalist -->
                        <div class="absolute inset-0 p-6 flex flex-col">
                            <div class="w-full h-[2px] bg-white/30 mb-8"></div>
                            <div class="flex gap-5">
                                <div class="w-16 h-20 bg-white/10 rounded border border-white/10"></div>
                                <div class="flex flex-col gap-3 py-2">
                                    <div class="h-2 w-24 bg-white/40 rounded-full"></div>
                                    <div class="h-1.5 w-16 bg-white/20 rounded-full"></div>
                                    <div class="h-1.5 w-20 bg-white/20 rounded-full"></div>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($design['id'] == 3): ?>
                        <!-- DESIGN 3 — Playful Academy -->
                        <div class="absolute inset-0 p-4 flex flex-col items-center">
                            <div class="flex-1 w-full bg-white/10 rounded-[2rem] flex flex-col items-center justify-center p-4 mb-4">
                                <div class="w-24 h-24 bg-white/20 rounded-3xl border-4 border-white/10 shadow-lg"></div>
                            </div>
                            <div class="w-full h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                                <div class="h-2.5 w-24 bg-white/40 rounded-full"></div>
                            </div>
                        </div>

                    <?php elseif ($design['id'] == 4): ?>
                        <!-- DESIGN 4 — Premium Gold -->
                        <div class="absolute inset-0 flex flex-col items-center">
                            <div class="w-full h-12 bg-gradient-to-b from-white/20 to-transparent mb-6"></div>
                            <div class="w-20 h-24 bg-white/5 border-2 border-amber-400/30 rounded shadow-2xl flex items-center justify-center mb-6">
                                <div class="w-16 h-20 bg-white/10"></div>
                            </div>
                            <div class="h-2.5 w-32 bg-white/40 rounded-full mb-3"></div>
                            <div class="h-1.5 w-20 bg-white/20 rounded-full mb-auto"></div>
                            <div class="w-full h-4 bg-amber-500/20"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="px-2">
                    <h3 class="text-xl font-bold text-slate-900 mb-2"><?= $design['name'] ?></h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6"><?= $design['desc'] ?></p>
                    <a href="?select=<?= $design['id'] ?>" class="block w-full text-center py-4 rounded-2xl font-bold transition-all duration-300 <?= $_SESSION['design_id'] == $design['id'] ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600' ?>">
                        <?= $_SESSION['design_id'] == $design['id'] ? 'Selected' : 'Select Design' ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
