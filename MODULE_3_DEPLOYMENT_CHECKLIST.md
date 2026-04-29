# 📋 Multiple File Upload - Deployment Checklist

## 🎯 Purpose

This checklist ensures a smooth deployment of the multiple file upload feature to production. Follow each step in order and check off items as you complete them.

---

## ✅ Pre-Deployment Checklist

### 1. Environment Verification

- [ ] **PHP Version**
  ```bash
  php -v
  # Should be 8.2 or higher
  ```

- [ ] **Laravel Version**
  ```bash
  php artisan --version
  # Should be 11.x
  ```

- [ ] **Redis Status**
  ```bash
  redis-cli ping
  # Should return: PONG
  ```

- [ ] **Composer Dependencies**
  ```bash
  composer install --no-dev --optimize-autoloader
  ```

### 2. PHP Configuration

- [ ] **Check current settings**
  ```bash
  php -i | grep upload_max_filesize
  php -i | grep post_max_size
  php -i | grep max_execution_time
  php -i | grep memory_limit
  ```

- [ ] **Update php.ini** (if needed)
  ```ini
  upload_max_filesize = 2048M
  post_max_size = 10240M
  max_execution_time = 300
  memory_limit = 512M
  ```

- [ ] **Restart PHP-FPM**
  ```bash
  sudo systemctl restart php8.2-fpm
  ```

- [ ] **Verify changes**
  ```bash
  php -i | grep upload_max_filesize
  # Should show: 2048M
  ```

### 3. Web Server Configuration

#### For Nginx

- [ ] **Update nginx.conf**
  ```nginx
  http {
      client_max_body_size 10240M;
      client_body_timeout 300s;
      proxy_read_timeout 300s;
  }
  ```

- [ ] **Test configuration**
  ```bash
  sudo nginx -t
  ```

- [ ] **Reload nginx**
  ```bash
  sudo systemctl reload nginx
  ```

#### For Apache

- [ ] **Update .htaccess or httpd.conf**
  ```apache
  LimitRequestBody 10737418240
  Timeout 300
  ```

- [ ] **Restart Apache**
  ```bash
  sudo systemctl restart apache2
  ```

### 4. Storage Configuration

- [ ] **Create evidence directory**
  ```bash
  mkdir -p storage/app/evidence
  ```

- [ ] **Set permissions**
  ```bash
  chmod -R 775 storage/app/evidence
  chown -R www-data:www-data storage/app/evidence
  ```

- [ ] **Verify permissions**
  ```bash
  ls -la storage/app/
  # Should show: drwxrwxr-x evidence
  ```

- [ ] **Check disk space**
  ```bash
  df -h
  # Ensure adequate space for evidence files
  ```

### 5. Queue Configuration

- [ ] **Verify .env settings**
  ```env
  QUEUE_CONNECTION=redis
  REDIS_HOST=127.0.0.1
  REDIS_PASSWORD=null
  REDIS_PORT=6379
  ```

- [ ] **Test Redis connection**
  ```bash
  php artisan tinker
  ```
  ```php
  \Illuminate\Support\Facades\Redis::connection()->ping();
  // Should return: true
  exit
  ```

- [ ] **Create systemd service** (if not exists)
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

- [ ] **Enable and start queue worker**
  ```bash
  sudo systemctl enable laravel-worker
  sudo systemctl start laravel-worker
  sudo systemctl status laravel-worker
  ```

### 6. File Verification

- [ ] **Verify updated files exist**
  ```bash
  ls -l resources/views/evidence/upload.blade.php
  ls -l app/Http/Controllers/EvidenceController.php
  ```

- [ ] **Check for syntax errors**
  ```bash
  php -l app/Http/Controllers/EvidenceController.php
  # Should show: No syntax errors detected
  ```

- [ ] **Verify file permissions**
  ```bash
  chmod 644 resources/views/evidence/upload.blade.php
  chmod 644 app/Http/Controllers/EvidenceController.php
  ```

### 7. Cache Management

- [ ] **Clear all caches**
  ```bash
  php artisan config:clear
  php artisan cache:clear
  php artisan view:clear
  php artisan route:clear
  ```

- [ ] **Rebuild caches** (production only)
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

---

## 🧪 Testing Checklist

### 1. Single File Upload (Backward Compatibility)

- [ ] **Navigate to upload page**
  ```
  https://your-domain.com/evidence/upload
  ```

- [ ] **Select ONE file** (< 10 MB)

- [ ] **Fill metadata**
  - Case Number: TEST-SINGLE-001
  - Category: Document
  - Title: Test Single Upload
  - Description: Testing backward compatibility

- [ ] **Submit form**

- [ ] **Verify redirect** to evidence detail page

- [ ] **Verify success message**
  - "Successfully uploaded 1 file!"

