<?php 
require_once 'auth_check.php'; 
require_once 'id_card_template_custom.php';

if (!isset($_SESSION['premium_bulk_data'])) {
    header("Location: premium_dashboard.php");
    exit;
}

$students = $_SESSION['premium_bulk_data'];

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
    die("No custom layout found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Premium Bulk Preview | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; }
        .print-page { padding: 40px; display: flex; flex-wrap: wrap; gap: 40px; justify-content: center; background: white; }
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .print-page { padding: 0; gap: 20px; }
            .id-card-wrapper { break-inside: avoid; margin-bottom: 20px; }
        }
        .id-card-wrapper { transition: transform 0.2s; }
        .id-card-wrapper:hover { transform: scale(1.02); }
    </style>
</head>
<body class="pb-12">

    <nav class="no-print bg-white/80 backdrop-blur-md border-b border-pink-100 px-8 py-4 sticky top-0 z-50 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Premium Bulk Preview</h1>
            <p class="text-xs text-pink-500 font-bold tracking-tighter uppercase">Total: <?= count($students) ?> Cards Generated</p>
        </div>
        <div class="flex gap-4">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold shadow-lg">Print All Cards</button>
            <a href="premium_dashboard.php" class="bg-white border text-gray-600 px-6 py-2 rounded-xl font-bold">Back</a>
        </div>
    </nav>

    <?php if (empty($students)): ?>
        <div class="max-w-md mx-auto mt-20 text-center p-12 bg-white rounded-3xl shadow-xl">
            <p class="text-gray-400 mb-6 italic">No data processed. Check your CSV file.</p>
            <a href="premium_dashboard.php" class="text-blue-600 font-bold">← Go Back</a>
        </div>
    <?php else: ?>
        <div class="print-page">
            <?php foreach ($students as $index => $student): ?>
                <div class="id-card-wrapper flex flex-col items-center">
                    <div class="flex gap-10">
                        <div class="relative">
                            <span class="no-print absolute -left-8 top-0 text-xs font-bold text-gray-300">#<?= $index + 1 ?> Front</span>
                            <?= generateCustomIDCard($student, $layout, true) ?>
                        </div>
                        <div class="relative">
                            <span class="no-print absolute -left-8 top-0 text-xs font-bold text-gray-300">#<?= $index + 1 ?> Back</span>
                            <?= generateCustomIDCard($student, $layout, false) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</body>
</html>
