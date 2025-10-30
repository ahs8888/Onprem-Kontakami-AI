# On-Prem Kontakami AI - QC Scoring System

**Dual QC Scoring Application with AI Micro-Decisions**

This is the **On-Premise** component of a dual-app system for recording management, speech-to-text processing, and AI-driven quality control.

---

## 🚀 Quick Start

Get up and running in **5 minutes**:

```bash
# Run the automated setup script
./quick-start.sh

# Or manual setup:
cp .env.docker .env
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

**Access the application**: http://localhost:8080

📖 **Full setup guide**: [DOCKER_SETUP_README.md](DOCKER_SETUP_README.md)

---

## 🎯 Key Features

### Phase 9: AI Micro-Decisions & Quality Control ✅

- **STT Quality Evaluation**: Automated confidence and quality scoring
- **Selective Upload Logic**: Only high-quality, approved recordings are uploaded to cloud
- **Privacy & PII Detection**: Automatic detection and redaction of sensitive information
- **Encryption & Compression**: AES-256 encryption for secure cloud transfer
- **Telemetry Monitoring**: `/status/local` endpoint for system health
- **Ticket Linking**: Optional CSV/Excel import to link recordings with ticket data

### Core Features

- **Recording Management**: Upload, process, and manage audio recordings
- **Speech-to-Text**: Gemini AI-powered transcription
- **Transcript Cleaning**: Remove filler words, noise tags, normalize text
- **Queue Processing**: Background job system for asynchronous tasks
- **Database**: MySQL with full relational data model

---

## 📋 Documentation

| Document | Purpose |
|----------|---------|
| [DOCKER_SETUP_README.md](DOCKER_SETUP_README.md) | Complete Docker setup and usage guide |
| [MANUAL_TESTING_GUIDE.md](MANUAL_TESTING_GUIDE.md) | Comprehensive testing procedures (10 test suites) |
| [PHASE_9_SETUP.md](PHASE_9_SETUP.md) | Phase 9 feature setup instructions |
| [PHASE_10A_COMPLETE.md](PHASE_10A_COMPLETE.md) | Phase 10A completion summary |
| [HANDOFF.md](HANDOFF.md) | Project context and handoff information |

---

## 🏗️ Architecture

### Tech Stack

- **Backend**: Laravel 12 (PHP 8.2)
- **Frontend**: Vue 3 + Inertia.js + Tailwind CSS
- **Database**: MySQL 8.0
- **Queue**: Laravel Queue (database driver)
- **AI**: Gemini 2.5 Flash (STT & AI filtering)
- **Development**: Docker Compose

### Services (Docker)

| Service | Port | Purpose |
|---------|------|---------|
| Nginx | 8080 | Web server |
| PHP-FPM | 9000 | Laravel application |
| MySQL | 3307 | Database |
| Node.js | 5173 | Vite dev server (hot reload) |
| Queue Worker | - | Background jobs |

---

## 🧪 Testing

### Automated Tests

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific suite
docker compose exec app php artisan test --filter=RecordingUploadTest

# With coverage
docker compose exec app php artisan test --coverage
```

**Test Coverage**:
- ✅ Recording upload and validation
- ✅ Ticket import and linking
- ✅ AI quality evaluation
- ✅ PII detection and redaction
- ✅ Encryption and compression
- ✅ Telemetry monitoring

### Manual Testing

Follow the comprehensive guide: [MANUAL_TESTING_GUIDE.md](MANUAL_TESTING_GUIDE.md)

**10 Test Suites** covering:
1. Basic setup
2. Recording upload & STT
3. Ticket linking
4. AI quality evaluation
5. PII detection & privacy
6. Selective upload logic
7. Encryption & compression
8. Telemetry monitoring
9. Queue processing
10. End-to-end workflow

---

## 🔧 Configuration

### Required Environment Variables

Edit `.env` and configure:

