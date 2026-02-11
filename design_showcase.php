<?php
require_once 'auth_check.php';

// Fetch current selection
$selected_design = 'design1';
if (isDatabaseConnected()) {
    try {
        $stmt = $pdo->prepare("SELECT selected_design FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $selected_design = $user['selected_design'] ?? 'design1';
        }
    } catch (PDOException $e) {
        // Column probably doesn't exist. Auto-fix attempt:
        if ($e->getCode() == '42S22') {
             try {
                $pdo->exec("ALTER TABLE users ADD COLUMN selected_design VARCHAR(50) DEFAULT 'design1'");
             } catch (Exception $ex) {}
        }
        $selected_design = 'design1';
    }
}

// Handle design selection update
if (isset($_POST['action']) && $_POST['action'] === 'select_design') {
    $new_design = $_POST['design_id'] ?? 'design1';
    $valid_designs = ['design1', 'design2', 'design3', 'design4'];
    
    if (in_array($new_design, $valid_designs) && isDatabaseConnected()) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET selected_design = ? WHERE id = ?");
            $stmt->execute([$new_design, $_SESSION['user_id']]);
            $selected_design = $new_design;
        } catch (PDOException $e) {
            // Silently fail if column missing
            $selected_design = $new_design;
        }
    }
}

// Initialize variables
$student_name = '';
$dob = '';
$blood_group = '';
$class = '';
$parent_name = '';
$contact_no = '';
$address = '';
$photo_url = '';
$school_logo = 'assets/images/trishul-logo.png'; // Default logo

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_name'])) {
    $student_name = htmlspecialchars($_POST['student_name'] ?? '');
    $dob = htmlspecialchars($_POST['dob'] ?? '');
    $blood_group = htmlspecialchars($_POST['blood_group'] ?? '');
    $class = htmlspecialchars($_POST['class'] ?? '');
    $parent_name = htmlspecialchars($_POST['parent_name'] ?? '');
    $contact_no = htmlspecialchars($_POST['contact_no'] ?? '');
    $address = htmlspecialchars($_POST['address'] ?? '');
    
    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $photo_url = 'data:image/' . $ext . ';base64,' . base64_encode(file_get_contents($_FILES['photo']['tmp_name']));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vishwas Montessori ID Card Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .id-card {
            width: 350px;
            height: 550px;
            background: linear-gradient(135deg, #fef9e7 0%, #fdf5dc 100%);
            position: relative;
            overflow: hidden;
        }
        
        .id-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255, 200, 100, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 180, 80, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .marble-texture {
            background-image: 
                linear-gradient(135deg, transparent 25%, rgba(255, 220, 150, 0.2) 25%, rgba(255, 220, 150, 0.2) 50%, transparent 50%, transparent 75%, rgba(255, 220, 150, 0.2) 75%),
                linear-gradient(45deg, transparent 25%, rgba(255, 200, 120, 0.15) 25%, rgba(255, 200, 120, 0.15) 50%, transparent 50%, transparent 75%, rgba(255, 200, 120, 0.15) 75%);
            background-size: 60px 60px, 80px 80px;
        }
        
        .photo-frame {
            width: 140px;
            height: 160px;
            background: white;
            border: 3px solid #f39c12;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .grass-decoration {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 60px;
            background: linear-gradient(to top, #9b9b42 0%, #b8b85c 30%, transparent 100%);
            clip-path: polygon(
                0% 100%, 2% 85%, 4% 90%, 6% 80%, 8% 88%, 10% 75%, 12% 85%, 14% 78%, 
                16% 86%, 18% 82%, 20% 88%, 22% 80%, 24% 90%, 26% 83%, 28% 88%, 30% 78%,
                32% 86%, 34% 82%, 36% 90%, 38% 80%, 40% 87%, 42% 82%, 44% 88%, 46% 80%,
                48% 90%, 50% 83%, 52% 88%, 54% 78%, 56% 86%, 58% 82%, 60% 90%, 62% 80%,
                64% 87%, 66% 82%, 68% 88%, 70% 80%, 72% 90%, 74% 83%, 76% 88%, 78% 78%,
                80% 86%, 82% 82%, 84% 90%, 86% 80%, 88% 87%, 90% 82%, 92% 88%, 94% 80%,
                96% 90%, 98% 85%, 100% 88%, 100% 100%
            );
        }
        
        .leaf {
            position: absolute;
            font-size: 24px;
            opacity: 0.6;
        }
        
        .school-building {
            width: 280px;
            height: 180px;
            margin: 0 auto;
        }
        
        @media print {
            body { margin: 0; padding: 0; background: white; }
            .no-print { display: none !important; }
            .id-card-container { page-break-after: always; display: block !important; }
        }

        /* --- Design 2: Modern Corporate --- */
        .id-design2 { background: #ffffff; border: 1px solid #ddd; border-radius: 0; }
        .id-design2 .header-strip { height: 12px; background: #2c3e50; width: 100%; position: absolute; top: 0; left: 0; }
        .id-design2 .photo-box { width: 130px; height: 130px; border: 4px solid #f8f9fa; border-radius: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .id-design2 .student-name { border-bottom: 2px solid #2c3e50; display: inline-block; padding-bottom: 2px; }

        /* --- Design 3: Vertical Split --- */
        .id-design3 { background: #ffffff; display: flex; flex-direction: row; }
        .id-design3 .side-bar { width: 80px; height: 100%; background: #2980b9; position: absolute; left: 0; top: 0; }
        .id-design3 .photo-circle { width: 140px; height: 140px; border-radius: 50%; border: 5px solid #ffffff; box-shadow: 0 0 15px rgba(0,0,0,0.1); z-index: 10; }
        .id-design3 .content-area { margin-left: 80px; width: 270px; padding: 20px; text-align: left; }

        /* --- Design 4: Premium Dark --- */
        .id-design4 { background: #1a1a2e; color: #ffffff; }
        .id-design4 .glow-border { border: 2px solid #e9c46a; box-shadow: 0 0 20px rgba(233, 196, 106, 0.2); }
        .id-design4 .premium-text { color: #e9c46a; font-family: 'Outfit', sans-serif; letter-spacing: 1px; }
        .id-design4 .field-label { color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; }

        /* --- Preview Cards UI --- */
        .design-preview-card {
            transition: all 0.3s ease;
            cursor: pointer;
            border: 3px solid transparent;
        }
        .design-preview-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .design-preview-card.active {
            border-color: #0d9488;
            background: #f0fdfa;
        }
        .mini-preview {
            width: 100%;
            height: 120px;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            color: #666;
            overflow: hidden;
            border: 1px solid #eee;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 p-8">
    
    <!-- Form Section -->
    <div class="no-print max-w-4xl mx-auto mb-8 bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-3xl font-bold text-teal-600 mb-6 text-center">ID Card Generator</h2>
        
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Student Name *</label>
                    <input type="text" name="student_name" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                           value="<?php echo $student_name; ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date of Birth *</label>
                    <input type="date" name="dob" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                           value="<?php echo $dob; ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Blood Group *</label>
                    <select name="blood_group" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                        <option value="">Select Blood Group</option>
                        <option value="A+" <?php echo $blood_group === 'A+' ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo $blood_group === 'A-' ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo $blood_group === 'B+' ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo $blood_group === 'B-' ? 'selected' : ''; ?>>B-</option>
                        <option value="O+" <?php echo $blood_group === 'O+' ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo $blood_group === 'O-' ? 'selected' : ''; ?>>O-</option>
                        <option value="AB+" <?php echo $blood_group === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo $blood_group === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Class *</label>
                    <input type="text" name="class" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                           value="<?php echo $class; ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Parent's Name *</label>
                    <input type="text" name="parent_name" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                           value="<?php echo $parent_name; ?>">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number *</label>
                    <input type="tel" name="contact_no" required 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                           value="<?php echo $contact_no; ?>">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Address *</label>
                    <textarea name="address" required rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"><?php echo $address; ?></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Student Photo</label>
                    <input type="file" name="photo" accept="image/*" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="flex gap-4 justify-center mt-6">
                <button type="submit" 
                        class="bg-teal-600 hover:bg-teal-700 text-white font-semibold px-8 py-3 rounded-lg transition duration-200">
                    Generate ID Card
                </button>
                <button type="button" onclick="window.print()" 
                        class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-8 py-3 rounded-lg transition duration-200">
                    Print ID Card
                </button>
            </div>
        </form>
    </div>

    <!-- Design Showcase Selection -->
    <div class="no-print max-w-5xl mx-auto mb-12">
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="text-2xl">🎨</span> Design Showcase
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            $designs = [
                ['id' => 'design1', 'name' => 'Design 1 - Eco Montessori', 'desc' => 'Bright, leaf-based nature theme'],
                ['id' => 'design2', 'name' => 'Design 2 - Modern Corp', 'desc' => 'Sharp, clean professional look'],
                ['id' => 'design3', 'name' => 'Design 3 - Vertical Split', 'desc' => 'Elegant split-side branding'],
                ['id' => 'design4', 'name' => 'Design 4 - Premium Dark', 'desc' => 'Gold accents on dark luxury'],
            ];

            foreach ($designs as $d):
                $isActive = ($selected_design === $d['id']);
            ?>
            <div class="design-preview-card rounded-2xl p-4 bg-white shadow-sm <?= $isActive ? 'active' : '' ?>">
                <!-- Thumbnail Logic -->
                <div class="mini-preview <?= $d['id'] === 'design4' ? 'bg-slate-900' : ($d['id'] === 'design3' ? 'bg-blue-50' : 'bg-slate-50') ?>">
                    <div class="text-center">
                        <div class="font-bold text-[10px] <?= $d['id'] === 'design4' ? 'text-amber-400' : ($d['id'] === 'design3' ? 'text-blue-600' : 'text-slate-400') ?>"><?= strtoupper($d['id']) ?></div>
                        <div class="w-12 h-1 bg-slate-200 mx-auto mt-1 rounded-full"></div>
                    </div>
                </div>

                <h4 class="font-bold text-sm text-gray-800 mb-1 text-center"><?= $d['name'] ?></h4>
                <p class="text-[10px] text-gray-500 mb-4 text-center"><?= $d['desc'] ?></p>

                <form method="POST">
                    <input type="hidden" name="action" value="select_design">
                    <input type="hidden" name="design_id" value="<?= $d['id'] ?>">
                    <button type="submit" 
                            class="w-full py-2 rounded-xl border-2 font-bold text-xs transition-all
                                   <?= $isActive 
                                       ? 'bg-teal-600 border-teal-600 text-white cursor-default' 
                                       : 'bg-white border-teal-100 text-teal-600 hover:bg-teal-50 hover:border-teal-500' ?>">
                        <?= $isActive ? '✓ Active' : 'Select Design' ?>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ID Cards Display -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): 
        // Variable mapping for ease of use in designs
        $blood = $blood_group;
        $contact = $contact_no;
        $photo = $photo_url;
        // School logo is statically defined in Design 1, 
        // but for others we can use a placeholder or the same SVG logic.
    ?>
    <div class="flex gap-8 flex-wrap justify-center pb-20">
        
        <?php if ($selected_design === 'design1'): ?>
            <!-- EXISTING Design 1 (Eco Montessori) - UNTOUCHED -->
            <div class="id-card-container">
                <div class="id-card border-4 border-gray-800 rounded-lg shadow-2xl relative ">
                    <div class="text-center pt-6 px-6 relative z-10">
                        <div class="flex justify-center mb-3">
                            <svg width="100" height="70" viewBox="0 0 100 70" class="mx-auto">
                                <path d="M20,30 Q50,20 80,30 L80,50 Q50,60 20,50 Z" fill="#f39c12" stroke="#e67e22" stroke-width="2"/>
                                <line x1="50" y1="20" x2="50" y2="60" stroke="#e67e22" stroke-width="2"/>
                                <circle cx="50" cy="40" r="8" fill="#16a085" stroke="#0e6655" stroke-width="2"/>
                                <ellipse cx="50" cy="40" rx="8" ry="5" fill="none" stroke="#0e6655" stroke-width="1"/>
                                <line x1="42" y1="40" x2="58" y2="40" stroke="#0e6655" stroke-width="1"/>
                                <g transform="translate(35, 10)">
                                    <circle cx="0" cy="0" r="4" fill="#16a085"/><path d="M-3,4 L-5,12 M3,4 L5,12 M-2,7 L2,7" stroke="#16a085" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>
                                <g transform="translate(65, 10)">
                                    <circle cx="0" cy="0" r="4" fill="#16a085"/><path d="M-3,4 L-5,12 M3,4 L5,12 M-2,7 L2,7" stroke="#16a085" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>
                                <path d="M30,15 Q50,5 70,15" fill="none" stroke="#f39c12" stroke-width="3"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-teal-600 tracking-wide mb-0">VISHWAS MONTESSORI</h1>
                        <p class="text-orange-500 font-semibold text-sm tracking-wider">DISCOVER - LEARN & GROW</p>
                    </div>
                    <div class="leaf" style="left: 15px; top: 200px;">🍃</div>
                    <div class="leaf" style="right: 15px; top: 280px; transform: rotate(180deg);">🍃</div>
                    <div class="flex justify-center mt-6 relative z-10">
                        <div class="photo-frame"><?php if ($photo): ?><img src="<?= $photo ?>" alt="Photo"><?php endif; ?></div>
                    </div>
                    <div class="px-8 mt-6 space-y-3 relative z-10">
                        <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-semibold text-sm">Name: <span class="font-normal"><?= $student_name ?></span></p>
                        </div>
                        <div class="flex gap-3">
                            <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-xs">D.O.B: <span class="font-normal"><?= $dob ?></span></p>
                            </div>
                            <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-xs">Blood: <span class="font-normal"><?= $blood ?></span></p>
                            </div>
                        </div>
                        <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-semibold text-sm">Class: <span class="font-normal"><?= $class ?></span></p>
                        </div>
                    </div>
                    <div class="grass-decoration h-[300px] bg-bottom bg-cover" style="background-image: url('assets/images/11.png');"></div>
                </div>
            </div>
            <!-- Back Side (Design 1) -->
            <div class="id-card-container">
                <div class="id-card border-4 border-gray-800 rounded-lg shadow-2xl relative ">
                    <div class="px-8 pt-8 space-y-4 relative z-10">
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-bold text-sm mb-1">Parent's Name</p>
                            <p class="text-gray-700 text-sm"><?= $parent_name ?></p>
                        </div>
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-bold text-sm mb-1">Contact No</p>
                            <p class="text-gray-700 text-sm"><?= $contact ?></p>
                        </div>
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300 min-h-[80px]">
                            <p class="text-gray-800 font-bold text-sm mb-1">Address</p>
                            <p class="text-gray-700 text-xs"><?= nl2br($address) ?></p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-b from-yellow-300 via-yellow-400 to-yellow-500 pb-4" style="height: 257px;">
                        <div class="school-building mt-4 mx-4  ml-8 h-[115px] rounded-lg overflow-hidden shadow-md bg-center bg-cover" style="background-image: url('assets/images/9.png');"></div>
                        <div class="text-center px-4 mt-2">
                             <h2 class="text-2xl font-bold text-red-800 mb-1" style="text-shadow: 2px 2px 0px #fff, -1px -1px 0px #fff, 1px -1px 0px #fff, -1px 1px 0px #fff;">EDUVISHWAS</h2>
                             <p class="text-purple-800 font-bold text-sm mb-2">MONTESSORI PVT. LTD</p>
                             <div class="text-[10px] space-y-0.5 font-semibold text-blue-900 leading-tight">
                                <p>Address: 1st floor, Eshwar Towers, Reddy Gunta, Karakambadi Road, Tirupati- 517501</p>
                                <p>Mobile: 9440274135 | Email: Vishwasmontessori@gmail.com</p>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($selected_design === 'design2'): ?>
            <!-- Front Side (Design 2 - Modern Corporate) -->
            <div class="id-card-container">
                <div class="id-card border-4 border-gray-800 rounded-lg shadow-2xl relative bg-gradient-to-b from-green-100 via-green-200 to-green-300 ">
                    <div class="text-center pt-6 px-6 relative z-10">
                        <div class="flex justify-center mb-3">
                            <svg width="100" height="70" viewBox="0 0 100 70" class="mx-auto">
                                <path d="M20,30 Q50,20 80,30 L80,50 Q50,60 20,50 Z" fill="#f39c12" stroke="#e67e22" stroke-width="2"/>
                                <line x1="50" y1="20" x2="50" y2="60" stroke="#e67e22" stroke-width="2"/>
                                <circle cx="50" cy="40" r="8" fill="#16a085" stroke="#0e6655" stroke-width="2"/>
                                <ellipse cx="50" cy="40" rx="8" ry="5" fill="none" stroke="#0e6655" stroke-width="1"/>
                                <line x1="42" y1="40" x2="58" y2="40" stroke="#0e6655" stroke-width="1"/>
                                <g transform="translate(35, 10)">
                                    <circle cx="0" cy="0" r="4" fill="#16a085"/><path d="M-3,4 L-5,12 M3,4 L5,12 M-2,7 L2,7" stroke="#16a085" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>
                                <g transform="translate(65, 10)">
                                    <circle cx="0" cy="0" r="4" fill="#16a085"/><path d="M-3,4 L-5,12 M3,4 L5,12 M-2,7 L2,7" stroke="#16a085" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>
                                <path d="M30,15 Q50,5 70,15" fill="none" stroke="#f39c12" stroke-width="3"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-teal-600 tracking-wide mb-0">VISHWAS MONTESSORI</h1>
                        <p class="text-orange-500 font-semibold text-sm tracking-wider">DISCOVER - LEARN & GROW</p>
                    </div>
                          <div class="leaf absolute" style="left: 15px; top: 200px;">
    <img src="assets/images/6.png" class="w-8 h-8 object-contain">
</div>

<div class="leaf absolute" style="right: 15px; top: 280px; transform: rotate(180deg);">
    <img src="assets/images/7.png" class="w-8 h-8 object-contain">
</div>
                    <div class="flex justify-center mt-6 relative z-10">
                        <div class="photo-frame"><?php if ($photo): ?><img src="<?= $photo ?>" alt="Photo"><?php endif; ?></div>
                    </div>
                    <div class="px-8 mt-6 space-y-3 relative z-10">
                        <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-semibold text-sm">Name: <span class="font-normal"><?= $student_name ?></span></p>
                        </div>
                        <div class="flex gap-3">
                            <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-xs">D.O.B: <span class="font-normal"><?= $dob ?></span></p>
                            </div>
                            <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-xs">Blood: <span class="font-normal"><?= $blood ?></span></p>
                            </div>
                        </div>
                        <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-semibold text-sm">Class: <span class="font-normal"><?= $class ?></span></p>
                        </div>
                    </div>
                    <div class="grass-decoration h-[300px] bg-bottom bg-cover" style="background-image: url('assets/images/11.png');"></div>
                </div>
            </div>
            <!-- Back Side (Design 2) -->
            <div class="id-card-container">
                <div class="id-card border-4 border-gray-800 rounded-lg shadow-2xl relative bg-gradient-to-b from-green-100 via-green-200 to-green-300 ">
                    <div class="px-8 pt-8 space-y-4 relative z-10">
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-bold text-sm mb-1">Parent's Name</p>
                            <p class="text-gray-700 text-sm"><?= $parent_name ?></p>
                        </div>
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-bold text-sm mb-1">Contact No</p>
                            <p class="text-gray-700 text-sm"><?= $contact ?></p>
                        </div>
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300 min-h-[80px]">
                            <p class="text-gray-800 font-bold text-sm mb-1">Address</p>
                            <p class="text-gray-700 text-xs"><?= nl2br($address) ?></p>
                        </div>
                    </div>
                <div class="absolute bottom-0 left-0 right-0 
              bg-gradient-to-b from-green-300 via-green-400 to-green-500 
                pb-4" style="height: 257px;">
                        <div class="school-building mt-4 mx-4 h-[115px] rounded-lg overflow-hidden  bg-center bg-contain w-auto" style="background-image: url('assets/images/4.png');"></div>
                        <div class="text-center px-4 mt-2">
                             <h2 class="text-2xl font-bold text-red-800 mb-1" style="text-shadow: 2px 2px 0px #fff, -1px -1px 0px #fff, 1px -1px 0px #fff, -1px 1px 0px #fff;">EDUVISHWAS</h2>
                             <p class="text-purple-800 font-bold text-sm mb-2">MONTESSORI PVT. LTD</p>
                             <div class="text-[10px] space-y-0.5 font-semibold text-blue-900 leading-tight">
                                <p>Address: 1st floor, Eshwar Towers, Reddy Gunta, Karakambadi Road, Tirupati- 517501</p>
                                <p>Mobile: 9440274135 | Email: Vishwasmontessori@gmail.com</p>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($selected_design === 'design3'): ?>
            <!-- Front Side (Design 3 - Vertical Split) -->
           <div class="id-card-container">
<div class="id-card border-4 border-gray-800 rounded-lg shadow-2xl relative 
bg-gradient-to-b from-red-100 via-red-200 to-red-300">
                    <div class="text-center pt-6 px-6 relative z-10">
                        <div class="flex justify-center mb-3">
                            <svg width="100" height="70" viewBox="0 0 100 70" class="mx-auto">
                                <path d="M20,30 Q50,20 80,30 L80,50 Q50,60 20,50 Z" fill="#f39c12" stroke="#e67e22" stroke-width="2"/>
                                <line x1="50" y1="20" x2="50" y2="60" stroke="#e67e22" stroke-width="2"/>
                                <circle cx="50" cy="40" r="8" fill="#16a085" stroke="#0e6655" stroke-width="2"/>
                                <ellipse cx="50" cy="40" rx="8" ry="5" fill="none" stroke="#0e6655" stroke-width="1"/>
                                <line x1="42" y1="40" x2="58" y2="40" stroke="#0e6655" stroke-width="1"/>
                                <g transform="translate(35, 10)">
                                    <circle cx="0" cy="0" r="4" fill="#16a085"/><path d="M-3,4 L-5,12 M3,4 L5,12 M-2,7 L2,7" stroke="#16a085" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>
                                <g transform="translate(65, 10)">
                                    <circle cx="0" cy="0" r="4" fill="#16a085"/><path d="M-3,4 L-5,12 M3,4 L5,12 M-2,7 L2,7" stroke="#16a085" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>
                                <path d="M30,15 Q50,5 70,15" fill="none" stroke="#f39c12" stroke-width="3"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-teal-600 tracking-wide mb-0">VISHWAS MONTESSORI</h1>
                        <p class="text-orange-500 font-semibold text-sm tracking-wider">DISCOVER - LEARN & GROW</p>
                    </div>
                          <div class="leaf absolute" style="left: 15px; top: 200px;">
    <img src="assets/images/2.png" class="w-8 h-8 object-contain">
</div>

<div class="leaf absolute" style="right: 15px; top: 280px; transform: rotate(180deg);">
    <img src="assets/images/8.png" class="w-8 h-8 object-contain">
</div>
                    <div class="flex justify-center mt-6 relative z-10">
                        <div class="photo-frame"><?php if ($photo): ?><img src="<?= $photo ?>" alt="Photo"><?php endif; ?></div>
                    </div>
                    <div class="px-8 mt-6 space-y-3 relative z-10">
                        <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-semibold text-sm">Name: <span class="font-normal"><?= $student_name ?></span></p>
                        </div>
                        <div class="flex gap-3">
                            <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-xs">D.O.B: <span class="font-normal"><?= $dob ?></span></p>
                            </div>
                            <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-xs">Blood: <span class="font-normal"><?= $blood ?></span></p>
                            </div>
                        </div>
                        <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-semibold text-sm">Class: <span class="font-normal"><?= $class ?></span></p>
                        </div>
                    </div>
                    <div class="grass-decoration h-[300px] bg-bottom bg-cover clip-path-border-box" style="background-image: url('assets/images/11.png');"></div>
                </div>
            </div>
            <!-- Back Side (Design 3) -->
            <div class="id-card-container">
<div class="id-card border-4 border-gray-800 rounded-lg shadow-2xl relative 
bg-gradient-to-b from-red-100 via-red-200 to-red-300">
                    <div class="px-8 pt-8 space-y-4 relative z-10">
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-bold text-sm mb-1">Parent's Name</p>
                            <p class="text-gray-700 text-sm"><?= $parent_name ?></p>
                        </div>
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-bold text-sm mb-1">Contact No</p>
                            <p class="text-gray-700 text-sm"><?= $contact ?></p>
                        </div>
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300 min-h-[80px]">
                            <p class="text-gray-800 font-bold text-sm mb-1">Address</p>
                            <p class="text-gray-700 text-xs"><?= nl2br($address) ?></p>
                        </div>
                    </div>
                <div class="absolute bottom-0 left-0 right-0 
             bg-gradient-to-b from-red-100 via-red-200 to-red-300
                pb-4" style="height: 257px;">
                        <div class="school-building mt-4 mx-4 h-[115px] overflow-hidden  bg-center bg-contain w-auto" style="background-image: url('assets/images/4.png');"></div>
                        <div class="text-center px-4 mt-2">
                             <h2 class="text-2xl font-bold text-red-800 mb-1" style="text-shadow: 2px 2px 0px #fff, -1px -1px 0px #fff, 1px -1px 0px #fff, -1px 1px 0px #fff;">EDUVISHWAS</h2>
                             <p class="text-purple-800 font-bold text-sm mb-2">MONTESSORI PVT. LTD</p>
                             <div class="text-[10px] space-y-0.5 font-semibold text-blue-900 leading-tight">
                                <p>Address: 1st floor, Eshwar Towers, Reddy Gunta, Karakambadi Road, Tirupati- 517501</p>
                                <p>Mobile: 9440274135 | Email: Vishwasmontessori@gmail.com</p>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($selected_design === 'design4'): ?>
            <!-- Front Side (Design 4 - Premium Dark) -->
            <div class="id-card-container">
                <div class="id-card border-4 border-gray-800 rounded-lg shadow-2xl relative bg-gradient-to-b from-green-100 via-green-200 to-green-300 ">
                    <div class="text-center pt-6 px-6 relative z-10">
                        <div class="flex justify-center mb-3">
                            <svg width="100" height="70" viewBox="0 0 100 70" class="mx-auto">
                                <path d="M20,30 Q50,20 80,30 L80,50 Q50,60 20,50 Z" fill="#f39c12" stroke="#e67e22" stroke-width="2"/>
                                <line x1="50" y1="20" x2="50" y2="60" stroke="#e67e22" stroke-width="2"/>
                                <circle cx="50" cy="40" r="8" fill="#16a085" stroke="#0e6655" stroke-width="2"/>
                                <ellipse cx="50" cy="40" rx="8" ry="5" fill="none" stroke="#0e6655" stroke-width="1"/>
                                <line x1="42" y1="40" x2="58" y2="40" stroke="#0e6655" stroke-width="1"/>
                                <g transform="translate(35, 10)">
                                    <circle cx="0" cy="0" r="4" fill="#16a085"/><path d="M-3,4 L-5,12 M3,4 L5,12 M-2,7 L2,7" stroke="#16a085" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>
                                <g transform="translate(65, 10)">
                                    <circle cx="0" cy="0" r="4" fill="#16a085"/><path d="M-3,4 L-5,12 M3,4 L5,12 M-2,7 L2,7" stroke="#16a085" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>
                                <path d="M30,15 Q50,5 70,15" fill="none" stroke="#f39c12" stroke-width="3"/>
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-teal-600 tracking-wide mb-0">VISHWAS MONTESSORI</h1>
                        <p class="text-orange-500 font-semibold text-sm tracking-wider">DISCOVER - LEARN & GROW</p>
                    </div>
                    <div class="leaf" style="left: 15px; top: 200px;">🍃</div>
                    <div class="leaf" style="right: 15px; top: 280px; transform: rotate(180deg);">🍃</div>
                    <div class="flex justify-center mt-6 relative z-10">
                        <div class="photo-frame"><?php if ($photo): ?><img src="<?= $photo ?>" alt="Photo"><?php endif; ?></div>
                    </div>
                    <div class="px-8 mt-6 space-y-3 relative z-10">
                        <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-semibold text-sm">Name: <span class="font-normal"><?= $student_name ?></span></p>
                        </div>
                        <div class="flex gap-3">
                            <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-xs">D.O.B: <span class="font-normal"><?= $dob ?></span></p>
                            </div>
                            <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-xs">Blood: <span class="font-normal"><?= $blood ?></span></p>
                            </div>
                        </div>
                        <div class="bg-white/50 rounded-lg p-2 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-semibold text-sm">Class: <span class="font-normal"><?= $class ?></span></p>
                        </div>
                    </div>
                    <div class="grass-decoration h-[300px] bg-bottom bg-cover" style="background-image: url('assets/images/11.png');"></div>
                </div>
            </div>
            <!-- Back Side (Design 2) -->
            <div class="id-card-container">
                <div class="id-card border-4 border-gray-800 rounded-lg shadow-2xl relative bg-gradient-to-b from-green-100 via-green-200 to-green-300 ">
                    <div class="px-8 pt-8 space-y-4 relative z-10">
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-bold text-sm mb-1">Parent's Name</p>
                            <p class="text-gray-700 text-sm"><?= $parent_name ?></p>
                        </div>
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300">
                            <p class="text-gray-800 font-bold text-sm mb-1">Contact No</p>
                            <p class="text-gray-700 text-sm"><?= $contact ?></p>
                        </div>
                        <div class="bg-white/50 rounded-lg p-3 border-b-2 border-gray-300 min-h-[80px]">
                            <p class="text-gray-800 font-bold text-sm mb-1">Address</p>
                            <p class="text-gray-700 text-xs"><?= nl2br($address) ?></p>
                        </div>
                    </div>
                <div class="absolute bottom-0 left-0 right-0 
              bg-gradient-to-b from-green-300 via-green-400 to-green-500 
                pb-4" style="height: 257px;">
                        <div class="school-building mt-4 mx-4 h-[115px] rounded-lg overflow-hidden shadow-md bg-center bg-contain w-auto" style="background-image: url('assets/images/4.png');"></div>
                        <div class="text-center px-4 mt-2">
                             <h2 class="text-2xl font-bold text-red-800 mb-1" style="text-shadow: 2px 2px 0px #fff, -1px -1px 0px #fff, 1px -1px 0px #fff, -1px 1px 0px #fff;">EDUVISHWAS</h2>
                             <p class="text-purple-800 font-bold text-sm mb-2">MONTESSORI PVT. LTD</p>
                             <div class="text-[10px] space-y-0.5 font-semibold text-blue-900 leading-tight">
                                <p>Address: 1st floor, Eshwar Towers, Reddy Gunta, Karakambadi Road, Tirupati- 517501</p>
                                <p>Mobile: 9440274135 | Email: Vishwasmontessori@gmail.com</p>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>
    
</body>
</html>