- [ ] **Check database**
  ```bash
  php artisan tinker
  ```
  ```php
  \App\Models\Evidence::where('case_number', 'TEST-SINGLE-001')->first();
  // Should show the uploaded evidence
  exit
  ```

- [ ] **Check queue processing**
  ```bash
  sudo journalctl -u laravel-worker -f
  # Should see: Processing: App\Jobs\CalculateEvidenceHash
  ```

- [ ] **Verify file storage**
  ```bash
  ls -lh storage/app/evidence/$(date +%Y/%m)/
  # Should show the uploaded file with UUID name
  ```

### 2. Multiple File Upload (New Feature)

- [ ] **Navigate to upload page**

- [ ] **Select MULTIPLE files** (3-5 files, various types)

- [ ] **Verify file preview list**
  - All files displayed
  - File names shown
  - File sizes shown
  - Total count and size displayed
  - Remove buttons visible

- [ ] **Test remove file**
  - Click X on one file
  - Verify file removed from list
  - Verify count/size updated

- [ ] **Fill metadata**
  - Case Number: TEST-MULTI-001
  - Category: Document
  - Title: Test Multiple Upload
  - Description: Testing multiple file upload
  - Tags: test, multiple, batch

- [ ] **Submit form**

- [ ] **Verify progress bar** appears

- [ ] **Verify redirect** to custody index page

- [ ] **Verify success message**
  - "Successfully uploaded X files!"

- [ ] **Check database**
  ```bash
  php artisan tinker
  ```
  ```php
  \App\Models\Evidence::where('case_number', 'TEST-MULTI-001')->get();
  // Should show all uploaded files
  exit
  ```

- [ ] **Verify batch metadata in activity log**
  ```php
  php artisan tinker
  ```
  ```php
  use Spatie\Activitylog\Models\Activity;
  $activities = Activity::where('log_name', 'evidence_upload')
      ->latest()
      ->take(5)
      ->get();
  foreach ($activities as $a) {
      $props = $a->properties;
      echo "Batch: " . ($props['batch_upload'] ?? 'false') . "\n";
  }
  exit
  ```

### 3. Validation Tests

- [ ] **Test max files (10)**
  - Try to select 11 files
  - Verify error: "Maximum 10 files allowed"

- [ ] **Test max file size (2 GB)**
  - Try to upload file > 2 GB
  - Verify error: "File too large"

- [ ] **Test max total size (10 GB)**
  - Select files totaling > 10 GB
  - Verify error: "Total size exceeds 10 GB limit"

- [ ] **Test duplicate files**
  - Select same file twice
  - Verify error: "Already selected"

- [ ] **Test invalid file type**
  - Try to upload .exe or .bat file
  - Verify error: "File type not permitted"

- [ ] **Test no files selected**
  - Don't select any files
  - Try to submit
  - Verify error: "Please select at least one file"

### 4. Integration Tests

- [ ] **Verify evidence records created**
  ```sql
  SELECT id, case_number, title, status 
  FROM evidence 
  ORDER BY created_at DESC 
  LIMIT 10;
  ```

- [ ] **Verify chain of custody records**
  ```sql
  SELECT evidence_id, action, to_user_id, timestamp 
  FROM chain_of_custody 
  ORDER BY timestamp DESC 
  LIMIT 10;
  ```

- [ ] **Verify evidence hashes**
  ```sql
  SELECT evidence_id, hash_type, LEFT(hash_value, 16) as hash_preview 
  FROM evidence_hashes 
  ORDER BY generated_at DESC 
  LIMIT 10;
  ```

- [ ] **Verify activity logs**
  ```sql
  SELECT log_name, description, subject_id, 
         JSON_EXTRACT(properties, '$.batch_upload') as batch 
  FROM activity_log 
  WHERE log_name = 'evidence_upload' 
  ORDER BY created_at DESC 
  LIMIT 10;
  ```

### 5. Error Handling Tests

- [ ] **Test storage failure**
  - Temporarily make storage read-only
  - Try to upload
  - Verify error message
  - Restore permissions

- [ ] **Test queue failure**
  - Stop queue worker
  - Upload files
  - Verify files uploaded but status = pending
  - Start queue worker
  - Verify status changes to active

- [ ] **Test partial failure**
  - Simulate failure for one file
  - Verify partial success message
  - Verify successful files created
  - Verify failed file not created

---

## 🔍 Monitoring Checklist

### 1. Queue Monitoring

- [ ] **Check queue worker status**
  ```bash
  sudo systemctl status laravel-worker
  # Should show: active (running)
  ```

- [ ] **Monitor queue in real-time**
  ```bash
  sudo journalctl -u laravel-worker -f
  ```

