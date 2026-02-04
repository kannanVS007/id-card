<?php
session_start();
require_once 'id_card_template.php';

// Sample data for 12 students matching your uploaded photos
$sampleStudents = [
    [
        'name' => 'MANOJ M',
        'dob' => '2020-08-15',
        'blood' => 'AB+ve',
        'parent' => 'RAVI M',
        'phone' => '+91 98808 38396',
        'address' => 'No. 18, 5th Street, RB Avenue, Kamarajapuram, Chennai - 73.',
        'class' => 'SENIOR KG',
        'photo' => 'uploads/photos/data-1.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'V. MANOJ KUMAR',
        'dob' => '2020-05-20',
        'blood' => 'O+ve',
        'parent' => 'KUMAR V',
        'phone' => '+91 95009 87902',
        'address' => 'Block A S2, 2nd floor, Joel Enclave Apartment, Iyappan Nagar, Chennai - 73.',
        'class' => 'SENIOR KG',
        'photo' => 'uploads/photos/data-2.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'ARUN KUMAR P',
        'dob' => '2020-03-12',
        'blood' => 'B+ve',
        'parent' => 'PRAKASH A',
        'phone' => '+91 99529 44411',
        'address' => 'No. 4, Navaneetham Nagar, 4th Street, Sembakkam, Chennai - 600073.',
        'class' => 'JR. KG',
        'photo' => 'uploads/photos/data-3.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'RAJAGOPAL M',
        'dob' => '2020-07-08',
        'blood' => 'O-ve',
        'parent' => 'MOHAN R',
        'phone' => '+91 97103 72278',
        'address' => '21, S2 Deep Homes, 2nd Street, Sundaram Colony, Chennai - 600073',
        'class' => 'NURSERY',
        'photo' => 'uploads/photos/data-4.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'SRIRAM V',
        'dob' => '2020-09-25',
        'blood' => 'B+ve',
        'parent' => 'VIJAY S',
        'phone' => '+91 98405 81967',
        'address' => 'Plot no: 7, S1 Vaibahv Gatik, Prashanth Colony, 3rd street, Sembakkam, Chennai - 600073',
        'class' => 'SENIOR KG',
        'photo' => 'uploads/photos/data-5.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'J. ARANGANATHAN',
        'dob' => '2020-11-30',
        'blood' => 'B+ve',
        'parent' => 'JAYARAM A',
        'phone' => '+91 99405 29308',
        'address' => '13/F2, Vaibhav Sadanandam Apartment, Senthil Avenue 2nd Street, Sembakkam, Chennai - 600073',
        'class' => 'JR. KG',
        'photo' => 'uploads/photos/data-6.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'KARTHIKEYAN D',
        'dob' => '2020-04-18',
        'blood' => 'O+ve',
        'parent' => 'DINESH K',
        'phone' => '+91 90030 98938',
        'address' => 'Plot no:10, Sundar Avenue, Sembakkam, Kamarajapuram, Chennai - 600073',
        'class' => 'SENIOR KG',
        'photo' => 'uploads/photos/data-7.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'GNANA SAI MANI',
        'dob' => '2020-06-22',
        'blood' => 'O+ve',
        'parent' => 'MANI G',
        'phone' => '+91 80569 68145',
        'address' => 'C305, Shirdi Shelters, Bhavananthiyar Main Road, 3rd street, Sembakkam, Chennai - 73.',
        'class' => 'NURSERY',
        'photo' => 'uploads/photos/data-8.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'S. AMBARASAN',
        'dob' => '2020-10-05',
        'blood' => 'AB+ve',
        'parent' => 'SURESH A',
        'phone' => '+91 98407 55979',
        'address' => 'WBE Shriyam Apartments, F2 Block B, Senthil Avenue, 1st Main Road, Sembakkam, Chennai - 600073.',
        'class' => 'SENIOR KG',
        'photo' => 'uploads/photos/data-9.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'PRIYA SHARMA',
        'dob' => '2020-02-14',
        'blood' => 'A+ve',
        'parent' => 'SHARMA P',
        'phone' => '+91 99876 54321',
        'address' => 'No. 25, Gandhi Street, T.Nagar, Chennai - 600017',
        'class' => 'JR. KG',
        'photo' => 'uploads/photos/data-10.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'ROHIT KRISHNAN',
        'dob' => '2020-12-01',
        'blood' => 'B-ve',
        'parent' => 'KRISHNAN R',
        'phone' => '+91 98765 43210',
        'address' => 'Flat 5B, Lakshmi Apartments, Anna Nagar, Chennai - 600040',
        'class' => 'NURSERY',
        'photo' => 'uploads/photos/data-11.jpeg',
        'year' => '2025-26'
    ],
    [
        'name' => 'ANANYA REDDY',
        'dob' => '2020-01-28',
        'blood' => 'O+ve',
        'parent' => 'REDDY A',
        'phone' => '+91 97654 32109',
        'address' => 'Plot 12, Jayalakshmi Nagar, Velachery, Chennai - 600042',
        'class' => 'SENIOR KG',
        'photo' => 'uploads/photos/data-12.jpeg',
        'year' => '2025-26'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Test - 12 Students ID Cards</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');
  body { font-family: 'Poppins', sans-serif; }
  
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
    }
    .no-print { display: none !important; }
  }
