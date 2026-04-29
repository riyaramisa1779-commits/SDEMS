# Multiple File Upload - Flow Diagram

## 📊 System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE                          │
│                    (Blade + Alpine.js)                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP POST (multipart/form-data)
                              │ files[], case_number, title, etc.
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    EVIDENCE CONTROLLER                          │
│                  (EvidenceController@store)                     │
│                                                                 │
│  1. Validate request (files array, metadata)                   │
│  2. Check total size (10 GB limit)                             │
│  3. Loop through each file:                                    │
│     ├─ Generate UUID filename                                  │
│     ├─ Store file on private disk                              │
│     ├─ Create Evidence record                                  │
│     ├─ Dispatch hash job                                       │
│     └─ Log activity                                            │
│  4. Return success/failure summary                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   STORAGE    │    │   DATABASE   │    │  REDIS QUEUE │
│              │    │              │    │              │
│ Private Disk │    │  Evidence    │    │  Hash Jobs   │
│ YYYY/MM/     │    │  ChainOfCust │    │  Dispatched  │
│ {uuid}.ext   │    │  Activity    │    │              │
└──────────────┘    └──────────────┘    └──────────────┘
                                                │
                                                │
                                                ▼
                                    ┌──────────────────────┐
                                    │   QUEUE WORKER       │
                                    │                      │
                                    │ CalculateEvidence    │
                                    │ Hash Job             │
                                    │                      │
                                    │ 1. Read file         │
                                    │ 2. Calculate SHA-256 │
                                    │ 3. Store hash        │
                                    │ 4. Update status     │
                                    └──────────────────────┘
                                                │
                                                ▼
                                    ┌──────────────────────┐
                                    │   DATABASE           │
                                    │                      │
                                    │ evidence_hashes      │
                                    │ status: active       │
                                    └──────────────────────┘
```

---

## 🔄 Upload Flow (Step by Step)

### Phase 1: User Selection

```
User Action                     Frontend (Alpine.js)
───────────                     ────────────────────
                                
1. Click upload area     ──────▶ Open file picker
   OR drag files                 
                                
2. Select multiple files ──────▶ handleFileSelect()
   (up to 10)                    │
                                 ├─ Validate file count (max 10)
                                 ├─ Validate file size (max 2 GB each)
                                 ├─ Check duplicates
                                 ├─ Calculate total size (max 10 GB)
                                 └─ Add to selectedFiles[]
                                
3. Review file list      ──────▶ Display preview
                                 │
                                 ├─ Show file name
                                 ├─ Show file size
                                 ├─ Show total count/size
                                 └─ Show remove buttons
                                
4. Remove unwanted files ──────▶ removeFile(index)
   (optional)                    │
                                 └─ Update selectedFiles[]
                                
5. Fill metadata         ──────▶ Form inputs
   - Case number                 (case_number, title,
   - Title                        category, description,
   - Category                     tags)
   - Description
   - Tags
                                
6. Click "Upload X Files"──────▶ startUpload()
                                 │
                                 ├─ Set isUploading = true
                                 ├─ Show progress bar
                                 └─ Submit form
```

### Phase 2: Server Processing

```
Backend (Laravel)                Database/Storage
─────────────────                ────────────────

1. Receive POST request
   └─ files[] array
   └─ metadata fields
   
2. Validate request       ──────▶ Validation rules
   ├─ files: array, min 1, max 10
   ├─ files.*: file, max 2 GB
   ├─ case_number: required
   ├─ title: required
   └─ category: required
   
3. Check total size
   └─ Sum all file sizes
   └─ Must be ≤ 10 GB
   
4. Loop through files:
   
   For each file:
   
   4a. Generate UUID      ──────▶ Str::uuid()
       └─ {uuid}.{ext}
   
   4b. Store file         ──────▶ Storage::disk('evidence')
       └─ YYYY/MM/{uuid}.ext     ->putFileAs()
   
   4c. Create Evidence    ──────▶ INSERT INTO evidence
       record                     (case_number, title,
       └─ Title: base + filename  file_path, original_name,
       └─ Status: pending         mime_type, file_size,
                                  uploaded_by, status)
   
   4d. Model boot event   ──────▶ INSERT INTO chain_of_custody
       └─ Create custody          (evidence_id, action='upload',
          record                   to_user_id, timestamp)
   
   4e. Dispatch hash job  ──────▶ RPUSH redis:queue
       └─ CalculateEvidence       {job: CalculateEvidenceHash,
          Hash::dispatch()         evidence_id: xxx}
   
   4f. Log activity       ──────▶ INSERT INTO activity_log
       └─ Spatie activity         (log_name='evidence_upload',
          log                      properties={batch_upload: true,
                                    batch_index: X,
                                    batch_total: Y})
   
