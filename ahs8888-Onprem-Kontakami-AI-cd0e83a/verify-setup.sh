#!/bin/bash

# QC Scoring App - Setup Verification Script
# Run this script to verify your environment is ready for testing

echo "============================================"
echo "QC Scoring App - Setup Verification"
echo "============================================"
echo ""

ERRORS=0
WARNINGS=0

# Check .env file
echo "Checking environment configuration..."
if [ -f .env ]; then
    echo "✅ .env file exists"
else
    echo "❌ .env file not found. Copy .env.example to .env"
    ERRORS=$((ERRORS + 1))
fi

# Check Composer dependencies
echo ""
echo "Checking PHP dependencies..."
if [ -d vendor ]; then
    echo "✅ Composer dependencies installed"
    
    # Check PhpSpreadsheet
    if [ -d vendor/phpoffice/phpspreadsheet ]; then
        echo "✅ PhpSpreadsheet installed"
    else
        echo "❌ PhpSpreadsheet not found. Run: composer require phpoffice/phpspreadsheet"
        ERRORS=$((ERRORS + 1))
    fi
else
    echo "❌ Vendor directory not found. Run: composer install"
    ERRORS=$((ERRORS + 1))
fi

# Check Node dependencies
echo ""
echo "Checking JavaScript dependencies..."
if [ -d node_modules ]; then
    echo "✅ NPM dependencies installed"
else
    echo "❌ node_modules not found. Run: npm install or yarn install"
    ERRORS=$((ERRORS + 1))
fi

# Check database connection
echo ""
echo "Checking database connection..."
if php artisan db:show 2>/dev/null | grep -q "Connection:"; then
    echo "✅ Database connection successful"
    
    # Check migrations
    echo ""
    echo "Checking migrations..."
    if php artisan migrate:status | grep -q "add_ticket_linking"; then
        echo "✅ Ticket linking migration found"
    else
        echo "⚠️  Ticket linking migration not found. Run: php artisan migrate"
        WARNINGS=$((WARNINGS + 1))
    fi
else
    echo "❌ Database connection failed. Check .env credentials"
    ERRORS=$((ERRORS + 1))
fi

# Check storage permissions
echo ""
echo "Checking file permissions..."
if [ -w storage ]; then
    echo "✅ storage/ directory writable"
else
    echo "❌ storage/ directory not writable. Run: chmod -R 775 storage"
    ERRORS=$((ERRORS + 1))
fi

if [ -w storage/logs ]; then
    echo "✅ storage/logs/ writable"
else
    echo "❌ storage/logs/ not writable"
    ERRORS=$((ERRORS + 1))
fi

# Check if test data exists
echo ""
echo "Checking test data..."
if [ -d test-data ]; then
    echo "✅ Test data directory exists"
    echo "   Available test files:"
    ls -1 test-data/*.csv 2>/dev/null | sed 's/^/   - /'
else
    echo "⚠️  test-data directory not found"
    WARNINGS=$((WARNINGS + 1))
fi

# Check Laravel key
echo ""
echo "Checking Laravel application key..."
if grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "✅ Application key set"
else
    echo "⚠️  Application key not set. Run: php artisan key:generate"
    WARNINGS=$((WARNINGS + 1))
fi

# Check queue configuration
echo ""
echo "Checking queue configuration..."
QUEUE_CONNECTION=$(grep "QUEUE_CONNECTION" .env 2>/dev/null | cut -d'=' -f2)
if [ "$QUEUE_CONNECTION" = "database" ] || [ "$QUEUE_CONNECTION" = "redis" ]; then
    echo "✅ Queue configured: $QUEUE_CONNECTION"
    echo "   Remember to run: php artisan queue:work"
else
    echo "⚠️  Queue connection is: ${QUEUE_CONNECTION:-sync}"
    echo "   Cloud updates may not work. Consider using 'database' or 'redis'"
    WARNINGS=$((WARNINGS + 1))
fi

# Summary
echo ""
echo "============================================"
echo "Summary"
echo "============================================"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo "✅ All checks passed! Ready for testing."
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo "⚠️  Setup complete with $WARNINGS warning(s)."
    echo "   You can proceed with testing."
    exit 0
else
    echo "❌ Found $ERRORS error(s) and $WARNINGS warning(s)."
    echo "   Please fix errors before proceeding."
    exit 1
fi
