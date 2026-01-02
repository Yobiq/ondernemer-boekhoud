# 🔐 MARCOFIC SYSTEM - LOGIN GEGEVENS

**Laatst Bijgewerkt:** 18 December 2024  
**Status:** ✅ Alle Accounts Actief

---

## 👥 **KLANTEN PORTAAL**

**URL:** http://localhost:8000/klanten/login

### **Demo Klant 1: Restaurant De Gouden Lepel BV**
```
👤 Naam:     Jan Jansen
📧 Email:    jan@goudenlepel.nl
🔑 Password: demo123
🏢 Bedrijf:  Restaurant De Gouden Lepel BV
🆔 KVK:      12345678
```

### **Demo Klant 2: TechStart Nederland BV**
```
👤 Naam:     Lisa de Vries
📧 Email:    lisa@techstart.nl
🔑 Password: demo123
🏢 Bedrijf:  TechStart Nederland BV
🆔 KVK:      87654321
```

### **Demo Klant 3: Kledingwinkel Amsterdam**
```
👤 Naam:     Mohammed Ali
📧 Email:    mo@kledingwinkel-ams.nl
🔑 Password: demo123
🏢 Bedrijf:  Kledingwinkel Amsterdam
🆔 KVK:      11223344
```

---

## 👨‍💼 **ADMIN / BOEKHOUDER PORTAAL**

**URL:** http://localhost:8000/admin/login

### **Boekhouder Account (Primary)**
```
👤 Naam:     Boekhouder Demo
📧 Email:    boekhouder@nlaccounting.nl
🔑 Password: boekhouder123
🎭 Rol:      Boekhouder (volledige rechten)
```

### **Admin Account (Backup)**
```
👤 Naam:     Administrator
📧 Email:    admin@nlaccounting.nl
🔑 Password: admin123
🎭 Rol:      Admin (volledige rechten)
```

---

## 🎯 **QUICK ACCESS:**

### **Voor Klanten:**
1. Open: http://localhost:8000/klanten/login
2. Kies een demo account
3. Login
4. Klik "Document Uploaden"
5. Test camera upload! 📸

### **Voor MARCOFIC Team:**
1. Open: http://localhost:8000/admin/login
2. Login als boekhouder
3. Dashboard met 6 widgets
4. Klik "Document Beoordeling"
5. Review documenten!

---

## 📱 **MOBILE TESTING:**

### **Op Uw Telefoon:**
```
1. Vind uw computer IP:
   Mac: System Preferences → Network
   Of: ifconfig | grep "inet " | grep -v 127.0.0.1

2. Open op telefoon:
   http://[uw-ip]:8000/klanten/login

3. Login:
   📧 jan@goudenlepel.nl
   🔑 demo123

4. Test camera upload!
```

---

## 🔒 **BEVEILIGING:**

### **Development (NU):**
- ⚠️ Passwords in plain text (demo only!)
- ⚠️ Test accounts zichtbaar op login
- ⚠️ HTTP (geen SSL)
- ✅ Alleen local access

### **Production (LATER):**
- ✅ Verwijder demo accounts
- ✅ Sterke passwords
- ✅ HTTPS/SSL required
- ✅ 2FA optie
- ✅ Rate limiting
- ✅ Session timeout

---

## 👥 **NIEUWE KLANT AANMAKEN:**

### **Via Tinker:**
```bash
php artisan tinker

# Maak klant aan
$client = App\Models\Client::create([
    'name' => 'Uw Bedrijf BV',
    'email' => 'info@uwbedrijf.nl',
    'kvk_number' => '99887766',
    'active' => true
]);

# Maak user aan
$user = App\Models\User::create([
    'name' => 'Voornaam Achternaam',
    'email' => 'naam@uwbedrijf.nl',
    'password' => Hash::make('veiligwachtwoord'),
    'client_id' => $client->id,
    'email_verified_at' => now()
]);

# Geef client rol
$user->assignRole('client');
```

### **Via Admin UI:**
1. Login als admin
2. Ga naar "Clients"
3. Klik "Nieuwe Client"
4. Vul gegevens in
5. Sla op
6. Maak bijbehorende user aan in Users sectie

---

## 📊 **ACCOUNT OVERZICHT:**

| Type | Aantal | Portal | Rechten |
|------|--------|--------|---------|
| **Klanten** | 3 | /klanten | Upload only, view own |
| **Boekhouders** | 2 | /admin | Volledige toegang |
| **Total** | 5 | - | - |

---

## 🎓 **ROLLEN & PERMISSIONS:**

### **Client Rol:**
- ✅ Documenten uploaden
- ✅ Eigen documenten bekijken
- ✅ Taken bekijken
- ✅ Status tracking
- ❌ Andere klanten zien
- ❌ Admin functies

### **Boekhouder/Admin Rol:**
- ✅ Alle documenten bekijken
- ✅ Document review
- ✅ BTW rapporten
- ✅ Klantenbeheer
- ✅ Grootboek beheer
- ✅ Alle admin functies

---

## 💡 **TIPS:**

### **Voor Testing:**
- Gebruik Jan (restaurant) voor dagelijkse bonnetjes
- Gebruik Lisa (tech) voor software/online facturen
- Gebruik Mohammed (retail) voor leverancier facturen

### **Voor Demo:**
- Laat klanten inloggen op hun telefoon
- Demonstreer camera upload (WOW-factor!)
- Laat automatische verwerking zien
- Toon dashboard met realtime status

---

## 🎉 **ENHANCED LOGIN PAGES:**

### **Klanten Login** (`/klanten/login`)
- ✅ MARCOFIC goud logo (💎)
- ✅ Welkom bericht
- ✅ Demo credentials zichtbaar
- ✅ 3 feature badges
- ✅ Contact info onderaan
- ✅ "Terug naar home" link

### **Admin Login** (`/admin/login`)
- ✅ MARCOFIC blauw logo (🔐)
- ✅ "Boekhouder Portaal" titel
- ✅ Admin info box
- ✅ Demo credentials
- ✅ 3 feature badges
- ✅ Professional styling

---

## 🚀 **GEBRUIK:**

**Login als klant en test:**
1. Camera upload wizard
2. Dashboard widgets
3. Document status tracking
4. Task management

**Login als boekhouder en test:**
1. Dashboard met 6 KPI widgets
2. Document review (split-view)
3. Keyboard shortcuts (Enter, ←, →)
4. BTW validatie
5. Grootboek suggesties
6. Bulk approve

---

**🔐 Bewaar deze gegevens veilig!**

**Voor productie: Wijzig alle passwords!** ⚠️

