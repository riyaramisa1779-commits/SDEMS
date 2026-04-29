# Multiple File Upload - Step-by-Step Integration & Testing

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ Laravel 11 application running
- ✅ Redis installed and running
- ✅ PHP 8.2+ with required extensions
- ✅ Composer dependencies installed
- ✅ Database migrated
- ✅ Storage directory writable

---

## 🔧 Step 1: Verify PHP Configuration

### Check Current Settings
```bash
php -i | grep upload_max_filesize
php -i | grep post_max_size
php -i | grep max_execution_time
php -i | grep memory_limit
```

### Update php.ini (if needed)
```bash
# Find php.ini location
php --ini

# Edit php.ini (use your actual path)
sudo nano /etc/php/8.2/fpm/php.ini
```

Add/update these values:
```ini
upload_max_filesize = 2048M
post_max_size = 10240M
max_execution_time = 300
memory_limit = 512M
```

### Restart PHP-FPM
```bash
# For Ubuntu/Debian
sudo systemctl restart php8.2-fpm

# For macOS (Homebrew)
brew services restart php@8.2

# Verify changes
php -i | grep upload_max_filesize
```

---

## 🔧 Step 2: Verify Redis Configuration

### Check Redis Status
```bash
# Check if Redis is running
redis-cli ping
# Should return: PONG

# Check Redis connection
redis-cli
> INFO server
> EXIT
```

### Install Redis (if not installed)
```bash
# Ubuntu/Debian
sudo apt-get install redis-server
sudo systemctl start redis
sudo systemctl enable redis

# macOS
brew install redis
brew services start redis

# Windows (WSL recommended)
# Use WSL and follow Ubuntu instructions
```

### Verify Laravel Redis Configuration
```bash
# Check .env file
cat .env | grep QUEUE
cat .env | grep REDIS
```

Should contain:
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 🔧 Step 3: Verify Storage Configuration

### Check Storage Disk Configuration
```bash
cat config/filesystems.php | grep -A 10 "'evidence'"
```

Should contain:
```php
'evidence' => [
    'driver' => 'local',
    'root' => storage_path('app/evidence'),
    'visibility' => 'private',
],
```

### Create Storage Directory
```bash
cd SDEMS
mkdir -p storage/app/evidence
chmod -R 775 storage/app/evidence
```

### Verify Permissions
```bash
ls -la storage/app/
# Should show: drwxrwxr-x evidence
```

---

## 🔧 Step 4: Clear All Caches