```bash
# Gemini AI (Required)
GEMINI_KEY="your-gemini-api-key-here"

# Encryption (Required)
ENCRYPTION_KEY="your-32-character-encryption-key"

# AI Filter Thresholds (Optional)
AI_MIN_CONFIDENCE=0.7
AI_MIN_QUALITY=0.6
AI_ENABLE_PII_DETECTION=true
AI_ENABLE_PII_REDACTION=true
```

**Restart services after changes**:
```bash
docker compose restart app queue
```

---

## 📊 Project Status

### Completed Phases

- ✅ **Phase 1**: Database migration (MongoDB → MySQL)
- ✅ **Phase 5**: Ticket linking system
- ✅ **Phase 6**: Frontend enhancements
- ✅ **Phase 7**: CSV/Excel import wizard
- ✅ **Phase 8**: Modular API routing
- ✅ **Phase 9**: AI micro-decisions & quality control
- ✅ **Phase 10A**: Docker Compose setup & testing infrastructure

### Current Status

**On-Prem App**: ~95% complete
- ✅ All features implemented
- ⏳ Testing in progress (Docker environment ready)
- ⏳ Bug fixes pending test results

**Cloud App**: Not yet enhanced
- ⏳ Pending Phase 10B (awaiting on-prem test completion)

---

## 🛠️ Development

### Useful Commands

```bash
# Container management
docker compose up -d          # Start all services
docker compose down           # Stop all services
docker compose restart        # Restart all services
docker compose logs -f        # View logs (all services)
docker compose logs -f queue  # View queue worker logs

# Laravel commands
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
docker compose exec app php artisan queue:work
docker compose exec app php artisan cache:clear

# Database
docker compose exec mysql mysql -u laravel_user -p onprem_kontakami
# Password: laravel_password

# Frontend
docker compose exec node npm install
docker compose exec node npm run build
```

### Hot Reload

- **Backend**: Changes reflected immediately (no restart needed)
- **Frontend**: Vite hot reload enabled (automatic browser refresh)
- **Config**: Requires cache clear: `docker compose exec app php artisan config:clear`

---

## 🔐 Security Notes

⚠️ **Development Environment**: This Docker setup is for **development and testing only**.

For production deployment:
- Use separate `.env.production` file
- Set `APP_DEBUG=false`
- Configure SSL/TLS certificates
- Implement proper backup strategies
- Use Redis for cache/sessions
- Set up monitoring and alerting
- Review and harden security settings

---

## 🤝 Contributing

### Before Making Changes

1. Pull latest code
2. Start Docker environment
3. Run existing tests to ensure they pass
4. Make your changes
5. Write tests for new features
6. Run tests again
7. Document changes in relevant PHASE_X files

---

## 📞 Support

### Common Issues

**Issue**: Services won't start
```bash
# Check logs
docker compose logs

# Verify ports are available
lsof -i :8080
lsof -i :3307
```

**Issue**: Database connection errors
```bash
# Wait for MySQL to be ready
docker compose exec mysql mysqladmin ping -h localhost -u root -proot_password

# Restart services
docker compose restart app
```

**Issue**: Frontend not loading
```bash
# Check Nginx logs
docker compose logs nginx

# Rebuild assets
docker compose exec node npm run build
```

For more troubleshooting: [DOCKER_SETUP_README.md](DOCKER_SETUP_README.md)

---

## 📝 License

MIT

---

## 🗺️ Roadmap

### Immediate Next Steps

1. ⏳ Complete testing (automated + manual)
2. ⏳ Fix bugs found during testing
3. ⏳ Phase 10B: Cloud app enhancement
4. ⏳ End-to-end integration testing
5. ⏳ Monorepo organization
6. ⏳ GitHub push

### Future Enhancements

- Cloud app decryption service
- Real-time telemetry dashboard
- Advanced AI quality metrics
- Multi-language STT support
- Enhanced PII detection (more types)
- Webhook notifications for cloud transfer
- API rate limiting and throttling
- Admin dashboard for system monitoring

---

**Last Updated**: January 29, 2025

**Current Phase**: Phase 10A - Testing Ready 🧪
