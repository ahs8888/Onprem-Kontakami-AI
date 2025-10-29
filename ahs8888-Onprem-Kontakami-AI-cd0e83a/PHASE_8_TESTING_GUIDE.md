# Phase 8: Comprehensive Testing & Validation Guide

## Overview

This document provides a complete testing plan for the QC Scoring Application's ticket linking system. It covers setup verification, functional testing, edge cases, and acceptance criteria.

## Prerequisites

### Environment Setup Checklist

Before testing, ensure the following are completed:

```bash
# 1. Environment file
[ ] .env file exists and configured
[ ] Database credentials correct
[ ] CLOUDS_URL set correctly
[ ] APP_KEY generated

# 2. Dependencies installed
[ ] composer install completed
[ ] npm install / yarn install completed
[ ] phpoffice/phpspreadsheet present in vendor/

# 3. Database setup
[ ] Migrations run successfully
[ ] recording_details table has ticket fields
[ ] status enum includes: unlinked, linked, no_ticket_needed

# 4. Services running
[ ] Laravel dev server running (php artisan serve)
[ ] Vite dev server running (npm run dev)
[ ] Queue worker running (php artisan queue:work)

# 5. Permissions
[ ] storage/ writable
[ ] storage/logs/ writable
[ ] storage/app/public/ writable
```

### Setup Verification Script

Run this to verify your environment:

```bash
# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found"
else
    echo "✅ .env file exists"
fi

# Check if vendor directory exists
if [ ! -d vendor ]; then
    echo "❌ Composer dependencies not installed"
else
    echo "✅ Composer dependencies installed"
fi

# Check if node_modules exists
if [ ! -d node_modules ]; then
    echo "❌ NPM dependencies not installed"
else
    echo "✅ NPM dependencies installed"
fi

# Check if PhpSpreadsheet is installed
if [ -d vendor/phpoffice/phpspreadsheet ]; then
    echo "✅ PhpSpreadsheet installed"
else
    echo "❌ PhpSpreadsheet not installed"
fi

# Check database connection
php artisan db:show 2>/dev/null && echo "✅ Database connected" || echo "❌ Database connection failed"

# Check migrations
php artisan migrate:status | grep "add_ticket_linking" && echo "✅ Ticket linking migration present" || echo "❌ Migration missing"
```

## Test Data

Sample CSV files are provided in `/test-data/`:

1. **sample_tickets_valid.csv** - Valid data with all fields
2. **sample_tickets_different_headers.csv** - Different column names (tests auto-detection)
3. **sample_tickets_minimal.csv** - Only required fields
4. **sample_tickets_no_matches.csv** - Recordings not in database
5. **sample_tickets_invalid.csv** - Missing required fields

## Testing Phases

### Phase 8.1: Backend Unit Tests

#### Test 1: TicketLinkingService

```php
// Test display name generation
$service = new TicketLinkingService();

// Test case 1: Full data
$recording = RecordingDetail::factory()->create(['name' => 'recording_001.wav']);
$ticketData = [
    'ticket_id' => 'TKT-12345',
    'customer_name' => 'John Doe'
];
$displayName = $service->generateDisplayName($recording, $ticketData);
// Expected: JohnDoe_TKT12345_20250129 (or similar with date)

// Test case 2: Missing customer name
$ticketData = ['ticket_id' => 'TKT-12345'];
$displayName = $service->generateDisplayName($recording, $ticketData);
// Expected: TKT12345_20250129

// Test case 3: Special characters in name
$ticketData = [
    'ticket_id' => 'TKT-12345',
    'customer_name' => 'O\'Brien & Sons, Inc.'
];
$displayName = $service->generateDisplayName($recording, $ticketData);
// Expected: OBrienSonsInc_TKT12345_20250129
```

**Validation Points:**
- [ ] Display name sanitizes special characters
- [ ] Display name includes date when available
- [ ] Fallback to original filename if no data
- [ ] Underscores used as separators
- [ ] No double underscores

#### Test 2: CSV Parsing

```php
$service = new TicketLinkingService();

// Test valid CSV
$result = $service->parseCSV('/path/to/sample_tickets_valid.csv');
// Expected: success=true, headers array, rows array

// Test invalid file
$result = $service->parseCSV('/path/to/nonexistent.csv');
// Expected: success=false, error message

// Test large CSV (1000+ rows)
// Expected: Only first 1000 rows returned
```

**Validation Points:**
- [ ] Headers extracted correctly
- [ ] Rows parsed as associative arrays
- [ ] Limit of 1000 rows enforced
- [ ] Error handling for invalid files
- [ ] CSV with quotes handled correctly

