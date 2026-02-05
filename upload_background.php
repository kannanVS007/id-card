<?php
require_once 'auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$side = $_POST['side'] ?? 'front'; // 'front' or 'back'

if (!isset($_FILES['background'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['background'];
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$allowed = ['jpg', 'jpeg', 'png'];

if (!in_array(strtolower($extension), $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG and PNG allowed.']);
    exit;
}

$upload_dir = 'uploads/backgrounds/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$filename = 'bg_' . $user_id . '_' . $side . '_' . time() . '.' . $extension;
$target_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    echo json_encode(['success' => true, 'path' => $target_path]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}
?>