- [ ] **Check failed jobs**
  ```bash
  php artisan queue:failed
  # Should show: No failed jobs
  ```

### 2. Log Monitoring

- [ ] **Watch Laravel logs**
  ```bash
  tail -f storage/logs/laravel.log
  ```

- [ ] **Check for errors**
  ```bash
  grep -i error storage/logs/laravel.log | tail -20
  ```

- [ ] **Check for warnings**
  ```bash
  grep -i warning storage/logs/laravel.log | tail -20
  ```

### 3. Storage Monitoring

- [ ] **Check disk usage**
  ```bash
  df -h
  du -sh storage/app/evidence
  ```

- [ ] **Count uploaded files**
  ```bash
  find storage/app/evidence -type f | wc -l
  ```

- [ ] **Check file permissions**
  ```bash
  find storage/app/evidence -type f ! -perm 644
  # Should return nothing
  ```

### 4. Database Monitoring

- [ ] **Check evidence count**
  ```sql
  SELECT COUNT(*) FROM evidence;
  ```

- [ ] **Check hash completion rate**
  ```sql
  SELECT 
      COUNT(*) as total,
      SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
      SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
  FROM evidence;
  ```

- [ ] **Check activity log entries**
  ```sql
  SELECT COUNT(*) 
  FROM activity_log 
  WHERE log_name = 'evidence_upload' 
  AND created_at > NOW() - INTERVAL 1 DAY;
  ```

---

## 📊 Performance Checklist

### 1. Upload Performance

- [ ] **Test single file upload time**
  - Upload 10 MB file
  - Record time: _____ seconds

- [ ] **Test multiple file upload time**
  - Upload 5 files (50 MB total)
  - Record time: _____ seconds

- [ ] **Test large file upload**
  - Upload 1 GB file
  - Record time: _____ seconds

### 2. Queue Performance

- [ ] **Test hash calculation time**
  - Upload 100 MB file
  - Check time from upload to status=active
  - Record time: _____ seconds

- [ ] **Test multiple hash calculations**
  - Upload 5 files
  - Check time for all to complete
  - Record time: _____ seconds

### 3. Server Performance

- [ ] **Monitor CPU usage during upload**
  ```bash
  top
  # Check PHP-FPM and queue worker CPU usage
  ```

- [ ] **Monitor memory usage**
  ```bash
  free -h
  # Check available memory
  ```

- [ ] **Monitor disk I/O**
  ```bash
  iostat -x 1
  # Check disk write speed
  ```

---

## 🔒 Security Checklist

### 1. File Security

- [ ] **Verify UUID filenames**
  ```bash
  ls storage/app/evidence/$(date +%Y/%m)/
  # Should show UUID-based names, not original names
  ```

- [ ] **Verify private storage**
  ```bash
  curl https://your-domain.com/storage/evidence/2026/04/test.pdf
  # Should return 404 or 403
  ```

- [ ] **Verify file permissions**
  ```bash
  ls -la storage/app/evidence/$(date +%Y/%m)/
  # Should show: -rw-r--r-- (644)
  ```

### 2. Access Control

- [ ] **Test unauthenticated access**
  - Logout
  - Try to access /evidence/upload
  - Should redirect to login

- [ ] **Test rank-based access**
  - Login as rank 0 user
  - Try to access /evidence/upload
  - Should show access denied

- [ ] **Test authenticated access**
  - Login as rank 1+ user
  - Access /evidence/upload
  - Should show upload form

### 3. Validation Security

- [ ] **Test MIME type validation**
  - Try to upload .exe file
  - Should be rejected

- [ ] **Test file size validation**
  - Try to upload > 2 GB file
  - Should be rejected

- [ ] **Test total size validation**
  - Try to upload > 10 GB total
  - Should be rejected

### 4. Audit Trail

- [ ] **Verify activity logs**
  - Upload files
  - Check activity_log table
  - Verify all uploads logged

- [ ] **Verify chain of custody**
  - Upload files
  - Check chain_of_custody table
  - Verify custody records created

- [ ] **Verify evidence hashes**
  - Upload files
  - Wait for queue processing
  - Check evidence_hashes table
  - Verify hashes created

---

## 📱 User Acceptance Testing

### 1. User Interface

- [ ] **Test on desktop browser**
  - Chrome
  - Firefox
  - Safari
  - Edge

- [ ] **Test on tablet**
  - iPad
  - Android tablet

- [ ] **Test on mobile**
  - iPhone
  - Android phone

- [ ] **Test dark mode**
  - Enable dark mode
  - Verify UI readable

### 2. User Experience

- [ ] **Test drag and drop**
  - Drag files from desktop
  - Drop on upload area
  - Verify files added

- [ ] **Test file removal**
  - Select multiple files
  - Remove individual files
  - Verify list updates