#### Test 3: Validation Logic

```php
// Test matching recordings
$csvData = [
    ['recording_001.wav', 'TKT-12345', 'John Doe'],
    // ... more rows
];
$columnMapping = [
    'recording_name' => 'recording_001.wav',
    'ticket_id' => 'TKT-12345',
    'customer_name' => 'John Doe'
];
$results = $service->validateTicketData($csvData, $columnMapping);

// Check results
foreach ($results as $result) {
    // Should have: recording_name, ticket_id, status, message
}
```

**Validation Points:**
- [ ] Exact filename match works
- [ ] Fuzzy match without extension works
- [ ] Missing required fields marked as 'failed'
- [ ] Recordings not in DB marked as 'unmatched'
- [ ] Valid matches marked as 'matched'
- [ ] recording_id included for matched records

#### Test 4: Bulk Linking

```php
$validatedRecords = [
    [
        'status' => 'matched',
        'recording_id' => '123',
        'ticket_id' => 'TKT-12345',
        'customer_name' => 'John Doe',
        // ... other fields
    ],
    // ... more records
];

$result = $service->bulkLinkFromCSV($validatedRecords);
```

**Validation Points:**
- [ ] Linked count accurate
- [ ] Failed count accurate
- [ ] Errors array populated for failures
- [ ] Database updated correctly
- [ ] Cloud update jobs dispatched (if transfer_cloud=1)

### Phase 8.2: Cloud Transfer Tests

#### Test 5: CloudTransferService

```php
$cloudService = new CloudTransferService();

// Test with ticket info
$recording = RecordingDetail::create([
    'name' => 'recording_001.wav',
    'ticket_id' => 'TKT-12345',
    'customer_name' => 'John Doe',
    // ... other fields
]);

$data = $cloudService->prepareRecordingData($recording, 1234);
```

**Validation Points:**
- [ ] Basic fields included (filename, size, token, transcribe)
- [ ] ticket_info object included when ticket_id exists
- [ ] ticket_info has all expected fields
- [ ] ticket_info is null/absent when no ticket data
- [ ] File size calculated correctly
- [ ] formatBytes helper works

#### Test 6: UpdateCloudTicketInfo Job

```bash
# Dispatch job manually
php artisan tinker
>>> $recordingId = 'your-recording-id';
>>> UpdateCloudTicketInfo::dispatch($recordingId);
>>> exit

# Check logs
tail -f storage/logs/laravel.log

# Expected log entries:
# - "UpdateCloudTicketInfo: Preparing to update cloud"
# - HTTP request to cloud API
# - Success or error message
```

**Validation Points:**
- [ ] Job queues successfully
- [ ] Job processes without errors
- [ ] HTTP request sent to correct endpoint
- [ ] Request body includes ticket_info
- [ ] Success logged on 200 response
- [ ] Error logged on non-200 response

### Phase 8.3: Frontend UI Tests

#### Test 7: Upload Flow

**Manual Test Steps:**
1. Navigate to Recordings page
2. Click "New Recording" button
3. **Verify:** Modal appears
4. **Verify:** "Requires Ticket" checkbox is checked by default
5. **Verify:** Explanation text is clear
6. Uncheck "Requires Ticket"
7. Click "Continue"
8. Select folder with audio files
9. **Verify:** Upload progress shows
10. Wait for completion
11. Check database: `requires_ticket` should be 0
12. Check database: `status` should be 'no_ticket_needed'

**Repeat with checkbox checked:**
11. Check database: `requires_ticket` should be 1
12. Check database: `status` should be 'unlinked'

**Validation Points:**
- [ ] Modal appears and functions correctly
- [ ] Checkbox state changes
- [ ] Cancel button works
- [ ] requiresTicket parameter sent to backend
- [ ] Database records created with correct values
- [ ] Upload progress accurate

#### Test 8: Statistics Dashboard

**Manual Test Steps:**
1. Navigate to Recordings page
2. **Verify:** Three stat cards appear at top
3. **Verify:** "Total Recordings" shows correct count
4. **Verify:** "Linked to Tickets" shows count and percentage
5. **Verify:** "Needs Linking" shows unlinked count
6. Upload some recordings
7. **Verify:** Total count increases
8. Import tickets for some recordings
9. **Verify:** Linked count increases
10. **Verify:** Unlinked count decreases
11. **Verify:** Percentage updates correctly

**Validation Points:**
- [ ] All three cards display
- [ ] Icons show correctly
- [ ] Colors appropriate (blue/green/yellow)
- [ ] Links work
- [ ] Counts accurate
- [ ] Percentage calculation correct
- [ ] Responsive on mobile

