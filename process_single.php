<?php require_once 'auth_check.php'; ?>
<?php

// Auto-crop and resize image function
function processImage($sourcePath, $destPath) {
    $targetW = 300;
    $targetH = 350;

    // Detect image type
    $imageInfo = getimagesize($sourcePath);
    $mime = $imageInfo['mime'];

    switch($mime) {
        case 'image/jpeg':
            $img = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $img = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $img = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    $w = imagesx($img);
    $h = imagesy($img);

    $srcRatio = $w / $h;
    $targetRatio = $targetW / $targetH;

    $dst = imagecreatetruecolor($targetW, $targetH);
    
    // Preserve transparency for PNG
    if ($mime == 'image/png') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    if ($srcRatio > $targetRatio) {
        // Landscape - crop width
        $newW = $h * $targetRatio;
        $x = ($w - $newW) / 2;
        imagecopyresampled($dst, $img, 0, 0, $x, 0, $targetW, $targetH, $newW, $h);
    } else {
        // Portrait - crop height
        $newH = $w / $targetRatio;
        $y = ($h - $newH) / 2;
        imagecopyresampled($dst, $img, 0, 0, 0, $y, $targetW, $targetH, $w, $newH);
    }

    imagejpeg($dst, $destPath, 95);
    imagedestroy($img);
    imagedestroy($dst);
    
    return true;
}

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$photoPath = '';

// Process uploaded photo
if (!empty($_FILES['photo']['tmp_name'])) {
    $fileName = 'student_' . time() . '.jpg';
    $photoPath = $uploadDir . $fileName;
    
    if (processImage($_FILES['photo']['tmp_name'], $photoPath)) {
        // Convert to relative URL for display
        $photoPath = $photoPath;
    } else {
        $_SESSION['error'] = 'Failed to process image';
        header("Location: index.php");
        exit;
    }
}

// Format date
$dob = $_POST['dob'];
if (strtotime($dob)) {
    $dobFormatted = date('d.m.Y', strtotime($dob));
} else {
    $dobFormatted = $dob;
}

// Save student data to session
$_SESSION['student'] = [
    'name'    => strtoupper($_POST['student_name']),
    'dob'     => $dob,
    'blood'   => strtoupper($_POST['blood_group']),
    'parent'  => strtoupper($_POST['parent_name']),
    'phone'   => $_POST['phone'],
    'address' => $_POST['address'],
    'class'   => strtoupper($_POST['student_class']),
    'photo'   => $photoPath,
    'year'    => '2025-26'
];

header("Location: view_card.php");
exit;
?>