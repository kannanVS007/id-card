<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Little Krish ID Card System - Demo</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap');
  body { font-family: 'Poppins', sans-serif; }
  
  .id-card {
    width: 340px;
    height: 480px;
    position: relative;
  }
  
  @keyframes slideIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .animate-slide-in {
    animation: slideIn 0.5s ease-out;
  }
</style>
</head>
<body class="bg-gradient-to-br from-teal-50 via-blue-50 to-purple-50 min-h-screen">

<!-- HERO SECTION -->
<div class="bg-gradient-to-r from-teal-600 to-blue-600 text-white py-12">
  <div class="max-w-7xl mx-auto px-4">
    <div class="text-center">
      <h1 class="text-4xl md:text-5xl font-black mb-4">Little Krish Montessori</h1>
      <p class="text-xl md:text-2xl font-semibold mb-2">ID Card Generator System</p>
      <p class="text-lg opacity-90">Professional • Automated • Easy to Use</p>
    </div>
  </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">

  <!-- FEATURES GRID -->
  <div class="grid md:grid-cols-3 gap-6 mb-12 animate-slide-in">
    
    <div class="bg-white rounded-xl shadow-xl p-6 hover:shadow-2xl transition transform hover:-translate-y-1">
      <div class="text-5xl mb-4">🎨</div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Exact Design Match</h3>
      <p class="text-gray-600">Perfectly replicates your school's ID card design with professional quality output</p>
    </div>

    <div class="bg-white rounded-xl shadow-xl p-6 hover:shadow-2xl transition transform hover:-translate-y-1">
      <div class="text-5xl mb-4">⚡</div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Bulk Generation</h3>
      <p class="text-gray-600">Generate hundreds of ID cards from Excel in minutes with automatic photo matching</p>
    </div>

    <div class="bg-white rounded-xl shadow-xl p-6 hover:shadow-2xl transition transform hover:-translate-y-1">
      <div class="text-5xl mb-4">🖨️</div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Print Ready</h3>
      <p class="text-gray-600">Optimized for professional printing with duplex support and lamination</p>
    </div>

  </div>

  <!-- SAMPLE ID CARD PREVIEW -->
  <div class="bg-white rounded-xl shadow-2xl p-8 mb-12">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Sample ID Card Preview</h2>
    
    <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
      
      <!-- FRONT SIDE PREVIEW -->
      <div>
        <h3 class="text-lg font-bold text-gray-700 mb-4 text-center">Front Side</h3>
        <div class="id-card border-4 border-black rounded-lg overflow-hidden bg-gradient-to-b from-[#FFF4E0] via-[#E8F8F5] to-[#B8F3E9] shadow-xl">
          
          <div class="bg-gradient-to-r from-[#FFF4E0] to-[#FFE8CC] text-center pt-3 pb-2 border-b-2 border-teal-700">
            <div class="flex justify-center mb-1">
              <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center border-4 border-teal-700">
                <svg class="w-12 h-12 text-teal-700" viewBox="0 0 100 100">
                  <circle cx="50" cy="30" r="8" fill="currentColor"/>
                  <path d="M35 40 L50 30 L65 40" stroke="currentColor" stroke-width="3" fill="none"/>
                  <rect x="25" y="45" width="50" height="35" fill="currentColor" rx="3"/>
                  <circle cx="35" cy="55" r="4" fill="white"/>
                  <circle cx="65" cy="55" r="4" fill="white"/>
                </svg>
              </div>
            </div>
            <h2 class="text-orange-600 font-extrabold text-base leading-tight" style="letter-spacing: 0.5px;">LITTLE KRISH MONTESSORI PRE-SCHOOL</h2>
            <p class="text-pink-600 font-bold text-xs tracking-widest mt-1">WHERE CURIOSITY AND WONDER ARE CELEBRATED!</p>
            <p class="text-teal-800 text-[10px] font-semibold mt-1">No. 10, Krishna Street, Suresh Nagar, Porur, Chennai - 600 116.</p>
          </div>

          <div class="flex justify-center mt-4">
            <div class="w-40 h-48 rounded-3xl overflow-hidden border-4 border-teal-600 shadow-xl bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
              <div class="text-center text-blue-600">
                <svg class="w-20 h-20 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <p class="text-sm font-semibold">Student<br/>Photo</p>
              </div>
            </div>
          </div>

          <div class="text-center mt-3 px-4">
            <h1 class="text-red-600 font-black text-xl tracking-wide" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">
              MANDI RISHAN SAI REDDY
            </h1>
            <div class="bg-pink-500 text-white font-black text-lg py-1 px-4 rounded-full inline-block mt-2 shadow-lg">
              SENIOR KG
            </div>
            <div class="flex justify-center items-center gap-3 mt-3">
              <div class="bg-pink-500 text-white font-black text-2xl py-1 px-6 rounded-full shadow-lg">
                2025-26
              </div>
              <div class="text-purple-700 font-bold text-sm italic">
                Principal
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- BACK SIDE PREVIEW -->
      <div>
        <h3 class="text-lg font-bold text-gray-700 mb-4 text-center">Back Side</h3>
        <div class="id-card border-4 border-black rounded-lg overflow-hidden bg-gradient-to-b from-[#FFF4E0] via-[#E8F8F5] to-[#B8F3E9] shadow-xl">
          
          <div class="p-6 space-y-3 text-sm">
            
            <div class="flex items-start">
              <span class="font-bold text-teal-800 w-36">Date of Birth</span>
              <span class="font-semibold">:</span>
              <span class="ml-3 font-semibold text-teal-900">20.08.2020</span>
            </div>

            <div class="flex items-start">
              <span class="font-bold text-teal-800 w-36">Blood Group</span>
              <span class="font-semibold">:</span>
              <span class="ml-3 font-semibold text-red-600">AB+ve</span>
            </div>

            <div class="flex items-start">
              <span class="font-bold text-teal-800 w-36">Name of Parent</span>
              <span class="font-semibold">:</span>
              <span class="ml-3 font-semibold text-teal-900">RAVI.M</span>
            </div>

            <div class="flex items-start">
              <span class="font-bold text-teal-800 w-36">Phone No</span>
              <span class="font-semibold">:</span>
              <span class="ml-3 font-semibold text-teal-900">+91 73389 05319</span>
            </div>

            <div class="flex items-start">
              <span class="font-bold text-teal-800 w-36">Address</span>
              <span class="font-semibold">:</span>
              <span class="ml-3 font-semibold text-teal-900 leading-relaxed">No.02, Krishna Street, Porur, Chennai - 600116.</span>
            </div>

          </div>

          <div class="absolute bottom-4 left-0 right-0 px-4">
            <div class="text-center mb-3">
              <div class="w-12 h-12 bg-white rounded-full mx-auto flex items-center justify-center border-3 border-teal-700 shadow-lg">
                <svg class="w-8 h-8 text-teal-700" viewBox="0 0 100 100">
                  <circle cx="50" cy="30" r="8" fill="currentColor"/>
                  <path d="M35 40 L50 30 L65 40" stroke="currentColor" stroke-width="3" fill="none"/>
                  <rect x="25" y="45" width="50" height="35" fill="currentColor" rx="3"/>
                </svg>
              </div>
            </div>
            
            <div class="text-center">
              <h3 class="text-orange-600 font-extrabold text-sm">LITTLE KRISH MONTESSORI PRE-SCHOOL</h3>
              <p class="text-pink-600 font-bold text-[10px] tracking-wide">WHERE CURIOSITY AND WONDER ARE CELEBRATED!</p>
            </div>
            
            <div class="flex justify-center gap-1 mt-2 flex-wrap">
              <span class="bg-teal-800 text-white text-[10px] font-bold px-2 py-1 rounded">Todd Care</span>
              <span class="bg-pink-600 text-white text-[10px] font-bold px-2 py-1 rounded">Nursery</span>
              <span class="bg-orange-500 text-white text-[10px] font-bold px-2 py-1 rounded">Jr. KG</span>
              <span class="bg-cyan-500 text-white text-[10px] font-bold px-2 py-1 rounded">Sr. KG</span>
              <span class="bg-purple-600 text-white text-[10px] font-bold px-2 py-1 rounded">Day Care</span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  <!-- HOW IT WORKS -->
  <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-2xl p-8 text-white mb-12">
    <h2 class="text-3xl font-bold text-center mb-8">How It Works</h2>
    
    <div class="grid md:grid-cols-4 gap-6">
      
      <div class="text-center">
        <div class="bg-white text-purple-600 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
        <h3 class="font-bold text-lg mb-2">Upload Data</h3>
        <p class="text-sm opacity-90">Upload Excel file with student information</p>
      </div>

      <div class="text-center">
        <div class="bg-white text-purple-600 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
        <h3 class="font-bold text-lg mb-2">Add Photos</h3>
        <p class="text-sm opacity-90">Upload ZIP file with student photos</p>
      </div>

      <div class="text-center">
        <div class="bg-white text-purple-600 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
        <h3 class="font-bold text-lg mb-2">Auto Generate</h3>
        <p class="text-sm opacity-90">System automatically creates all ID cards</p>
      </div>

      <div class="text-center">
        <div class="bg-white text-purple-600 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">4</div>
        <h3 class="font-bold text-lg mb-2">Print & Laminate</h3>
        <p class="text-sm opacity-90">Print cards and laminate for durability</p>
      </div>

    </div>
  </div>

  <!-- CTA SECTION -->
  <div class="text-center bg-white rounded-xl shadow-2xl p-12">
    <h2 class="text-3xl font-bold text-gray-800 mb-4">Ready to Get Started?</h2>
    <p class="text-lg text-gray-600 mb-8">Create professional ID cards for your students in minutes!</p>
    
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="index.php" class="bg-gradient-to-r from-teal-600 to-blue-600 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition inline-block">
        🚀 Start Creating ID Cards
      </a>
      <a href="sample_data.csv" download class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-4 rounded-lg font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition inline-block">
        📥 Download Sample Excel
      </a>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="mt-12 text-center text-gray-600">
    <p class="text-sm">© 2025 Little Krish Montessori Pre-School. All rights reserved.</p>
    <p class="text-xs mt-2">No. 10, Krishna Street, Suresh Nagar, Porur, Chennai - 600 116</p>
    <div class="flex justify-center gap-4 mt-2 text-xs">
      <span>🌐 www.littlekrishpreschool.com</span>
      <span>📧 littlekrishpreschool@gmail.com</span>
    </div>
  </div>

</div>

</body>
</html>