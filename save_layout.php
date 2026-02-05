<?php
require_once 'auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No layout data provided']);
    exit;
}

$layout_json = json_encode($data['layout']);
$background_front = $data['background_front'] ?? null;
$background_back = $data['background_back'] ?? null;

try {
    // Check if layout already exists for this user
    $stmt = $pdo->prepare("SELECT id FROM custom_layouts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("UPDATE custom_layouts SET layout_json = ?, background_front = ?, background_back = ? WHERE user_id = ?");
        $stmt->execute([$layout_json, $background_front, $background_back, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO custom_layouts (user_id, layout_json, background_front, background_back) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $layout_json, $background_front, $background_back]);
    }

    echo json_encode(['success' => true, 'message' => 'Layout saved successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