5. Build response
   ├─ Count successes
   ├─ Track failures
   └─ Generate summary
   
6. Redirect
   ├─ Single file  ──────▶ /evidence/{id}
   └─ Multiple files ────▶ /custody
```

### Phase 3: Background Processing

```
Queue Worker                     Database
────────────                     ────────

1. Poll Redis queue
   └─ BLPOP redis:queue
   
2. Receive job
   └─ CalculateEvidenceHash
   └─ evidence_id: xxx
   
3. Load Evidence record  ──────▶ SELECT * FROM evidence
   └─ Get file_path              WHERE id = xxx
   
4. Read file from storage
   └─ Storage::disk('evidence')
      ->readStream(file_path)
   
5. Calculate SHA-256 hash
   └─ hash_init('sha256')
   └─ hash_update_stream()
   └─ hash_final()
   
6. Store hash            ──────▶ INSERT INTO evidence_hashes
   └─ EvidenceHash::create()     (evidence_id, hash_value,
                                  hash_type='sha256',
                                  generated_at, created_by)
   
7. Update Evidence status ─────▶ UPDATE evidence
   └─ pending → active           SET status = 'active'
                                  WHERE id = xxx
   
8. Log completion
   └─ Log::info()
   
9. Job complete
   └─ Remove from queue
```

---

## 🎭 User Experience Flow

### Scenario 1: Single File Upload (Backward Compatible)

```
┌─────────────────────────────────────────────────────────────┐
│ Step 1: Navigate to /evidence/upload                       │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 2: Select 1 file                                       │
│ ┌─────────────────────────────────────────────────────┐    │
│ │  📄 document.pdf                              2.3 MB │    │
│ └─────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 3: Fill metadata                                       │
│ Case Number: CASE-2026-001                                  │
│ Category: Document                                          │
│ Title: Evidence Report                                      │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 4: Click "Upload 1 File"                              │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 5: Redirect to /evidence/{id}                         │
│ ✅ Successfully uploaded 1 file!                            │
│ Hash calculation is in progress.                            │
└─────────────────────────────────────────────────────────────┘
```

### Scenario 2: Multiple File Upload

```
┌─────────────────────────────────────────────────────────────┐
│ Step 1: Navigate to /evidence/upload                       │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 2: Select 5 files (or drag & drop)                    │
│ ┌─────────────────────────────────────────────────────┐    │
│ │  📄 report.pdf                                1.2 MB │ ❌ │
│ │  📷 photo1.jpg                                3.5 MB │ ❌ │
│ │  📷 photo2.jpg                                2.8 MB │ ❌ │
│ │  🎥 video.mp4                               125.0 MB │ ❌ │
│ │  📊 spreadsheet.xlsx                          0.5 MB │ ❌ │
│ └─────────────────────────────────────────────────────┘    │
│ 5 files selected (132.0 MB total)              [Clear All] │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 3: Fill metadata (applies to all files)               │
│ Case Number: CASE-2026-002                                  │
│ Category: Document                                          │
│ Title: Crime Scene Evidence                                │
│ Description: Evidence collected from scene                  │
│ Tags: crime-scene, photos, video                           │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 4: Click "Upload 5 Files"                             │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 5: Progress bar appears                                │
│ ████████████████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 45%      │
│ Please wait while your files are being uploaded...          │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 6: Redirect to /custody                                │
│ ✅ Successfully uploaded 5 files!                           │
│ All files uploaded successfully.                            │
│ Hash calculations are in progress.                          │
└─────────────────────────────────────────────────────────────┘
```

### Scenario 3: Partial Failure

```
┌─────────────────────────────────────────────────────────────┐
│ Step 1-4: Same as Scenario 2                               │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 5: Upload processing                                   │
│ ✅ report.pdf - Success                                     │
│ ✅ photo1.jpg - Success                                     │
│ ✅ photo2.jpg - Success                                     │
│ ❌ video.mp4 - Failed (storage error)                       │
│ ✅ spreadsheet.xlsx - Success                               │
└─────────────────────────────────────────────────────────────┘
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ Step 6: Redirect to /custody                                │
│ ⚠️  Uploaded 4 of 5 files.                                  │
│ Some files failed: video.mp4                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔍 Data Flow Diagram

### Evidence Record Creation

