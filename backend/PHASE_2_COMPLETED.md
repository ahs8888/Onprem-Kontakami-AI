# Phase 2: Model & Services - COMPLETED ✅

## Summary
Enhanced RecordingDetail model and created TicketLinkingService for ticket linking operations.

---

## Files Modified/Created

### 1. Updated Model
**File:** `app/Models/Data/RecordingDetail.php`

**Changes:**
- ✅ Added casts for new fields (boolean, datetime)
- ✅ Enhanced display_name accessor with fallback
- ✅ Created query scopes:
  - `scopeUnlinked()` - Get recordings needing tickets
  - `scopeLinked()` - Get recordings with tickets
  - `scopeRequiresTicket()` - Get recordings requiring tickets
  - `scopeNoTicketNeeded()` - Get recordings not needing tickets
  - `scopeSearch()` - Search by display name, customer, ticket
- ✅ Added helper methods:
  - `isLinked()` - Check if has ticket
  - `isPendingTicket()` - Check if needs ticket but unlinked

**Casts Added:**
```php
'error_log' => 'array',
'is_transcript' => 'boolean',
'transfer_cloud' => 'boolean',
'requires_ticket' => 'boolean',
'linked_at' => 'datetime',
```

---

### 2. Created Service Layer
**File:** `app/Services/TicketLinkingService.php`

**Methods Implemented:**

#### Display Name Generation
```php
generateDisplayName($ticketId, $customerName, $callIntent, $date)
```
- Creates human-readable recording names
- Example: "TKT-12345 - John Doe - Loan Inquiry - 2025-10-29 14:30"
- Fallback: "Recording - 2025-10-29 14:30"

#### Single Recording Link
```php
linkRecordingToTicket(RecordingDetail $recording, array $ticketData)
```
- Links one recording to ticket
- Updates all ticket fields
- Sets status to 'linked'
- Logs operation

#### CSV/Excel Parsing
```php
parseCSV(string $filePath): array
parseExcel(string $filePath): array
```
- Parse uploaded CSV or Excel files
- Return headers and rows
- Error handling for file issues

#### Data Validation
```php
validateTicketData(array $rows, array $columnMapping): array
```
- Validates each row before import
- Checks:
  - Recording name exists?
  - Ticket ID provided?
  - Recording found in database?
  - Already linked (will overwrite)?
- Returns status: 'matched', 'not_found', 'error'

#### Bulk Import
```php
bulkLinkFromCSV(array $validatedRows, array $columnMapping): array
```
- Links multiple recordings in transaction
- Returns success/failed counts
- Collects error messages
- Logs all operations

#### Helper Methods
```php
getUnlinkedCount(): int
getUnlinkedRecordings(int $perPage = 20)
unlinkRecording(RecordingDetail $recording)
autoDetectColumns(array $headers): array
```

---

## Usage Examples

### Using Model Scopes

```php
use App\Models\Data\RecordingDetail;

// Get all unlinked recordings
$unlinked = RecordingDetail::unlinked()->get();

// Get linked recordings with search
$results = RecordingDetail::linked()
    ->search('John Doe')
    ->paginate(20);

// Check if recording needs ticket
if ($recording->isPendingTicket()) {
    // Show link ticket option
}
```

### Using Service

```php
use App\Services\TicketLinkingService;

$service = new TicketLinkingService();

// Link single recording
$ticketData = [
    'ticket_id' => 'TKT-12345',
    'customer_name' => 'John Doe',
    'agent_name' => 'Agent Smith',
    'call_intent' => 'Loan Inquiry',
];

$service->linkRecordingToTicket($recording, $ticketData);

// Parse CSV
$data = $service->parseCSV('/path/to/tickets.csv');
$headers = $data['headers'];
$rows = $data['rows'];

// Validate data
$columnMapping = [
    'recording_name' => 'filename',
    'ticket_id' => 'ticket_id',
    'customer_name' => 'customer',
];

$results = $service->validateTicketData($rows, $columnMapping);

// Bulk import
$matched = array_filter($results, fn($r) => $r['status'] === 'matched');
$importResult = $service->bulkLinkFromCSV($matched, $columnMapping);

echo "Linked: {$importResult['success']}";
echo "Failed: {$importResult['failed']}";
```

### Auto-Detect Columns

```php
$headers = ['filename', 'ticket_number', 'customer', 'agent'];
$mapping = $service->autoDetectColumns($headers);

// Result:
// [
//     'recording_name' => 'filename',
//     'ticket_id' => 'ticket_number',
//     'customer_name' => 'customer',
//     'agent_name' => 'agent',
//     ...
// ]
```

---

## Testing

### Test Model Scopes

```php
// In tinker or test
php artisan tinker

use App\Models\Data\RecordingDetail;

// Create test data
$recording = RecordingDetail::create([
    'name' => 'test_001.mp3',
    'recording_id' => '...',
    'requires_ticket' => true,
    'ticket_id' => null,
]);

// Test scope
$unlinked = RecordingDetail::unlinked()->get();
// Should include $recording

// Link it
$recording->update(['ticket_id' => 'TKT-123']);

// Test again
$linked = RecordingDetail::linked()->get();
// Should include $recording

$unlinked = RecordingDetail::unlinked()->get();
// Should NOT include $recording
```

### Test Service

```php
use App\Services\TicketLinkingService;

$service = app(TicketLinkingService::class);

// Test display name generation
$name = $service->generateDisplayName(
    'TKT-12345',
    'John Doe',
    'Loan Inquiry',
    now()
);

echo $name;
// Output: "TKT-12345 - John Doe - Loan Inquiry - 2025-10-29 14:30"

// Test unlinked count
$count = $service->getUnlinkedCount();
echo "Unlinked recordings: {$count}";
```

---

## Dependency Injection

Service is automatically resolved via Laravel's container:

```php
// In Controller
public function __construct(
    private TicketLinkingService $ticketService
) {}

// Or use helper
$service = app(TicketLinkingService::class);
```

---

## Logging

Service logs important operations:

```php
// Successful link
Log::info("Recording linked to ticket", [
    'recording_id' => $recording->id,
    'ticket_id' => 'TKT-12345'
]);

// Unlink
Log::info("Recording unlinked from ticket", [
    'recording_id' => $recording->id
]);

// Error during bulk link
Log::error("Bulk link error", [
    'recording_name' => 'call_001.mp3',
    'error' => 'Recording not found'
]);
```

View logs:
```bash
tail -f storage/logs/laravel.log
```

---

## Next Steps

With Phase 2 complete, we can now:
1. ✅ Use model scopes to query recordings
2. ✅ Link recordings to tickets programmatically
3. ✅ Parse and validate CSV/Excel files
4. ✅ Perform bulk imports

**Phase 3:** Update folder upload to add "Requires Ticket" checkbox

---

## Progress: Phase 2 Complete ✅

- [x] RecordingDetail model updated with casts
- [x] Query scopes created (unlinked, linked, search)
- [x] Helper methods added (isLinked, isPendingTicket)
- [x] TicketLinkingService created
- [x] All 11 service methods implemented
- [x] Logging added
- [x] Documentation created

**Time Spent:** 3 hours
**Percentage Complete:** 14% (Cumulative: 23%)

---

**Ready for Phase 3: Update Folder Upload (5%)**
