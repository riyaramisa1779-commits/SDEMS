# Module 3: Multiple File Upload - Implementation Guide

## Overview

The Evidence Upload feature has been upgraded to support **multiple file uploads** in a single operation. Users can now select and upload up to 10 files simultaneously, with a total size limit of 10 GB.

---

## Features Implemented

### Frontend (Tailwind CSS + Alpine.js)

✅ **Multiple File Selection**
- File input accepts multiple files: `<input type="file" multiple>`
- Drag-and-drop area clearly indicates "Drop multiple files here"
- Visual feedback during drag operations

✅ **File Preview List**
- Shows all selected files with:
  - File name
  - File size (human-readable format)
  - Individual remove button (X)
  - File icon
- Scrollable list (max height 256px) for many files
- Total file count and total size display
- "Clear All" button to remove all files at once

✅ **Client-Side Validation**
- Maximum 10 files per upload
- Maximum 2 GB per individual file
- Maximum 10 GB total size across all files
- Duplicate file detection (same name + size)
- Real-time error messages for validation failures

✅ **Upload Progress**
- Progress bar showing upload status
- Disabled submit button during upload
- Visual feedback with percentage display

✅ **Shared Metadata**
- Single metadata form applies to all files in the batch
- For multiple files, original filename is appended to title automatically
- Case number, category, description, and tags are shared

### Backend (Laravel 11)

✅ **Multiple File Processing**
- `EvidenceController@store` handles array of files
- Each file processed individually in a loop
- Separate `Evidence` record created for each file
- UUID-based secure filenames for each file
- Year/month directory structure maintained

✅ **Validation Rules**
- `files` array: required, min 1, max 10 files
- `files.*` individual validation: max 2 GB, allowed MIME types
- Total size validation: 10 GB limit across all files
- Custom error messages for clarity

✅ **Hash Calculation**
- SHA-256 hash generated for each file via queue job
- Redis queue used for background processing
- Each file gets its own `CalculateEvidenceHash` job

✅ **Chain of Custody**
- Initial ChainOfCustody entry created for each file automatically
- Model boot event handles custody record creation
- Each file tracked independently

✅ **Activity Logging**
- Each file upload logged individually via Spatie Activitylog
- Batch metadata included: `batch_upload`, `batch_index`, `batch_total`
- Full forensic trail maintained

✅ **Error Handling**
- Try-catch for each file upload
- Failed files tracked and reported
- Partial success supported (some files succeed, some fail)
- Detailed error messages returned to user

✅ **Upload Summary**
- Success count displayed
- Failed file names listed
- Redirect to custody index for multiple files
- Redirect to single evidence detail for single file

---

## File Changes

### 1. Updated View
**File:** `SDEMS/resources/views/evidence/upload.blade.php`

**Key Changes:**
- Changed file input to `name="files[]"` with `multiple` attribute
- Updated Alpine.js component to `uploadForm()` with array handling
- Added file list preview with individual remove buttons
- Added total size calculation and display
- Added upload progress bar
- Updated validation error display for array errors
- Added client-side validation for max files, max size, duplicates

### 2. Updated Controller
**File:** `SDEMS/app/Http/Controllers/EvidenceController.php`

**Key Changes:**
- Updated validation rules to accept `files` array instead of single `file`
- Added total size validation (10 GB limit)
- Added loop to process each file individually
- Added success/failure tracking
- Added batch metadata to activity logs
- Updated redirect logic based on number of uploaded files
- Added custom error messages

---

## Validation Rules

### Request Validation

```php
'files'       => ['required', 'array', 'min:1', 'max:10'],
'files.*'     => [
    'required',
    'file',
    'max:2097152', // 2 GB in kilobytes
    function ($attribute, $value, $fail) {
        $mime = $value->getMimeType();
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            $fail("File type '{$mime}' is not permitted.");
        }
    },
],
```

### Limits

| Limit | Value | Reason |
|-------|-------|--------|
| Max files per upload | 10 | Prevents server overload |
| Max size per file | 2 GB | Matches original single upload limit |
| Max total size | 10 GB | Prevents excessive storage/bandwidth usage |
| Max tags | 20 | Prevents metadata bloat |

### Allowed File Types

Same as original implementation:
- **Documents:** PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT, CSV
- **Images:** JPG, JPEG, PNG, GIF, WEBP, TIFF, BMP
- **Videos:** MP4, AVI, MOV, MKV, WEBM
- **Audio:** MP3, WAV, OGG, M4A
- **Archives:** ZIP, TAR, GZ, 7Z
- **Forensic:** Octet-stream (raw binary/forensic images)

---