```
User Input                Evidence Table              Related Tables
──────────                ──────────────              ──────────────

files[0]: report.pdf  ──▶ INSERT evidence         ──▶ INSERT chain_of_custody
  + metadata              id: uuid-1                   evidence_id: uuid-1
                          case_number: CASE-001        action: 'upload'
                          title: Title - report.pdf    to_user_id: user_id
                          file_path: 2026/04/uuid.pdf  timestamp: now()
                          original_name: report.pdf
                          mime_type: application/pdf ──▶ INSERT activity_log
                          file_size: 1234567           log_name: evidence_upload
                          uploaded_by: user_id         subject_id: uuid-1
                          status: pending              properties: {batch_*}
                          
                                                    ──▶ RPUSH redis:queue
                                                        job: CalculateHash
                                                        evidence_id: uuid-1

files[1]: photo.jpg   ──▶ INSERT evidence         ──▶ INSERT chain_of_custody
  + metadata              id: uuid-2                   evidence_id: uuid-2
                          case_number: CASE-001        action: 'upload'
                          title: Title - photo.jpg     to_user_id: user_id
                          file_path: 2026/04/uuid.jpg  timestamp: now()
                          original_name: photo.jpg
                          mime_type: image/jpeg     ──▶ INSERT activity_log
                          file_size: 3456789           log_name: evidence_upload
                          uploaded_by: user_id         subject_id: uuid-2
                          status: pending              properties: {batch_*}
                          
                                                    ──▶ RPUSH redis:queue
                                                        job: CalculateHash
                                                        evidence_id: uuid-2

... (repeat for each file)
```

### Hash Calculation Flow

```
Queue Job                 Evidence Table           Evidence Hashes Table
─────────                 ──────────────           ─────────────────────

BLPOP redis:queue     ──▶ SELECT * FROM evidence
job: CalculateHash        WHERE id = uuid-1
evidence_id: uuid-1       
                          file_path: 2026/04/uuid.pdf
                          ▼
                      Read file from storage
                      Calculate SHA-256 hash
                          ▼
                      INSERT INTO              ──▶ evidence_hashes
                      evidence_hashes              evidence_id: uuid-1
                                                   hash_value: abc123...
                                                   hash_type: sha256
                                                   generated_at: now()
                                                   created_by: user_id
                          ▼
                      UPDATE evidence
                      SET status = 'active'
                      WHERE id = uuid-1
```

---

## 🎯 Validation Flow

### Client-Side Validation (Alpine.js)

```
File Selection
      │
      ▼
┌─────────────────────┐
│ Check file count    │ ──▶ > 10? ──▶ Error: "Maximum 10 files"
└─────────────────────┘
      │ ≤ 10
      ▼
┌─────────────────────┐
│ Check file size     │ ──▶ > 2 GB? ──▶ Error: "File too large"
└─────────────────────┘
      │ ≤ 2 GB
      ▼
┌─────────────────────┐
│ Check duplicates    │ ──▶ Duplicate? ──▶ Error: "Already selected"
└─────────────────────┘
      │ Not duplicate
      ▼
┌─────────────────────┐
│ Add to list         │
└─────────────────────┘
      │
      ▼
┌─────────────────────┐
│ Check total size    │ ──▶ > 10 GB? ──▶ Error: "Total size exceeds limit"
└─────────────────────┘     │
      │ ≤ 10 GB              ▼
      ▼                  Remove last files
┌─────────────────────┐     until under limit
│ Display in list     │
└─────────────────────┘
```

### Server-Side Validation (Laravel)

```
POST Request
      │
      ▼
┌─────────────────────┐
│ Validate files      │ ──▶ Not array? ──▶ Error: "Invalid format"
│ array               │ ──▶ < 1 file?  ──▶ Error: "Select at least one"
└─────────────────────┘ ──▶ > 10 files? ──▶ Error: "Maximum 10 files"
      │ Valid array
      ▼
┌─────────────────────┐
│ Validate each file  │ ──▶ Not file?  ──▶ Error: "Invalid file"
│ files.*             │ ──▶ > 2 GB?    ──▶ Error: "File too large"
└─────────────────────┘ ──▶ Bad MIME?  ──▶ Error: "Type not permitted"
      │ All valid
      ▼
┌─────────────────────┐
│ Check total size    │ ──▶ > 10 GB?   ──▶ Error: "Total exceeds limit"
└─────────────────────┘
      │ ≤ 10 GB
      ▼
┌─────────────────────┐
│ Validate metadata   │ ──▶ Missing?   ──▶ Error: "Field required"
│ case_number, title  │ ──▶ Invalid?   ──▶ Error: "Invalid format"
└─────────────────────┘
      │ All valid
      ▼
┌─────────────────────┐
│ Process upload      │
└─────────────────────┘
```

---

## 📊 Database Schema Relationships

