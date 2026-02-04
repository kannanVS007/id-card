<?php
function generateIDCard($data, $isFront = true) {
    // Offline-ready Base64 placeholder (Gray person icon)
    $placeholder = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iI2UyZThmMCI+PHBhdGggZD0iTTEyIDEyYzIuMjEgMCA0LTEuNzkgNC00cy0xLjc5LTQtNC00LTQgMS43OS00IDQgMS43OSA0IDQgNHptMCAyYzIuNjcgMCA4IDEuMzMgOCA0djJoLTE2di0yYzAtMi42NyA1LjMzLTQgOC00eiIvPjwvc3ZnPg==';
    
    $photoUrl = $data['photo'] ?? '';
    if (empty($photoUrl) || $photoUrl === 'offline_placeholder' || strpos($photoUrl, 'via.placeholder.com') !== false) {
        $photoUrl = $placeholder;
    }

    $studentName = strtoupper($data['name'] ?? 'STUDENT NAME');
    $dob = $data['dob'] ?? '';
    $bloodGroup = $data['blood'] ?? 'N/A';
    $parentName = strtoupper($data['parent'] ?? 'PARENT NAME');
    $phone = $data['phone'] ?? '0000000000';
    $address = $data['address'] ?? 'Address goes here...';
    $studentClass = strtoupper($data['class'] ?? 'NURSERY');
    $year = $data['year'] ?? '2025-26';
    
    // Format DOB to DD.MM.YYYY
    if ($dob && strtotime($dob)) {
        $dob = date('d.m.Y', strtotime($dob));
    }
    
    ob_start();
    
    if ($isFront) {
?>
<div class="id-card relative bg-white overflow-hidden" style="width: 325px; height: 500px; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); font-family: 'Outfit', sans-serif;">
    
    <!-- Design Elements -->
    <div class="absolute top-0 left-0 w-full h-[180px] bg-gradient-to-br from-blue-700 to-indigo-900 rounded-b-[40px]"></div>
    <div class="absolute top-[-50px] right-[-50px] w-40 h-40 bg-white/10 rounded-full"></div>
    
    <!-- Header Content -->
    <div class="relative pt-6 px-4 text-center">
        <div class="flex justify-center mb-2">
            <div class="w-12 h-12 bg-white rounded-xl shadow-lg flex items-center justify-center p-2">
                <svg class="text-blue-700 w-full h-full" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.89 9L12 4.57 20.11 9 12 13.43 3.89 9zM12 17l-6-3.27v2.09L12 19.09l6-3.27v-2.09L12 17z"/>
                </svg>
            </div>
        </div>
        <h2 class="text-white font-extrabold text-[13px] uppercase tracking-wider mb-1">Little Krish Montessori</h2>
        <p class="text-blue-200 text-[9px] font-medium uppercase tracking-[2px]">Pre-School Academy</p>
    </div>

    <!-- Photo Section -->
    <div class="relative flex justify-center mt-6">
        <div class="relative">
            <div class="absolute -inset-1.5 bg-gradient-to-tr from-blue-500 to-indigo-500 rounded-[2.5rem] blur opacity-30"></div>
            <div class="relative w-[150px] h-[180px] rounded-[2.2rem] overflow-hidden border-4 border-white shadow-2xl bg-slate-100">
                <img src="<?= $photoUrl ?>" alt="Student Photo" class="w-full h-full object-cover">
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="mt-6 text-center px-4">
        <div class="inline-block px-3 py-1 bg-blue-50 rounded-full mb-2">
            <span class="text-blue-700 font-bold text-[10px] uppercase tracking-widest"><?= $studentClass ?></span>
        </div>
        <h1 class="text-slate-900 font-black text-[20px] leading-tight mb-4 tracking-tight"><?= $studentName ?></h1>
        
        <div class="flex items-center justify-center gap-2 mb-6">
            <div class="bg-indigo-600 text-white font-bold text-[14px] px-4 py-1.5 rounded-xl shadow-lg shadow-indigo-200">
                <?= $year ?>
            </div>
            <div class="text-[11px] text-slate-400 font-bold italic border-l-2 border-slate-100 pl-3">
                Authorized Signature
            </div>
        </div>
    </div>

    <!-- Bottom Wave -->
    <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"></div>
</div>
<?php
    } else {
?>
<div class="id-card relative bg-slate-50 overflow-hidden" style="width: 325px; height: 500px; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); font-family: 'Outfit', sans-serif;">
    
    <div class="p-6">
        <h3 class="text-blue-700 font-black text-[12px] uppercase tracking-[3px] mb-8 border-b-2 border-blue-100 pb-2">Student Particulars</h3>
        
        <div class="space-y-5">
            <div class="flex flex-col">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Parent/Guardian</span>
                <span class="text-[13px] text-slate-800 font-bold"><?= $parentName ?></span>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Birth Date</span>
                    <span class="text-[13px] text-slate-800 font-bold"><?= $dob ?></span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Blood Group</span>
                    <span class="text-[13px] text-red-600 font-black"><?= $bloodGroup ?></span>
                </div>
            </div>

            <div class="flex flex-col">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Emergency Contact</span>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-[13px] text-slate-800 font-bold"><?= $phone ?></span>
                </div>
            </div>

            <div class="flex flex-col">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Residential Address</span>
                <span class="text-[11px] text-slate-600 font-medium leading-relaxed"><?= $address ?></span>
            </div>
        </div>

        <!-- Footer Contact -->
        <div class="absolute bottom-8 left-0 right-0 px-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <p class="text-[9px] text-slate-500 font-medium leading-tight">No. 10, Krishna Street, Suresh Nagar, Porur, Chennai - 600 116.</p>
                </div>
                <div class="flex justify-between text-[8px] text-blue-600 font-bold tracking-wider">
                    <span>WEB: LITTLEKRISH.COM</span>
                    <span>TEL: +91 73389 05319</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Accent -->
    <div class="absolute top-0 right-0 w-24 h-24 bg-blue-100/50 rounded-bl-[100px]"></div>
</div>
<?php
    }
    
    return ob_get_clean();
}
?>