## Testing Instructions

### 1. Basic Multiple Upload Test

```bash
# Start the development server
cd SDEMS
php artisan serve

# In another terminal, start the queue worker
php artisan queue:work redis --tries=3
```

**Steps:**
1. Navigate to `/evidence/upload`
2. Click the upload area or drag multiple files (2-5 files)
3. Verify all files appear in the preview list
4. Fill in metadata (case number, category, title)
5. Click "Upload X Files" button
6. Verify success message shows correct count
7. Check custody index page for all uploaded files

### 2. Validation Tests

**Test Max Files (10):**
1. Try to upload 11 files
2. Verify error: "Maximum 10 files allowed"

**Test Max File Size (2 GB):**
1. Try to upload a file > 2 GB
2. Verify error: "File too large (max 2 GB per file)"

**Test Max Total Size (10 GB):**
1. Upload 6 files of 2 GB each (12 GB total)
2. Verify error: "Total size exceeds 10 GB limit"

**Test Duplicate Files:**
1. Select the same file twice
2. Verify error: "Already selected"

**Test Invalid File Type:**
1. Try to upload an .exe or .bat file
2. Verify error: "File type not permitted"

### 3. Queue Job Test

```bash
# Monitor queue jobs
php artisan queue:work redis --verbose

# Upload multiple files and watch the output
# You should see:
# [timestamp] Processing: App\Jobs\CalculateEvidenceHash
# [timestamp] Processed:  App\Jobs\CalculateEvidenceHash
```

### 4. Database Verification

```sql
-- Check evidence records
SELECT id, case_number, title, original_name, status, created_at 
FROM evidence 
ORDER BY created_at DESC 
LIMIT 10;

-- Check chain of custody records
SELECT evidence_id, action, to_user_id, timestamp 
FROM chain_of_custody 
ORDER BY timestamp DESC 
LIMIT 10;

-- Check evidence hashes
SELECT evidence_id, hash_type, hash_value, generated_at 
FROM evidence_hashes 
ORDER BY generated_at DESC 
LIMIT 10;

-- Check activity log
SELECT log_name, description, subject_id, properties 
FROM activity_log 
WHERE log_name = 'evidence_upload' 
ORDER BY created_at DESC 
LIMIT 10;
```

### 5. Activity Log Verification

Navigate to `/admin/activity-log` (requires rank 8+) and verify:
- Each file upload has a separate log entry
- Batch metadata is present: `batch_upload: true`, `batch_index`, `batch_total`
- All forensic metadata is captured (IP, user agent, file size, etc.)

---

## User Experience Flow

### Single File Upload (Backward Compatible)
1. User selects 1 file
2. File appears in preview
3. User fills metadata
4. Clicks "Upload 1 File"
5. Redirected to evidence detail page
6. Success message: "Successfully uploaded 1 file!"

### Multiple File Upload
1. User selects 5 files (or drags them)
2. All 5 files appear in preview list with sizes
3. Total size shown: "5 files selected (1.2 GB total)"
4. User fills metadata (applied to all files)
5. Clicks "Upload 5 Files"
6. Progress bar appears
7. Redirected to custody index page
8. Success message: "Successfully uploaded 5 files! All files uploaded successfully. Hash calculations are in progress."

### Partial Failure Scenario
1. User uploads 5 files
2. 4 succeed, 1 fails (e.g., storage error)
3. Success message: "Uploaded 4 of 5 files."
4. Summary: "Some files failed: document.pdf"
5. 4 evidence records created
6. User can retry the failed file

---

## Security Considerations

✅ **All original security features maintained:**
- UUID-based filenames (prevents enumeration)
- Private storage disk (not publicly accessible)
- MIME type validation
- File size limits
- Rank-based access control
- Activity logging for forensic audit
- Chain of custody for each file
- SHA-256 hash integrity verification

✅ **Additional security for multiple uploads:**
- Total size limit prevents storage exhaustion
- Max file count prevents DoS via many small files
- Each file validated independently
- Failed uploads don't affect successful ones
- Batch metadata in logs for correlation

---

## Performance Considerations

### Upload Performance
- Files uploaded via standard multipart/form-data
- Browser handles upload progress natively
- Server processes files sequentially (not parallel) to avoid memory issues
- Each file stored immediately before processing next

### Hash Calculation Performance
- Hash jobs dispatched to Redis queue
- Jobs run in background (non-blocking)
- Each file gets its own job (parallel processing by queue workers)
- Status transitions from 'pending' → 'active' when hash completes

### Recommendations
- Run multiple queue workers for faster hash processing:
  ```bash
  php artisan queue:work redis --tries=3 --processes=4
  ```
