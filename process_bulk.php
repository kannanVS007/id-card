<?php require_once 'auth_check.php'; ?>
<?php

// Function to read Excel/CSV file
function readExcelFile($filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return readCSV($filePath);
}

function readCSV($filePath) {
    $students = [];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        if (!$headers) return [];

        $headers = array_map(function($h) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
        }, $headers);
        
        $idx = [
            'name'  => array_search('name', $headers),
            'class' => array_search('grade', $headers) !== false ? array_search('grade', $headers) : array_search('class', $headers),
            'dob'   => array_search('dob', $headers),
            'parent'=> array_search('fname', $headers),
            'mob'   => array_search('mob', $headers),
            'add'   => array_search('add', $headers),
            'bg'    => array_search('bg', $headers)
        ];

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($idx['name'] !== false && isset($data[$idx['name']])) {
                $mobile = isset($data[$idx['mob']]) ? $data[$idx['mob']] : '';
                preg_match('/(\d{10})/', $mobile, $matches);
                $primaryMob = $matches[1] ?? trim($mobile);
                
                $students[] = [
                    'name'    => $data[$idx['name']] ?? '',
                    'class'   => $data[$idx['class']] ?? 'NURSERY',
                    'dob'     => $data[$idx['dob']] ?? '',
                    'parent'  => $data[$idx['parent']] ?? '',
                    'mobile'  => $primaryMob,
                    'address' => $data[$idx['add']] ?? '',
                    'blood'   => $data[$idx['bg']] ?? ''
                ];
            }
        }
        fclose($handle);
    }
    return $students;
}

function clearDirectory($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = "$dir/$file";
        (is_dir($path)) ? clearDirectory($path) : unlink($path);
    }
}

function extractPhotos($zipPath, $extractTo) {
    $extractTo = rtrim($extractTo, '/') . '/';
    if (!is_dir($extractTo)) mkdir($extractTo, 0777, true);
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo($extractTo);
        $zip->close();
        flattenAndExtract($extractTo, $extractTo);
        return true;
    }
    return false;
}

function flattenAndExtract($currentDir, $targetRoot) {
    if (!is_dir($currentDir)) return;
    $targetRoot = rtrim($targetRoot, '/') . '/';
    $items = array_diff(scandir($currentDir), array('.', '..'));
    foreach ($items as $item) {
        $path = $currentDir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            flattenAndExtract($path, $targetRoot);
        } else {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                @rename($path, $targetRoot . basename($path));
            } elseif ($ext === 'zip') {
                $zip = new ZipArchive;
                if ($zip->open($path) === TRUE) {
                    $zip->extractTo($targetRoot);
                    $zip->close();
                    flattenAndExtract($targetRoot, $targetRoot);
                    @unlink($path);
                }
            }
        }
    }
}

function processImage($sourcePath, $destPath) {
    if (!file_exists($sourcePath)) return false;
    list($width, $height, $type) = @getimagesize($sourcePath);
    if (!$width) return false;

    switch ($type) {
        case IMAGETYPE_JPEG: $img = @imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG:  $img = @imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_GIF:  $img = @imagecreatefromgif($sourcePath); break;
        default: return false;
    }
    if (!$img) return false;

    $targetW = 300; $targetH = 350;
    $dst = imagecreatetruecolor($targetW, $targetH);
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    
    $srcRatio = $width / $height;
    $targetRatio = $targetW / $targetH;

    if ($srcRatio > $targetRatio) {
        $newW = $height * $targetRatio;
        $x = ($width - $newW) / 2;
        imagecopyresampled($dst, $img, 0, 0, $x, 0, $targetW, $targetH, $newW, $height);
    } else {
        $newH = $width / $targetRatio;
        $y = ($height - $newH) / 2;
        imagecopyresampled($dst, $img, 0, 0, 0, $y, $targetW, $targetH, $width, $newH);
    }

    imagejpeg($dst, $destPath, 90);
    imagedestroy($img);
    imagedestroy($dst);
    return true;
}

try {
    $uploadDir = 'uploads/';
    $photosDir = 'uploads/photos/';
    $processedDir = 'uploads/processed/';
    
    foreach ([$uploadDir, $photosDir, $processedDir] as $dir) if (!is_dir($dir)) mkdir($dir, 0777, true);
    
    if (empty($_FILES['excel_file']['tmp_name'])) throw new Exception('Please upload a CSV file');
    $excelPath = $uploadDir . 'data_' . time() . '.csv';
    move_uploaded_file($_FILES['excel_file']['tmp_name'], $excelPath);
    
    if (empty($_FILES['photos_zip']['tmp_name'])) throw new Exception('Please upload photos ZIP file');
    $zipPath = $uploadDir . 'photos_' . time() . '.zip';
    move_uploaded_file($_FILES['photos_zip']['tmp_name'], $zipPath);
    
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

    clearDirectory($photosDir);
    clearDirectory($processedDir);
    if (!extractPhotos($zipPath, $photosDir)) throw new Exception('Failed to extract photos');
    
    $students = readExcelFile($excelPath);
    $allFiles = array_diff(scandir($photosDir), array('.', '..'));
    $imageFiles = array_values(array_filter($allFiles, function($f) use ($photosDir) {
        return !is_dir($photosDir . $f);
    }));
    natsort($imageFiles);
    $imageFiles = array_values($imageFiles);

    $processedStudents = [];
    foreach ($students as $studentIdx => $student) {
        $photoPath = '';
        $cleanMob = trim($student['mobile']);
        $foundFile = '';
        foreach ($allFiles as $file) {
            if (!empty($cleanMob) && strpos($file, $cleanMob) === 0) { $foundFile = $file; break; }
        }
        if (!$foundFile && isset($imageFiles[$studentIdx])) $foundFile = $imageFiles[$studentIdx];
        
        if ($foundFile) {
            $destPath = $processedDir . 'p_' . $studentIdx . '_' . md5($foundFile) . '.jpg';
            if (processImage($photosDir . $foundFile, $destPath)) $photoPath = $destPath;
        }
        
        $processedStudents[] = [
            'name'    => strtoupper($student['name']),
            'dob'     => $student['dob'], 
            'blood'   => strtoupper($student['blood']),
            'parent'  => strtoupper($student['parent']),
            'phone'   => $student['mobile'],
            'address' => $student['address'],
            'class'   => strtoupper($student['class']),
            'photo'   => $photoPath ?: 'offline_placeholder',
            'logo'    => $logoPath,
            'year'    => $academic_year,
            'design_id' => $design_id
        ];
    }
    
    // Log to Database
    $total = count($processedStudents);
    try {
        $stmt = $pdo->prepare("INSERT INTO id_generations (user_id, mode, design_id, logo_path, total_cards, academic_year) VALUES (?, 'bulk', ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $design_id, $logoPath, $total, $academic_year]);
    } catch (Exception $e) {}

    // Email Alert
    $userName = $_SESSION['username'];
    $to = ADMIN_EMAIL;
    $subject = "ID Cards Generated - Bulk - $userName";
    $message = "User: $userName\nMode: Bulk\nTotal: $total\nDesign: $design_id\nYear: $academic_year\nDate: " . date('Y-m-d H:i:s');
    @mail($to, $subject, $message, "From: noreply@littlekrish.com");

    $_SESSION['bulk_students'] = $processedStudents;
    $_SESSION['success'] = $total . ' ID cards generated successfully!';
    header("Location: view_bulk.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: index.php");
    exit;
}
?>
?>