<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | <?= PROJECT_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        
        /* Premium Background Gradients */
        .bg-premium-mesh {
            background-color: #020617;
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(147, 51, 234, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(79, 70, 229, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(147, 51, 234, 0.1) 0px, transparent 50%);
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        @keyframes logo-load {
            from { opacity: 0; transform: translateY(10px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-logo-load {
            animation: logo-load 1s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }

        @keyframes shadow-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(79, 70, 229, 0.1); }
            50% { box-shadow: 0 0 40px rgba(79, 70, 229, 0.3); }
        }
        .animate-shadow-pulse {
            animation: shadow-pulse 4s ease-in-out infinite;
        }

        .text-glow {
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-premium-mesh text-slate-300 min-h-screen flex flex-col items-center justify-center p-6 py-12 lg:py-6 overflow-x-hidden relative">
    
    <!-- Deep Glow Shapes -->
    <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-indigo-600/10 rounded-full blur-[160px] animate-pulse pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-purple-600/10 rounded-full blur-[140px] animate-pulse pointer-events-none" style="animation-delay: 2s;"></div>
    <div class="absolute top-[40%] left-[20%] w-[30%] h-[30%] bg-blue-600/5 rounded-full blur-[120px] animate-pulse pointer-events-none" style="animation-delay: 4s;"></div>

    <div class="max-w-7xl w-full grid lg:grid-cols-2 gap-12 lg:gap-24 items-center relative z-10">
        
        <!-- LEFT SIDE: BRAND & FEATURES -->
        <div class="space-y-10">
            <!-- Logo area -->
            <div class="flex items-center gap-4">
                <div class="w-24 h-24 flex items-center justify-center transition-transform duration-500 ease-out hover:scale-110 animate-logo-load cursor-pointer">
    <img 
        src="assets/images/trishul-logo.png"
        alt="Trishul Logo"
        class="w-24 h-24 object-contain"
    >
</div>

                <h2 class="text-3xl font-black tracking-tighter text-white"><?= PROJECT_NAME ?></h2>
            </div>

            <div class="space-y-6">
                <h1 class="text-6xl lg:text-8xl font-black text-white leading-none tracking-tighter text-glow">
                    Premium <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-cyan-400">
                        Institutional ID 
                    </span> Solutions
                </h1>
                <p class="text-slate-400 text-xl max-w-lg leading-relaxed font-medium">
                    The elite ecosystem for generating secure, high-fidelity institutional credentials. Seamlessly scalable, exceptionally designed.
                </p>
            </div>

            <!-- Features -->
            <div class="grid gap-6">
                <div class="flex items-start gap-4 group">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Multiple Design Templates</h4>
                        <p class="text-sm text-slate-500">Choose from professional, modern, or gold layout presets.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 group">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Bulk & Manual Creation</h4>
                        <p class="text-sm text-slate-500">Generate thousands of IDs from CSV or create single cards manually.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4 group">
                    <div class="w-10 h-10 rounded-xl bg-pink-500/10 border border-pink-500/20 flex items-center justify-center text-pink-400 group-hover:bg-pink-500 group-hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Print-Ready Output</h4>
                        <p class="text-sm text-slate-500">High-resolution PDF generation ready for physical card printing.</p>
                    </div>
                </div>
            </div>

            <!-- Trust badge / Footer mock -->
            <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row items-start sm:items-center gap-6 sm:gap-10">
                <div class="flex items-center gap-4">
                    <div class="flex -space-x-3">
                        <div class="w-8 h-8 rounded-full bg-slate-700 border-2 border-[#020617]"></div>
                        <div class="w-8 h-8 rounded-full bg-indigo-600 border-2 border-[#020617]"></div>
                        <div class="w-8 h-8 rounded-full bg-slate-600 border-2 border-[#020617]"></div>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Trusted by 200+ Institutions across India</p>
                </div>

                <!-- Official Social Links -->
                <div class="flex items-center gap-4">
                    <!-- Website - Official Brand Blue -->
                    <a href="https://trishultrades.com/" target="_blank" title="Trishul Trades Website" class="w-12 h-12 rounded-2xl bg-[#1a73e8] border border-white/20 flex items-center justify-center text-white shadow-[0_0_20px_rgba(26,115,232,0.3)] transition-all duration-300 hover:scale-110 hover:shadow-[0_0_30px_rgba(26,115,232,0.5)] group shadow-lg">
                        <svg class="w-6 h-6 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 5.523 4.477 10 10 10s10-4.477 10-10c0-5.523-4.477-10-10-10zm-1 17.93c-3.946-.492-7-3.858-7-7.93 0-.62.071-1.222.203-1.8h3.597c-.13 1-.2 1.385-.2 1.8 0 3.324 1.343 6.426 3.4 7.93zm0-15.86c-2.057 1.504-3.4 4.606-3.4 7.93 0 .415.07 1.25.2 1.8H4.203C4.07 13.222 4 12.62 4 12c0-4.072 3.054-7.438 7-7.93zm2 0c3.946.492 7 3.858 7 7.93 0 .62-.071 1.222-.203 1.8h-3.597c.13-1 .2-1.385.2-1.8 0-3.324-1.343-6.426-3.4-7.93zm0 15.86c2.057-1.504 3.4-4.606 3.4-7.93 0-.415-.07-1.25-.2-1.8h3.597c.133.578.203 1.18.203 1.8 0 4.072-3.054 7.438-7 7.93zM12 4.1c1.866 1.487 3.091 4.544 3.091 7.9s-1.225 6.413-3.091 7.9c-1.866-1.487-3.091-4.544-3.091-7.9s1.225-6.413 3.091-7.9z"></path>
                        </svg>
                    </a>
                    
                    <style>
                        .ig-brand {
                            background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);
                            border-color: rgba(255, 255, 255, 0.2);
                            box-shadow: 0 0 20px rgba(214, 36, 159, 0.3);
                        }
                        .ig-brand:hover {
                            box-shadow: 0 0 35px rgba(214, 36, 159, 0.5);
                            transform: scale(1.1);
                        }
                    </style>
                    <!-- Instagram - Official Gradient -->
                    <a href="https://www.instagram.com/tt.trishul/" target="_blank" title="Trishul Trades Instagram" class="ig-brand w-12 h-12 rounded-2xl flex items-center justify-center text-white transition-all duration-300 shadow-lg">
                        <svg class="w-6 h-6 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: AUTH ENTRY -->
        <div class="lg:justify-self-end w-full max-w-md">
            <div class="glass-panel p-8 sm:p-10 rounded-[2.5rem] shadow-2xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                
                <div class="relative z-10">
                    <div class="text-center mb-10">
                        <h3 class="text-3xl font-bold text-white mb-2 tracking-tight">Access Portal</h3>
                        <p class="text-slate-500 text-sm">Select an option to continue to your dashboard</p>
                    </div>

                    <div class="space-y-4">
                        <a href="login.php" class="flex items-center justify-between w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white p-5 rounded-2xl font-bold transition-all shadow-xl shadow-indigo-600/20 group/btn border border-indigo-400/20">
                            <span class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-5 h-5 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                </div>
                                Account Login
                            </span>
                            <svg class="w-6 h-6 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>

                        <div class="relative py-2">
                            <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-px bg-white/5"></div>
                            <span class="relative bg-[#020617]/0 backdrop-blur-3xl px-4 text-[10px] font-black uppercase text-slate-500 tracking-widest mx-auto block w-max">OR SYSTEM ACCESS</span>
                        </div>

                        <a href="register.php" class="flex items-center justify-between w-full bg-white/5 hover:bg-white/10 text-white p-5 rounded-2xl font-bold transition-all border border-white/10 shadow-lg group/btn2">
                            <span class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                </div>
                                Create Account
                            </span>
                             <div class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center group-hover/btn2:bg-white/10 transition-colors">
                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                             </div>
                        </a>
                    </div>

                    <div class="mt-12 text-center p-6 bg-indigo-500/5 rounded-3xl border border-indigo-500/10">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Need institutional setup? <br> Reach out at <a href="mailto:<?= ADMIN_EMAIL ?>" class="text-indigo-400 hover:underline"><?= ADMIN_EMAIL ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
