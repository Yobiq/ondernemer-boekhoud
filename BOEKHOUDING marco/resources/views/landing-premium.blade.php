<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARCOFIC - Premium AI-Powered Accounting | #1 in Nederland</title>
    <meta name="description" content="📸 Camera upload • 🤖 90% automatisering • ✅ 100% BTW correct. De meest geavanceerde boekhoudoplossing van Nederland.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #fafafa; }
        .font-display { font-family: 'Playfair Display', serif; }
        
        .gradient-premium { 
            background: linear-gradient(135deg, #0f172a 0%, #1e40af 50%, #7c3aed 100%);
            background-size: 200% 200%;
            animation: gradientShift 12s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .glass { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.5); }
        
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-30px); } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        
        .float-animation { animation: float 8s ease-in-out infinite; }
        .fade-in-up { animation: fadeInUp 1s ease-out; }
        
        .shadow-luxury { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .shadow-premium { box-shadow: 0 35px 70px -20px rgba(59, 130, 246, 0.5); }
        
        .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.5); transition: all 0.4s ease; }
        .btn-primary:hover { box-shadow: 0 20px 40px -5px rgba(59, 130, 246, 0.7); transform: translateY(-3px) scale(1.02); }
        
        .btn-secondary { background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.3); }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.25); transform: translateY(-2px); }
        
        .feature-card { transition: all 0.5s ease; }
        .feature-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 30px 60px -10px rgba(59, 130, 246, 0.3); }
    </style>
