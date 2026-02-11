<?php
require_once 'auth_check.php';

// Fetch current selection and signature
$selected_design = 'design1';
$authorized_signature = null; // Initialize to prevent undefined variable errors
if (isDatabaseConnected()) {
    try {
        $stmt = $pdo->prepare("SELECT selected_design, authorized_signature FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $selected_design = $user['selected_design'] ?? 'design1';
            $authorized_signature = $user['authorized_signature'] ?? null;
        }
    } catch (PDOException $e) {
        // Column probably doesn't exist. Auto-fix attempt:
        if ($e->getCode() == '42S22') {
             try {
                $pdo->exec("ALTER TABLE users ADD COLUMN selected_design VARCHAR(50) DEFAULT 'design1'");
             } catch (Exception $ex) {}
        }
        $selected_design = 'design1';
        $authorized_signature = null;
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

// Handle Authorized Signature Upload (Design 4 Admin Only)
$upload_error = '';
$upload_success = '';
if (isset($_POST['action']) && $_POST['action'] === 'upload_signature' && isAdmin()) {
    if (isset($_FILES['authorized_signature']) && $_FILES['authorized_signature']['error'] === 0) {
        $file = $_FILES['authorized_signature'];
        $allowed_exts = ['png', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed_exts)) {
            $upload_error = "Only PNG, JPG, and JPEG are allowed.";
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $upload_error = "File size must be less than 2MB.";
        } else {
            $upload_dir = 'uploads/signatures/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $new_filename = 'sig_' . uniqid() . '.' . $ext;
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET authorized_signature = ? WHERE id = ?");
                    $stmt->execute([$new_filename, $_SESSION['user_id']]);
                    $authorized_signature = $new_filename;
                    $upload_success = "Signature uploaded successfully!";
                } catch (Exception $e) {
                    $upload_error = "Database update failed.";
                }
            } else {
                $upload_error = "Failed to move uploaded file.";
            }
        }
    } else {
        $upload_error = "Please select a valid image file.";
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
        
        /* ========================================
           CR80 STANDARD CARD DIMENSIONS
           Physical: 2.125" × 3.375"
           Screen: 204px × 324px
           Print: 2.125in × 3.375in @ 300 DPI
           Scale Factor: 0.58 (from 350px to 204px)
        ======================================== */
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        /* Base Card - Screen Display */
        .id-card {
            width: 204px;
            height: 324px;
            background: linear-gradient(135deg, #fef9e7 0%, #fdf5dc 100%);
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
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
            background-size: 35px 35px, 45px 45px;
        }
        
        .photo-frame {
            width: 80px;
            height: 92px;
            background: white;
            border: 2px solid #f39c12;
            border-radius: 8px;
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
            height: 35px;
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
            font-size: 14px;
            opacity: 0.6;
        }
        
        .school-building {
            width: 160px;
            height: 105px;
            margin: 0 auto;
        }

        /* --- Design 2: Modern Corporate --- */
        .id-design2 { background: #ffffff; border: 1px solid #ddd; border-radius: 0; }
        .id-design2 .header-strip { height: 7px; background: #2c3e50; width: 100%; position: absolute; top: 0; left: 0; }
        .id-design2 .photo-box { width: 75px; height: 75px; border: 2px solid #f8f9fa; border-radius: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .id-design2 .student-name { border-bottom: 1px solid #2c3e50; display: inline-block; padding-bottom: 1px; }

        /* --- Design 3: Vertical Split --- */
        .id-design3 { background: #ffffff; display: flex; flex-direction: row; }
        .id-design3 .side-bar { width: 46px; height: 100%; background: #2980b9; position: absolute; left: 0; top: 0; }
        .id-design3 .photo-circle { width: 80px; height: 80px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 0 8px rgba(0,0,0,0.1); z-index: 10; }
        .id-design3 .content-area { margin-left: 46px; width: 158px; padding: 12px; text-align: left; }

        /* --- Design 4: Premium Dark --- */
        .id-design4 { background: #1a1a2e; color: #ffffff; }
        .id-design4 .glow-border { border: 1px solid #e9c46a; box-shadow: 0 0 12px rgba(233, 196, 106, 0.2); }
        .id-design4 .premium-text { color: #e9c46a; font-family: 'Outfit', sans-serif; letter-spacing: 0.6px; }
        .id-design4 .field-label { color: #94a3b8; font-size: 0.5rem; text-transform: uppercase; }

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
        
        /* ========================================
           PRINT MEDIA QUERY - CR80 STANDARD
           Exact physical dimensions for printing
        ======================================== */
        @media print {
            /* Reset body and hide UI elements */
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Hide all non-print elements */
            .no-print,
            button,
            form,
            .design-preview-card,
            .mini-preview,
            nav,
            header,
            footer:not(.premium-footer) {
                display: none !important;
            }
            
            /* Fix the flex wrapper that causes overlapping */
            .print-cards-wrapper,
            .flex.gap-8.flex-wrap {
                display: block !important;
                gap: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                flex-wrap: nowrap !important;
            }
            
            /* Each card container gets its own page */
            .id-card-container {
                page-break-after: always !important;
                page-break-inside: avoid !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 2.125in !important;
                height: 3.375in !important;
                position: relative !important;
                overflow: visible !important;
            }
            
            /* CR80 Standard Card Sizing */
            .id-card {
                width: 2.125in !important;
                height: 3.375in !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                page-break-inside: avoid !important;
                position: relative !important;
                overflow: hidden !important;
                display: block !important;
                transform: none !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Preserve backgrounds and gradients */
            .id-card,
            .id-card * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* Remove shadows and transforms that can cause issues */
            .shadow-2xl,
            .shadow-lg,
            .shadow-md {
                box-shadow: none !important;
            }
            
            /* Ensure absolute positioned elements stay in place */
            .absolute {
                position: absolute !important;
            }
            
            /* Ensure relative positioned elements stay in place */
            .relative {
                position: relative !important;
            }
            
            /* Page size configuration */
            @page {
                size: 2.125in 3.375in !important;
                margin: 0 !important;
            }
            
            /* Prevent any unwanted page breaks within card content */
            .id-card > * {
                page-break-inside: avoid !important;
            }
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

    <!-- Authorized Signature Upload (Design 4 Only + Admin Only) -->
    <?php if ($selected_design === 'design4' && isAdmin()): ?>
    <div class="no-print max-w-4xl mx-auto mb-12 bg-slate-900 rounded-[2.5rem] p-8 shadow-2xl border border-amber-500/30">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="flex-1">
                <h3 class="text-2xl font-black text-amber-400 mb-2">Upload Authorized Signature</h3>
                <p class="text-slate-400 text-sm">Upload a transparent PNG signature to be displayed on Design 4. Max size 2MB.</p>
                
                <?php if ($upload_error): ?>
                    <p class="text-red-400 text-xs font-bold mt-2">⚠️ <?= $upload_error ?></p>
                <?php endif; ?>
                <?php if ($upload_success): ?>
                    <p class="text-emerald-400 text-xs font-bold mt-2">✓ <?= $upload_success ?></p>
                <?php endif; ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4 items-center">
                <input type="hidden" name="action" value="upload_signature">
                <div class="relative group">
                    <input type="file" name="authorized_signature" accept=".png,.jpg,.jpeg" required
                           class="hidden" id="sig_input" onchange="document.getElementById('sig_btn_text').innerText = this.files[0].name.substring(0, 15) + '...'">
                    <label for="sig_input" class="cursor-pointer bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-2xl border-2 border-dashed border-amber-500/30 flex items-center gap-3 transition-all duration-300">
                        <span id="sig_btn_text" class="text-sm font-bold opacity-80">Choose Signature</span>
                    </label>
                </div>
                <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-slate-900 px-8 py-3 rounded-2xl font-black shadow-lg shadow-amber-500/20 transition-all duration-300">
                    Upload Signature
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ID Cards Display -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): 
        // Variable mapping for ease of use in designs
        $blood = $blood_group;
        $contact = $contact_no;
        $photo = $photo_url;
        // School logo is statically defined in Design 1, 
        // but for others we can use a placeholder or the same SVG logic.
    ?>
    <div class="print-cards-wrapper flex gap-8 flex-wrap justify-center pb-20">
        
        <?php if ($selected_design === 'design1'): ?>
            <!-- EXISTING Design 1 (Eco Montessori) - CR80 OPTIMIZED -->
            <div class="id-card-container">
                <div class="id-card rounded-lg shadow-2xl relative">
                    <div class="text-center pt-3 px-3 relative z-10">
                        <div class="flex justify-center mb-1">
                            <svg width="58" height="41" viewBox="0 0 100 70" class="mx-auto">
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
                        <h1 class="text-sm font-bold text-teal-600 tracking-wide mb-0">VISHWAS MONTESSORI</h1>
                        <p class="text-orange-500 font-semibold text-[7px] tracking-wider">DISCOVER - LEARN & GROW</p>
                    </div>
                    <div class="leaf" style="left: 9px; top: 116px;">🍃</div>
                    <div class="leaf" style="right: 9px; top: 163px; transform: rotate(180deg);">🍃</div>
                    <div class="flex justify-center mt-2 relative z-10">
                        <div class="photo-frame"><?php if ($photo): ?><img src="<?= $photo ?>" alt="Photo"><?php endif; ?></div>
                    </div>
                    <div class="px-4 mt-2 space-y-1 relative z-10">
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-semibold text-[7px]">Name: <span class="font-normal"><?= $student_name ?></span></p>
                        </div>
                        <div class="flex gap-1">
                            <div class="bg-white/50 rounded p-1 border-b border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-[6px]">D.O.B: <span class="font-normal"><?= $dob ?></span></p>
                            </div>
                            <div class="bg-white/50 rounded p-1 border-b border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-[6px]">Blood: <span class="font-normal"><?= $blood ?></span></p>
                            </div>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-semibold text-[7px]">Class: <span class="font-normal"><?= $class ?></span></p>
                        </div>
                    </div>
                    <div class="grass-decoration h-[175px] bg-bottom bg-cover" style="background-image: url('assets/images/11.png');"></div>
                </div>
            </div>
            <!-- Back Side (Design 1) -->
            <div class="id-card-container">
                <div class="id-card rounded-lg shadow-2xl relative" style="background: linear-gradient(135deg, #fef9e7 0%, #fdf5dc 100%);">
                    <div class="px-4 pt-4 space-y-2 relative z-10">
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Parent's Name</p>
                            <p class="text-gray-700 text-[7px]"><?= $parent_name ?></p>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Contact No</p>
                            <p class="text-gray-700 text-[7px]"><?= $contact ?></p>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300 min-h-[47px]">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Address</p>
                            <p class="text-gray-700 text-[6px]"><?= nl2br($address) ?></p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-b from-yellow-300 via-yellow-400 to-yellow-500 pb-2" style="height: 150px;">
                        <div class="school-building mt-2 mx-2 ml-5 h-[67px] rounded-lg overflow-hidden shadow-md bg-center bg-cover" style="background-image: url('assets/images/9.png');"></div>
                        <div class="text-center px-2 mt-1">
                             <h2 class="text-sm font-bold text-red-800 mb-0.5" style="text-shadow: 1px 1px 0px #fff, -1px -1px 0px #fff, 1px -1px 0px #fff, -1px 1px 0px #fff;">EDUVISHWAS</h2>
                             <p class="text-purple-800 font-bold text-[7px] mb-1">MONTESSORI PVT. LTD</p>
                             <div class="text-[6px] space-y-0 font-semibold text-blue-900 leading-tight">
                                <p>Address: 1st floor, Eshwar Towers, Reddy Gunta, Karakambadi Road, Tirupati- 517501</p>
                                <p>Mobile: 9440274135 | Email: Vishwasmontessori@gmail.com</p>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($selected_design === 'design2'): ?>
            <!-- Front Side (Design 2 - Modern Corporate - CR80 OPTIMIZED) -->
            <div class="id-card-container">
                <div class="id-card rounded-lg shadow-2xl relative bg-gradient-to-b from-green-100 via-green-200 to-green-300">
                    <div class="text-center pt-3 px-3 relative z-10">
                        <div class="flex justify-center mb-1">
                            <svg width="58" height="41" viewBox="0 0 100 70" class="mx-auto">
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
                        <h1 class="text-sm font-bold text-teal-600 tracking-wide mb-0">VISHWAS MONTESSORI</h1>
                        <p class="text-orange-500 font-semibold text-[7px] tracking-wider">DISCOVER - LEARN & GROW</p>
                    </div>
                    <div class="leaf" style="left: 9px; top: 116px;">🍃</div>
                    <div class="leaf" style="right: 9px; top: 163px; transform: rotate(180deg);">🍃</div>
                    <div class="flex justify-center mt-2 relative z-10">
                        <div class="photo-frame"><?php if ($photo): ?><img src="<?= $photo ?>" alt="Photo"><?php endif; ?></div>
                    </div>
                    <div class="px-4 mt-2 space-y-1 relative z-10">
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-semibold text-[7px]">Name: <span class="font-normal"><?= $student_name ?></span></p>
                        </div>
                        <div class="flex gap-1">
                            <div class="bg-white/50 rounded p-1 border-b border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-[6px]">D.O.B: <span class="font-normal"><?= $dob ?></span></p>
                            </div>
                            <div class="bg-white/50 rounded p-1 border-b border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-[6px]">Blood: <span class="font-normal"><?= $blood ?></span></p>
                            </div>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-semibold text-[7px]">Class: <span class="font-normal"><?= $class ?></span></p>
                        </div>
                    </div>
                    <div class="grass-decoration h-[175px] bg-bottom bg-cover" style="background-image: url('assets/images/11.png');"></div>
                </div>
            </div>
            <!-- Back Side (Design 2 - CR80 OPTIMIZED) -->
            <div class="id-card-container">
                <div class="id-card rounded-lg shadow-2xl relative bg-gradient-to-b from-green-100 via-green-200 to-green-300">
                    <div class="px-4 pt-4 space-y-2 relative z-10">
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Parent's Name</p>
                            <p class="text-gray-700 text-[7px]"><?= $parent_name ?></p>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Contact No</p>
                            <p class="text-gray-700 text-[7px]"><?= $contact ?></p>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300 min-h-[47px]">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Address</p>
                            <p class="text-gray-700 text-[6px]"><?= nl2br($address) ?></p>
                        </div>
                    </div>
                <div class="absolute bottom-0 left-0 right-0 
              bg-gradient-to-b from-green-300 via-green-400 to-green-500 
                pb-2" style="height: 150px;">
                        <div class="school-building mt-2 mx-2 h-[67px] rounded-lg overflow-hidden shadow-md bg-center bg-contain w-auto" style="background-image: url('assets/images/4.png');"></div>
                        <div class="text-center px-2 mt-1">
                             <h2 class="text-sm font-bold text-red-800 mb-0.5" style="text-shadow: 1px 1px 0px #fff, -1px -1px 0px #fff, 1px -1px 0px #fff, -1px 1px 0px #fff;">EDUVISHWAS</h2>
                             <p class="text-purple-800 font-bold text-[7px] mb-1">MONTESSORI PVT. LTD</p>
                             <div class="text-[6px] space-y-0 font-semibold text-blue-900 leading-tight">
                                <p>Address: 1st floor, Eshwar Towers, Reddy Gunta, Karakambadi Road, Tirupati- 517501</p>
                                <p>Mobile: 9440274135 | Email: Vishwasmontessori@gmail.com</p>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($selected_design === 'design3'): ?>
            <!-- Front Side (Design 3 - Vertical Split - CR80 OPTIMIZED) -->
           <div class="id-card-container">
<div class="id-card rounded-lg shadow-2xl relative 
bg-gradient-to-b from-red-100 via-red-200 to-red-300">
                    <div class="text-center pt-3 px-3 relative z-10">
                        <div class="flex justify-center mb-1">
                            <svg width="58" height="41" viewBox="0 0 100 70" class="mx-auto">
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
                        <h1 class="text-sm font-bold text-teal-600 tracking-wide mb-0">VISHWAS MONTESSORI</h1>
                        <p class="text-orange-500 font-semibold text-[7px] tracking-wider">DISCOVER - LEARN & GROW</p>
                    </div>
                          <div class="leaf absolute" style="left: 9px; top: 116px;">
    <img src="assets/images/2.png" class="w-5 h-5 object-contain">
</div>

<div class="leaf absolute" style="right: 9px; top: 163px; transform: rotate(180deg);">
    <img src="assets/images/8.png" class="w-5 h-5 object-contain">
</div>
                    <div class="flex justify-center mt-2 relative z-10">
                        <div class="photo-frame"><?php if ($photo): ?><img src="<?= $photo ?>" alt="Photo"><?php endif; ?></div>
                    </div>
                    <div class="px-4 mt-2 space-y-1 relative z-10">
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-semibold text-[7px]">Name: <span class="font-normal"><?= $student_name ?></span></p>
                        </div>
                        <div class="flex gap-1">
                            <div class="bg-white/50 rounded p-1 border-b border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-[6px]">D.O.B: <span class="font-normal"><?= $dob ?></span></p>
                            </div>
                            <div class="bg-white/50 rounded p-1 border-b border-gray-300 flex-1">
                                <p class="text-gray-800 font-semibold text-[6px]">Blood: <span class="font-normal"><?= $blood ?></span></p>
                            </div>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-semibold text-[7px]">Class: <span class="font-normal"><?= $class ?></span></p>
                        </div>
                    </div>
                    <div class="grass-decoration h-[175px] bg-bottom bg-cover clip-path-border-box" style="background-image: url('assets/images/11.png');"></div>
                </div>
            </div>
            <!-- Back Side (Design 3 - CR80 OPTIMIZED) -->
            <div class="id-card-container">
<div class="id-card rounded-lg shadow-2xl relative 
bg-gradient-to-b from-red-100 via-red-200 to-red-300">
                    <div class="px-4 pt-4 space-y-2 relative z-10">
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Parent's Name</p>
                            <p class="text-gray-700 text-[7px]"><?= $parent_name ?></p>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Contact No</p>
                            <p class="text-gray-700 text-[7px]"><?= $contact ?></p>
                        </div>
                        <div class="bg-white/50 rounded p-1 border-b border-gray-300 min-h-[47px]">
                            <p class="text-gray-800 font-bold text-[7px] mb-0.5">Address</p>
                            <p class="text-gray-700 text-[6px]"><?= nl2br($address) ?></p>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-b from-red-300 via-red-400 to-red-500 pb-2" style="height: 150px;">
                        <div class="school-building mt-2 mx-2 h-[67px] rounded-lg overflow-hidden shadow-md bg-center bg-contain" style="background-image: url('assets/images/3.png');"></div>
                        <div class="text-center px-2 mt-1">
                             <h2 class="text-sm font-bold text-red-800 mb-0.5" style="text-shadow: 1px 1px 0px #fff, -1px -1px 0px #fff, 1px -1px 0px #fff, -1px 1px 0px #fff;">EDUVISHWAS</h2>
                             <p class="text-purple-800 font-bold text-[7px] mb-1">MONTESSORI PVT. LTD</p>
                             <div class="text-[6px] space-y-0 font-semibold text-blue-900 leading-tight">
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
                <style>
                    .premium-dark-card {
                        width: 204px;
                        height: 324px;
                        background: darkslategray ;
                        border-radius: 10px;
                        display: flex;
                        flex-direction: column;
                        overflow: hidden;
                        position: relative;
                        box-sizing: border-box;
                    }
                    .premium-header {
                        text-align: center;
                        padding: 12px 12px 9px;
                        border-bottom: 1px solid rgba(212, 175, 55, 0.3);
                    }
                    .premium-logo {
                        width: 30px;
                        height: 30px;
                        margin: 0 auto 5px;
                        object-fit: contain;
                    }
                    .premium-title {
                        font-size: 11px;
                        font-weight: 800;
                        color: #d4af37;
                        letter-spacing: 0.6px;
                        margin: 0;
                        text-transform: uppercase;
                        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
                    }
                    .premium-subtitle {
                        font-size: 6px;
                        color: #94a3b8;
                        margin-top: 2px;
                        letter-spacing: 1.2px;
                        text-transform: uppercase;
                    }
                    .premium-photo-section {
                        padding: 12px;
                        text-align: center;
                    }
                    .premium-photo-frame {
                        width: 80px;
                        height: 92px;
                        margin: 0 auto;
                        border: 2px solid #d4af37;
                        border-radius: 7px;
                        overflow: hidden;
                        box-shadow: 0 2px 7px rgba(212, 175, 55, 0.3);
                        background: #0f172a;
                    }
                    .premium-photo-frame img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                    }
                    .premium-details {
                        padding: 0 15px;
                        flex-grow: 1;
                    }
                    .premium-name {
                        font-size: 12px;
                        font-weight: 800;
                        color: #ffffff;
                        text-align: center;
                        margin: 0 0 9px 0;
                        letter-spacing: 0.3px;
                        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
                    }
                    .premium-divider {
                        height: 1px;
                        background: linear-gradient(90deg, transparent, #d4af37, transparent);
                        margin: 9px 0;
                    }
                    .premium-info-row {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 7px;
                        padding: 5px 7px;
                        background: rgba(255, 255, 255, 0.05);
                        border-left: 2px solid #d4af37;
                        border-radius: 2px;
                    }
                    .premium-label {
                        font-size: 6.5px;
                        color: #94a3b8;
                        text-transform: uppercase;
                        letter-spacing: 0.6px;
                        font-weight: 600;
                    }
                    .premium-value {
                        font-size: 7px;
                        color: #ffffff;
                        font-weight: 600;
                    }
                    .premium-footer {
                        padding: 9px 12px;
                        background: rgba(0, 0, 0, 0.3);
                        border-top: 1px solid rgba(212, 175, 55, 0.3);
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-end;
                    }
                    .premium-id-info {
                        font-size: 5.5px;
                        color: #64748b;
                        line-height: 1.4;
                    }
                    .premium-signature-box {
                        text-align: center;
                        min-width: 78px;
                    }
                    .premium-signature-area {
                        width: 78px;
                        height: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        border-bottom: 1px solid #d4af37;
                        margin-bottom: 3px;
                    }
                    .premium-signature-area img {
                        max-width: 100%;
                        max-height: 100%;
                        object-fit: contain;
                        filter: brightness(1.2) contrast(1.1);
                    }
                    .premium-signature-label {
                        font-size: 6px;
                        color: #d4af37;
                        font-weight: 700;
                        letter-spacing: 0.6px;
                        text-transform: uppercase;
                    }
                </style>
                
                <div class="premium-dark-card">
                    <!-- Header Section -->
                    <div class="premium-header">
                        <?php if ($school_logo): ?>
                            <img src="<?= $school_logo ?>" class="premium-logo" alt="School Logo">
                        <?php endif; ?>
                        <h1 class="premium-title">Vishwas Montessori</h1>
                        <p class="premium-subtitle">Student Identity Card</p>
                    </div>
                    
                    <!-- Photo Section -->
                    <div class="premium-photo-section">
                        <div class="premium-photo-frame">
                            <?php if ($photo): ?>
                                <img src="<?= $photo ?>" alt="Student Photo">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Student Details -->
                    <div class="premium-details">
                        <h2 class="premium-name"><?= htmlspecialchars($student_name) ?></h2>
                        <div class="premium-divider"></div>
                        
                        <div class="premium-info-row">
                            <span class="premium-label">Class</span>
                            <span class="premium-value"><?= htmlspecialchars($class) ?></span>
                        </div>
                        
                        <div class="premium-info-row">
                            <span class="premium-label">Blood Group</span>
                            <span class="premium-value"><?= htmlspecialchars($blood) ?></span>
                        </div>
                        
                        <div class="premium-info-row">
                            <span class="premium-label">Contact</span>
                            <span class="premium-value"><?= htmlspecialchars($contact) ?></span>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
            
            <!-- Back Side (Design 4 - Premium Dark) -->
            <div class="id-card-container">
                <style>
                    .premium-back-section {
                        padding: 12px;
                    }
                    .premium-back-title {
                        font-size: 7px;
                        color: #d4af37;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.9px;
                        margin-bottom: 5px;
                        border-bottom: 1px solid rgba(212, 175, 55, 0.3);
                        padding-bottom: 3px;
                    }
                    .premium-back-content {
                        font-size: 8px;
                        color: #e2e8f0;
                        line-height: 1.6;
                        margin-bottom: 11px;
                    }
                    .premium-school-info {
                        background: rgba(0, 0, 0, 0.3);
                        padding: 9px;
                        border-radius: 5px;
                        border: 1px solid rgba(212, 175, 55, 0.2);
                        margin-top: auto;
                    }
                    .premium-school-name {
                        font-size: 10px;
                        color: #d4af37;
                        font-weight: 800;
                        margin-bottom: 5px;
                        text-align: center;
                    }
                    .premium-school-details {
                        font-size: 6px;
                        color: #94a3b8;
                        line-height: 1.6;
                        text-align: center;
                    }
                </style>
                
                <div class="premium-dark-card" style="padding: 0;">
                    <div class="premium-back-section" style="display: flex; flex-direction: column; height: 100%;">
                        <!-- Header -->
                        <div class="premium-header" style="border-bottom: 1px solid rgba(212, 175, 55, 0.3); margin-bottom: 12px;">
                            <h1 class="premium-title" style="font-size: 10px;">Emergency Contact</h1>
                        </div>
                        
                        <!-- Parent Details -->
                        <div style="padding: 0 3px;">
                            <div class="premium-back-title">Parent / Guardian</div>
                            <div class="premium-back-content"><?= htmlspecialchars($parent_name) ?></div>
                            
                            <div class="premium-back-title">Contact Number</div>
                            <div class="premium-back-content"><?= htmlspecialchars($contact) ?></div>
                            
                            <div class="premium-back-title">Address</div>
                            <div class="premium-back-content"><?= nl2br(htmlspecialchars($address)) ?></div>
                        </div>

                        <!-- Footer with Signature -->
                    <div class="premium-footer">
                        <div class="premium-id-info">
                            <div>ID: VIS-2024-<?= str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) ?></div>
                            <div>Valid: <?= date('Y') ?>-<?= date('Y') + 1 ?></div>
                        </div>
                        
                        <div class="premium-signature-box">
                            <div class="premium-signature-area">
                                <?php if (!empty($authorized_signature)): ?>
                                    <img src="uploads/signatures/<?= htmlspecialchars($authorized_signature) ?>" alt="Signature">
                                <?php else: ?>
                                    <div style="width: 100%; border-bottom: 1.5px solid #64748b; margin-bottom: 8px;"></div>
                                <?php endif; ?>
                            </div>
                            <div class="premium-signature-label">Principal</div>
                        </div>
                    </div>
                        
                        <!-- School Information -->
                        <div class="premium-school-info" style="margin-top: auto;">
                            <div class="premium-school-name">EDUVISHWAS MONTESSORI PVT. LTD</div>
                            <div class="premium-school-details">
                                1st Floor, Eshwar Towers, Reddy Gunta<br>
                                Karakambadi Road, Tirupati - 517501<br>
                                Ph: 9440274135<br>
                                Email: vishwasmontessori@gmail.com
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