<!DOCTYPE html>
<html lang="nl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARCOFIC - Professionele Boekhouding | 200+ Tevreden Klanten</title>
    <meta name="description" content="Upload documenten met camera. 90% automatische verwerking. 100% BTW correct. Moderne boekhouding voor Nederlandse ondernemers.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #000; color: #fff; }
        
        .gradient-premium { background: linear-gradient(135deg, #0f172a 0%, #1e40af 50%, #7c3aed 100%); background-size: 200%; animation: gradientShift 12s ease infinite; }
        @keyframes gradientShift { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); }
        
        .text-gold { color: #D4AF37; }
        .bg-gold { background: linear-gradient(135deg, #B8972F 0%, #D4AF37 100%); }
        .border-gold { border-color: rgba(212, 175, 55, 0.3); }
        
        .btn-gold { background: linear-gradient(135deg, #D4AF37 0%, #F4E4B6 100%); box-shadow: 0 10px 30px -5px rgba(212, 175, 55, 0.4); transition: all 0.3s ease; }
        .btn-gold:hover { box-shadow: 0 20px 40px -5px rgba(212, 175, 55, 0.6); transform: translateY(-2px); }
        
        .btn-dark { background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(255, 255, 255, 0.1); transition: all 0.3s ease; }
        .btn-dark:hover { background: rgba(255, 255, 255, 0.1); border-color: rgba(212, 175, 55, 0.3); }
        
        .card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -10px rgba(212, 175, 55, 0.2); }
    </style>
</head>
<body>
    
    <!-- Navigatie -->
    <nav class="glass backdrop-blur-2xl fixed w-full top-0 z-50 border-b border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gold rounded-xl flex items-center justify-center text-2xl shadow-lg">💎</div>
                    <div>
                        <div class="text-2xl font-bold text-gold">MARCOFIC</div>
                        <div class="text-[10px] text-gray-400 tracking-wider -mt-1">PROFESSIONELE BOEKHOUDING</div>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-6">
                    <a href="#diensten" class="text-gray-300 hover:text-gold transition">Diensten</a>
                    <a href="#voordelen" class="text-gray-300 hover:text-gold transition">Voordelen</a>
                    <a href="#contact" class="text-gray-300 hover:text-gold transition">Contact</a>
                    <a href="/klanten/login" class="btn-gold px-6 py-2.5 text-black rounded-lg font-semibold text-sm">Klant Login</a>
                    <a href="/admin/login" class="btn-dark px-6 py-2.5 text-white rounded-lg font-semibold text-sm">Boekhouder</a>
                </div>
                <button onclick="toggleMenu()" class="md:hidden btn-dark p-3 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>
        <div id="mobileMenu" class="hidden md:hidden glass border-t border-white/5">
            <div class="px-4 py-4 space-y-3">
                <a href="#diensten" class="block py-2 text-gray-300 hover:text-gold">Diensten</a>
                <a href="#voordelen" class="block py-2 text-gray-300 hover:text-gold">Voordelen</a>
                <a href="#contact" class="block py-2 text-gray-300 hover:text-gold">Contact</a>
                <a href="/klanten/login" class="block btn-gold px-6 py-3 text-black rounded-lg font-semibold text-center mt-4">📱 Klant Login</a>
                <a href="/admin/login" class="block btn-dark px-6 py-3 text-white rounded-lg font-semibold text-center">🔐 Boekhouder</a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="relative min-h-screen flex items-center overflow-hidden pt-20">
        <div class="absolute inset-0 gradient-premium"></div>
        <div class="absolute top-20 left-10 w-72 h-72 bg-yellow-600/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full py-16">
            <div class="text-center lg:text-left max-w-3xl mx-auto lg:mx-0">
                <div class="inline-flex items-center gap-2 glass px-4 py-2 rounded-full mb-6 text-xs">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                    <span class="text-yellow-200 font-semibold">SINDS 2019 — 200+ TEVREDEN KLANTEN</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 leading-tight">
                    <span class="text-white">Moderne Boekhouding</span><br>
                    <span class="text-gold">Voor Nederlandse</span><br>
                    <span class="text-white">Ondernemers</span>
                </h1>
                
                <p class="text-lg sm:text-xl text-gray-300 mb-8 leading-relaxed">
                    Upload documenten met uw telefoon camera. AI verwerkt alles automatisch. 90% van documenten worden direct goedgekeurd.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 mb-10">
                    <a href="/klanten/login" class="btn-gold px-8 py-4 text-black rounded-xl font-bold text-base sm:text-lg">
                        📱 Start Gratis
                    </a>
                    <a href="#voordelen" class="btn-dark px-8 py-4 text-white rounded-xl font-bold text-base sm:text-lg">
                        📊 Meer Info
                    </a>
                </div>
                
                <div class="grid grid-cols-3 gap-4 max-w-md mx-auto lg:mx-0">
                    <div class="text-center"><div class="text-2xl sm:text-3xl font-bold text-gold mb-1">200+</div><div class="text-xs text-gray-400">Klanten</div></div>
                    <div class="text-center border-x border-white/10"><div class="text-2xl sm:text-3xl font-bold text-gold mb-1">5+</div><div class="text-xs text-gray-400">Jaar</div></div>
                    <div class="text-center"><div class="text-2xl sm:text-3xl font-bold text-gold mb-1">100%</div><div class="text-xs text-gray-400">BTW OK</div></div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0"><svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg"><path d="M0 80L48 85.3C96 91 192 101 288 96C384 91 480 69 576 64C672 59 768 69 864 80C960 91 1056 101 1152 101.3C1248 101 1344 91 1392 85.3L1440 80V120H0V80Z" fill="#000"/></svg></div>
    </section>

    <!-- Stats -->
    <section class="py-12 sm:py-16 bg-black">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                <div class="text-center p-6 glass rounded-xl border border-white/5 card transition-all"><div class="text-4xl font-bold text-gold mb-2">90%</div><div class="text-xs text-gray-400">Automatisering</div></div>
                <div class="text-center p-6 glass rounded-xl border border-white/5 card transition-all"><div class="text-4xl font-bold text-gold mb-2">5s</div><div class="text-xs text-gray-400">Upload Tijd</div></div>
                <div class="text-center p-6 glass rounded-xl border border-white/5 card transition-all"><div class="text-4xl font-bold text-gold mb-2">200+</div><div class="text-xs text-gray-400">Klanten</div></div>
                <div class="text-center p-6 glass rounded-xl border border-white/5 card transition-all"><div class="text-4xl font-bold text-gold mb-2">100%</div><div class="text-xs text-gray-400">BTW Correct</div></div>
            </div>
        </div>
    </section>

    <!-- Diensten -->
    <section id="diensten" class="py-16 sm:py-20 bg-gradient-to-b from-black to-gray-950">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <div class="inline-block px-4 py-2 glass rounded-full text-gold font-semibold text-xs mb-4">ONZE DIENSTEN</div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-4">Waarom MARCOFIC?</h2>
                <p class="text-lg text-gray-400">Als enige in Nederland: Camera upload met AI-automatisering</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="glass rounded-2xl p-8 border border-white/5 card transition-all">
                    <div class="w-16 h-16 bg-gold rounded-xl flex items-center justify-center text-3xl mb-6 shadow-lg">📸</div>
                    <h3 class="text-2xl font-bold mb-3 text-white">Camera Upload</h3>
                    <p class="text-gray-400 mb-4">Maak een foto met uw telefoon. Camera opent automatisch. Upload in 5 seconden. Geen scanner nodig.</p>
                    <div class="text-gold text-sm font-semibold">15-20% betere OCR →</div>
                </div>

                <div class="glass rounded-2xl p-8 border border-white/5 card transition-all">
                    <div class="w-16 h-16 bg-gold rounded-xl flex items-center justify-center text-3xl mb-6 shadow-lg">🤖</div>
                    <h3 class="text-2xl font-bold mb-3 text-white">90% Automatisch</h3>
                    <p class="text-gray-400 mb-4">AI-systeem verwerkt documenten volledig automatisch. BTW, grootboek, matching. U hoeft niets te doen.</p>
                    <div class="text-gold text-sm font-semibold">Zelflerende AI →</div>
                </div>

                <div class="glass rounded-2xl p-8 border border-white/5 card transition-all sm:col-span-2 lg:col-span-1">
                    <div class="w-16 h-16 bg-gold rounded-xl flex items-center justify-center text-3xl mb-6 shadow-lg">✅</div>
                    <h3 class="text-2xl font-bold mb-3 text-white">100% BTW Correct</h3>
                    <p class="text-gray-400 mb-4">€0.02 precisie. BTW fouten onmogelijk. 7-jaar audit trail. Volledig Belastingdienst compliant.</p>
                    <div class="text-gold text-sm font-semibold">Audit-proof →</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Voordelen -->
    <section id="voordelen" class="py-16 sm:py-20 bg-black">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-4">Uw Voordelen</h2>
                <p class="text-lg text-gray-400">Waarom 200+ ondernemers kiezen voor MARCOFIC</p>
            </div>
            
            <div class="grid sm:grid-cols-2 gap-6">
                <div class="flex gap-4 glass rounded-xl p-6 border border-white/5">
                    <div class="text-3xl">⚡</div>
                    <div><h3 class="font-bold text-lg text-white mb-2">95% Tijdwinst</h3><p class="text-gray-400 text-sm">Van 30 minuten scannen naar 5 seconden foto maken.</p></div>
                </div>
                <div class="flex gap-4 glass rounded-xl p-6 border border-white/5">
                    <div class="text-3xl">💰</div>
                    <div><h3 class="font-bold text-lg text-white mb-2">Geen BTW Boetes</h3><p class="text-gray-400 text-sm">€0.02 precisie. Fouten onmogelijk. 100% compliant.</p></div>
                </div>
                <div class="flex gap-4 glass rounded-xl p-6 border border-white/5">
                    <div class="text-3xl">📱</div>
                    <div><h3 class="font-bold text-lg text-white mb-2">Altijd & Overal</h3><p class="text-gray-400 text-sm">Upload direct na aankoop. Geen bonnetjes vergeten.</p></div>
                </div>
                <div class="flex gap-4 glass rounded-xl p-6 border border-white/5">
                    <div class="text-3xl">🔍</div>
                    <div><h3 class="font-bold text-lg text-white mb-2">Realtime Inzicht</h3><p class="text-gray-400 text-sm">Dashboard toont status van al uw documenten.</p></div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 gradient-premium relative">
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <h2 class="text-4xl sm:text-5xl font-bold mb-6 text-white">Klaar Om Te Beginnen?</h2>
            <p class="text-xl text-gray-300 mb-8">Sluit u aan bij 200+ tevreden ondernemers</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-6">
                <a href="/klanten/login" class="btn-gold px-10 py-5 text-black rounded-xl font-bold text-lg">📱 Start Gratis</a>
                <a href="tel:0624995871" class="btn-dark px-10 py-5 text-white rounded-xl font-bold text-lg">📞 Bel Ons</a>
            </div>
            <p class="text-sm text-gray-400">✅ Geen verplichtingen • ✅ Direct toegang • ✅ Persoonlijke ondersteuning</p>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="py-16 bg-gradient-to-b from-black to-gray-950 border-t border-white/5">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid md:grid-cols-2 gap-10">
                <div>
                    <h2 class="text-3xl font-bold mb-6 text-white">Neem Contact Op</h2>
                    <div class="space-y-4">
                        <a href="mailto:marcofic2010@gmail.com" class="flex items-center gap-3 text-gray-300 hover:text-gold transition group">
                            <div class="w-12 h-12 glass rounded-lg flex items-center justify-center text-xl group-hover:scale-110 transition">📧</div>
                            <span>marcofic2010@gmail.com</span>
                        </a>
                        <a href="tel:0624995871" class="flex items-center gap-3 text-gray-300 hover:text-gold transition group">
                            <div class="w-12 h-12 glass rounded-lg flex items-center justify-center text-xl group-hover:scale-110 transition">📞</div>
                            <span>06-24995871</span>
                        </a>
                        <div class="flex items-center gap-3 text-gray-300">
                            <div class="w-12 h-12 glass rounded-lg flex items-center justify-center text-xl">🕐</div>
                            <div><div>Ma-Vr 09:00-17:00</div><div class="text-sm text-gray-500">Za 11:00-15:00</div></div>
                        </div>
                    </div>
                </div>
                <div class="glass rounded-2xl p-8 border border-white/5">
                    <h3 class="text-2xl font-bold mb-4 text-gold">MARCOFIC</h3>
                    <div class="space-y-3 text-gray-300 text-sm">
                        <div class="flex gap-2"><span class="text-gold">→</span><span>5+ jaar ervaring in automatisering</span></div>
                        <div class="flex gap-2"><span class="text-gold">→</span><span>200+ tevreden Nederlandse bedrijven</span></div>
                        <div class="flex gap-2"><span class="text-gold">→</span><span>Enige met camera upload in NL</span></div>
                        <div class="flex gap-2"><span class="text-gold">→</span><span>90% automatisering gegarandeerd</span></div>
                        <div class="flex gap-2"><span class="text-gold">→</span><span>100% betrouwbaarheid</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black py-12 border-t border-white/5">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-8">
                <div class="flex items-center gap-2"><div class="w-10 h-10 bg-gold rounded-lg flex items-center justify-center text-xl">💎</div><span class="text-2xl font-bold text-gold">MARCOFIC</span></div>
                <div class="flex gap-6 text-sm text-gray-400">
                    <a href="/klanten/login" class="hover:text-gold transition">Klanten</a>
                    <a href="/admin/login" class="hover:text-gold transition">Admin</a>
                    <a href="https://www.marcofic.nl" class="hover:text-gold transition">Website</a>
                </div>
            </div>
            <div class="border-t border-white/5 pt-6 text-center text-gray-500 text-sm">
                <p>&copy; 2024 MARCOFIC. Alle rechten voorbehouden.</p>
            </div>
        </div>
    </footer>

    <!-- Floating Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <a href="/klanten/login" class="flex items-center gap-2 btn-gold px-6 py-4 text-black rounded-full shadow-lg hover:scale-110 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
            <span class="font-bold">Upload</span>
        </a>
    </div>

    <script>
        function toggleMenu() { document.getElementById('mobileMenu').classList.toggle('hidden'); }
        document.querySelectorAll('#mobileMenu a').forEach(a => a.addEventListener('click', () => document.getElementById('mobileMenu').classList.add('hidden')));
    </script>
</body>
</html>