#### Test 9: Ticket Import Wizard - Step 1 (Upload)

**Manual Test Steps:**
1. Navigate to Ticket Import page
2. **Verify:** "How it works" section displays
3. Expand "View Sample CSV Format"
4. **Verify:** Sample shows correct format
5. **Verify:** Required/optional fields indicated
6. Drag valid CSV file to upload zone
7. **Verify:** File name and size display
8. **Verify:** Remove file button works
9. Click "Next: Map Columns"
10. **Verify:** Progress to Step 2

**Test with invalid file:**
- Try uploading .txt file
- **Verify:** Error message shown

**Validation Points:**
- [ ] Help section readable and helpful
- [ ] Sample CSV format correct
- [ ] Drag and drop works
- [ ] Click to browse works
- [ ] File validation works
- [ ] File size display correct
- [ ] Remove file works
- [ ] Can't proceed without file

#### Test 10: Ticket Import Wizard - Step 2 (Mapping)

**Manual Test Steps:**
1. Upload `sample_tickets_different_headers.csv`
2. **Verify:** Auto-detection maps columns
3. **Verify:** "filename" → "recording_name"
4. **Verify:** "case_id" → "ticket_id"
5. **Verify:** Preview values show for each field
6. Change a mapping manually
7. **Verify:** Preview updates
8. Try to proceed without mapping required field
9. **Verify:** Button disabled
10. Map all required fields
11. **Verify:** Button enabled
12. Click "Next: Validate"

**Validation Points:**
- [ ] Auto-detection works
- [ ] All CSV headers available in dropdowns
- [ ] Preview shows actual data
- [ ] Required fields marked with *
- [ ] Validation prevents missing required fields
- [ ] Back button works

#### Test 11: Ticket Import Wizard - Step 3 (Validation)

**Manual Test Steps:**
1. Upload valid CSV with mix of matches/non-matches
2. Map columns
3. Proceed to validation
4. **Verify:** Three summary cards show counts
5. **Verify:** Green card: matched count
6. **Verify:** Yellow card: unmatched count
7. **Verify:** Red card: failed count
8. Click each tab
9. **Verify:** Correct records in each tab
10. **Verify:** Status badges color-coded
11. **Verify:** Messages explain status
12. **Verify:** "Ready to import X recordings" shows
13. Click "Import"

**Test all-unmatched scenario:**
- Upload `sample_tickets_no_matches.csv`
- **Verify:** Warning message shows
- **Verify:** Import button disabled

**Validation Points:**
- [ ] Summary cards accurate
- [ ] Tabs work correctly
- [ ] Tables display all fields
- [ ] Status badges correct
- [ ] Messages helpful
- [ ] Warning shows when needed
- [ ] Can't import if no matches
- [ ] Back button works

#### Test 12: Ticket Import Wizard - Step 4 (Complete)

**Manual Test Steps:**
1. Complete import
2. **Verify:** Success icon shows
3. **Verify:** "Import Complete!" message
4. **Verify:** Linked count shows
5. **Verify:** Failed count shows (if any)
6. **Verify:** Error details expandable (if any)
7. Click "View Recordings"
8. **Verify:** Redirects to recordings page
9. **Verify:** Statistics updated

**Or click "Import More":**
8. **Verify:** Returns to Step 1
9. **Verify:** Form reset

**Validation Points:**
- [ ] Success state displays correctly
- [ ] Counts accurate
- [ ] Error details available
- [ ] Links work
- [ ] Can start new import
- [ ] Database updated

### Phase 8.4: Edge Cases & Error Scenarios

#### Test 13: Large File Handling

```bash
# Create large CSV (5000 rows)
for i in {1..5000}; do
    echo "recording_$i.wav,TKT-$i,Customer $i" >> large_test.csv
done
```

**Test Steps:**
1. Upload large_test.csv
2. **Verify:** Only 1000 rows loaded
3. **Verify:** Warning message shown
4. **Verify:** No performance issues

**Validation Points:**
- [ ] 1000 row limit enforced
- [ ] User notified of limit
- [ ] No timeout or crash
- [ ] Memory usage reasonable

#### Test 14: Special Characters

Create test CSV with:
- Customer names: `O'Brien`, `José García`, `测试用户`
- Filenames: `recording (1).wav`, `recording-2.wav`, `recording_3.wav`
- Ticket IDs: `TKT-123`, `CASE#456`, `INC_789`