- Monitor queue with Laravel Horizon (optional):
  ```bash
  composer require laravel/horizon
  php artisan horizon:install
  php artisan horizon
  ```

---

## Troubleshooting

### Issue: Files not uploading
**Check:**
1. PHP `upload_max_filesize` and `post_max_size` in php.ini
2. Web server timeout settings (nginx/Apache)
3. Storage disk permissions (`storage/app/evidence`)

**Fix:**
```ini
; php.ini
upload_max_filesize = 2048M
post_max_size = 10240M
max_execution_time = 300
```

### Issue: Hash jobs not processing
**Check:**
1. Queue worker is running: `php artisan queue:work redis`
2. Redis is running: `redis-cli ping` (should return PONG)
3. Queue connection in `.env`: `QUEUE_CONNECTION=redis`

**Fix:**
```bash
# Restart queue worker
php artisan queue:restart

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Issue: "Total size exceeds 10 GB" error
**Cause:** Sum of all selected files > 10 GB

**Fix:** User should:
1. Remove some files from selection
2. Upload in multiple batches
3. Or compress files before uploading

### Issue: Some files fail silently
**Check:**
1. Laravel log: `storage/logs/laravel.log`
2. Look for "Evidence upload failed" entries
3. Check storage disk space: `df -h`

**Fix:**
- Ensure adequate disk space
- Check file permissions on storage directory
- Review error logs for specific failure reasons

---

## API Response Examples

### Successful Upload (All Files)
```
Redirect to: /custody
Flash message: "Successfully uploaded 5 files!"
Flash summary: "All files uploaded successfully. Hash calculations are in progress."
```

### Partial Success
```
Redirect to: /custody
Flash message: "Uploaded 4 of 5 files."
Flash summary: "Some files failed: large-video.mp4"
```

### Complete Failure
```
Redirect to: /evidence/upload (back)
Error: "All file uploads failed. Please try again."
```

### Validation Error
```
Redirect to: /evidence/upload (back)
Error: "You can upload a maximum of 10 files at once."
```

---

## Future Enhancements (Optional)

### Potential Improvements
1. **Per-file metadata:** Allow different title/description for each file
2. **Chunked uploads:** For files > 2 GB, use chunked upload
3. **Resume capability:** Allow resuming interrupted uploads
4. **Drag-and-drop folders:** Upload entire folder structures
5. **Real-time progress:** WebSocket-based progress updates
6. **Thumbnail generation:** Auto-generate thumbnails for images/videos
7. **Duplicate detection:** Check SHA-256 hash before upload to prevent duplicates
8. **Batch operations:** Bulk edit metadata after upload

### Implementation Notes
These enhancements would require:
- Additional JavaScript libraries (e.g., Resumable.js, Uppy)
- WebSocket server (Laravel Echo + Pusher/Socket.io)
- Image processing library (Intervention Image)
- Additional database queries for duplicate detection

---

## Rollback Instructions

If you need to revert to single file upload:

### 1. Restore Original View
```bash
git checkout HEAD~1 -- resources/views/evidence/upload.blade.php
```

### 2. Restore Original Controller
```bash
git checkout HEAD~1 -- app/Http/Controllers/EvidenceController.php
```

### 3. Clear Cache
```bash
php artisan view:clear
php artisan cache:clear
```

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check queue logs: `php artisan queue:work --verbose`
3. Review activity log in admin panel
4. Check database records for evidence/custody/hashes

---

## Changelog

### Version 2.0 (Multiple Upload)
- ✅ Added support for multiple file uploads (up to 10 files)
- ✅ Added total size validation (10 GB limit)
- ✅ Added file preview list with individual remove buttons
- ✅ Added upload progress indicator
- ✅ Added batch metadata to activity logs
- ✅ Added partial success handling
- ✅ Improved error messages and validation
- ✅ Maintained backward compatibility with single file uploads

### Version 1.0 (Single Upload)
- Original single file upload implementation
- UUID-based secure storage
- SHA-256 hash calculation
- Chain of custody tracking
- Activity logging

---

## Conclusion

The multiple file upload feature is now fully integrated and production-ready. All security features, validation, logging, and integrity checks from the original single-file implementation are preserved and enhanced for batch operations.

**Key Benefits:**
- ⚡ Faster evidence collection (upload multiple files at once)
- 🔒 Same security standards maintained
- 📊 Better user experience with progress feedback
- 🔍 Full audit trail for each file
- 💪 Robust error handling and partial success support

**Testing Status:** ✅ Ready for testing
**Production Status:** ✅ Ready for deployment (after testing)
