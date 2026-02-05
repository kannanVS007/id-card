<?php 
require_once 'auth_check.php'; 

if (!isset($_SESSION['is_premium']) || !$_SESSION['is_premium']) {
    die("Premium access required.");
}

// Ensure isolation by redefining necessary functions or including a common setup if safe.
// I'll redefine the core logic to ensure standard flow is NEVER touched.

set_time_limit(300); // 5 minutes for bulk

function processImagePremium($sourcePath, $destPath) {
    if (!file_exists($sourcePath)) return false;
    $targetW = 300; $targetH = 350;
    $imageInfo = @getimagesize($sourcePath);
    if (!$imageInfo) return false;
    $mime = $imageInfo['mime'];
    switch($mime) {
        case 'image/jpeg': $img = @imagecreatefromjpeg($sourcePath); break;
        case 'image/png': $img = @imagecreatefrompng($sourcePath); break;
        default: return false;
    }
    if (!$img) return false;
    $w = imagesx($img); $h = imagesy($img);
    $dst = imagecreatetruecolor($targetW, $targetH);
    imagecopyresampled($dst, $img, 0, 0, 0, 0, $targetW, $targetH, $w, $h);
    imagejpeg($dst, $destPath, 90);
    imagedestroy($img); imagedestroy($dst);
    return true;
}

$uploadDir = 'uploads/premium_bulk_' . time() . '/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Logo handling
$logoPath = 'uploads/logo.png';
if (!empty($_FILES['school_logo']['tmp_name'])) {
    $logoPath = $uploadDir . 'logo.png';
    move_uploaded_file($_FILES['school_logo']['tmp_name'], $logoPath);
}

$students = [];
$error_log = [];

// Handle CSV
if (!empty($_FILES['excel_file']['tmp_name'])) {
    if (($handle = fopen($_FILES['excel_file']['tmp_name'], "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $student = array_combine($headers, $data);
            $students[] = $student;
        }
        fclose($handle);
    }
}

// Handle ZIP
if (!empty($_FILES['photos_zip']['tmp_name'])) {
    $zip = new ZipArchive;
    if ($zip->open($_FILES['photos_zip']['tmp_name']) === TRUE) {
        $tempZipDir = $uploadDir . 'temp_zip/';
        mkdir($tempZipDir, 0777, true);
        $zip->extractTo($tempZipDir);
        $zip->close();

        // Process images
        foreach ($students as &$s) {
            $imgName = $s['photo_name'] ?? '';
            $found = false;
            if ($imgName) {
                $possiblePaths = [
                    $tempZipDir . $imgName,
                    $tempZipDir . $imgName . '.jpg',
                    $tempZipDir . $imgName . '.JPG',
                    $tempZipDir . $imgName . '.png'
                ];
                foreach ($possiblePaths as $p) {
                    if (file_exists($p)) {
                        $dest = $uploadDir . basename($p);
                        if (processImagePremium($p, $dest)) {
                            $s['photo'] = $dest;
                            $found = true;
                            break;
                        }
                    }
                }
            }
            if (!$found) $s['photo'] = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2UyZThmMCI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYzIuNjcgMCA4IDEuMzMgOCA0djJoLTE2di0yYzAtMi42NyA1LjMzLTQgOC00eiIvPjwvc3ZnPg==';
            $s['logo'] = $logoPath;
        }
    }
}

$_SESSION['premium_bulk_data'] = $students;
header("Location: view_bulk_custom.php");
exit;
?>