```bash
cd SDEMS
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## 🚀 Step 5: Start Development Environment

### Terminal 1: Laravel Development Server
```bash
cd SDEMS
php artisan serve
```

Output should show:
```
INFO  Server running on [http://127.0.0.1:8000].
```

### Terminal 2: Queue Worker
```bash
cd SDEMS
php artisan queue:work redis --tries=3 --verbose
```

Output should show:
```
INFO  Processing jobs from the [default] queue.
```

Keep both terminals running during testing.

---

## 🧪 Step 6: Basic Functionality Test

### Test 1: Single File Upload (Backward Compatibility)

1. **Navigate to upload page:**
   ```
   http://localhost:8000/evidence/upload
   ```

2. **Select ONE file:**
   - Click the upload area
   - Select a small PDF or image (< 10 MB)

3. **Fill metadata:**
   - Case Number: `TEST-2026-001`
   - Category: `Document`
   - Title: `Test Single Upload`
   - Description: `Testing single file upload`
   - Tags: `test, single`

4. **Submit:**
   - Click "Upload 1 File" button
   - Should redirect to evidence detail page
   - Success message: "Successfully uploaded 1 file!"

5. **Verify in database:**
   ```bash
   php artisan tinker
   ```
   ```php
   \App\Models\Evidence::latest()->first();
   // Should show your uploaded evidence
   exit
   ```

6. **Verify queue job:**
   - Check Terminal 2 (queue worker)
   - Should see: "Processing: App\Jobs\CalculateEvidenceHash"
   - Should see: "Processed: App\Jobs\CalculateEvidenceHash"

---

### Test 2: Multiple File Upload (New Feature)

1. **Navigate to upload page:**
   ```
   http://localhost:8000/evidence/upload
   ```

2. **Select MULTIPLE files:**
   - Click the upload area
   - Select 3-5 small files (PDFs, images, etc.)
   - OR drag and drop multiple files

3. **Verify file preview:**
   - All files should appear in the list
   - Each file shows name and size
   - Total count and size displayed
   - Each file has an X button to remove

4. **Test remove file:**
   - Click X on one file
   - File should be removed from list
   - Count and total size should update

5. **Fill metadata:**
   - Case Number: `TEST-2026-002`
   - Category: `Document`
   - Title: `Test Multiple Upload`
   - Description: `Testing multiple file upload`
   - Tags: `test, multiple, batch`

6. **Submit:**
   - Click "Upload X Files" button
   - Progress bar should appear
   - Should redirect to custody index page
   - Success message: "Successfully uploaded X files!"

7. **Verify in database:**
   ```bash
   php artisan tinker
   ```
   ```php
   \App\Models\Evidence::where('case_number', 'TEST-2026-002')->get();
   // Should show all uploaded files
   exit
   ```

8. **Verify queue jobs:**
   - Check Terminal 2
   - Should see multiple "Processing: CalculateEvidenceHash" entries
   - One for each uploaded file

---

## 🧪 Step 7: Validation Tests

### Test 3: Max Files Limit (10 files)

1. Try to select 11 files
2. Expected: Error message "Maximum 10 files allowed"
3. Only first 10 files should be added

### Test 4: Max File Size (2 GB per file)

1. Create a large test file:
   ```bash
   # Create a 2.1 GB test file
   dd if=/dev/zero of=large-file.bin bs=1M count=2150
   ```

2. Try to upload this file
3. Expected: Error message "File too large (max 2 GB per file)"

### Test 5: Max Total Size (10 GB)

1. Select 6 files of ~2 GB each (if available)
2. Expected: Error message "Total size exceeds 10 GB limit"
3. Files should be automatically removed to get under limit

### Test 6: Duplicate Files

1. Select the same file twice
2. Expected: Error message "Already selected"
3. File should only appear once in the list

### Test 7: Invalid File Type

1. Create a test executable:
   ```bash
   echo "test" > test.exe
   ```

2. Try to upload test.exe
3. Expected: Server-side error "File type not permitted"

### Test 8: No Files Selected

1. Don't select any files
2. Fill metadata
3. Click submit
4. Expected: Error "Please select at least one file to upload"

---

## 🧪 Step 8: Database Verification

### Check Evidence Records
```bash
php artisan tinker
```

```php
// Get recent uploads
$evidence = \App\Models\Evidence::latest()->take(10)->get();
foreach ($evidence as $e) {
    echo "{$e->id} | {$e->case_number} | {$e->title} | {$e->status}\n";
}

// Check specific case
$batch = \App\Models\Evidence::where('case_number', 'TEST-2026-002')->get();
echo "Found {$batch->count()} files in batch\n";

exit
```

### Check Chain of Custody
```php
php artisan tinker
```

```php
// Get recent custody records
$custody = \App\Models\ChainOfCustody::latest()->take(10)->get();
foreach ($custody as $c) {
    echo "{$c->evidence_id} | {$c->action} | {$c->timestamp}\n";
}

// Verify each evidence has custody record
$evidence = \App\Models\Evidence::latest()->first();
echo "Custody records: {$evidence->custodyChain->count()}\n";

exit
```

### Check Evidence Hashes
```php
php artisan tinker
```

```php
// Get recent hashes
$hashes = \App\Models\EvidenceHash::latest('generated_at')->take(10)->get();
foreach ($hashes as $h) {
    echo "{$h->evidence_id} | {$h->hash_type} | " . substr($h->hash_value, 0, 16) . "...\n";
}

// Check if all evidence has hashes
$evidence = \App\Models\Evidence::latest()->take(5)->get();
foreach ($evidence as $e) {
    $hashCount = $e->hashes->count();
    echo "{$e->title}: {$hashCount} hash(es)\n";
}

exit
```

---

## 🧪 Step 9: Activity Log Verification

### Via Tinker
```php
php artisan tinker
```

```php
use Spatie\Activitylog\Models\Activity;

// Get recent upload activities
$activities = Activity::where('log_name', 'evidence_upload')
    ->latest()
    ->take(10)
    ->get();

foreach ($activities as $activity) {
    $props = $activity->properties;
    echo "{$activity->description} | ";
    echo "Batch: " . ($props['batch_upload'] ?? 'false') . " | ";
    echo "Index: " . ($props['batch_index'] ?? 'N/A') . "\n";
}

exit
```

### Via Admin Panel (Rank 8+ required)

1. Navigate to: `http://localhost:8000/admin/activity-log`
2. Filter by: `evidence_upload`
3. Verify each upload has:
   - Case number
   - File size
   - MIME type
   - IP address
   - User agent
   - Batch metadata (if multiple files)

---

## 🧪 Step 10: File Storage Verification

### Check Physical Files
```bash
cd SDEMS/storage/app/evidence

# List all stored files
find . -type f

# Check current month's uploads
ls -lh $(date +%Y/%m)/

# Verify UUID naming
ls -lh $(date +%Y/%m)/ | head -5
# Should show files like: 9a7b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p.pdf
```

### Verify File Integrity
```bash
php artisan tinker
```

```php
$evidence = \App\Models\Evidence::latest()->first();

// Check if file exists
$exists = \Storage::disk('evidence')->exists($evidence->file_path);
echo "File exists: " . ($exists ? 'YES' : 'NO') . "\n";

// Check file size matches
$storedSize = \Storage::disk('evidence')->size($evidence->file_path);
echo "Stored size: {$storedSize} bytes\n";
echo "DB size: {$evidence->file_size} bytes\n";
echo "Match: " . ($storedSize === $evidence->file_size ? 'YES' : 'NO') . "\n";

exit
```

---

## 🧪 Step 11: Error Handling Test

### Test Partial Failure

1. **Simulate storage failure:**
   ```bash
   # Make evidence directory read-only temporarily
   chmod 555 storage/app/evidence
   ```

2. **Try to upload multiple files:**
   - Should fail with storage error
   - Check logs: `tail -f storage/logs/laravel.log`

3. **Restore permissions:**
   ```bash
   chmod 775 storage/app/evidence
   ```

4. **Verify error handling:**
   - Failed uploads should be logged
   - User should see error message
   - No partial evidence records should be created

---

## 🧪 Step 12: Performance Test

### Test with Maximum Files (10)

1. Select 10 files (various sizes, total < 10 GB)
2. Fill metadata
3. Submit
4. Monitor:
   - Upload time (should be reasonable)
   - Queue processing time
   - Memory usage: `php artisan queue:work --memory=256`

### Test with Large Files

1. Create test files:
   ```bash
   dd if=/dev/zero of=test-1gb.bin bs=1M count=1024
   dd if=/dev/zero of=test-2gb.bin bs=1M count=2048
   ```

2. Upload both files
3. Monitor queue worker for hash calculation time
4. Verify both files process successfully

---

## 🧪 Step 13: User Experience Test

### Test Drag and Drop

1. Open upload page
2. Drag 3-5 files from file explorer
3. Drop onto upload area
4. Verify:
   - Visual feedback during drag (blue border)
   - All files added to list
   - No duplicates

### Test Clear All

1. Select 5 files
2. Click "Clear All" button
3. Verify:
   - All files removed from list
   - File input cleared
   - Can select new files

### Test Progress Bar

1. Select multiple files
2. Click upload
3. Verify:
   - Progress bar appears
   - Percentage updates
   - Submit button disabled during upload

---

## ✅ Step 14: Final Verification Checklist

Run through this checklist:

- [ ] Single file upload works
- [ ] Multiple file upload works (2-10 files)
- [ ] File preview list displays correctly
- [ ] Individual file removal works
- [ ] Clear all files works
- [ ] Drag and drop works
- [ ] Max files validation (10) works
- [ ] Max file size validation (2 GB) works
- [ ] Max total size validation (10 GB) works
- [ ] Duplicate detection works
- [ ] Invalid file type rejected
- [ ] Progress bar displays
- [ ] Success message shows correct count
- [ ] Evidence records created in database
- [ ] Chain of custody records created
- [ ] Hash jobs dispatched and processed
- [ ] Activity logs created with batch metadata
- [ ] Files stored with UUID names
- [ ] Files stored in year/month directories
- [ ] Redirect works (single → detail, multiple → index)
- [ ] Partial failure handled gracefully
- [ ] Error messages clear and helpful

---

## 🚀 Step 15: Production Deployment

### Before Deploying

1. **Update production php.ini:**
   ```bash
   sudo nano /etc/php/8.2/fpm/php.ini
   # Update upload_max_filesize, post_max_size, etc.
   sudo systemctl restart php8.2-fpm
   ```

2. **Configure queue worker (systemd):**
   ```bash
   sudo nano /etc/systemd/system/laravel-worker.service
   ```

   ```ini
   [Unit]
   Description=Laravel Queue Worker
   After=network.target

   [Service]
   User=www-data
   Group=www-data
   Restart=always
   ExecStart=/usr/bin/php /var/www/sdems/artisan queue:work redis --tries=3 --timeout=120

   [Install]
   WantedBy=multi-user.target
   ```

   ```bash
   sudo systemctl enable laravel-worker
   sudo systemctl start laravel-worker
   sudo systemctl status laravel-worker
   ```

3. **Configure web server (nginx example):**
   ```nginx
   server {
       # ... other config ...
       
       client_max_body_size 10240M;
       client_body_timeout 300s;
       
       # ... other config ...
   }
   ```

   ```bash
   sudo nginx -t
   sudo systemctl reload nginx
   ```

4. **Set storage permissions:**
   ```bash
   sudo chown -R www-data:www-data /var/www/sdems/storage
   sudo chmod -R 775 /var/www/sdems/storage
   ```

5. **Run migrations (if any):**
   ```bash
   php artisan migrate --force
   ```

6. **Clear caches:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### After Deploying

1. **Test in production:**
   - Upload single file
   - Upload multiple files
   - Verify queue processing
   - Check logs for errors

2. **Monitor:**
   ```bash
   # Watch queue
   sudo journalctl -u laravel-worker -f
   
   # Watch Laravel logs
   tail -f /var/www/sdems/storage/logs/laravel.log
   
   # Watch nginx logs
   tail -f /var/log/nginx/error.log
   ```

---

## 🆘 Troubleshooting Guide

### Issue: "Maximum upload size exceeded"
**Solution:**
1. Check PHP settings: `php -i | grep upload_max_filesize`
2. Check web server settings (nginx: `client_max_body_size`)
3. Restart PHP-FPM and web server

### Issue: Queue jobs not processing
**Solution:**
1. Check Redis: `redis-cli ping`
2. Check queue worker: `ps aux | grep queue:work`
3. Restart queue worker: `php artisan queue:restart`
4. Check failed jobs: `php artisan queue:failed`

### Issue: Files not appearing in storage
**Solution:**
1. Check permissions: `ls -la storage/app/evidence`
2. Check disk space: `df -h`
3. Check Laravel logs: `tail -f storage/logs/laravel.log`

### Issue: Hash not calculated
**Solution:**
1. Check queue worker is running
2. Check Redis connection
3. Manually dispatch job: `php artisan tinker` → `CalculateEvidenceHash::dispatch($evidenceId);`

---

## 📞 Support

If you encounter issues:

1. **Check logs:**
   - `storage/logs/laravel.log`
   - Queue worker output (Terminal 2)
   - Web server error logs

2. **Check database:**
   - Evidence records
   - Chain of custody records
   - Evidence hashes
   - Activity log

3. **Check storage:**
   - File exists on disk
   - Permissions correct
   - Disk space available

4. **Test components individually:**
   - Upload single file
   - Check queue manually
   - Verify Redis connection

---

## ✅ Success Criteria

Your implementation is successful when:

1. ✅ Single file upload works (backward compatible)
2. ✅ Multiple file upload works (2-10 files)
3. ✅ All validations work correctly
4. ✅ Files stored securely with UUID names
5. ✅ Hash jobs process successfully
6. ✅ Chain of custody created for each file
7. ✅ Activity logs contain batch metadata
8. ✅ Error handling works (partial failures)
9. ✅ UI is responsive and user-friendly
10. ✅ No security vulnerabilities introduced

---

**Congratulations!** 🎉

You have successfully integrated and tested the multiple file upload feature. The system is now ready for production use.

**Next Steps:**
- Train users on the new feature
- Monitor performance in production
- Gather user feedback
- Consider future enhancements (see MODULE_3_MULTIPLE_UPLOAD_GUIDE.md)

---

**Last Updated:** April 25, 2026
**Version:** 2.0
