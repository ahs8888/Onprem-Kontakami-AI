# On-Premises Application

## Purpose
Handles recording upload, Speech-to-Text processing, ticket linking, and transfer to cloud.

## Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue 3 + TypeScript + Inertia.js
- **Database:** MySQL 8.0+
- **Styling:** Tailwind CSS 4
- **STT:** Google Gemini 2.5-flash

## Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and Yarn
- MySQL 8.0+
- Gemini API Key

### Installation

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
yarn install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=onprem_db
# DB_USERNAME=root
# DB_PASSWORD=

# Add Gemini API key to .env
# GEMINI_KEY=your_api_key_here

# Run migrations
php artisan migrate

# Build frontend assets
yarn build

# Start development server
php artisan serve
```

### Development

```bash
# Start backend server
php artisan serve --port=8000

# Start frontend dev server (separate terminal)
yarn dev

# Run queue worker (separate terminal)
php artisan queue:work
```

## Features

### Current Features
- ✅ Recording upload (folder-based)
- ✅ Speech-to-Text with Gemini
- ✅ Speaker identification (Agent/Customer)
- ✅ Sentiment analysis
- ✅ Transfer to cloud API
- ✅ Retry mechanism

### Enhanced Features (In Progress)
- 🔄 Ticket linking (CSV/Excel import)
- 🔄 Display name generation
- 🔄 Unlinked recordings management
- 🔄 Retroactive cloud updates

## Directory Structure

```
app/
├── Actions/              # Business logic
├── Http/Controllers/     # Request handlers
├── Models/              # Eloquent models
├── Services/            # Service layer
└── Jobs/                # Queue jobs

database/
└── migrations/          # Database migrations

routes/
├── web.php             # Main entry point
└── admin/              # Modular routes
    ├── recording.php   # Recording routes
    └── ticket.php      # Ticket import routes

resources/
└── js/
    ├── Pages/          # Vue pages
    └── Components/     # Vue components
```

## Configuration

### Cloud Integration

Configure cloud URL and access token in Settings:
1. Login to admin panel
2. Go to Settings
3. Set "cloud_url" and "access_token"

### STT Configuration

Add Gemini API key in `.env`:
```
GEMINI_KEY=your_api_key_here
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=RecordingTest

# Run with coverage
php artisan test --coverage
```

## Deployment

See `/docs/deployment.md` in root directory for production deployment instructions.

## Troubleshooting

### Common Issues

**Queue not processing:**
```bash
php artisan queue:restart
```

**Storage permissions:**
```bash
chmod -R 775 storage bootstrap/cache
```

**Clear caches:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Support

Contact development team for issues or questions.

---

**Version:** 1.0.0-alpha
**Last Updated:** October 29, 2025
