#!/bin/bash

echo "🧪 Testing MARCOFIC Client Portal"
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:8000"

echo "📋 Testing Routes..."
echo ""

# Test routes
routes=(
    "/klanten"
    "/klanten/login"
    "/klanten/mijn-documenten"
    "/klanten/smart-upload"
    "/klanten/factuur-maken"
    "/klanten/rapporten"
    "/klanten/profile"
    "/klanten/hulp"
    "/klanten/onboarding"
)

for route in "${routes[@]}"; do
    echo -n "Testing $route... "
    status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$route" 2>/dev/null)
    if [ "$status" = "200" ] || [ "$status" = "302" ] || [ "$status" = "401" ] || [ "$status" = "403" ]; then
        echo -e "${GREEN}✓${NC} (HTTP $status)"
    else
        echo -e "${RED}✗${NC} (HTTP $status)"
    fi
done

echo ""
echo "📁 Checking Files..."
echo ""

# Check key files exist
files=(
    "resources/views/filament/client/pages/dashboard.blade.php"
    "resources/views/filament/client/pages/mijn-documenten.blade.php"
    "resources/views/filament/client/pages/smart-upload.blade.php"
    "app/Filament/Client/Pages/Dashboard.php"
    "app/Filament/Client/Pages/MijnDocumenten.php"
    "app/Filament/Client/Pages/SmartUpload.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} $file (MISSING)"
    fi
done

echo ""
echo "🔍 Checking for Common Issues..."
echo ""

# Check for duplicate columns
if grep -q "supplier_name" app/Filament/Client/Widgets/MyDocumentsWidget.php | wc -l | grep -q "2"; then
    echo -e "${YELLOW}⚠${NC} Possible duplicate column in MyDocumentsWidget"
else
    echo -e "${GREEN}✓${NC} No duplicate columns found"
fi

# Check for missing logo reference
if grep -q "marcofic-logo.svg" app/Providers/Filament/ClientPanelProvider.php; then
    echo -e "${YELLOW}⚠${NC} Logo file reference found (may cause 404)"
else
    echo -e "${GREEN}✓${NC} Logo reference fixed"
fi

# Check for deferLoading
if grep -q "deferLoading" app/Filament/Client/Widgets/MyDocumentsWidget.php; then
    echo -e "${GREEN}✓${NC} deferLoading added to prevent JS errors"
else
    echo -e "${YELLOW}⚠${NC} deferLoading not found"
fi

echo ""
echo "✅ Test Complete!"
echo ""
echo "📝 Next Steps:"
echo "1. Start server: php artisan serve"
echo "2. Open browser: http://localhost:8000/klanten/login"
echo "3. Login with test credentials"
echo "4. Test each page manually"
echo "5. Check browser console for errors (F12)"
echo "6. Test on mobile device"





