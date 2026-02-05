<?php require_once 'auth_check.php'; ?>
<?php

// Auto-crop and resize image function
function processImage($sourcePath, $destPath) {
    $targetW = 300;
    $targetH = 350;

    // Detect image type
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) return false;
    $mime = $imageInfo['mime'];

    switch($mime) {
        case 'image/jpeg': $img = imagecreatefromjpeg($sourcePath); break;
        case 'image/png': $img = imagecreatefrompng($sourcePath); break;
        case 'image/gif': $img = imagecreatefromgif($sourcePath); break;
        default: return false;
    }

    $w = imagesx($img);
    $h = imagesy($img);
    $srcRatio = $w / $h;
    $targetRatio = $targetW / $targetH;
    $dst = imagecreatetruecolor($targetW, $targetH);
    
    if ($mime == 'image/png') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    if ($srcRatio > $targetRatio) {
        $newW = $h * $targetRatio;
        $x = ($w - $newW) / 2;
        imagecopyresampled($dst, $img, 0, 0, $x, 0, $targetW, $targetH, $newW, $h);
    } else {
        $newH = $w / $targetRatio;
        $y = ($h - $newH) / 2;
        imagecopyresampled($dst, $img, 0, 0, 0, $y, $targetW, $targetH, $w, $newH);
    }

    imagejpeg($dst, $destPath, 95);
    imagedestroy($img);
    imagedestroy($dst);
    return true;
}

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$photoPath = '';
if (!empty($_FILES['photo']['tmp_name'])) {
    $fileName = 'student_' . time() . '.jpg';
    $photoPath = $uploadDir . $fileName;
    if (!processImage($_FILES['photo']['tmp_name'], $photoPath)) {
        $_SESSION['error'] = 'Failed to process image';
        header("Location: index.php"); exit;
    }
}

// Logo processing
$logoPath = $_SESSION['custom_logo'] ?? 'uploads/logo.png';
if (!empty($_FILES['school_logo']['tmp_name'])) {
    $logoName = 'logo_' . time() . '.png';
    $logoPath = $uploadDir . $logoName;
    move_uploaded_file($_FILES['school_logo']['tmp_name'], $logoPath);
    $_SESSION['custom_logo'] = $logoPath;
}

$academic_year = $_POST['academic_year'] ?? '2025-26';
$design_id = $_SESSION['design_id'] ?? 1;

// Log to Database
try {
    $stmt = $pdo->prepare("INSERT INTO id_generations (user_id, mode, design_id, logo_path, total_cards, academic_year) VALUES (?, 'manual', ?, ?, 1, ?)");
    $stmt->execute([$_SESSION['user_id'], $design_id, $logoPath, $academic_year]);
} catch (Exception $e) {}

// Send Email Alert
$userName = $_SESSION['username'];
$userEmail = $_SESSION['email'] ?? 'N/A';
$to = ADMIN_EMAIL;
$subject = "ID Card Generated - Manual - $userName";
$message = "
User: $userName
Email: $userEmail
Action: Manual Generation
Total Cards: 1
Design ID: $design_id
Academic Year: $academic_year
Date: " . date('Y-m-d H:i:s') . "
";
@mail($to, $subject, $message, "From: noreply@littlekrish.com");

// Save student data to session
$_SESSION['student'] = [
    'name'    => strtoupper($_POST['student_name']),
    'dob'     => $_POST['dob'],
    'blood'   => strtoupper($_POST['blood_group']),
    'parent'  => strtoupper($_POST['parent_name']),
    'phone'   => $_POST['phone'],
    'address' => $_POST['address'],
    'class'   => strtoupper($_POST['student_class']),
    'photo'   => $photoPath,
    'logo'    => $logoPath,
    'year'    => $academic_year,
    'design_id' => $design_id
];

header("Location: view_card.php");
exit;
?>
