#!/bin/bash

# Quick Start Script for On-Prem Kontakami AI
# This script automates the initial setup process

set -e  # Exit on any error

echo "========================================"
echo "On-Prem Kontakami AI - Quick Start"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed${NC}"
    echo "Please install Docker first: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker compose &> /dev/null; then
    echo -e "${RED}Error: Docker Compose is not installed${NC}"
    echo "Please install Docker Compose first: https://docs.docker.com/compose/install/"
    exit 1
fi

echo -e "${GREEN}✓ Docker and Docker Compose are installed${NC}"
echo ""

# Step 1: Copy environment file
echo "Step 1: Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.docker .env
    echo -e "${GREEN}✓ Environment file created${NC}"
else
    echo -e "${YELLOW}⚠ .env file already exists, skipping...${NC}"
fi
echo ""

# Step 2: Generate application key (will be done after container starts)
echo "Step 2: Application key will be generated after containers start"
echo ""

# Step 3: Start Docker containers
echo "Step 3: Starting Docker containers..."
echo "This may take a few minutes on first run (downloading images, building)..."
echo ""
docker compose up -d

echo ""
echo -e "${GREEN}✓ Containers started${NC}"
echo ""

# Step 4: Wait for MySQL to be ready
echo "Step 4: Waiting for MySQL to be ready..."
max_attempts=30
attempt=0
while [ $attempt -lt $max_attempts ]; do
    if docker compose exec mysql mysqladmin ping -h localhost -u root -proot_password --silent 2>/dev/null; then
        echo -e "${GREEN}✓ MySQL is ready${NC}"
        break
    fi
    attempt=$((attempt + 1))
    echo -n "."
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo -e "${RED}Error: MySQL failed to start${NC}"
    echo "Check logs: docker compose logs mysql"
    exit 1
fi
echo ""

# Step 5: Generate Laravel application key
echo "Step 5: Generating Laravel application key..."
docker compose exec -T app php artisan key:generate
echo -e "${GREEN}✓ Application key generated${NC}"
echo ""

# Step 6: Run database migrations
echo "Step 6: Running database migrations..."
docker compose exec -T app php artisan migrate --force
echo -e "${GREEN}✓ Database migrations completed${NC}"
echo ""

# Step 7: Set storage permissions
echo "Step 7: Setting storage permissions..."
docker compose exec -T app chown -R www-data:www-data /var/www/html/storage
docker compose exec -T app chmod -R 755 /var/www/html/storage
echo -e "${GREEN}✓ Storage permissions set${NC}"
echo ""

# Step 8: Install NPM dependencies (if not already done)
echo "Step 8: Installing frontend dependencies..."
echo "This may take a few minutes..."
docker compose exec -T node npm install --silent
echo -e "${GREEN}✓ Frontend dependencies installed${NC}"
echo ""

echo "========================================"
echo -e "${GREEN}Setup Complete!${NC}"
echo "========================================"
echo ""
echo "Your application is now running:"
echo ""
echo "  • Frontend:    http://localhost:8080"
echo "  • Vite Dev:     http://localhost:5173"
echo "  • MySQL:        localhost:3307"
echo "    - Database:  onprem_kontakami"
echo "    - Username:  laravel_user"
echo "    - Password:  laravel_password"
echo ""
echo "Useful commands:"
echo "  • View logs:           docker compose logs -f"
echo "  • Stop services:       docker compose down"
echo "  • Restart services:    docker compose restart"
echo "  • Run tests:           docker compose exec app php artisan test"
echo "  • Access Laravel:      docker compose exec app php artisan tinker"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "  1. Configure Gemini API key in .env file (GEMINI_KEY)"
echo "  2. Configure encryption key in .env file (ENCRYPTION_KEY)"
echo "  3. Restart services: docker compose restart app queue"
echo "  4. Follow MANUAL_TESTING_GUIDE.md for testing"
echo ""
echo "For detailed documentation, see:"
echo "  • DOCKER_SETUP_README.md"
echo "  • MANUAL_TESTING_GUIDE.md"
echo ""
