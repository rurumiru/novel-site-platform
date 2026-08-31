#!/bin/bash
set -e

echo "🚀 Starting deployment of novel-site-platform..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Update system packages
echo -e "${YELLOW}[1/10]${NC} Updating system packages..."
sudo apt update
sudo apt install -y nginx postgresql redis-server \
  php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis php8.2-mbstring \
  php8.2-gd php8.2-intl php8.2-zip php8.2-bcmath php8.2-curl php8.2-fileinfo

# Step 2: Create application directory
echo -e "${YELLOW}[2/10]${NC} Creating application directory..."
sudo mkdir -p /var/www/shiba
cd /var/www/shiba

# Step 3: Clone repository
echo -e "${YELLOW}[3/10]${NC} Cloning repository..."
sudo git clone https://github.com/rurumiru/novel-site-platform.git .

# Step 4: Install Composer dependencies
echo -e "${YELLOW}[4/10]${NC} Installing Composer dependencies..."
sudo composer install --no-dev --optimize-autoloader

# Step 5: Install NPM dependencies and build
echo -e "${YELLOW}[5/10]${NC} Installing NPM dependencies..."
sudo npm ci
echo -e "${YELLOW}[6/10]${NC} Building frontend assets..."
sudo npm run build

# Step 6: Create .env file
echo -e "${YELLOW}[7/10]${NC} Configuring environment..."
sudo cp .env.example .env
sudo php artisan key:generate

# Step 7: Set up database
echo -e "${YELLOW}[8/10]${NC} Setting up database..."
sudo php artisan migrate --force
sudo php artisan db:seed --force

# Step 8: Create storage symlink
echo -e "${YELLOW}[9/10]${NC} Creating storage symlink..."
sudo php artisan storage:link

# Step 9: Set permissions
echo -e "${YELLOW}[10/10]${NC} Setting permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Step 10: Cache for production
echo -e "${YELLOW}Caching configuration...${NC}"
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
sudo php artisan filament:optimize

echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo -e "${GREEN}Next steps:${NC}"
echo "1. Edit .env with your production database and Redis credentials"
echo "2. Configure Nginx (see deployment documentation)"
echo "3. Set up HTTPS with Certbot"
echo "4. Start services:"
echo "   sudo systemctl start nginx postgresql redis-server php8.2-fpm"
echo "5. Set up queue worker with Supervisor (if needed)"
echo "6. Configure cron for scheduler: * * * * * cd /var/www/shiba && php artisan schedule:run"