</head>
<body>
    
    <!-- Premium Nav -->
    <nav class="glass-card backdrop-blur-2xl sticky top-0 z-50 border-b border-gray-200/50 shadow-lg">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center text-3xl shadow-lg hover:rotate-12 transition-transform">📊</div>
                    <div>
                        <span class="text-3xl font-display font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">MARCOFIC</span>
                        <div class="text-xs font-bold text-gray-500 -mt-1">PREMIUM ACCOUNTING</div>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features" class="text-gray-700 hover:text-blue-600 font-semibold">Features</a>
                    <a href="#voordelen" class="text-gray-700 hover:text-blue-600 font-semibold">Voordelen</a>
                    <a href="#contact" class="text-gray-700 hover:text-blue-600 font-semibold">Contact</a>
                    <a href="/klanten/login" class="btn-primary px-6 py-3 text-white rounded-xl font-bold">📱 Klant Login</a>
                    <a href="/admin/login" class="px-6 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800">🔐 Admin</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Premium Hero -->
    <section class="relative overflow-hidden min-h-screen flex items-center">
        <div class="absolute inset-0 gradient-premium"></div>
        <div class="absolute top-20 left-10 w-72 h-72 bg-purple-500/30 rounded-full blur-3xl opacity-50 animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-500/30 rounded-full blur-3xl opacity-50 animate-pulse"></div>
        
        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 w-full py-20">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="text-white">
                    <div class="inline-flex items-center gap-3 px-5 py-2 glass rounded-full mb-8">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <span class="text-sm font-bold">🚀 NIEUW: AI Camera Upload</span>
                        <span class="px-2 py-0.5 bg-yellow-400 text-gray-900 text-xs font-bold rounded-full">PREMIUM</span>
                    </div>
                    
                    <h1 class="font-display text-6xl md:text-8xl font-bold mb-8 leading-tight">
                        De Toekomst<br>Van <span class="bg-gradient-to-r from-yellow-300 to-yellow-200 bg-clip-text text-transparent">Boekhouding</span>
                    </h1>
                    
                    <p class="text-2xl md:text-3xl mb-10 text-blue-100 leading-relaxed">
                        <span class="font-bold text-white">📸 Foto.</span> 5 seconden.<br>
                        <span class="font-bold text-white">🤖 AI.</span> 90% automatisch.<br>
                        <span class="font-bold text-white">✅ Perfect.</span> 100% BTW correct.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-5 mb-12">
                        <a href="/klanten/login" class="group px-10 py-6 bg-white text-blue-600 rounded-2xl font-bold text-xl shadow-luxury hover:shadow-premium hover:scale-105 transition-all">
                            <span class="text-3xl mr-2">📱</span> Start Gratis →
                        </a>
                        <a href="#demo" class="btn-secondary px-10 py-6 text-white rounded-2xl font-bold text-xl">
                            <span class="text-3xl mr-2">🎥</span> Demo
                        </a>
                    </div>
                    
                    <div class="flex gap-4 flex-wrap">
                        <div class="glass px-6 py-3 rounded-2xl"><span class="text-2xl mr-2">⭐</span><span class="font-bold">200+</span> Klanten</div>
                        <div class="glass px-6 py-3 rounded-2xl"><span class="text-2xl mr-2">🏆</span><span class="font-bold">5+</span> Jaar</div>
                        <div class="glass px-6 py-3 rounded-2xl"><span class="text-2xl mr-2">💯</span><span class="font-bold">100%</span> Betrouwbaar</div>
                    </div>
                </div>

                <div class="lg:block hidden float-animation">
                    <div class="glass-card rounded-3xl shadow-premium p-8 border-2 border-white/20">
                        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl">
                            <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                                <div class="flex justify-between mb-4">
                                    <div class="flex gap-2"><span>📊</span><div><div class="font-bold">MARCOFIC</div><div class="text-xs">Portaal</div></div></div>
                                    <div class="flex gap-2">
                                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                        <div class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
                                    </div>
                                </div>
                                <div class="text-2xl font-bold">Welkom, Jan! 👋</div>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl p-6 text-white text-center shadow-lg">
                                    <div class="text-5xl mb-3">📸</div>
                                    <div class="font-bold text-xl">Document Uploaden</div>
                                    <div class="text-sm text-blue-100">Camera opent automatisch</div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-green-50 rounded-xl p-4 text-center"><div class="text-2xl font-bold text-green-600">12</div><div class="text-xs">Goedgekeurd</div></div>
                                    <div class="bg-yellow-50 rounded-xl p-4 text-center"><div class="text-2xl font-bold text-yellow-600">3</div><div class="text-xs">Bezig</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg"><path d="M0 80L48 85.3C96 91 192 101 288 96C384 91 480 69 576 64C672 59 768 69 864 80C960 91 1056 101 1152 101.3C1248 101 1344 91 1392 85.3L1440 80V120H0V80Z" fill="#fafafa"/></svg>
        </div>
    </section>

    <!-- Stats -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-8 text-center">
                <div class="observe p-8 rounded-3xl bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 shadow-lg hover:shadow-xl transition-all">
                    <div class="text-6xl font-display font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">90%</div>
                    <div class="font-semibold text-gray-700">Automatisering</div>
                </div>
                <div class="observe p-8 rounded-3xl bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 shadow-lg hover:shadow-xl transition-all">
                    <div class="text-6xl font-display font-bold bg-gradient-to-r from-emerald-600 to-emerald-500 bg-clip-text text-transparent mb-2">5s</div>
                    <div class="font-semibold text-gray-700">Upload Tijd</div>
                </div>
                <div class="observe p-8 rounded-3xl bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 shadow-lg hover:shadow-xl transition-all">
                    <div class="text-6xl font-display font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">200+</div>
                    <div class="font-semibold text-gray-700">Tevreden Klanten</div>
                </div>
                <div class="observe p-8 rounded-3xl bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 shadow-lg hover:shadow-xl transition-all">
                    <div class="text-6xl font-display font-bold bg-gradient-to-r from-amber-600 to-orange-600 bg-clip-text text-transparent mb-2">100%</div>
                    <div class="font-semibold text-gray-700">BTW Correct</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-24 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-20 observe">
                <div class="inline-block px-4 py-2 bg-blue-100 text-blue-600 rounded-full font-bold text-sm mb-4">✨ PREMIUM FEATURES</div>
                <h2 class="font-display text-5xl md:text-6xl font-bold text-gray-900 mb-6">Waarom MARCOFIC?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Als enige in Nederland: Camera upload met AI-gestuurde automatisering</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="observe feature-card glass-card rounded-3xl p-10 shadow-luxury">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-lg">📸</div>
                    <h3 class="text-2xl font-display font-bold mb-4">Camera Upload</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">Maak een foto met uw telefoon. Camera opent automatisch. Geen scanner, geen gedoe.</p>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-full text-sm font-bold">
                        <span>⚡</span> 15-20% betere OCR!
                    </div>
                </div>

                <div class="observe feature-card glass-card rounded-3xl p-10 shadow-luxury" style="animation-delay: 0.1s;">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-lg">🤖</div>
                    <h3 class="text-2xl font-display font-bold mb-4">90% Automatisch</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">AI verwerkt alles automatisch. BTW, grootboek, matching. U hoeft niets te doen!</p>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-600 rounded-full text-sm font-bold">
                        <span>🧠</span> Self-learning AI
                    </div>
                </div>

                <div class="observe feature-card glass-card rounded-3xl p-10 shadow-luxury" style="animation-delay: 0.2s;">
                    <div class="w-20 h-20 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-lg">✅</div>
                    <h3 class="text-2xl font-display font-bold mb-4">100% BTW Correct</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">€0.02 precisie. Fouten onmogelijk. Audit-proof. Garantie tegen boetes.</p>
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full text-sm font-bold">
                        <span>🔒</span> 100% Compliant
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-24 gradient-premium text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"><div class="w-full h-full bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC40Ij48cGF0aCBkPSJNMzYgMzRjMC0yLTItNC00LTRzLTQgMi00IDQgMiA0IDQgNCA0LTIgNC00eiIvPjwvZz48L2c+PC9zdmc+')]"></div></div>
        <div class="relative max-w-4xl mx-auto px-6 text-center">
            <h2 class="font-display text-5xl md:text-7xl font-bold mb-6">Klaar Om Te Beginnen?</h2>
            <p class="text-2xl mb-10 text-blue-100">Sluit u aan bij 200+ tevreden ondernemers</p>
            <div class="flex flex-col sm:flex-row gap-5 justify-center mb-8">
                <a href="/klanten/login" class="group px-12 py-6 bg-white text-blue-600 rounded-2xl font-bold text-2xl shadow-luxury hover:shadow-premium hover:scale-105 transition-all">
                    📱 Start Gratis →
                </a>
                <a href="tel:0624995871" class="btn-secondary px-12 py-6 text-white rounded-2xl font-bold text-2xl">
                    📞 06-24995871
                </a>
            </div>
            <p class="text-blue-100">✨ Geen creditcard • ✅ 30 dagen gratis • 🚀 Direct aan de slag</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div>
                    <div class="flex items-center gap-2 mb-4"><span class="text-4xl">📊</span><span class="text-3xl font-display font-bold">MARCOFIC</span></div>
                    <p class="text-gray-400 leading-relaxed">Premium AI-powered boekhouding voor het moderne bedrijf.</p>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-4">Portalen</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="/klanten/login" class="hover:text-white transition flex items-center gap-2"><span>📱</span> Klant Login</a></li>
                        <li><a href="/admin/login" class="hover:text-white transition flex items-center gap-2"><span>🔐</span> Admin Login</a></li>
                        <li><a href="https://www.marcofic.nl" class="hover:text-white transition flex items-center gap-2"><span>🌐</span> Website</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-4">Contact</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li class="flex items-center gap-2"><span>📧</span> marcofic2010@gmail.com</li>
                        <li class="flex items-center gap-2"><span>📞</span> 06-24995871</li>
                        <li class="flex items-center gap-2"><span>🕐</span> Ma-Vr 09:00-17:00</li>
                        <li class="flex items-center gap-2"><span>🕐</span> Za 11:00-15:00</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-4">Garanties</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li>✅ 90% Automatisering</li>
                        <li>✅ €0.02 BTW Precisie</li>
                        <li>✅ 7-Jaar Audit Trail</li>
                        <li>✅ GDPR Compliant</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center">
                <p class="text-gray-400">&copy; 2024 MARCOFIC. Alle rechten voorbehouden.</p>
                <p class="mt-2 text-sm text-gray-500">🚀 Powered by Laravel 11 + Filament v3 + AI Technology</p>
            </div>
        </div>
    </footer>

    <!-- Floating Mobile CTA -->
    <div class="fixed bottom-8 right-8 z-50">
        <a href="/klanten/login" class="flex items-center gap-3 px-8 py-5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full shadow-2xl hover:shadow-premium hover:scale-110 transition-all duration-300">
            <span class="text-3xl">📸</span>
            <span class="font-bold text-lg">Upload Nu</span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) entry.target.classList.add('fade-in-up');
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.observe').forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
