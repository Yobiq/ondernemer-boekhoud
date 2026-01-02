<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARCOFIC — Luxury Accounting Redefined</title>
    <meta name="description" content="Where precision meets elegance. Premium AI-powered accounting for distinguished businesses.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --gold-light: #F4E4B6;
            --gold-dark: #B8972F;
        }
        
        body { 
            font-family: 'Inter', sans-serif;
            background: #000000;
            color: #ffffff;
            overflow-x: hidden;
        }
        
        .font-serif { font-family: 'Cormorant Garamond', serif; }
        
        /* Responsive Typography */
        @media (max-width: 640px) {
            h1 { font-size: clamp(2.5rem, 8vw, 4rem) !important; }
            h2 { font-size: clamp(2rem, 6vw, 3rem) !important; }
            h3 { font-size: clamp(1.5rem, 4vw, 2rem) !important; }
        }
        
        /* Luxury Dark Gradients */
        .gradient-luxury-dark { 
            background: linear-gradient(135deg, #000000 0%, #1a1a2e 50%, #16213e 100%);
        }
        
        .gradient-gold { 
            background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 50%, var(--gold-light) 100%);
        }
        
        /* Premium Glass on Dark */
        .glass-luxury {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(40px) saturate(150%);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glass-gold {
            background: rgba(212, 175, 55, 0.08);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        /* Luxury Animations */
        @keyframes luxuryFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-25px) scale(1.02); }
        }
        
        @keyframes fadeInLuxury {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes shimmerGold {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        
        @keyframes pulseGold {
            0%, 100% { box-shadow: 0 0 30px rgba(212, 175, 55, 0.3); }
            50% { box-shadow: 0 0 60px rgba(212, 175, 55, 0.6), 0 0 90px rgba(212, 175, 55, 0.3); }
        }
        
        .float-luxury { animation: luxuryFloat 10s ease-in-out infinite; }
        .fade-luxury { animation: fadeInLuxury 1.2s ease-out; }
        
        .text-shimmer-gold {
            background: linear-gradient(90deg, var(--gold-dark) 0%, var(--gold-light) 50%, var(--gold-dark) 100%);
            background-size: 200% auto;
            color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            animation: shimmerGold 3s linear infinite;
        }
        
        /* Luxury Shadows */
        .shadow-luxury-gold { 
            box-shadow: 0 25px 50px -12px rgba(212, 175, 55, 0.25),
                        0 0 0 1px rgba(212, 175, 55, 0.1);
        }
        
        .shadow-luxury-dark {
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.6),
                        0 0 0 1px rgba(255, 255, 255, 0.05);
        }
        
        /* Premium Buttons */
        .btn-luxury-gold {
            background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 100%);
            box-shadow: 0 10px 40px -10px rgba(212, 175, 55, 0.5);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-luxury-gold:hover {
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            box-shadow: 0 20px 60px -10px rgba(212, 175, 55, 0.7);
            transform: translateY(-3px) scale(1.03);
        }
        
        .btn-luxury-dark {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            transition: all 0.4s ease;
        }
        
        .btn-luxury-dark:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(212, 175, 55, 0.5);
            transform: translateY(-2px);
        }
        
        /* Luxury Card Hover */
        .card-luxury {
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-luxury:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 60px -10px rgba(212, 175, 55, 0.2);
            border-color: rgba(212, 175, 55, 0.3);
        }
        
        /* Shine Effect */
        .shine {
            position: relative;
            overflow: hidden;
        }
        
        .shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s;
        }
        
        .shine:hover::before {
            left: 100%;
        }
    </style>
