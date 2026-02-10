<?php require_once 'auth_check.php'; ?>
<?php
require_once 'id_card_template.php';

$student = $_SESSION['student'] ?? null;

if (!$student) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student ID Card - <?= $student['name'] ?></title>
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
  }
  
  @media print {
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .print-area { 
      position: absolute;
      left: 0;
      top: 0;
    }
    .no-print { display: none !important; }
    @page { margin: 0.5cm; }
  }
</style>
</head>
<body class="bg-gray-100 min-h-screen py-8">

<div class="max-w-7xl mx-auto px-4">
  
  <!-- HEADER -->
  <div class="bg-white rounded-lg shadow-lg p-6 mb-8 no-print">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-teal-600">ID Card Generated Successfully! ✅</h1>
        <p class="text-gray-600">Review and print the ID card below</p>
      </div>
      <div class="flex gap-3">
        <button onclick="window.print()" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg font-semibold transition">
          🖨️ Print Cards
        </button>
        <a href="dashboard.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold transition inline-block">
          ← Back
        </a>
      </div>
    </div>
  </div>

  <!-- ID CARDS DISPLAY -->
  <div class="print-area">
    <div class="grid md:grid-cols-2 gap-8 mb-8">
      
      <!-- FRONT SIDE -->
      <div>
        <h2 class="text-lg font-bold text-gray-800 mb-4 no-print">Front Side</h2>
        <?= generateIDCard($student, true) ?>
      </div>

      <!-- BACK SIDE -->
      <div>
        <h2 class="text-lg font-bold text-gray-800 mb-4 no-print">Back Side</h2>
        <?= generateIDCard($student, false) ?>
      </div>

    </div>
  </div>

  <!-- INSTRUCTIONS -->
  <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg no-print">
    <h3 class="font-bold text-blue-900 mb-2">📋 Printing Instructions:</h3>
    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
      <li>Click "Print Cards" button to print both sides</li>
      <li>For duplex printing: Print front side first, then flip the paper and print back side</li>
      <li>Recommended paper: Glossy photo paper or laminated cardstock</li>
      <li>After printing, laminate the card for durability</li>
    </ul>
  </div>

  <!-- STUDENT DATA SUMMARY -->
  <div class="bg-white rounded-lg shadow-lg p-6 mt-6 no-print">
    <h3 class="font-bold text-gray-800 mb-4">Student Information Summary:</h3>
    <div class="grid md:grid-cols-2 gap-4 text-sm">
      <div><span class="font-semibold">Name:</span> <?= $student['name'] ?></div>
      <div><span class="font-semibold">Class:</span> <?= $student['class'] ?></div>
      <div><span class="font-semibold">Date of Birth:</span> <?= date('d.m.Y', strtotime($student['dob'])) ?></div>
      <div><span class="font-semibold">Blood Group:</span> <?= $student['blood'] ?></div>
      <div><span class="font-semibold">Parent:</span> <?= $student['parent'] ?></div>
      <div><span class="font-semibold">Phone:</span> <?= $student['phone'] ?></div>
      <div class="md:col-span-2"><span class="font-semibold">Address:</span> <?= $student['address'] ?></div>
    </div>
  </div>

</div>

</body>
</html>