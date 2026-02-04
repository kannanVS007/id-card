<?php require_once 'auth_check.php'; ?>
<?php
require_once 'id_card_template.php';

$students = $_SESSION['bulk_students'] ?? [];

if (empty($students)) {
    header("Location: index.php");
    exit;
}

$successMsg = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bulk ID Cards Generated</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');
  
  body { 
    font-family: 'Poppins', sans-serif;
  }
  
  .id-card {
    width: 340px;
    height: 480px;
    position: relative;
    page-break-after: always;
  }
  
  @media print {
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .print-area { 
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
    }
    .no-print { display: none !important; }
    @page { 
      margin: 0.5cm;
      size: A4;
    }
    .card-wrapper {
      page-break-after: always;
      margin-bottom: 1cm;
    }
  }
</style>
</head>
<body class="bg-gray-100 min-h-screen py-8">

<div class="max-w-7xl mx-auto px-4">
  
  <!-- HEADER -->
  <div class="bg-white rounded-lg shadow-lg p-6 mb-8 no-print">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-teal-600">
          <?= $successMsg ?: count($students) . ' ID Cards Generated!' ?> ✅
        </h1>
        <p class="text-gray-600">Review and print all ID cards below</p>
      </div>
      <div class="flex gap-3">
        <button onclick="window.print()" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg font-semibold transition">
          🖨️ Print All Cards
        </button>
        <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold transition inline-block">
          ← Back
        </a>
      </div>
    </div>
  </div>

  <!-- STATISTICS -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 no-print">
    <div class="bg-blue-500 text-white rounded-lg p-4 shadow-lg">
      <div class="text-3xl font-bold"><?= count($students) ?></div>
      <div class="text-sm">Total Students</div>
    </div>
    <div class="bg-green-500 text-white rounded-lg p-4 shadow-lg">
      <div class="text-3xl font-bold"><?= count(array_filter($students, fn($s) => !empty($s['photo']) && strpos($s['photo'], 'placeholder') === false)) ?></div>
      <div class="text-sm">With Photos</div>
    </div>
    <div class="bg-orange-500 text-white rounded-lg p-4 shadow-lg">
      <div class="text-3xl font-bold"><?= count(array_filter($students, fn($s) => empty($s['photo']) || strpos($s['photo'], 'placeholder') !== false)) ?></div>
      <div class="text-sm">Missing Photos</div>
    </div>
    <div class="bg-purple-500 text-white rounded-lg p-4 shadow-lg">
      <div class="text-3xl font-bold"><?= count($students) * 2 ?></div>
      <div class="text-sm">Total Pages</div>
    </div>
  </div>

  <!-- VIEW TOGGLE -->
  <div class="bg-white rounded-lg shadow-lg p-4 mb-6 no-print">
    <div class="flex gap-4 items-center">
      <span class="font-semibold text-gray-700">View:</span>
      <button onclick="showSide('front')" id="btn-front" class="px-4 py-2 bg-teal-600 text-white rounded-lg font-semibold">Front Side</button>
      <button onclick="showSide('back')" id="btn-back" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold">Back Side</button>
      <button onclick="showSide('both')" id="btn-both" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold">Both Sides</button>
    </div>
  </div>

  <!-- ID CARDS DISPLAY -->
  <div class="print-area">
    <?php foreach ($students as $index => $student): ?>
    
    <div class="card-wrapper mb-8">
      <div class="bg-gray-200 p-4 rounded-t-lg no-print">
        <h3 class="font-bold text-gray-800">Card #<?= $index + 1 ?> - <?= $student['name'] ?></h3>
      </div>
      
      <div class="grid md:grid-cols-2 gap-6 bg-white p-6 rounded-b-lg shadow-lg">
        
        <!-- FRONT SIDE -->
        <div class="front-side">
          <h4 class="text-sm font-bold text-gray-600 mb-3 no-print">Front Side</h4>
          <?= generateIDCard($student, true) ?>
        </div>

        <!-- BACK SIDE -->
        <div class="back-side">
          <h4 class="text-sm font-bold text-gray-600 mb-3 no-print">Back Side</h4>
          <?= generateIDCard($student, false) ?>
        </div>

      </div>
    </div>
    
    <?php endforeach; ?>
  </div>

  <!-- PRINTING INSTRUCTIONS -->
  <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mt-8 no-print">
    <h3 class="font-bold text-blue-900 mb-2">📋 Bulk Printing Instructions:</h3>
    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
      <li>Click "Print All Cards" to print all ID cards at once</li>
      <li>For duplex printing: Use "Front Side" button, print all fronts, then flip papers and use "Back Side" button</li>
      <li>Each card will be on a separate page for easy cutting</li>
      <li>Recommended: Use A4 glossy photo paper or cardstock</li>
      <li>After printing, cut cards to size (340mm x 480mm) and laminate</li>
    </ul>
  </div>

</div>

<script>
function showSide(side) {
  const fronts = document.querySelectorAll('.front-side');
  const backs = document.querySelectorAll('.back-side');
  const btnFront = document.getElementById('btn-front');
  const btnBack = document.getElementById('btn-back');
  const btnBoth = document.getElementById('btn-both');
  
  // Reset button styles
  [btnFront, btnBack, btnBoth].forEach(btn => {
    btn.classList.remove('bg-teal-600', 'text-white');
    btn.classList.add('bg-gray-300', 'text-gray-700');
  });
  
  if (side === 'front') {
    fronts.forEach(el => el.style.display = 'block');
    backs.forEach(el => el.style.display = 'none');
    btnFront.classList.remove('bg-gray-300', 'text-gray-700');
    btnFront.classList.add('bg-teal-600', 'text-white');
  } else if (side === 'back') {
    fronts.forEach(el => el.style.display = 'none');
    backs.forEach(el => el.style.display = 'block');
    btnBack.classList.remove('bg-gray-300', 'text-gray-700');
    btnBack.classList.add('bg-teal-600', 'text-white');
  } else {
    fronts.forEach(el => el.style.display = 'block');
    backs.forEach(el => el.style.display = 'block');
    btnBoth.classList.remove('bg-gray-300', 'text-gray-700');
    btnBoth.classList.add('bg-teal-600', 'text-white');
  }
}
</script>

</body>
</html>