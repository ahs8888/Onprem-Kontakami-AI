# Cloud Application

## Purpose
Handles text analysis, QC scoring, insights generation, and AI-powered decision-making.

## Tech Stack
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue 3 + TypeScript + Inertia.js
- **Database:** MySQL 8.0+ (migrating from SQLite)
- **AI:** OpenAI / Gemini (dynamic selection)
- **Vector Store:** MySQL Vector Search
- **Real-time:** Socket.io

## Quick Start

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and Yarn
- MySQL 8.0+
- OpenAI/Gemini API Key

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
# DB_DATABASE=cloud_db
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Build frontend assets
yarn build

# Start development server
php artisan serve --port=8001
```

### Development

```bash
# Start backend server
php artisan serve --port=8001

# Start frontend dev server (separate terminal)
yarn dev

# Run queue worker (separate terminal)
php artisan queue:work
```

## Features

### Current Features
- ✅ Receive transcripts from on-prem
- ✅ User-defined scoring criteria
- ✅ AI-powered analysis (Gemini/OpenAI)
- ✅ Sentiment classification
- ✅ Agent profiling
- ✅ Export functionality (Excel)
- ✅ Real-time notifications

### Enhanced Features (Planned)
- ⏳ Ticket information reception
- ⏳ Dynamic AI provider selection (OpenAI + Gemini)
- ⏳ Correlation engine
- ⏳ Vector search for semantic similarity
- ⏳ Pattern detection
- ⏳ Recommendation engine
- ⏳ Auto-scoring system

## Directory Structure

```
app/
├── Actions/              # Business logic
├── Helpers/             # Helper functions
├── Http/Controllers/    # Request handlers
├── Models/             # Eloquent models
└── Console/Commands/   # Artisan commands

database/
└── migrations/         # Database migrations

routes/
├── web.php            # Web routes
└── api.php            # API routes

resources/
└── js/
    ├── Pages/         # Vue pages
    └── Components/    # Vue components
```

## API Endpoints

### External API (From On-Prem)

**Receive Recording:**
```
POST /api/external/v1/recording
Authorization: Bearer {token}
```

**Update Ticket Info:**
```
PATCH /api/external/v1/recording-file/{id}
Authorization: Bearer {token}
```

See `/docs/api-contract.md` for full API documentation.

## Configuration

### AI Provider

To be implemented: Dynamic selection between OpenAI and Gemini models.

### Database Migration

Migrating from SQLite to MySQL for production use.

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=AnalysisTest

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

**Socket.io connection issues:**
Check firewall and port configuration.

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
