# QC Scoring System - Monorepo

AI-powered Quality Control Scoring System with on-premises recording management and cloud-based analysis.

## 🏗️ Architecture

This monorepo contains two separate applications:

### **On-Premises App** (`onprem-app/`)
- **Purpose:** Recording upload, Speech-to-Text processing, ticket linking
- **Tech Stack:** Laravel 12 + Vue 3 + MySQL + Gemini STT
- **Location:** Customer premises (audio files never leave)
- **Database:** MySQL

### **Cloud App** (`cloud-app/`)
- **Purpose:** Text analysis, QC scoring, insights, AI decision-making
- **Tech Stack:** Laravel 12 + Vue 3 + MySQL
- **Location:** Cloud server
- **Database:** MySQL (migrated from SQLite)

---

## 📊 Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                         ON-PREMISES                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Upload Recording (folder)                                   │
│     ↓                                                            │
│  2. Speech-to-Text (Gemini)                                     │
│     ↓                                                            │
│  3. Link to Ticket (CSV import)                                 │
│     ↓                                                            │
│  4. Send to Cloud                                               │
│     ↓                                                            │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             │ HTTPS API
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                            CLOUD                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  5. Receive Transcript + Ticket Info                            │
│     ↓                                                            │
│  6. AI Analysis (OpenAI/Gemini)                                 │
│     ↓                                                            │
│  7. Generate Insights & Correlations                            │
│     ↓                                                            │
│  8. Decision Layer (Recommendations)                            │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🚀 Quick Start

### **Prerequisites**
- PHP 8.2+
- Composer
- Node.js 18+ & Yarn
- MySQL 8.0+
- Gemini API Key (for STT)

### **Setup On-Prem App**

```bash
cd onprem-app

# Install dependencies
composer install
yarn install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database
# Edit .env with your MySQL credentials

# Run migrations
php artisan migrate

# Build frontend
yarn build

# Start development
php artisan serve
```

### **Setup Cloud App**

```bash
cd cloud-app

# Install dependencies
composer install
yarn install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database
# Edit .env with your MySQL credentials

# Run migrations
php artisan migrate

# Build frontend
yarn build

# Start development
php artisan serve
```

---

## 📁 Project Structure

```
qc-scoring-system/
├── README.md                      # This file
├── .gitignore                     # Ignore patterns
│
├── onprem-app/                    # On-Premises Application
│   ├── app/                       # Laravel application code
│   │   ├── Actions/               # Business logic actions
│   │   ├── Http/Controllers/      # Controllers
│   │   ├── Models/                # Eloquent models
│   │   ├── Services/              # Service layer
│   │   └── Jobs/                  # Background jobs
│   ├── database/                  # Migrations & seeders
│   │   └── migrations/            
│   ├── routes/                    # Route definitions
│   │   ├── web.php
│   │   └── admin/                 # Modular admin routes
│   │       ├── recording.php
│   │       └── ticket.php
│   ├── resources/                 # Frontend assets
│   │   └── js/
│   │       ├── Pages/             # Vue pages
│   │       └── Components/        # Vue components
│   ├── composer.json              # PHP dependencies
│   ├── package.json               # Node dependencies
│   └── README.md                  # On-prem specific docs
│
├── cloud-app/                     # Cloud Application
│   ├── app/                       # Laravel application code
│   ├── database/                  # Migrations & seeders
│   ├── routes/                    # Route definitions
│   ├── resources/                 # Frontend assets
│   ├── composer.json              # PHP dependencies
│   ├── package.json               # Node dependencies
│   └── README.md                  # Cloud specific docs
│
└── docs/                          # Shared Documentation
    ├── architecture.md            # System architecture
    ├── api-contract.md            # API between on-prem & cloud
    ├── deployment.md              # Deployment guide
    └── enhancement-phases.md      # Enhancement roadmap
```

---

## 🔗 API Integration

### **On-Prem → Cloud Communication**

**Endpoint:** `POST /api/external/v1/recording`

**Authentication:** Bearer Token (configured in on-prem settings)

**Payload:**
```json
{
  "folder": "recording_batch_001",
  "files": [
    {
      "filename": "TKT-12345 - John Doe - Loan Inquiry",
      "transcribe": "Agent: Hello...",
      "token": 1500,
      "size": "2.5MB",
      "ticket_id": "TKT-12345",
      "customer_name": "John Doe",
      "agent_name": "Agent Smith",
      "call_intent": "Loan Inquiry",
      "call_outcome": "Approved"
    }
  ]
}
```

---

## 🎯 Enhancement Status

### **On-Prem App**
- ✅ Phase 1: Database Schema (9% complete)
- 🔄 Phase 2: Model & Services (In progress)
- ⏳ Phase 3: Folder Upload Enhancement
- ⏳ Phase 4: CSV Import Backend
- ⏳ Phase 5: CSV Import Frontend
- ⏳ Phase 6: Cloud Transfer Update
- ⏳ Phase 7: UI Polish
- ⏳ Phase 8: Testing

### **Cloud App**
- ⏳ Database Migration (SQLite → MySQL)
- ⏳ Receive Ticket Info Enhancement
- ⏳ AI Provider Selection (OpenAI + Gemini)
- ⏳ Analysis Layer Enhancement
- ⏳ Decision Layer Implementation

---

## 📚 Documentation

See `docs/` folder for detailed documentation:

- **Architecture:** System design and data flow
- **API Contract:** Communication between apps
- **Deployment:** Production deployment guide
- **Enhancement Phases:** Detailed roadmap

---

## 🛠️ Development

### **Running Both Apps Simultaneously**

**Terminal 1 - On-Prem:**
```bash
cd onprem-app
php artisan serve --port=8000
```

**Terminal 2 - On-Prem Frontend:**
```bash
cd onprem-app
yarn dev
```

**Terminal 3 - Cloud:**
```bash
cd cloud-app
php artisan serve --port=8001
```

**Terminal 4 - Cloud Frontend:**
```bash
cd cloud-app
yarn dev
```

---

## 🧪 Testing

### **On-Prem Tests**
```bash
cd onprem-app
php artisan test
```

### **Cloud Tests**
```bash
cd cloud-app
php artisan test
```

---

## 📦 Deployment

See `docs/deployment.md` for production deployment instructions.

---

## 🤝 Contributing

This is a private monorepo. Contact the development team for contribution guidelines.

---

## 📄 License

Proprietary - All rights reserved

---

## 🆘 Support

For issues or questions, contact the development team.

---

**Last Updated:** October 29, 2025
**Version:** 1.0.0-alpha
**Status:** Under Active Development
