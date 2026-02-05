<?php 
require_once 'auth_check.php'; 

if (!isset($_SESSION['is_premium']) || !$_SESSION['is_premium']) {
    die("Premium access required.");
}

// Reuse processImage from standard flow if possible, or redefine if isolation must be absolute.
// For stability, I'll redefine it to ensure zero coupling.
function processImage($sourcePath, $destPath) {
    $targetW = 300;
    $targetH = 350;
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
    $fileName = 'premium_student_' . time() . '.jpg';
    $photoPath = $uploadDir . $fileName;
    processImage($_FILES['photo']['tmp_name'], $photoPath);
}

$logoPath = $_SESSION['custom_logo'] ?? 'uploads/logo.png';
if (!empty($_FILES['school_logo']['tmp_name'])) {
    $logoName = 'premium_logo_' . time() . '.png';
    $logoPath = $uploadDir . $logoName;
    move_uploaded_file($_FILES['school_logo']['tmp_name'], $logoPath);
}

$student_data = [
    'school_name' => $_POST['school_name'] ?? '',
    'name'    => strtoupper($_POST['student_name']),
    'dob'     => $_POST['dob'],
    'blood'   => strtoupper($_POST['blood_group']),
    'parent'  => strtoupper($_POST['parent_name']),
    'contact' => $_POST['contact'],
    'address' => $_POST['address'],
    'class'   => strtoupper($_POST['student_class']),
    'photo'   => $photoPath,
    'logo'    => $logoPath,
    'mode'    => 'premium'
];

$_SESSION['premium_student'] = $student_data;
header("Location: view_card_custom.php");
exit;
?>