```
┌─────────────────────────────────────────────────────────────┐
│                         EVIDENCE                            │
├─────────────────────────────────────────────────────────────┤
│ id (UUID, PK)                                               │
│ case_number (string)                                        │
│ title (string)                                              │
│ file_path (string) ──────────────────────┐                 │
│ original_name (string)                   │                 │
│ mime_type (string)                       │                 │
│ file_size (integer)                      │                 │
│ uploaded_by (FK → users.id)              │                 │
│ status (enum)                            │                 │
│ created_at, updated_at                   │                 │
└──────────────────────────────────────────┼─────────────────┘
                │                          │
                │ 1:N                      │ File Storage
                ▼                          ▼
┌───────────────────────────┐   ┌──────────────────────┐
│   CHAIN_OF_CUSTODY        │   │  STORAGE DISK        │
├───────────────────────────┤   ├──────────────────────┤
│ id (PK)                   │   │ evidence/            │
│ evidence_id (FK)          │   │   2026/              │
│ from_user_id (FK)         │   │     04/              │
│ to_user_id (FK)           │   │       {uuid}.pdf     │
│ action (enum)             │   │       {uuid}.jpg     │
│ timestamp                 │   │       {uuid}.mp4     │
└───────────────────────────┘   └──────────────────────┘
                │
                │ 1:N
                ▼
┌───────────────────────────┐
│   EVIDENCE_HASHES         │
├───────────────────────────┤
│ id (PK)                   │
│ evidence_id (FK)          │
│ hash_value (string)       │
│ hash_type (enum)          │
│ generated_at              │
│ created_by (FK)           │
└───────────────────────────┘
                │
                │ 1:N
                ▼
┌───────────────────────────┐
│   ACTIVITY_LOG            │
├───────────────────────────┤
│ id (PK)                   │
│ log_name (string)         │
│ subject_id (FK)           │
│ causer_id (FK)            │
│ properties (JSON)         │
│   ├─ batch_upload: true   │
│   ├─ batch_index: 1       │
│   └─ batch_total: 5       │
│ created_at                │
└───────────────────────────┘
```

---

## 🔄 State Transitions

### Evidence Status Flow

```
                    Upload
                      │
                      ▼
              ┌───────────────┐
              │   PENDING     │ ◀── Initial status
              └───────────────┘     (hash not calculated)
                      │
                      │ Hash job completes
                      ▼
              ┌───────────────┐
              │   ACTIVE      │ ◀── Hash verified
              └───────────────┘     (ready for use)
                      │
                      │ Investigator reviews
                      ▼
              ┌───────────────┐
              │  IN_REVIEW    │ ◀── Under investigation
              └───────────────┘
                      │
                      ├─────────────┬─────────────┐
                      │             │             │
                      ▼             ▼             ▼
              ┌───────────┐  ┌───────────┐  ┌───────────┐
              │ ADMITTED  │  │ REJECTED  │  │ ARCHIVED  │
              └───────────┘  └───────────┘  └───────────┘
               (accepted)     (rejected)     (stored)
```

---

## 🎨 UI Component Hierarchy

```
upload.blade.php
│
├─ x-app-layout
│  │
│  ├─ Header
│  │  └─ "Upload Evidence"
│  │
│  └─ Main Content
│     │
│     ├─ Success/Error Messages
│     │  ├─ session('success')
│     │  └─ $errors->any()
│     │
│     ├─ Form (x-data="uploadForm()")
│     │  │
│     │  ├─ File Upload Section
│     │  │  ├─ Drop Zone
│     │  │  │  ├─ Drag indicator
│     │  │  │  ├─ Upload icon
│     │  │  │  └─ Instructions
│     │  │  │
│     │  │  ├─ File Input (hidden)
│     │  │  │  └─ name="files[]" multiple
│     │  │  │
│     │  │  ├─ File Preview List
│     │  │  │  ├─ File count & total size
│     │  │  │  ├─ Clear All button
│     │  │  │  └─ For each file:
│     │  │  │     ├─ File icon
│     │  │  │     ├─ File name
│     │  │  │     ├─ File size
│     │  │  │     └─ Remove button
│     │  │  │
│     │  │  └─ Error messages
│     │  │
│     │  ├─ Metadata Section
│     │  │  ├─ Case Number (required)
│     │  │  ├─ Category (required)
│     │  │  ├─ Title (required)
│     │  │  ├─ Description (optional)
│     │  │  └─ Tags (x-data="tagInput()")
│     │  │     ├─ Tag list
│     │  │     └─ Tag input
│     │  │
│     │  ├─ Upload Progress
│     │  │  ├─ Progress bar
│     │  │  └─ Status message
│     │  │
│     │  ├─ Security Notice
│     │  │  └─ Chain of custody warning
│     │  │
│     │  └─ Submit Section
│     │     ├─ Back link
│     │     └─ Upload button
│     │
│     └─ Alpine.js Scripts
│        ├─ uploadForm()
│        └─ tagInput()
```

---

**This flow diagram provides a comprehensive visual understanding of the multiple file upload system architecture, data flow, and user experience.**
