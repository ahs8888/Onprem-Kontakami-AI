# Enhancement Roadmap

## Current Status

### On-Prem App
- ✅ **Phase 1 COMPLETE (9%)**: Database schema with ticket linking fields

### Cloud App
- ⏳ Not started

---

## On-Prem Enhancement Phases (Total: 100%)

### ✅ Phase 1: Database Schema (9% - 2 hours) - COMPLETE
**Status:** ✅ Done

**Deliverables:**
- Migration file with 9 new fields
- Status enum extension
- Performance indexes
- Full-text search capability

**Files Created:**
- `database/migrations/2025_10_29_062341_add_ticket_linking_to_recording_details_table.php`
- `database/migrations/2025_10_29_062342_extend_status_enum_recording_details.php`

---

### 🔄 Phase 2: Model & Services (14% - 3 hours) - NEXT
**Status:** 🔄 In Progress

**Tasks:**
1. Update RecordingDetail model with new fields
2. Add casts for boolean/datetime fields
3. Create query scopes (unlinked, linked, requiresTicket)
4. Create TicketLinkingService with methods:
   - `generateDisplayName()`
   - `linkRecordingToTicket()`
   - `parseCSV()`
   - `parseExcel()`
   - `validateTicketData()`
   - `bulkLinkFromCSV()`

**Files to Create/Modify:**
- `app/Models/Data/RecordingDetail.php` (update)
- `app/Services/TicketLinkingService.php` (new)

---

### ⏳ Phase 3: Update Folder Upload (5% - 1 hour)
**Status:** ⏳ Pending

**Tasks:**
1. Update RecordingAction to accept `requiresTicket` parameter
2. Add checkbox to upload form
3. Update route: `routes/admin/recording.php`
4. Add unlinked count to recordings list

**Files to Modify:**
- `app/Actions/Data/RecordingAction.php`
- `app/Http/Controllers/Admin/RecordingController.php`
- `resources/js/Pages/Recording/Create.vue`
- `resources/js/Pages/Recording/Index.vue`

---

### ⏳ Phase 4: CSV Import Backend (18% - 4 hours)
**Status:** ⏳ Pending

**Tasks:**
1. Create modular route file: `routes/admin/ticket.php`
2. Update RouteServiceProvider to load modules
3. Create TicketImportController
4. Create TicketLinkController (optional API)
5. Implement parse, validate, bulk-link endpoints

**Files to Create:**
- `routes/admin/ticket.php` (new module)
- `app/Http/Controllers/Admin/TicketImportController.php`
- `app/Http/Controllers/Admin/TicketLinkController.php`
- Update: `app/Providers/RouteServiceProvider.php`

---

### ⏳ Phase 5: CSV Import Frontend (27% - 6 hours)
**Status:** ⏳ Pending

**Tasks:**
1. Create main import page (4-step wizard)
2. Create ColumnMapper component
3. Create ValidationResults component
4. Implement file upload with drag & drop
5. Auto-detect column names
6. Preview and validation UI
7. Import progress indicator

**Files to Create:**
- `resources/js/Pages/TicketImport/Index.vue`
- `resources/js/Pages/TicketImport/Components/ColumnMapper.vue`
- `resources/js/Pages/TicketImport/Components/ValidationResults.vue`

---

### ⏳ Phase 6: Cloud Transfer Update (9% - 2 hours)
**Status:** ⏳ Pending

**Tasks:**
1. Update ProcessRecordingBatch to include ticket fields
2. Create UpdateCloudTicketInfo job
3. Dispatch job after retroactive linking
4. Test cloud API update

**Files to Modify/Create:**
- `app/Jobs/ProcessRecordingBatch.php` (update)
- `app/Jobs/UpdateCloudTicketInfo.php` (new)

---

### ⏳ Phase 7: Navigation & UI Polish (5% - 1 hour)
**Status:** ⏳ Pending

**Tasks:**
1. Add "Link Tickets" menu item
2. Add unlinked banner to recordings list
3. Add badge with unlinked count
4. Polish UI/UX

**Files to Modify:**
- `resources/js/Layouts/AuthenticatedLayout.vue`
- `resources/js/Pages/Recording/Index.vue`

---

### ⏳ Phase 8: Testing & Validation (14% - 3 hours)
**Status:** ⏳ Pending

**Test Cases:**
- Upload without ticket requirement
- Upload with ticket requirement
- CSV import - happy path
- CSV import - not found recordings
- Excel import
- Retroactive linking after cloud transfer
- Error handling
- Performance testing

---

## Cloud App Enhancement Phases

### Phase 1: Database Migration (SQLite → MySQL)
**Status:** ⏳ Not Started

**Tasks:**
1. Update database config
2. Run migrations on MySQL
3. Test all existing functionality

---

### Phase 2: Ticket Info Reception
**Status:** ⏳ Not Started

**Tasks:**
1. Add ticket fields to recording_files table
2. Update API endpoint to receive ticket info
3. Create PATCH endpoint for retroactive updates

---

### Phase 3: Dynamic AI Provider Selection
**Status:** ⏳ Not Started

**Tasks:**
1. Add AI provider settings table
2. Create AIProviderService
3. Support OpenAI (3 models) + Gemini (3 models)
4. Build settings UI

---

### Phase 4: Analysis Layer Enhancement
**Status:** ⏳ Not Started

**Tasks:**
1. Implement correlation engine
2. Create insights generation system
3. Add MySQL vector search
4. Build pattern detection

---

### Phase 5: Decision Layer Implementation
**Status:** ⏳ Not Started

**Tasks:**
1. Build recommendation engine
2. Add auto-scoring system
3. Implement call/agent prioritization
4. Create decision API endpoints

---

## Timeline Summary

### On-Prem Enhancement
- **Total Time:** 22 hours (~3 working days)
- **Progress:** 9% complete
- **Remaining:** 20 hours

### Cloud Enhancement
- **Total Time:** ~20 hours (~2.5 working days)
- **Progress:** 0% complete
- **Remaining:** 20 hours

### Overall Project
- **Total Time:** 42 hours (~5-6 working days)
- **Progress:** 4.5% complete
- **Remaining:** 40 hours

---

## Priority Order

1. ✅ On-Prem Phase 1 (Database) - DONE
2. 🔄 On-Prem Phase 2 (Models) - IN PROGRESS
3. ⏳ On-Prem Phase 3-8 (Complete on-prem)
4. ⏳ Cloud Phase 1-2 (Database + API)
5. ⏳ Cloud Phase 3-5 (AI features)

---

**Last Updated:** October 29, 2025
**Current Phase:** On-Prem Phase 2
**Next Milestone:** On-Prem functional ticket linking (Phase 1-4 = 45%)
