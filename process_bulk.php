<?php require_once 'auth_check.php'; ?>
<?php
// ...

// Function to read Excel/CSV file
function readExcelFile($filePath) {
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    if ($ext == 'csv') {
        return readCSV($filePath);
    } else {
        // Since we don't have an XLSX library, we must assume it's a CSV or tell user to use CSV
        return readCSV($filePath);
    }
}

function readCSV($filePath) {
    $students = [];
    
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle, 1000, ",");
        if (!$headers) return [];

        // Clean headers (remove BOM, trim whitespace)
        $headers = array_map(function($h) {
            return strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
        }, $headers);
        
        // Dynamic column mapping based on screenshot
        // User's columns: name, grade, dob, fname, mob, add, bg
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
            // Check if we have at least student name and mobile
            if ($idx['name'] !== false && isset($data[$idx['name']])) {
                
                $mobile = isset($data[$idx['mob']]) ? $data[$idx['mob']] : '';
                // Extract 10 digit mobile if possible
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

// Helper to completely clear a directory
function clearDirectory($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = "$dir/$file";
        (is_dir($path)) ? clearDirectory($path) : unlink($path);
    }
}

// Improved ZIP extraction: Extracts everything, then flattens all images into the root folder
function extractPhotos($zipPath, $extractTo) {
    $extractTo = rtrim($extractTo, '/') . '/';
    if (!is_dir($extractTo)) mkdir($extractTo, 0777, true);
    
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo($extractTo);
        $zip->close();
        
        // Flatten all images from subfolders and handle nested ZIPs
        flattenAndExtract($extractTo, $extractTo);
        return true;
    }
    return false;
}

// Helper to move all images from subfolders to the root and extract nested ZIPs
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
                $newName = basename($path);
                $destPath = $targetRoot . $newName;
                if ($path !== $destPath) {
                    if (file_exists($destPath)) {
                        $newName = rand(100, 999) . '_' . $newName;
                        $destPath = $targetRoot . $newName;
                    }
                    @rename($path, $destPath);
                }
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

// Process image (crop and resize)
function processImage($sourcePath, $destPath) {
    if (!file_exists($sourcePath)) return false;
    
    list($width, $height, $type) = @getimagesize($sourcePath);
    if (!$width || !$height) return false;

    switch ($type) {
        case IMAGETYPE_JPEG: $img = @imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG:  $img = @imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_GIF:  $img = @imagecreatefromgif($sourcePath); break;
        default: return false;
    }

    if (!$img) return false;

    $targetW = 400; 
    $targetH = 480;
    
    $dst = imagecreatetruecolor($targetW, $targetH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
    imagefill($dst, 0, 0, $transparent);

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

    imagejpeg($dst, $destPath, 85);
    imagedestroy($img);
    imagedestroy($dst);
    return true;
}

// Main processing
try {
    $uploadDir = 'uploads/';
    $photosDir = 'uploads/photos/';
    $processedDir = 'uploads/processed/';
    
    foreach ([$uploadDir, $photosDir, $processedDir] as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0777, true);
    }
    
    if (empty($_FILES['excel_file']['tmp_name'])) throw new Exception('Please upload a CSV file');
    
    $fileExt = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
    if ($fileExt !== 'csv') throw new Exception('Only .CSV files are supported. Please convert your Excel file.');

    $excelPath = $uploadDir . 'data_' . time() . '.csv';
    move_uploaded_file($_FILES['excel_file']['tmp_name'], $excelPath);
    
    if (empty($_FILES['photos_zip']['tmp_name'])) throw new Exception('Please upload photos ZIP file');
    
    $zipPath = $uploadDir . 'photos_' . time() . '.zip';
    move_uploaded_file($_FILES['photos_zip']['tmp_name'], $zipPath);
    
    // Clear ALL old photos to avoid conflicts
    clearDirectory($photosDir);
    clearDirectory($processedDir);
    
    if (!extractPhotos($zipPath, $photosDir)) throw new Exception('Failed to extract photos');
    
    $students = readExcelFile($excelPath);
    if (empty($students)) throw new Exception('No valid student data found in CSV.');
    
    // Refresh files list after flattening
    $allFiles = array_diff(scandir($photosDir), array('.', '..'));
    $imageFiles = array_values(array_filter($allFiles, function($f) use ($photosDir) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) && !is_dir($photosDir . $f);
    }));
    natsort($imageFiles);
    $imageFiles = array_values($imageFiles);

    $processedStudents = [];
    foreach ($students as $studentIdx => $student) {
        $photoPath = '';
        $cleanMob = trim($student['mobile']);
        $studentNameClean = strtolower(preg_replace('/[^a-z0-9]/', '', $student['name']));
        
        $foundFile = '';
        // 1. Mobile Match
        foreach ($allFiles as $file) {
            if (!empty($cleanMob) && strpos($file, $cleanMob) === 0 && !is_dir($photosDir . $file)) {
                $foundFile = $file;
                break;
            }
        }
        
        // 2. Name Match
        if (!$foundFile && !empty($studentNameClean)) {
            foreach ($allFiles as $file) {
                $fileNameClean = strtolower(preg_replace('/[^a-z0-9]/', '', pathinfo($file, PATHINFO_FILENAME)));
                if ((strpos($fileNameClean, $studentNameClean) !== false || strpos($studentNameClean, $fileNameClean) !== false) && !is_dir($photosDir . $file)) {
                    $foundFile = $file;
                    break;
                }
            }
        }

        // 3. Sequential Fallback
        if (!$foundFile && isset($imageFiles[$studentIdx])) {
            $foundFile = $imageFiles[$studentIdx];
        }
        
        if ($foundFile) {
            $sourcePath = $photosDir . $foundFile;
            $destPath = $processedDir . 'p_' . $studentIdx . '_' . md5($foundFile) . '.jpg';
            if (processImage($sourcePath, $destPath)) {
                $photoPath = $destPath;
            }
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
            'year'    => '2025-26'
        ];
    }
    
    $_SESSION['bulk_students'] = $processedStudents;
    $_SESSION['success'] = count($processedStudents) . ' ID cards generated successfully!';
    header("Location: view_bulk.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: index.php");
    exit;
}
?>
?>