**Validation Points:**
- [ ] Special characters handled
- [ ] Display names sanitized correctly
- [ ] No SQL errors
- [ ] Unicode support works

#### Test 15: Concurrent Operations

**Test Steps:**
1. Start uploading a large folder
2. While uploading, try to import tickets
3. **Verify:** Both operations work
4. **Verify:** No race conditions
5. **Verify:** Queue handles jobs correctly

**Validation Points:**
- [ ] Concurrent uploads work
- [ ] Concurrent imports work
- [ ] No database locks
- [ ] Queue processes correctly

#### Test 16: Network Failures

**Simulate cloud API down:**
1. Stop cloud API (or block in firewall)
2. Link recordings that are already in cloud
3. **Verify:** UpdateCloudTicketInfo job fails gracefully
4. **Verify:** Error logged
5. **Verify:** Job retryable
6. Restore cloud API
7. **Verify:** Job retries and succeeds

**Validation Points:**
- [ ] Network errors caught
- [ ] Jobs don't crash
- [ ] Errors logged
- [ ] Retry mechanism works

#### Test 17: Duplicate Prevention

**Test Steps:**
1. Import tickets for recordings
2. Import same CSV again
3. **Verify:** Updates existing links (doesn't duplicate)
4. **Verify:** linked_at timestamp updates
5. **Verify:** Only one UpdateCloudTicketInfo job per recording

**Validation Points:**
- [ ] No duplicate links created
- [ ] Updates work correctly
- [ ] Timestamps updated
- [ ] Cloud updates deduped

### Phase 8.5: Integration Tests

#### Test 18: End-to-End: Upload → STT → Cloud Transfer (With Ticket)

**Test Steps:**
1. Upload folder with "Requires Ticket" checked
2. Wait for STT processing
3. Import CSV with ticket data
4. **Verify:** Recordings processed
5. **Verify:** Ticket information linked
6. **Verify:** Cloud transfer includes ticket_info
7. Check cloud database
8. **Verify:** Ticket fields populated

**Validation Points:**
- [ ] Full workflow completes
- [ ] All jobs execute
- [ ] ticket_info in cloud transfer
- [ ] Cloud database updated

#### Test 19: End-to-End: Upload → Link Later → Cloud Update

**Test Steps:**
1. Upload and process recordings (without tickets)
2. Recordings transfer to cloud
3. Later, import CSV with tickets
4. **Verify:** UpdateCloudTicketInfo job dispatched
5. **Verify:** Cloud receives update
6. Check cloud database
7. **Verify:** Ticket fields updated

**Validation Points:**
- [ ] Retroactive update works
- [ ] Job dispatched correctly
- [ ] Cloud API receives update
- [ ] Cloud database updated

#### Test 20: Manual Sync Command

```bash
# Test single recording sync
php artisan ticket:sync-to-cloud {recording_id}

# Test bulk sync
php artisan ticket:sync-to-cloud --all

# Check logs
tail -f storage/logs/laravel.log
```

**Validation Points:**
- [ ] Command executes without errors
- [ ] Progress bar shows (for --all)
- [ ] Jobs dispatched
- [ ] Cloud receives updates
- [ ] Logs show success/failure

### Phase 8.6: Performance Tests

#### Test 21: Bulk Import Performance

**Test Steps:**
1. Create 1000 recording records
2. Import CSV with 1000 ticket links
3. **Measure:** Time to validate
4. **Measure:** Time to import
5. **Measure:** Time for cloud updates

**Acceptance Criteria:**
- Validation: < 5 seconds
- Import: < 30 seconds
- Cloud updates: Background (asynchronous)

#### Test 22: Statistics Query Performance

**Test Steps:**
1. Create 10,000 recording records
2. Link 5,000 to tickets
3. Load Recordings page
4. **Measure:** Time to load statistics

**Acceptance Criteria:**
- Statistics load: < 500ms
- Page render: < 1 second

### Phase 8.7: Security Tests

#### Test 23: File Upload Security

**Test Steps:**
1. Try uploading PHP file with .csv extension
2. **Verify:** Only actual CSV content processed
3. Try uploading file with SQL injection in data
4. **Verify:** Data sanitized
5. Try uploading very large file (> 100MB)
6. **Verify:** Size limit enforced

**Validation Points:**
- [ ] File type validation works
- [ ] SQL injection prevented
- [ ] File size limits enforced
- [ ] No code execution possible

#### Test 24: Access Control

**Test Steps:**
1. Try accessing ticket import without login
2. **Verify:** Redirected to login
3. Check API endpoints without CSRF token
4. **Verify:** Requests rejected

**Validation Points:**
- [ ] Authentication required
- [ ] CSRF protection works
- [ ] Middleware applied correctly

## Acceptance Criteria

### Critical (Must Pass)

- [ ] Upload recordings with ticket requirement works
- [ ] CSV import wizard completes successfully
- [ ] Recordings link to tickets
- [ ] Cloud transfer includes ticket information
- [ ] Retroactive cloud updates work
- [ ] Statistics display correctly
- [ ] No data loss
- [ ] No SQL errors
- [ ] No JavaScript errors in console

### Important (Should Pass)

- [ ] Auto-column detection works
- [ ] File validation prevents bad data
- [ ] Error messages helpful
- [ ] Loading states show
- [ ] Responsive on mobile
- [ ] Performance acceptable
- [ ] Queue jobs process correctly

### Nice to Have (Improvements)

- [ ] Keyboard shortcuts work
- [ ] Tooltips helpful
- [ ] Animations smooth
- [ ] Color scheme consistent
- [ ] Icons appropriate

## Bug Reporting Template

When you find a bug, document it as:

```
**Bug ID:** BUG-001
**Severity:** Critical / High / Medium / Low
**Component:** Backend / Frontend / Integration
**Discovered:** 2025-01-29
**Status:** Open / In Progress / Fixed

**Description:**
Clear description of the issue

**Steps to Reproduce:**
1. Step one
2. Step two
3. Step three

**Expected Result:**
What should happen

**Actual Result:**
What actually happens

**Environment:**
- OS: macOS / Linux / Windows
- Browser: Chrome 120 / Firefox 115 / Safari 17
- PHP: 8.2.x
- Laravel: 12.x

**Screenshots:**
[Attach if applicable]

**Logs:**
```
[Paste relevant log entries]
```

**Fix Applied:**
[Once fixed, document the solution]
```

## Test Execution Log

Create a log file to track testing:

```
Date: 2025-01-29
Tester: [Name]
Environment: Development / Staging / Production

Phase 8.1: Backend Unit Tests
[ ] Test 1: TicketLinkingService - PASS / FAIL
[ ] Test 2: CSV Parsing - PASS / FAIL
[ ] Test 3: Validation Logic - PASS / FAIL
[ ] Test 4: Bulk Linking - PASS / FAIL

Phase 8.2: Cloud Transfer Tests
[ ] Test 5: CloudTransferService - PASS / FAIL
[ ] Test 6: UpdateCloudTicketInfo Job - PASS / FAIL

Phase 8.3: Frontend UI Tests
[ ] Test 7: Upload Flow - PASS / FAIL
[ ] Test 8: Statistics Dashboard - PASS / FAIL
[ ] Test 9: Import Wizard Step 1 - PASS / FAIL
[ ] Test 10: Import Wizard Step 2 - PASS / FAIL
[ ] Test 11: Import Wizard Step 3 - PASS / FAIL
[ ] Test 12: Import Wizard Step 4 - PASS / FAIL

Phase 8.4: Edge Cases
[ ] Test 13: Large Files - PASS / FAIL
[ ] Test 14: Special Characters - PASS / FAIL
[ ] Test 15: Concurrent Operations - PASS / FAIL
[ ] Test 16: Network Failures - PASS / FAIL
[ ] Test 17: Duplicate Prevention - PASS / FAIL

Phase 8.5: Integration Tests
[ ] Test 18: E2E Upload to Cloud - PASS / FAIL
[ ] Test 19: E2E Retroactive Update - PASS / FAIL
[ ] Test 20: Manual Sync Command - PASS / FAIL

Phase 8.6: Performance Tests
[ ] Test 21: Bulk Import Performance - PASS / FAIL
[ ] Test 22: Statistics Performance - PASS / FAIL

Phase 8.7: Security Tests
[ ] Test 23: File Upload Security - PASS / FAIL
[ ] Test 24: Access Control - PASS / FAIL

Total: __/24 Passed
Bugs Found: __
Critical Issues: __
```

## Next Steps After Testing

1. **All tests pass:** Ready for production deployment
2. **Minor issues:** Document and prioritize for future releases
3. **Critical issues:** Fix before deployment
4. **Performance issues:** Optimize and retest

## Deployment Checklist

Before deploying to production:

- [ ] All critical tests pass
- [ ] Database migrations tested
- [ ] Backup strategy in place
- [ ] Rollback plan documented
- [ ] Queue worker configured
- [ ] Monitoring set up
- [ ] Error tracking enabled
- [ ] Performance baseline established
- [ ] User documentation complete
- [ ] Team trained on new features
