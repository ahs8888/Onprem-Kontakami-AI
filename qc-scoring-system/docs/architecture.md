# System Architecture

## Overview

The QC Scoring System uses a two-tier architecture with on-premises recording management and cloud-based AI analysis.

## Architecture Diagram

```
┌───────────────────────────────────────────────────────────────────┐
│                     ON-PREMISES ENVIRONMENT                       │
│  (Customer Location - Audio Never Leaves)                         │
└───────────────────────────────────────────────────────────────────┘
                                │
                    ┌───────────┴───────────┐
                    │                       │
            ┌───────▼────────┐      ┌──────▼──────┐
            │  Call Center   │      │   PBX/VoIP  │
            │    Agents      │      │   System    │
            └───────┬────────┘      └──────┬──────┘
                    │                      │
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │  Recording Storage   │
                    │  (Local File System) │
                    └──────────┬───────────┘
                               │
                    ┌──────────▼────────────┐
                    │   ON-PREM APP         │
                    │   (Laravel + Vue)     │
                    ├───────────────────────┤
                    │ • Upload Recordings   │
                    │ • STT (Gemini)        │
                    │ • Ticket Linking      │
                    │ • Metadata Extract    │
                    └──────────┬────────────┘
                               │
                               │ HTTPS API
                               │ (Transcript + Metadata)
                               │
┌──────────────────────────────▼────────────────────────────────────┐
│                        CLOUD ENVIRONMENT                           │
│  (SaaS Platform - Analysis & Insights)                            │
└────────────────────────────────────────────────────────────────────┘
                               │
                    ┌──────────▼────────────┐
                    │    CLOUD APP          │
                    │   (Laravel + Vue)     │
                    ├───────────────────────┤
                    │ • Receive Transcripts │
                    │ • AI Analysis         │
                    │ • QC Scoring          │
                    │ • Insights Generation │
                    │ • Recommendations     │
                    └──────────┬────────────┘
                               │
                ┌──────────────┼──────────────┐
                │              │              │
        ┌───────▼──────┐ ┌────▼─────┐ ┌─────▼──────┐
        │  MySQL DB    │ │ OpenAI/  │ │  Vector    │
        │  (Analysis)  │ │ Gemini   │ │  Store     │
        └──────────────┘ └──────────┘ └────────────┘
```

## Three-Layer AI Architecture

### **Layer 1: Information Layer**

**On-Prem Tasks:**
- Speech-to-Text conversion (Gemini 2.5-flash)
- Basic metadata extraction (call_id, timestamp, agent_id)
- Optional PII anonymization
- Ticket linking (CSV import)

**Cloud Tasks:**
- Receive transcripts and metadata
- Enrich with contextual tags (intent, loan stage, product type)
- Run sentiment and tone models
- Store in MySQL with proper indexing

**Data Exchange:**
```json
{
  "call_id": "uuid",
  "ticket_id": "TKT-12345",
  "agent_id": "agent_001",
  "customer_id": "cust_456",
  "transcript_text": "...",
  "metadata": {
    "duration": "00:15:30",
    "date": "2025-10-29",
    "customer_name": "John Doe",
    "call_intent": "Loan Inquiry"
  }
}
```

---

### **Layer 2: Analysis Layer**

**On-Prem Tasks:**
- None (all analysis in cloud)

**Cloud Tasks:**
- Read from Information Layer tables
- Run correlation models:
  - Sentiment vs call outcome
  - Agent performance vs success rate
  - Time-of-day patterns
  - Product type correlations
- Generate insight and pattern tables
- Feed embedding vectors to vector store
- Semantic similarity search

**Data Exchange:**
```json
{
  "analysis_id": "uuid",
  "recording_id": "uuid",
  "correlations": [
    {
      "type": "sentiment_outcome",
      "correlation": 0.78,
      "insight": "Positive sentiment correlates with successful outcomes"
    }
  ],
  "patterns": [
    {
      "type": "peak_hours",
      "data": "Most successful calls happen 10am-12pm"
    }
  ]
}
```

---

### **Layer 3: Decision Layer**

**On-Prem Tasks:**
- Optional: Edge micro-decisions (real-time agent assist)

**Cloud Tasks:**
- Generate recommended actions
- Auto-score and reprioritize calls/agents
- Send decisions via API to dashboards
- Trigger alerts for quality issues
- Coach recommendations

**Data Exchange:**
```json
{
  "decision_id": "uuid",
  "call_id": "uuid",
  "recommended_action": "Follow-up required within 24h",
  "confidence": 0.92,
  "priority": "high",
  "coaching_points": [
    "Improve active listening",
    "Use customer name more frequently"
  ]
}
```

---

## Technology Stack

### **On-Prem App**
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue 3 + TypeScript + Inertia.js
- **Database:** MySQL 8.0+
- **Styling:** Tailwind CSS 4
- **STT:** Google Gemini 2.5-flash
- **Queue:** Laravel Queue (Database driver)

### **Cloud App**
- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Vue 3 + TypeScript + Inertia.js
- **Database:** MySQL 8.0+ (migrating from SQLite)
- **AI:** OpenAI GPT-4o / Gemini 2.0 (dynamic selection)
- **Vector Store:** MySQL Vector Search
- **Queue:** Laravel Queue
- **Real-time:** Socket.io

---

## Security Considerations

### **Data Privacy**
- Audio files never leave customer premises
- Only text transcripts sent to cloud
- Optional PII anonymization before cloud transfer
- Encrypted API communication (HTTPS)
- Token-based authentication

### **Authentication**
- On-Prem: Laravel Sanctum (session-based)
- Cloud: Laravel Sanctum (API tokens)
- API: Bearer token authentication

---

## Scalability

### **On-Prem**
- Horizontal: Multiple on-prem instances (different locations)
- Vertical: Queue workers for STT processing
- Storage: Local file system (customer-managed)

### **Cloud**
- Horizontal: Load balancer + multiple app servers
- Vertical: Queue workers for analysis
- Database: Read replicas for reporting
- Caching: Redis for frequently accessed data

---

## Deployment

### **On-Prem**
- Deploy at customer premises
- Docker container or VM
- Customer manages infrastructure
- Auto-update capability (optional)

### **Cloud**
- SaaS platform (AWS/GCP/Azure)
- Kubernetes deployment
- Auto-scaling based on load
- Multi-region support

---

## Monitoring

### **On-Prem**
- Laravel logs
- Queue monitoring
- STT API usage tracking
- Local health checks

### **Cloud**
- Application Performance Monitoring (APM)
- Error tracking (Sentry/Bugsnag)
- Database query performance
- AI API usage & costs
- Real-time dashboards

---

**Last Updated:** October 29, 2025