</head>
<body class="antialiased bg-black text-white">
    
    <!-- Ultra-Responsive Luxury Navigation -->
    <nav class="glass-luxury backdrop-blur-3xl fixed w-full top-0 z-50 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20 sm:h-24">
                <!-- Logo (Responsive) -->
                <div class="flex items-center gap-2 sm:gap-4 group cursor-pointer">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-gold rounded-xl sm:rounded-2xl flex items-center justify-center text-2xl sm:text-3xl shadow-luxury-gold transform group-hover:rotate-12 transition-all duration-500">
                        💎
                    </div>
                    <div>
                        <div class="font-serif text-2xl sm:text-4xl font-bold tracking-tight text-shimmer-gold">
                            MARCOFIC
                        </div>
                        <div class="text-[10px] sm:text-xs font-semibold text-gray-400 tracking-widest uppercase -mt-1">
                            Luxury Accounting
                        </div>
                    </div>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center gap-8 xl:gap-10">
                    <a href="#features" class="text-gray-300 hover:text-white font-medium transition-all duration-300 relative group text-sm">
                        Diensten
                        <span class="absolute -bottom-2 left-0 w-0 h-0.5 bg-gradient-gold group-hover:w-full transition-all duration-500"></span>
                    </a>
                    <a href="#voordelen" class="text-gray-300 hover:text-white font-medium transition-all duration-300 relative group text-sm">
                        Voordelen
                        <span class="absolute -bottom-2 left-0 w-0 h-0.5 bg-gradient-gold group-hover:w-full transition-all duration-500"></span>
                    </a>
                    <a href="#contact" class="text-gray-300 hover:text-white font-medium transition-all duration-300 relative group text-sm">
                        Contact
                        <span class="absolute -bottom-2 left-0 w-0 h-0.5 bg-gradient-gold group-hover:w-full transition-all duration-500"></span>
                    </a>
                    <div class="w-px h-8 bg-white/10"></div>
                    <a href="/klanten/login" class="btn-luxury-gold px-6 xl:px-8 py-3 text-black rounded-xl font-bold text-xs xl:text-sm uppercase tracking-wide shine">
                        Klant Portaal
                    </a>
                    <a href="/admin/login" class="btn-luxury-dark px-6 xl:px-8 py-3 text-white rounded-xl font-bold text-xs xl:text-sm uppercase tracking-wide">
                        Boekhouder
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="lg:hidden btn-luxury-dark p-3 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
                <!-- Mobile Menu (Dropdown) -->
        <div id="mobileMenu" class="hidden lg:hidden glass-luxury border-t border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 space-y-4">
                <a href="#features" class="block py-3 text-gray-300 hover:text-yellow-400 font-medium transition">Diensten</a>
                <a href="#voordelen" class="block py-3 text-gray-300 hover:text-yellow-400 font-medium transition">Voordelen</a>
                <a href="#contact" class="block py-3 text-gray-300 hover:text-yellow-400 font-medium transition">Contact</a>
                <div class="h-px bg-white/10 my-4"></div>
                <a href="/klanten/login" class="block btn-luxury-gold px-6 py-4 text-black rounded-xl font-bold text-sm text-center uppercase">
                    📱 Klant Portaal
                </a>
                <a href="/admin/login" class="block btn-luxury-dark px-6 py-4 text-white rounded-xl font-bold text-sm text-center uppercase">
                    🔐 Boekhouder Login
                </a>
            </div>
        </div>
    </nav>

    <!-- Luxury Hero Section -->
    <section class="relative min-h-screen flex items-center overflow-hidden pt-24">
        <!-- Premium Background Elements -->
        <div class="absolute inset-0 bg-gradient-to-br from-black via-gray-900 to-black"></div>
        
        <!-- Animated Grid Pattern -->
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(rgba(212, 175, 55, 0.15) 1px, transparent 1px); background-size: 50px 50px;"></div>
        
        <!-- Luxury Glow Orbs -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-yellow-600/10 rounded-full filter blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full filter blur-3xl"></div>
        
        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 w-full py-20">
            <div class="grid lg:grid-cols-2 gap-20 items-center">
                <!-- Left: Luxury Content -->
                <div class="fade-luxury">
                    <!-- Premium Badge -->
                    <div class="inline-flex items-center gap-3 glass-gold px-6 py-3 rounded-full mb-10">
                        <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                        <span class="text-sm font-semibold tracking-wide text-yellow-200">
                            SINCE 2019 — TRUSTED BY 200+ CLIENTS
                        </span>
                    </div>
                    
                    <!-- Main Heading -->
                    <h1 class="font-serif text-7xl md:text-8xl lg:text-9xl font-bold mb-10 leading-none">
                        <span class="text-white">Where</span><br>
                        <span class="text-shimmer-gold">Precision</span><br>
                        <span class="text-white">Meets</span><br>
                        <span class="text-shimmer-gold">Elegance</span>
                    </h1>
                    
                    <!-- Luxury Description -->
                    <p class="text-xl md:text-2xl mb-12 text-gray-300 leading-relaxed font-light">
                        The Netherlands' most sophisticated accounting platform.<br class="hidden md:block">
                        <span class="text-yellow-400">Camera-first.</span>
                        <span class="text-white font-medium">AI-powered.</span>
                        <span class="text-yellow-400">Flawlessly executed.</span>
                    </p>
                    
                    <!-- Luxury CTAs -->
                    <div class="flex flex-col sm:flex-row gap-6 mb-16">
                        <a href="/klanten/login" class="group relative btn-luxury-gold px-10 py-6 text-black rounded-2xl font-bold text-lg uppercase tracking-wider overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-200%] group-hover:translate-x-[200%] transition-transform duration-1000"></div>
                            <span class="relative flex items-center justify-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Begin Experience
                                <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </span>
                        </a>
                        <a href="#discover" class="btn-luxury-dark px-10 py-6 text-white rounded-2xl font-bold text-lg uppercase tracking-wider hover:scale-105 transition-all">
                            Discover More
                        </a>
                    </div>
                    
                    <!-- Luxury Trust Indicators -->
                    <div class="grid grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-4xl font-serif font-bold text-shimmer-gold mb-2">200+</div>
                            <div class="text-xs text-gray-400 uppercase tracking-widest">Distinguished<br>Clients</div>
                        </div>
                        <div class="text-center border-x border-white/10">
                            <div class="text-4xl font-serif font-bold text-shimmer-gold mb-2">5+</div>
                            <div class="text-xs text-gray-400 uppercase tracking-widest">Years Of<br>Excellence</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-serif font-bold text-shimmer-gold mb-2">100%</div>
                            <div class="text-xs text-gray-400 uppercase tracking-widest">Precision<br>Guarantee</div>
                        </div>
                    </div>
                </div>

                <!-- Right: Luxury Device Mockup -->
                <div class="relative lg:block hidden">
                    <div class="float-luxury">
                        <!-- Premium Device Frame -->
                        <div class="relative">
                            <!-- Glow Effect -->
                            <div class="absolute inset-0 bg-gradient-gold opacity-20 blur-3xl rounded-3xl"></div>
                            
                            <!-- Device -->
                            <div class="relative glass-luxury border-2 border-white/10 rounded-[3rem] p-3 shadow-luxury-dark">
                                <!-- Screen -->
                                <div class="bg-gradient-to-br from-gray-900 to-black rounded-[2.5rem] overflow-hidden">
                                    <!-- Status Bar -->
                                    <div class="px-8 py-4 flex justify-between items-center text-xs text-gray-400">
                                        <span>14:23</span>
                                        <div class="flex gap-1">
                                            <div class="w-4 h-3 border border-white/30 rounded-sm"></div>
                                            <div class="w-1 h-3 border border-white/30 rounded-sm"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- App Header -->
                                    <div class="px-8 py-8 bg-gradient-to-br from-yellow-600/20 to-purple-600/20 border-y border-white/5">
                                        <div class="flex items-center gap-3 mb-6">
                                            <div class="w-12 h-12 bg-gradient-gold rounded-2xl flex items-center justify-center text-2xl shadow-lg">💎</div>
                                            <div>
                                                <div class="font-serif text-xl font-bold text-shimmer-gold">MARCOFIC</div>
                                                <div class="text-xs text-gray-400 tracking-wide">PREMIUM PORTAL</div>
                                            </div>
                                        </div>
                                        <div class="font-serif text-3xl font-bold mb-2">Good Evening,</div>
                                        <div class="text-yellow-400 font-medium">Jan Jansen</div>
                                    </div>
                                    
                                    <!-- App Content -->
                                    <div class="p-8 space-y-6">
                                        <!-- Upload Card -->
                                        <div class="relative group cursor-pointer">
                                            <div class="absolute inset-0 bg-gradient-gold opacity-0 group-hover:opacity-10 rounded-3xl transition-opacity duration-500"></div>
                                            <div class="glass-gold border border-yellow-600/30 rounded-3xl p-8 text-center">
                                                <div class="text-5xl mb-4">📸</div>
                                                <div class="font-serif text-2xl font-bold mb-2 text-yellow-400">Capture Document</div>
                                                <div class="text-sm text-gray-400 mb-4">Instant camera access</div>
                                                <div class="inline-flex items-center gap-2 px-4 py-2 bg-black/30 rounded-full text-xs font-semibold text-yellow-300">
                                                    <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                                                    Premium OCR • 95% Accuracy
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Stats -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="glass-luxury border border-white/5 rounded-2xl p-5 text-center">
                                                <div class="text-3xl font-serif font-bold text-emerald-400 mb-1">12</div>
                                                <div class="text-xs text-gray-500 uppercase tracking-wide">Approved</div>
                                            </div>
                                            <div class="glass-luxury border border-white/5 rounded-2xl p-5 text-center">
                                                <div class="text-3xl font-serif font-bold text-yellow-400 mb-1">€2.4K</div>
                                                <div class="text-xs text-gray-500 uppercase tracking-wide">This Month</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Luxury Badges -->
                    <div class="absolute -top-10 -right-10 glass-gold px-6 py-4 rounded-2xl shadow-luxury-gold" style="animation: pulseGold 3s ease-in-out infinite;">
                        <div class="text-yellow-400 font-serif text-sm font-bold">5 sec</div>
                        <div class="text-xs text-gray-300">Lightning Fast</div>
                    </div>
                    <div class="absolute -bottom-10 -left-10 glass-gold px-6 py-4 rounded-2xl shadow-luxury-gold" style="animation: pulseGold 3s ease-in-out infinite; animation-delay: 1.5s;">
                        <div class="text-yellow-400 font-serif text-sm font-bold">90%</div>
                        <div class="text-xs text-gray-300">Automated</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Subtle Bottom Border -->
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-yellow-600/50 to-transparent"></div>
    </section>

    <!-- Ultra-Responsive Luxury Stats Bar -->
    <section class="py-12 sm:py-16 md:py-20 bg-gradient-to-b from-black to-gray-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 md:gap-8">
                <div class="text-center p-6 sm:p-8 glass-luxury rounded-xl sm:rounded-2xl border border-white/5 card-luxury">
                    <div class="text-4xl sm:text-5xl md:text-6xl font-serif font-bold text-shimmer-gold mb-2 sm:mb-3">90%</div>
                    <div class="text-xs sm:text-sm text-gray-400 uppercase tracking-wider sm:tracking-widest leading-tight">
                        Automation<br class="hidden sm:inline"> Excellence
                    </div>
                </div>
                <div class="text-center p-6 sm:p-8 glass-luxury rounded-xl sm:rounded-2xl border border-white/5 card-luxury">
                    <div class="text-4xl sm:text-5xl md:text-6xl font-serif font-bold text-shimmer-gold mb-2 sm:mb-3">5s</div>
                    <div class="text-xs sm:text-sm text-gray-400 uppercase tracking-wider sm:tracking-widest">
                        Upload<br class="hidden sm:inline"> Time
                    </div>
                </div>
                <div class="text-center p-6 sm:p-8 glass-luxury rounded-xl sm:rounded-2xl border border-white/5 card-luxury">
                    <div class="text-4xl sm:text-5xl md:text-6xl font-serif font-bold text-shimmer-gold mb-2 sm:mb-3">200+</div>
                    <div class="text-xs sm:text-sm text-gray-400 uppercase tracking-wider sm:tracking-widest">
                        Elite<br class="hidden sm:inline"> Clients
                    </div>
                </div>
                <div class="text-center p-6 sm:p-8 glass-luxury rounded-xl sm:rounded-2xl border border-white/5 card-luxury">
                    <div class="text-4xl sm:text-5xl md:text-6xl font-serif font-bold text-shimmer-gold mb-2 sm:mb-3">100%</div>
                    <div class="text-xs sm:text-sm text-gray-400 uppercase tracking-wider sm:tracking-widest">
                        VAT<br class="hidden sm:inline"> Precision
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ultra-Responsive Premium Features -->
    <section id="features" class="py-16 sm:py-24 md:py-32 bg-black relative">
        <div class="absolute inset-0 opacity-5" style="background-image: repeating-linear-gradient(90deg, rgba(212, 175, 55, 0.1) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.1) 41px), repeating-linear-gradient(0deg, rgba(212, 175, 55, 0.1) 0px, transparent 1px, transparent 40px, rgba(212, 175, 55, 0.1) 41px);"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12 sm:mb-16 md:mb-20">
                <div class="inline-block px-3 sm:px-4 py-1.5 sm:py-2 glass-gold rounded-full text-yellow-400 font-semibold text-xs sm:text-sm mb-4 sm:mb-6 uppercase tracking-wider">
                    Excellence In Every Detail
                </div>
                <h2 class="font-serif font-bold mb-4 sm:mb-6" style="font-size: clamp(2rem, 8vw, 4.5rem);">
                    <span class="text-white">Unparalleled</span><br>
                    <span class="text-shimmer-gold">Sophistication</span>
                </h2>
                <p class="text-base sm:text-lg md:text-xl text-gray-400 max-w-2xl mx-auto px-4">
                    Crafted for distinguished businesses who demand perfection
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <!-- Feature 1 (Responsive) -->
                <div class="glass-luxury border border-white/5 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-10 card-luxury shine">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-gold rounded-xl sm:rounded-2xl flex items-center justify-center text-3xl sm:text-4xl mb-6 sm:mb-8 shadow-luxury-gold mx-auto sm:mx-0">📸</div>
                    <h3 class="font-serif text-2xl sm:text-3xl font-bold mb-3 sm:mb-4 text-white text-center sm:text-left">Camera Intelligence</h3>
                    <p class="text-gray-400 leading-relaxed mb-4 sm:mb-6 text-sm sm:text-base text-center sm:text-left">Proprietary camera technology delivers 15-20% superior OCR accuracy. Instant capture. Professional results.</p>
                    <div class="flex items-center justify-center sm:justify-start gap-2 text-yellow-400 text-sm font-semibold cursor-pointer hover:gap-3 transition-all">
                        <span>Learn more</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Feature 2 (Responsive) -->
                <div class="glass-luxury border border-white/5 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-10 card-luxury shine">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-gold rounded-xl sm:rounded-2xl flex items-center justify-center text-3xl sm:text-4xl mb-6 sm:mb-8 shadow-luxury-gold mx-auto sm:mx-0">🤖</div>
                    <h3 class="font-serif text-2xl sm:text-3xl font-bold mb-3 sm:mb-4 text-white text-center sm:text-left">AI Mastery</h3>
                    <p class="text-gray-400 leading-relaxed mb-4 sm:mb-6 text-sm sm:text-base text-center sm:text-left">Self-evolving algorithms process 90% autonomously. Learns from every interaction. Continuously perfected.</p>
                    <div class="flex items-center justify-center sm:justify-start gap-2 text-yellow-400 text-sm font-semibold cursor-pointer hover:gap-3 transition-all">
                        <span>Explore AI</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Feature 3 (Responsive) -->
                <div class="glass-luxury border border-white/5 rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-10 card-luxury shine sm:col-span-2 lg:col-span-1">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-gold rounded-xl sm:rounded-2xl flex items-center justify-center text-3xl sm:text-4xl mb-6 sm:mb-8 shadow-luxury-gold mx-auto sm:mx-0">💎</div>
                    <h3 class="font-serif text-2xl sm:text-3xl font-bold mb-3 sm:mb-4 text-white text-center sm:text-left">Absolute Precision</h3>
                    <p class="text-gray-400 leading-relaxed mb-4 sm:mb-6 text-sm sm:text-base text-center sm:text-left">€0.02 tolerance. Zero errors. Immutable audit trail. Seven years compliant. Excellence guaranteed.</p>
                    <div class="flex items-center justify-center sm:justify-start gap-2 text-yellow-400 text-sm font-semibold cursor-pointer hover:gap-3 transition-all">
                        <span>View Details</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ultra-Responsive Luxury CTA -->
    <section class="py-16 sm:py-24 md:py-32 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-black to-gray-950"></div>
        <div class="absolute inset-0 opacity-30"><div class="w-full h-full" style="background-image: radial-gradient(circle at 1px 1px, rgba(212, 175, 55, 0.15) 1px, transparent 0); background-size: 40px 40px;"></div></div>
        
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 text-center">
            <div class="mb-6 sm:mb-8">
                <div class="inline-block w-1 h-12 sm:h-20 bg-gradient-to-b from-transparent via-yellow-600 to-transparent"></div>
            </div>
            
            <h2 class="font-serif font-bold mb-6 sm:mb-8" style="font-size: clamp(2.5rem, 10vw, 5rem);">
                <span class="text-white">Ready For</span><br>
                <span class="text-shimmer-gold">Excellence?</span>
            </h2>
            
            <p class="text-lg sm:text-xl md:text-2xl text-gray-300 mb-10 sm:mb-12 max-w-2xl mx-auto leading-relaxed px-4">
                Join an exclusive community of 200+ distinguished businesses experiencing the future of accounting
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center mb-8 sm:mb-10 max-w-2xl mx-auto">
                <a href="/klanten/login" class="btn-luxury-gold px-10 sm:px-14 py-5 sm:py-7 text-black rounded-2xl font-bold text-base sm:text-xl uppercase tracking-wide sm:tracking-wider shadow-luxury-gold hover:scale-105 transition-all touch-manipulation">
                    <span class="hidden sm:inline">Begin Your Journey</span>
                    <span class="sm:hidden">Start Now</span>
                </a>
                <a href="tel:0624995871" class="btn-luxury-dark px-10 sm:px-14 py-5 sm:py-7 text-white rounded-2xl font-bold text-base sm:text-xl uppercase tracking-wide sm:tracking-wider touch-manipulation">
                    <span class="hidden sm:inline">Speak With Us</span>
                    <span class="sm:hidden">Call Us</span>
                </a>
            </div>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-8 text-xs sm:text-sm text-gray-400 px-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                    </svg>
                    No obligations
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                    </svg>
                    Instant access
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path>
                    </svg>
                    <span class="hidden sm:inline">White-glove support</span>
                    <span class="sm:hidden">Premium support</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Ultra-Responsive Contact -->
    <section id="contact" class="py-16 sm:py-20 md:py-24 bg-gradient-to-b from-black to-gray-950 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid md:grid-cols-2 gap-10 sm:gap-12 md:gap-16">
                <div>
                    <h2 class="font-serif font-bold mb-6 sm:mb-8 text-white text-center md:text-left" style="font-size: clamp(2rem, 6vw, 3rem);">Get In Touch</h2>
                    <div class="space-y-6 text-lg">
                        <a href="mailto:marcofic2010@gmail.com" class="flex items-center gap-4 text-gray-300 hover:text-yellow-400 transition-all group">
                            <div class="w-14 h-14 glass-gold rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">📧</div>
                            <span class="font-medium">marcofic2010@gmail.com</span>
                        </a>
                        <a href="tel:0624995871" class="flex items-center gap-4 text-gray-300 hover:text-yellow-400 transition-all group">
                            <div class="w-14 h-14 glass-gold rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">📞</div>
                            <span class="font-medium">06-24995871</span>
                        </a>
                        <div class="flex items-center gap-4 text-gray-300">
                            <div class="w-14 h-14 glass-gold rounded-2xl flex items-center justify-center text-2xl">🕐</div>
                            <div>
                                <div class="font-medium">Ma-Vr 09:00-17:00</div>
                                <div class="text-sm text-gray-500">Za 11:00-15:00</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="glass-luxury border border-white/5 rounded-3xl p-10">
                    <h3 class="font-serif text-3xl font-bold mb-6 text-yellow-400">MARCOFIC Excellence</h3>
                    <div class="space-y-4 text-gray-300">
                        <div class="flex items-start gap-3">
                            <span class="text-yellow-400 text-xl">→</span>
                            <span>5+ years mastering accounting automation</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-yellow-400 text-xl">→</span>
                            <span>Trusted by 200+ distinguished businesses</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-yellow-400 text-xl">→</span>
                            <span>Netherlands' only camera-first platform</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="text-yellow-400 text-xl">→</span>
                            <span>90% automation rate guaranteed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Luxury Footer -->
    <footer class="bg-black py-16 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-12">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-gold rounded-xl flex items-center justify-center text-2xl">💎</div>
                    <div class="font-serif text-3xl font-bold text-shimmer-gold">MARCOFIC</div>
                </div>
                <div class="flex items-center gap-6 text-sm text-gray-400">
                    <a href="/klanten/login" class="hover:text-yellow-400 transition">Client Portal</a>
                    <span class="w-px h-4 bg-white/10"></span>
                    <a href="/admin/login" class="hover:text-yellow-400 transition">Admin Access</a>
                    <span class="w-px h-4 bg-white/10"></span>
                    <a href="https://www.marcofic.nl" class="hover:text-yellow-400 transition">Official Website</a>
                </div>
            </div>
            <div class="border-t border-white/5 pt-8 text-center text-gray-500 text-sm">
                <p class="mb-2">&copy; 2024 MARCOFIC. Crafted with precision.</p>
                <p>🚀 Powered by Laravel 11 • Filament v3 • AI Technology</p>
            </div>
        </div>
    </footer>

    <!-- Responsive Premium Floating Action -->
    <div class="fixed bottom-6 sm:bottom-8 md:bottom-10 right-4 sm:right-6 md:right-10 z-50">
        <a href="/klanten/login" class="group flex items-center gap-2 sm:gap-3 md:gap-4 btn-luxury-gold px-5 sm:px-6 md:px-8 py-4 sm:py-4 md:py-5 text-black rounded-full shadow-luxury-gold hover:scale-110 transition-all touch-manipulation">
            <svg class="w-6 h-6 sm:w-6 sm:h-6 md:w-7 md:h-7 group-hover:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
            </svg>
            <span class="font-bold text-sm sm:text-base md:text-lg">
                <span class="hidden sm:inline">Upload</span>
                <span class="sm:hidden">📸</span>
            </span>
        </a>
    </div>

    <script>
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const button = event.target.closest('button[onclick="toggleMobileMenu()"]');
            if (!menu.contains(event.target) && !button && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });
        
        // Close mobile menu on link click
        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('mobileMenu').classList.add('hidden');
            });
        });
        
        // Smooth scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '0';
                        entry.target.style.transform = 'translateY(30px)';
                        setTimeout(() => {
                            entry.target.style.transition = 'all 0.8s ease-out';
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, 100);
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.card-luxury, .glass-luxury').forEach(el => observer.observe(el));
            
            // Add touch-friendly hover effects on mobile
            if ('ontouchstart' in window) {
                document.querySelectorAll('.btn-luxury-gold, .btn-luxury-dark, .card-luxury').forEach(el => {
                    el.addEventListener('touchstart', function() {
                        this.style.transform = 'scale(0.98)';
                    });
                    el.addEventListener('touchend', function() {
                        this.style.transform = '';
                    });
                });
            }
        });
    </script>
</body>
</html>