- [ ] **Test clear all**
  - Select multiple files
  - Click "Clear All"
  - Verify all files removed

- [ ] **Test progress bar**
  - Upload files
  - Verify progress bar appears
  - Verify percentage updates

### 3. Error Messages

- [ ] **Test validation errors**
  - Trigger each validation error
  - Verify error message clear
  - Verify error message helpful

- [ ] **Test server errors**
  - Simulate server error
  - Verify error message displayed
  - Verify user can retry

---

## 📝 Documentation Checklist

- [ ] **README reviewed**
  - [MODULE_3_MULTIPLE_UPLOAD_README.md](MODULE_3_MULTIPLE_UPLOAD_README.md)

- [ ] **Implementation guide reviewed**
  - [MODULE_3_MULTIPLE_UPLOAD_GUIDE.md](MODULE_3_MULTIPLE_UPLOAD_GUIDE.md)

- [ ] **Quick reference reviewed**
  - [MODULE_3_QUICK_REFERENCE.md](MODULE_3_QUICK_REFERENCE.md)

- [ ] **Integration steps reviewed**
  - [MODULE_3_INTEGRATION_STEPS.md](MODULE_3_INTEGRATION_STEPS.md)

- [ ] **User training materials prepared**

- [ ] **Admin documentation updated**

---

## 🎓 Training Checklist

### 1. User Training

- [ ] **Training session scheduled**

- [ ] **Training materials prepared**
  - Screenshots
  - Step-by-step guide
  - Video tutorial (optional)

- [ ] **Users trained**
  - Field Officers (Rank 1-2)
  - Investigators (Rank 3-4)
  - Auditors (Rank 5-7)
  - Admins (Rank 8+)

- [ ] **Feedback collected**

### 2. Admin Training

- [ ] **Configuration training**
  - PHP settings
  - Queue worker
  - Monitoring

- [ ] **Troubleshooting training**
  - Common issues
  - Log analysis
  - Queue management

- [ ] **Maintenance training**
  - Regular checks
  - Performance monitoring
  - Storage management

---

## 🚀 Go-Live Checklist

### 1. Final Verification

- [ ] **All tests passed** (24/24)

- [ ] **All documentation complete** (7 documents)

- [ ] **All users trained**

- [ ] **Backup created**
  ```bash
  php artisan backup:run
  ```

- [ ] **Rollback plan prepared**

### 2. Deployment

- [ ] **Deploy to production**
  ```bash
  git pull origin main
  composer install --no-dev --optimize-autoloader
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

- [ ] **Restart services**
  ```bash
  sudo systemctl restart php8.2-fpm
  sudo systemctl restart nginx
  sudo systemctl restart laravel-worker
  ```

- [ ] **Verify deployment**
  - Test single file upload
  - Test multiple file upload
  - Check logs for errors

### 3. Post-Deployment

- [ ] **Monitor for 1 hour**
  - Watch logs
  - Watch queue
  - Check for errors

- [ ] **Monitor for 24 hours**
  - Daily check
  - Review metrics
  - Collect feedback

- [ ] **Monitor for 1 week**
  - Weekly review
  - Performance analysis
  - User feedback

---

## ✅ Sign-Off

### Deployment Team

- [ ] **Developer:** _________________ Date: _______
- [ ] **QA Tester:** _________________ Date: _______
- [ ] **System Admin:** ______________ Date: _______
- [ ] **Project Manager:** ___________ Date: _______

### Approval

- [ ] **Technical Lead:** _____________ Date: _______
- [ ] **Security Officer:** ___________ Date: _______
- [ ] **Operations Manager:** _________ Date: _______

---

## 📞 Emergency Contacts

| Role | Name | Contact |
|------|------|---------|
| Developer | __________ | __________ |
| System Admin | __________ | __________ |
| Database Admin | __________ | __________ |
| Security Officer | __________ | __________ |

---

## 🔄 Rollback Plan

If issues occur after deployment:

1. **Stop queue worker**
   ```bash
   sudo systemctl stop laravel-worker
   ```

2. **Restore previous files**
   ```bash
   git checkout HEAD~1 -- resources/views/evidence/upload.blade.php
   git checkout HEAD~1 -- app/Http/Controllers/EvidenceController.php
   ```

3. **Clear caches**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

4. **Restart services**
   ```bash
   sudo systemctl restart php8.2-fpm
   sudo systemctl restart nginx
   sudo systemctl start laravel-worker
   ```

5. **Verify rollback**
   - Test single file upload
   - Check logs

---

**Deployment Date:** _______________  
**Deployment Time:** _______________  
**Deployed By:** _______________  
**Status:** ⬜ Pending | ⬜ In Progress | ⬜ Complete | ⬜ Rolled Back

---

**Good luck with your deployment! 🚀**
