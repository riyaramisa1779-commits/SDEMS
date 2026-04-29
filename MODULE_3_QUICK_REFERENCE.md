# Multiple File Upload - Quick Reference

## 🚀 Quick Start

### Start Development Environment
```bash
cd SDEMS
php artisan serve
php artisan queue:work redis --tries=3
```

### Test Upload
1. Navigate to: `http://localhost:8000/evidence/upload`
2. Select multiple files (max 10)
3. Fill metadata
4. Click "Upload X Files"

---

## 📋 Validation Limits

| Rule | Limit | Error Message |
|------|-------|---------------|
| Max files | 10 | "Maximum 10 files allowed" |
| Max file size | 2 GB | "File too large (max 2 GB per file)" |
| Max total size | 10 GB | "Total size exceeds 10 GB limit" |
| Min files | 1 | "Please select at least one file" |

---

## 🔧 Configuration

### PHP Settings (php.ini)
```ini
upload_max_filesize = 2048M
post_max_size = 10240M
max_execution_time = 300
memory_limit = 512M
```

### Laravel Queue (.env)
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 📁 File Structure

```
SDEMS/
├── app/
│   └── Http/
│       └── Controllers/
│           └── EvidenceController.php    # Updated store() method
├── resources/
│   └── views/
│       └── evidence/
│           └── upload.blade.php          # Multiple file UI
└── storage/
    └── app/
        └── evidence/                     # Private storage
            └── YYYY/
                └── MM/
                    └── {uuid}.{ext}      # Stored files
```

---

## 🎯 Key Code Snippets

### Frontend: File Selection
```html
<input type="file" 
       name="files[]" 
       multiple 
       accept=".pdf,.jpg,.png,..."
       @change="handleFileSelect($event)">
```

### Backend: Validation
```php
$validated = $request->validate([
    'files'   => ['required', 'array', 'min:1', 'max:10'],
    'files.*' => ['required', 'file', 'max:2097152'],
    // ... other rules
]);
```

### Backend: Process Files
```php
foreach ($files as $index => $file) {
    $storedName = Str::uuid() . '.' . $file->getClientOriginalExtension();
    Storage::disk('evidence')->putFileAs($yearMonth, $file, $storedName);
    
    $evidence = Evidence::create([...]);
    CalculateEvidenceHash::dispatch($evidence->id);
    activity('evidence_upload')->log('Evidence uploaded (batch)');
}
```

---

## 🧪 Testing Commands

### Database Checks
```sql
-- Recent uploads
SELECT id, case_number, title, original_name, status 
FROM evidence 
ORDER BY created_at DESC LIMIT 10;

-- Hash status
SELECT e.title, eh.hash_type, eh.hash_value 
FROM evidence e 
LEFT JOIN evidence_hashes eh ON e.id = eh.evidence_id 
ORDER BY e.created_at DESC LIMIT 10;
```

### Queue Monitoring
```bash
# Watch queue in real-time
php artisan queue:work redis --verbose

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### Clear Cache
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

---

## 🐛 Common Issues

### Issue: Upload fails silently
**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
chmod -R 775 storage/app/evidence
chown -R www-data:www-data storage/app/evidence
```

### Issue: Hash jobs not running
**Solution:**
```bash
# Check Redis
redis-cli ping

# Restart queue
php artisan queue:restart
php artisan queue:work redis
```

### Issue: File size limit exceeded
**Solution:**
```bash
# Check PHP limits
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Update php.ini and restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

---

## 📊 Activity Log Properties

Each upload creates a log entry with:
```json
{
  "case_number": "CASE-2026-001",
  "title": "Evidence Title - filename.pdf",
  "category": "document",
  "file_size": 1048576,
  "mime_type": "application/pdf",
  "original_name": "filename.pdf",
  "user_rank": 3,
  "ip": "127.0.0.1",
  "user_agent": "Mozilla/5.0...",
  "batch_upload": true,
  "batch_index": 1,
  "batch_total": 5
}
```

---

## 🔐 Security Checklist

- ✅ UUID-based filenames (no original names in storage)
- ✅ Private storage disk (not web-accessible)
- ✅ MIME type validation
- ✅ File size limits enforced
- ✅ Total size validation
- ✅ Rank-based access control
- ✅ Activity logging for all uploads
- ✅ Chain of custody auto-created
- ✅ SHA-256 hash for integrity
- ✅ Try-catch error handling

---

## 🎨 UI Components (Alpine.js)

### File Preview List
```javascript
selectedFiles: [],  // Array of File objects
totalSize: computed(() => sum of all file sizes),
removeFile(index): removes file at index,
clearAllFiles(): removes all files,
formatBytes(bytes): human-readable size
```

### Upload Progress
```javascript
isUploading: false,
uploadProgress: 0,  // 0-100
startUpload(): sets isUploading = true
```

---

## 📦 Dependencies

### Required
- Laravel 11
- Alpine.js (included in Breeze)
- Tailwind CSS (included in Breeze)
- Redis (for queue)
- Spatie Laravel Activitylog

### Optional
- Laravel Horizon (queue monitoring)
- Laravel Telescope (debugging)

---

## 🚦 Status Flow

```
Upload → Pending → Active → In Review → Admitted/Archived
         ↓
    Hash Job Dispatched
         ↓
    Hash Calculated
         ↓
    Status → Active
```

---

## 📞 Support Resources

- **Laravel Docs:** https://laravel.com/docs/11.x
- **Alpine.js Docs:** https://alpinejs.dev
- **Tailwind CSS:** https://tailwindcss.com
- **Spatie Activitylog:** https://spatie.be/docs/laravel-activitylog

---

## ✅ Pre-Deployment Checklist

- [ ] PHP settings updated (upload_max_filesize, post_max_size)
- [ ] Redis installed and running
- [ ] Queue worker configured (systemd/supervisor)
- [ ] Storage permissions set correctly
- [ ] .env configured (QUEUE_CONNECTION=redis)
- [ ] Tested with various file types
- [ ] Tested with max file count (10)
- [ ] Tested with max file size (2 GB)
- [ ] Tested with max total size (10 GB)
- [ ] Verified activity logs
- [ ] Verified chain of custody
- [ ] Verified hash calculation
- [ ] Tested partial failure scenario

---

## 🎯 Key Routes

| Route | Method | Purpose |
|-------|--------|---------|
| `/evidence/upload` | GET | Show upload form |
| `/evidence` | POST | Process upload |
| `/evidence/{id}` | GET | View evidence detail |
| `/custody` | GET | List all evidence |
| `/custody/{id}` | GET | View custody chain |

---

## 💡 Tips

1. **For large files:** Increase `max_execution_time` in php.ini
2. **For many files:** Run multiple queue workers
3. **For debugging:** Use `--verbose` flag with queue:work
4. **For monitoring:** Install Laravel Horizon
5. **For testing:** Use small files first, then test with large files

---

**Last Updated:** April 25, 2026
**Version:** 2.0 (Multiple Upload)
