<?php 
require_once 'auth_check.php'; 
require_once 'id_card_template_custom.php';

if (!isset($_SESSION['premium_student'])) {
    header("Location: premium_dashboard.php");
    exit;
}

$student = $_SESSION['premium_student'];

// Fetch custom layout for the user
$layout = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM custom_layouts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $layout = $stmt->fetch();
    if ($layout) {
        $layout['layout_json'] = json_decode($layout['layout_json'], true);
    }
} catch (PDOException $e) {}

if (!$layout) {
    die("No custom layout found. Please design one first.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Premium ID Preview | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .id-card-view { box-shadow: none !important; margin: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col items-center py-10 min-h-screen">

    <div class="no-print max-w-4xl w-full px-6 mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Premium ID Preview</h1>
            <p class="text-pink-500 font-bold text-sm">CUSTOM LAYOUT MODE</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-blue-500/30">Print ID Card</button>
            <a href="premium_dashboard.php" class="bg-white border text-gray-600 px-6 py-2 rounded-xl font-bold">Back</a>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-8">
      <!-- FRONT SIDE -->
      <div class="id-card-view">
        <h2 class="text-sm font-black text-gray-400 mb-4 no-print uppercase tracking-widest">Front View</h2>
        <?= generateCustomIDCard($student, $layout, true) ?>
      </div>

      <!-- BACK SIDE -->
      <div class="id-card-view">
        <h2 class="text-sm font-black text-gray-400 mb-4 no-print uppercase tracking-widest">Back View</h2>
        <?= generateCustomIDCard($student, $layout, false) ?>
      </div>
    </div>

    <div class="no-print mt-12 bg-white p-8 rounded-[2rem] shadow-xl border border-pink-100 max-w-2xl w-full mx-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Print Instructions</h3>
        <ul class="space-y-2 text-sm text-gray-600">
            <li>• Use <strong>Chrome</strong> or Edge for the best print results.</li>
            <li>• Set <strong>Layout</strong> to Portrait.</li>
            <li>• Set <strong>Paper Size</strong> to A4 or 4x6 if using photo paper.</li>
            <li>• Set <strong>Margins</strong> to None.</li>
            <li>• Enable <strong>Background graphics</strong> in settings.</li>
        </ul>
    </div>
</body>
</html>
