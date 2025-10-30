# Docker Setup Guide - On-Prem Kontakami AI

This guide will help you set up and run the On-Prem Kontakami AI application using Docker Compose.

## Prerequisites

- Docker Engine 20.10 or higher
- Docker Compose 2.0 or higher
- At least 4GB of free RAM
- 10GB of free disk space

## Quick Start (5 Minutes)

### 1. Clone or Navigate to the Project Directory

```bash
cd /app/ahs8888-Onprem-Kontakami-AI-cd0e83a
```

### 2. Copy Environment File

```bash
cp .env.docker .env
```

### 3. Generate Application Key

```bash
# This will be done automatically after starting containers
# But you can also generate it manually:
docker compose run --rm app php artisan key:generate
```

### 4. Start All Services

```bash
docker compose up -d
```

This command will:
- Pull necessary Docker images
- Build the application container
- Start MySQL, PHP-FPM, Nginx, Node.js (Vite), and Queue Worker
- Set up networking between containers

### 5. Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

### 6. Access the Application

- **Frontend**: http://localhost:8080
- **Vite Dev Server**: http://localhost:5173
- **MySQL**: localhost:3307
  - Database: `onprem_kontakami`
  - Username: `laravel_user`
  - Password: `laravel_password`

## Service Overview

| Service | Container Name | Port(s) | Purpose |
|---------|---------------|---------|----------|
| MySQL | onprem_mysql | 3307 | Database storage |
| PHP-FPM | onprem_app | 9000 | Laravel application |
| Nginx | onprem_nginx | 8080 | Web server |
| Node.js | onprem_node | 5173 | Vite dev server (hot reload) |
| Queue | onprem_queue | - | Background job processing |

## Common Commands

### Container Management

```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down

# Restart all services
docker compose restart

# View logs (all services)
docker compose logs -f

# View logs (specific service)
docker compose logs -f app
docker compose logs -f queue
docker compose logs -f nginx

# Check service status
docker compose ps
```

### Laravel Commands

```bash
# Run artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:list

# Access Laravel Tinker
docker compose exec app php artisan tinker

# Run tests
docker compose exec app php artisan test
```

### Database Commands

```bash
# Access MySQL CLI
docker compose exec mysql mysql -u laravel_user -p onprem_kontakami
# Password: laravel_password

# Backup database
docker compose exec mysql mysqldump -u laravel_user -p onprem_kontakami > backup.sql

# Restore database
docker compose exec -T mysql mysql -u laravel_user -p onprem_kontakami < backup.sql
```

### Node/NPM Commands

```bash
# Install NPM packages
docker compose exec node npm install

# Build frontend assets
docker compose exec node npm run build

# Run frontend in development mode
docker compose exec node npm run dev
```

## Configuration

### Environment Variables

Edit `.env` file to customize:

```bash
# Gemini AI Key (Required for STT and AI filtering)
GEMINI_KEY="your-actual-gemini-api-key"

# AI Filter Thresholds (Optional)
AI_MIN_CONFIDENCE=0.7
AI_MIN_QUALITY=0.6
AI_ENABLE_PII_DETECTION=true
AI_ENABLE_PII_REDACTION=true

# Encryption Key (32 characters)
ENCRYPTION_KEY="your-32-character-encryption-key"
```

**Important**: After changing `.env`, restart the services:

```bash
docker compose restart app queue
```

### Storage Permissions

If you encounter permission issues:

```bash
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chmod -R 755 /var/www/html/storage
```

## Troubleshooting

### Services Won't Start

1. Check if ports are already in use:
   ```bash
   # Check port 8080 (Nginx)
   lsof -i :8080
   
   # Check port 3307 (MySQL)
   lsof -i :3307
   ```

2. View service logs:
   ```bash
   docker compose logs mysql
   docker compose logs app
   ```

### Database Connection Errors

```bash
# Wait for MySQL to be ready
docker compose exec mysql mysqladmin ping -h localhost -u root -proot_password

# Verify database exists
docker compose exec mysql mysql -u root -proot_password -e "SHOW DATABASES;"
```

### Frontend Not Loading

```bash
# Check Nginx logs
docker compose logs nginx

# Check app logs
docker compose logs app

# Rebuild assets
docker compose exec node npm run build
```

### Queue Not Processing Jobs

```bash
# Check queue worker logs
docker compose logs -f queue

# Restart queue worker
docker compose restart queue

# Check failed jobs
docker compose exec app php artisan queue:failed
```

### Complete Reset

If you want to start fresh:

```bash
# Stop and remove all containers, networks, and volumes
docker compose down -v

# Remove node_modules volume
docker volume rm ahs8888-onprem-kontakami-ai-cd0e83a_node_modules

# Start fresh
docker compose up -d
docker compose exec app php artisan migrate
```

## Development Workflow

### Making Code Changes

1. **Backend (PHP/Laravel)**:
   - Edit files in `app/`, `routes/`, `config/`, etc.
   - Changes are reflected immediately (no restart needed)
   - For config changes: `docker compose exec app php artisan config:clear`

2. **Frontend (Vue/Vite)**:
   - Edit files in `resources/js/`
   - Vite hot reload will update browser automatically
   - Running on http://localhost:5173

3. **Database Changes**:
   - Create migration: `docker compose exec app php artisan make:migration create_table_name`
   - Run migration: `docker compose exec app php artisan migrate`

### Running Tests

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific test file
docker compose exec app php artisan test --filter=RecordingTest

# Run with coverage
docker compose exec app php artisan test --coverage
```

## Production Considerations

⚠️ **This Docker setup is for DEVELOPMENT only**. For production:

1. Use separate `.env.production` file
2. Set `APP_DEBUG=false`
3. Use proper SSL certificates
4. Configure Redis for cache/sessions
5. Use production-grade web server (not Vite dev server)
6. Implement proper backup strategies
7. Set up monitoring and logging

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue 3 Documentation](https://vuejs.org/)

## Support

For issues or questions:
1. Check logs: `docker compose logs -f`
2. Review `MANUAL_TESTING_GUIDE.md` for testing procedures
3. Consult `PHASE_9_SETUP.md` for feature-specific setup
