<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARCOFIC - Premium AI-Powered Boekhouding | 200+ Tevreden Klanten</title>
    <meta name="description" content="De meest geavanceerde boekhoudoplossing van Nederland. Camera upload, 90% automatisering, 100% BTW correct.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }
        
        .font-display { font-family: 'Playfair Display', serif; }
        
        /* Premium Gradients */
        .gradient-luxury { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 200% 200%;
            animation: gradientShift 8s ease infinite;
        }
        
        .gradient-premium { 
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #8b5cf6 100%);
            background-size: 200% 200%;
            animation: gradientShift 10s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        /* Glass Morphism */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .glass-dark {
            background: rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Animations */
        @keyframes float { 
            0%, 100% { transform: translateY(0) rotate(0deg); } 
            50% { transform: translateY(-30px) rotate(2deg); } 
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        
        .float-animation { animation: float 6s ease-in-out infinite; }
        .fade-in-up { animation: fadeInUp 0.8s ease-out; }
        .scale-in { animation: scaleIn 0.6s ease-out; }
        
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            background-size: 1000px 100%;
            animation: shimmer 3s infinite;
        }
        
        /* Premium Shadows */
        .shadow-luxury { box-shadow: 0 20px 60px -10px rgba(59, 130, 246, 0.3); }
        .shadow-premium { box-shadow: 0 30px 80px -20px rgba(79, 70, 229, 0.4); }
        
        /* Smooth Scroll */
        html { scroll-behavior: smooth; }
        
        /* Premium Button Glow */
        .btn-glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
            transition: all 0.3s ease;
        }
        
        .btn-glow:hover {
            box-shadow: 0 0 40px rgba(59, 130, 246, 0.8);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="antialiased">
    
    <!-- Premium Navigation with Glass Effect -->
    <nav class="glass-dark backdrop-blur-xl sticky top-0 z-50 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3 scale-in">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center text-2xl shadow-lg">
                        📊
                    </div>
                    <div>
                        <span class="text-2xl font-display font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            MARCOFIC
                        </span>
                        <div class="text-xs text-gray-500 -mt-1">Premium Accounting</div>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="#features" class="text-gray-700 hover:text-blue-600 transition-all duration-300 font-medium relative group">
                        Features
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#voordelen" class="text-gray-700 hover:text-blue-600 transition-all duration-300 font-medium relative group">
                        Voordelen
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 transition-all duration-300 font-medium relative group">
                        Contact
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-600 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="/klanten/login" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:shadow-lg transition-all duration-300 font-semibold hover:scale-105 btn-glow">
                        Klant Login
                    </a>
                    <a href="/admin/login" class="px-6 py-2.5 bg-white border-2 border-blue-600 text-blue-600 rounded-xl hover:bg-blue-50 transition-all duration-300 font-semibold hover:scale-105">
                        Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-blue text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                        Boekhouding op zijn <span class="text-yellow-300">eenvoudigst</span>
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-blue-100">
                        📸 Maak een foto van uw bonnetje<br>
                        🤖 Wij verwerken het automatisch<br>
                        ✅ 90% volledig geautomatiseerd
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/klanten/login" class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 rounded-xl font-bold text-lg hover:bg-blue-50 transition shadow-lg transform hover:scale-105">
                            📱 Start Nu (Gratis)
                        </a>
                        <a href="#demo" class="inline-flex items-center justify-center px-8 py-4 bg-blue-700 text-white rounded-xl font-bold text-lg hover:bg-blue-800 transition">
                            🎥 Bekijk Demo
                        </a>
                    </div>
                    <div class="mt-8 flex items-center gap-8 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-3xl">⭐</span>
                            <span>200+ Klanten</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-3xl">🏆</span>
                            <span>5+ Jaar</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-3xl">💯</span>
                            <span>100% BTW Correct</span>
                        </div>
                    </div>
                </div>
                <div class="float-animation">
                    <div class="bg-white rounded-2xl shadow-2xl p-8">
                        <div class="text-center mb-6">
                            <div class="text-6xl mb-4">📸</div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Camera Upload</h3>
                            <p class="text-gray-600">Upload in 5 seconden vanaf uw telefoon</p>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 text-gray-700">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">1</div>
                                <span>Maak foto met camera</span>
                            </div>
                            <div class="flex items-center gap-3 text-gray-700">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">2</div>
                                <span>Automatische verwerking</span>
                            </div>
                            <div class="flex items-center gap-3 text-gray-700">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold">3</div>
                                <span>Klaar! ✨</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-5xl font-bold text-blue-600 mb-2">90%</div>
                    <div class="text-gray-600">Automatisering</div>
                </div>
                <div>
                    <div class="text-5xl font-bold text-blue-600 mb-2">5 sec</div>
                    <div class="text-gray-600">Upload tijd</div>
                </div>
                <div>
                    <div class="text-5xl font-bold text-blue-600 mb-2">200+</div>
                    <div class="text-gray-600">Tevreden klanten</div>
                </div>
                <div>
                    <div class="text-5xl font-bold text-blue-600 mb-2">100%</div>
                    <div class="text-gray-600">BTW correct</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Waarom MARCOFIC?
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Als enige in Nederland: Camera upload met AI-gestuurde automatisering
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-xl transition">
                    <div class="text-5xl mb-4">📸</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-900">Camera Upload</h3>
                    <p class="text-gray-600 mb-4">
                        Maak een foto met uw telefoon. Geen scanner nodig, geen gedoe. Upload in 5 seconden!
                    </p>
                    <div class="text-sm text-blue-600 font-semibold">15-20% betere OCR resultaten →</div>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-xl transition">
                    <div class="text-5xl mb-4">🤖</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-900">90% Automatisch</h3>
                    <p class="text-gray-600 mb-4">
                        Ons slim AI-systeem verwerkt 90% van alle documenten volledig automatisch. U hoeft niets te doen!
                    </p>
                    <div class="text-sm text-blue-600 font-semibold">500 uur per maand bespaard →</div>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-xl transition">
                    <div class="text-5xl mb-4">✅</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-900">100% BTW Correct</h3>
                    <p class="text-gray-600 mb-4">
                        €0.02 precisie. BTW fouten zijn onmogelijk. Garantie tegen boetes van de Belastingdienst.
                    </p>
                    <div class="text-sm text-blue-600 font-semibold">Audit-proof →</div>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-xl transition">
                    <div class="text-5xl mb-4">📊</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-900">Realtime Dashboard</h3>
                    <p class="text-gray-600 mb-4">
                        Altijd inzicht in de status van uw documenten. 6 live widgets tonen alle belangrijke cijfers.
                    </p>
                    <div class="text-sm text-blue-600 font-semibold">Live monitoring →</div>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-xl transition">
                    <div class="text-5xl mb-4">🔒</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-900">100% Veilig</h3>
                    <p class="text-gray-600 mb-4">
                        7-jaar audit trail. GDPR compliant. Elke klant ziet alleen eigen data. Bank-niveau beveiliging.
                    </p>
                    <div class="text-sm text-blue-600 font-semibold">Privacy first →</div>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white rounded-xl p-8 shadow-lg hover:shadow-xl transition">
                    <div class="text-5xl mb-4">🧠</div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-900">Zelflerende AI</h3>
                    <p class="text-gray-600 mb-4">
                        Het systeem leert van uw correcties. Wordt elke dag slimmer. 72+ intelligente regels.
                    </p>
                    <div class="text-sm text-blue-600 font-semibold">AI-powered →</div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Zo Simpel Werkt Het
                </h2>
                <p class="text-xl text-gray-600">In 3 stappen van bonnetje naar goedkeuring</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                        📸
                    </div>
                    <h3 class="text-2xl font-bold mb-3">1. Foto Maken</h3>
                    <p class="text-gray-600">
                        Open de app op uw telefoon. Camera start automatisch. Maak foto. Klaar in 5 seconden!
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                        🤖
                    </div>
                    <h3 class="text-2xl font-bold mb-3">2. Automatisch</h3>
                    <p class="text-gray-600">
                        Ons AI-systeem leest het document, controleert BTW, en wijst het grootboek toe. Zonder dat u iets doet!
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                        ✅
                    </div>
                    <h3 class="text-2xl font-bold mb-3">3. Goedgekeurd</h3>
                    <p class="text-gray-600">
                        90% wordt automatisch goedgekeurd. De rest controleert MARCOFIC in enkele seconden. U hoeft niets te doen!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="voordelen" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Uw Voordelen
                </h2>
            </div>

            <div class="grid md:grid-cols-2 gap-12">
                <div class="flex gap-4">
                    <div class="text-4xl">⚡</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">95% Tijdwinst</h3>
                        <p class="text-gray-600">Van 30 minuten scannen naar 5 seconden foto maken. 95% sneller!</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-4xl">💰</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Geen Boetes</h3>
                        <p class="text-gray-600">€0.02 BTW precisie. Fouten zijn onmogelijk. 100% Belastingdienst compliant.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-4xl">📱</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Altijd & Overal</h3>
                        <p class="text-gray-600">Upload direct na aankoop. Vanaf uw telefoon. Geen bonnetjes meer vergeten!</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-4xl">🔍</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Altijd Inzicht</h3>
                        <p class="text-gray-600">Realtime dashboard. Weet altijd waar u aan toe bent. Geen verrassingen.</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-4xl">🧠</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Slimmer & Slimmer</h3>
                        <p class="text-gray-600">AI leert van uw documenten. Shell = Brandstof. Albert Heijn = Kantoor. Automatisch!</p>
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="text-4xl">🏆</div>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Marktleider</h3>
                        <p class="text-gray-600">Enige met camera upload in Nederland. 2-3 jaar vooruit op concurrentie!</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Zie Het In Actie
                </h2>
                <p class="text-xl text-gray-600">Van foto naar goedkeuring in 15 seconden</p>
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl p-1 shadow-2xl">
                <div class="bg-white rounded-xl p-8">
                    <div class="aspect-video bg-gray-100 rounded-lg flex items-center justify-center">
                        <div class="text-center">
                            <div class="text-6xl mb-4">🎥</div>
                            <p class="text-gray-600 text-lg">Demo Video (Coming Soon)</p>
                            <p class="text-sm text-gray-500 mt-2">Of test direct in het klanten portaal!</p>
                            <a href="/klanten/login" class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Test Nu Gratis →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing / CTA Section -->
    <section class="py-20 gradient-blue text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Klaar Om Te Beginnen?
            </h2>
            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                Sluit u aan bij 200+ tevreden ondernemers die hun boekhouding hebben geautomatiseerd
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/klanten/login" class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 rounded-xl font-bold text-lg hover:bg-blue-50 transition shadow-lg">
                    📱 Start Direct (Gratis Test)
                </a>
                <a href="tel:0624995871" class="inline-flex items-center justify-center px-8 py-4 bg-blue-700 text-white rounded-xl font-bold text-lg hover:bg-blue-800 transition">
                    📞 Bel: 06-24995871
                </a>
            </div>
            <p class="mt-6 text-blue-100 text-sm">
                ✨ Geen creditcard nodig • ✅ 30 dagen gratis testen • 🚀 Binnen 5 minuten aan de slag
            </p>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-6">
                        Neem Contact Op
                    </h2>
                    <div class="space-y-4 text-lg">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">📧</div>
                            <a href="mailto:marcofic2010@gmail.com" class="text-blue-600 hover:underline">
                                marcofic2010@gmail.com
                            </a>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">📞</div>
                            <a href="tel:0624995871" class="text-blue-600 hover:underline">
                                06-24995871
                            </a>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">🕐</div>
                            <span class="text-gray-700">Ma-Vr 09:00-17:00, Za 11:00-15:00</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">🌐</div>
                            <a href="https://www.marcofic.nl" target="_blank" class="text-blue-600 hover:underline">
                                www.marcofic.nl
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-8">
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Waarom 200+ Klanten Kiezen Voor MARCOFIC</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="text-green-500 text-2xl">✓</span>
                            <span class="text-gray-700">5+ jaar ervaring in de branche</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-500 text-2xl">✓</span>
                            <span class="text-gray-700">Persoonlijke service en aandacht</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-500 text-2xl">✓</span>
                            <span class="text-gray-700">Hoogste technologische standaarden</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-500 text-2xl">✓</span>
                            <span class="text-gray-700">90% automatisering (uniek in NL!)</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="text-green-500 text-2xl">✓</span>
                            <span class="text-gray-700">100% betrouwbaarheid</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-3xl">📊</span>
                        <span class="text-2xl font-bold">MARCOFIC</span>
                    </div>
                    <p class="text-gray-400">
                        Professionele boekhouding met AI-gestuurde automatisering.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Snelle Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="/klanten/login" class="hover:text-white transition">Klant Login</a></li>
                        <li><a href="/admin/login" class="hover:text-white transition">Admin Login</a></li>
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="https://www.marcofic.nl" class="hover:text-white transition">Website</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>📧 marcofic2010@gmail.com</li>
                        <li>📞 06-24995871</li>
                        <li>🕐 Ma-Vr 09:00-17:00</li>
                        <li>🕐 Za 11:00-15:00</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold mb-4">Systeem</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>✅ 90% Automatisering</li>
                        <li>✅ €0.02 BTW Precisie</li>
                        <li>✅ 7-Jaar Audit Trail</li>
                        <li>✅ GDPR Compliant</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; 2024 MARCOFIC. Alle rechten voorbehouden.</p>
                <p class="mt-2 text-sm">
                    🚀 Powered by Laravel 11 + Filament v3 + AI OCR Technology
                </p>
            </div>
        </div>
    </footer>

    <!-- Floating CTA Button (Mobile) -->
    <div class="fixed bottom-6 right-6 z-50 md:hidden">
        <a href="/klanten/login" class="flex items-center gap-2 px-6 py-4 bg-blue-600 text-white rounded-full shadow-2xl hover:bg-blue-700 transition transform hover:scale-105">
            <span class="text-2xl">📸</span>
            <span class="font-bold">Upload Nu</span>
        </a>
    </div>

</body>
</html>