</style>
</head>
<body class="bg-gray-100 py-8">

<div class="max-w-7xl mx-auto px-4">
  
  <!-- HEADER -->
  <div class="bg-gradient-to-r from-teal-600 to-blue-600 text-white rounded-lg shadow-2xl p-8 mb-8 no-print">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold mb-2">🎉 Test Preview - 12 Students</h1>
        <p class="text-lg">All ID cards generated from your uploaded photos!</p>
      </div>
      <div class="flex gap-3">
        <button onclick="window.print()" class="bg-white text-teal-600 px-6 py-3 rounded-lg font-bold hover:bg-gray-100 transition">
          🖨️ Print All
        </button>
        <a href="index.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-bold transition">
          ← Back to Main
        </a>
      </div>
    </div>
  </div>

  <!-- STATISTICS -->
  <div class="grid grid-cols-4 gap-4 mb-8 no-print">
    <div class="bg-blue-500 text-white rounded-lg p-6 text-center shadow-lg">
      <div class="text-4xl font-bold">12</div>
      <div class="text-sm mt-2">Total Students</div>
    </div>
    <div class="bg-green-500 text-white rounded-lg p-6 text-center shadow-lg">
      <div class="text-4xl font-bold">5</div>
      <div class="text-sm mt-2">Senior KG</div>
    </div>
    <div class="bg-orange-500 text-white rounded-lg p-6 text-center shadow-lg">
      <div class="text-4xl font-bold">4</div>
      <div class="text-sm mt-2">Jr. KG</div>
    </div>
    <div class="bg-pink-500 text-white rounded-lg p-6 text-center shadow-lg">
      <div class="text-4xl font-bold">3</div>
      <div class="text-sm mt-2">Nursery</div>
    </div>
  </div>

  <!-- VIEW TOGGLE -->
  <div class="bg-white rounded-lg shadow-lg p-4 mb-6 no-print">
    <div class="flex gap-4 items-center">
      <span class="font-semibold text-gray-700">View:</span>
      <button onclick="showSide('both')" id="btn-both" class="px-4 py-2 bg-teal-600 text-white rounded-lg font-semibold">Both Sides</button>
      <button onclick="showSide('front')" id="btn-front" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold">Front Only</button>
      <button onclick="showSide('back')" id="btn-back" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg font-semibold">Back Only</button>
    </div>
  </div>

  <!-- ID CARDS -->
  <div class="print-area space-y-8">
    <?php foreach ($sampleStudents as $index => $student): ?>
    
    <div class="card-wrapper">
      <div class="bg-gradient-to-r from-teal-500 to-blue-500 text-white p-4 rounded-t-lg no-print">
        <h3 class="font-bold text-lg">Card #<?= $index + 1 ?> - <?= $student['name'] ?> (<?= $student['class'] ?>)</h3>
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

  <!-- INFO BOX -->
  <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mt-8 no-print">
    <h3 class="font-bold text-blue-900 mb-2">✅ Test Successful!</h3>
    <p class="text-blue-800 mb-4">All 12 ID cards have been generated from your photos. If everything looks good, you can now:</p>
    <ul class="list-disc list-inside text-blue-800 space-y-2">
      <li>Print these cards using the "Print All" button</li>
      <li>Go back to main system to create more cards with your actual data</li>
      <li>Use bulk upload to process Excel files</li>
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