# API Contract: On-Prem ↔ Cloud

## Overview

This document defines the API contract between the on-premises application and cloud application.

---

## Authentication

**Method:** Bearer Token

**Header:**
```
Authorization: Bearer {access_token}
```

**Token Location:**
- On-Prem: Configured in Settings table
- Cloud: Validates incoming requests

---

## 1. Send Recording to Cloud

**Endpoint:** `POST /api/external/v1/recording`

**Direction:** On-Prem → Cloud

**Purpose:** Transfer STT transcript and metadata to cloud for analysis

### Request

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {access_token}
```

**Body:**
```json
{
  "folder": "recording_batch_20251029_001",
  "files": [
    {
      "filename": "call_001.mp3",
      "transcribe": "Agent: Hello, how can I help you today?\nCustomer: I need information about loan...",
      "token": 1500,
      "size": "2.5MB",
      
      // Ticket information (if linked)
      "ticket_id": "TKT-12345",
      "ticket_url": "https://tickets.example.com/TKT-12345",
      "customer_name": "John Doe",
      "agent_name": "Agent Smith",
      "call_intent": "Loan Inquiry",
      "call_outcome": "Approved",
      "display_name": "TKT-12345 - John Doe - Loan Inquiry - 2025-10-29 14:30"
    }
  ]
}
```

### Response

**Success (200 OK):**
```json
{
  "success": true,
  "message": "Recording received successfully",
  "recording_uuid": "550e8400-e29b-41d4-a716-446655440000",
  "files_count": 1
}
```

**Error (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "folder": ["The folder field is required"],
    "files": ["The files field must be an array"]
  }
}
```

**Error (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid access token"
}
```

---

## 2. Update Recording Ticket Info (Retroactive)

**Endpoint:** `PATCH /api/external/v1/recording-file/{recording_id}`

**Direction:** On-Prem → Cloud

**Purpose:** Update ticket information for recording already sent to cloud

### Request

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {access_token}
```

**Body:**
```json
{
  "ticket_id": "TKT-12345",
  "ticket_url": "https://tickets.example.com/TKT-12345",
  "customer_name": "John Doe",
  "agent_name": "Agent Smith",
  "call_intent": "Loan Inquiry",
  "call_outcome": "Approved",
  "display_name": "TKT-12345 - John Doe - Loan Inquiry"
}
```

### Response

**Success (200 OK):**
```json
{
  "success": true,
  "message": "Ticket information updated successfully",
  "recording_file_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

**Error (404 Not Found):**
```json
{
  "success": false,
  "message": "Recording file not found"
}
```

---

## 3. Get Recording Status (Optional)

**Endpoint:** `GET /api/external/v1/recording/{recording_uuid}/status`

**Direction:** On-Prem → Cloud

**Purpose:** Check analysis status of recording in cloud

### Request

**Headers:**
```
Authorization: Bearer {access_token}
```

### Response

**Success (200 OK):**
```json
{
  "success": true,
  "recording_uuid": "550e8400-e29b-41d4-a716-446655440000",
  "status": "analyzed",
  "progress": 100,
  "analysis_complete": true,
  "insights_generated": true,
  "last_updated": "2025-10-29T15:30:00Z"
}
```

---

## 4. Health Check

**Endpoint:** `GET /api/external/v1/health`

**Direction:** On-Prem → Cloud

**Purpose:** Verify cloud API is reachable

### Request

**Headers:**
```
Authorization: Bearer {access_token}
```

### Response

**Success (200 OK):**
```json
{
  "status": "ok",
  "version": "1.0.0",
  "timestamp": "2025-10-29T15:30:00Z"
}
```

---

## Data Field Specifications

### Recording File Object

| Field | Type | Required | Max Length | Description |
|-------|------|----------|------------|-------------|
| `filename` | string | Yes | 255 | Original filename |
| `transcribe` | text | Yes | - | Full transcript text |
| `token` | integer | Yes | - | STT token usage |
| `size` | string | Yes | 50 | File size (e.g., "2.5MB") |
| `ticket_id` | string | No | 100 | Ticket system ID |
| `ticket_url` | string | No | - | URL to ticket |
| `customer_name` | string | No | 255 | Customer name |
| `agent_name` | string | No | 255 | Agent name |
| `call_intent` | string | No | 100 | Purpose of call |
| `call_outcome` | string | No | 100 | Result of call |
| `display_name` | string | No | 500 | Human-readable name |

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request - Invalid payload |
| 401 | Unauthorized - Invalid/missing token |
| 404 | Not Found - Resource doesn't exist |
| 422 | Unprocessable Entity - Validation failed |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error |
| 503 | Service Unavailable - Cloud maintenance |

---

## Rate Limiting

**Limit:** 100 requests per minute per access token

**Response when exceeded:**
```json
{
  "success": false,
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

---

## Security Best Practices

1. **Token Storage:** Store access token securely (encrypted in database)
2. **Token Rotation:** Implement token rotation every 90 days
3. **HTTPS Only:** Never use HTTP in production
4. **Payload Validation:** Validate all incoming data
5. **Logging:** Log all API calls (without sensitive data)
6. **Timeout:** Set reasonable timeout (30 seconds recommended)

---

## Implementation Example (On-Prem → Cloud)

### Laravel HTTP Client (On-Prem)

```php
use Illuminate\Support\Facades\Http;

// Get cloud credentials from settings
$cloudUrl = config('services.clouds_url');
$accessToken = Setting::where('key', 'token')->value('value');

// Send recording to cloud
$response = Http::withToken($accessToken)
    ->timeout(30)
    ->post("{$cloudUrl}/api/external/v1/recording", [
        'folder' => $recording->folder_name,
        'files' => $files->map(function ($file) {
            return [
                'filename' => $file->name,
                'transcribe' => $file->transcript,
                'token' => $file->token,
                'size' => $file->size,
                'ticket_id' => $file->ticket_id,
                'ticket_url' => $file->ticket_url,
                'customer_name' => $file->customer_name,
                'agent_name' => $file->agent_name,
                'call_intent' => $file->call_intent,
                'call_outcome' => $file->call_outcome,
                'display_name' => $file->display_name,
            ];
        })->toArray()
    ]);

if ($response->successful()) {
    $data = $response->json();
    $recording->update(['clouds_uuid' => $data['recording_uuid']]);
} else {
    throw new \Exception("Cloud API error: " . $response->body());
}
```

---

## Versioning

**Current Version:** v1

**Version Header (optional):**
```
X-API-Version: v1
```

Future versions will maintain backward compatibility or provide migration path.

---

## Testing

### Test Endpoint

**Endpoint:** `POST /api/external/v1/recording/test`

**Purpose:** Test integration without saving data

Returns same response structure but doesn't persist data.

---

**Last Updated:** October 29, 2025
