# How to Apply Rank 1 Evidence Upload Changes

## 📋 Prerequisites
- Laravel 11 application running
- Access to the codebase
- Terminal/command line access
- Admin access to test the changes

---

## 🚀 Step-by-Step Application

### Step 1: Verify Current State
```bash
cd SDEMS

# Check if files exist
ls -la app/Policies/EvidencePolicy.php
ls -la resources/views/layouts/navigation.blade.php
ls -la resources/views/dashboard.blade.php
ls -la resources/views/custody/index.blade.php
```

**Expected:** All 4 files should exist ✅

---

### Step 2: Backup Current Files (Optional but Recommended)
```bash
# Create backup directory
mkdir -p backups/rank1-changes

# Backup files
cp app/Policies/EvidencePolicy.php backups/rank1-changes/
cp resources/views/layouts/navigation.blade.php backups/rank1-changes/
cp resources/views/dashboard.blade.php backups/rank1-changes/
cp resources/views/custody/index.blade.php backups/rank1-changes/

echo "✅ Backup complete"
```

---

### Step 3: Apply Changes

The changes have already been applied by Kiro. Verify them:

#### 3.1: Check EvidencePolicy.php
```bash
grep -A 5 "public function create" app/Policies/EvidencePolicy.php
```

**Expected output should include:**
```php
return $user->hasMinimumRank(1);
```

#### 3.2: Check Navigation
```bash
grep -B 1 "evidence.create" resources/views/layouts/navigation.blade.php | grep "@if"
```

**Expected output should include:**
```blade
@if(Auth::user()->rank >= 1)
```

#### 3.3: Check Dashboard
```bash
grep -B 1 "Upload Evidence" resources/views/dashboard.blade.php | grep "@if"
```

**Expected output should include:**
```blade
@if($user->rank >= 1)
```

#### 3.4: Check Custody Index
```bash
grep -B 1 "evidence.create" resources/views/custody/index.blade.php | grep "@if"
```

**Expected output should include:**
```blade
@if(auth()->user()->rank >= 1)
```

---

### Step 4: Clear All Caches
```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear compiled views
php artisan view:clear

# Clear route cache (if cached)
php artisan route:clear

# Optional: Clear all caches at once
php artisan optimize:clear

echo "✅ All caches cleared"
```

---

### Step 5: Verify Routes
```bash
# Check evidence routes
php artisan route:list | grep evidence

# Should show:
# GET|HEAD  evidence/upload ........... evidence.create › EvidenceController@create
# POST      evidence .................. evidence.store › EvidenceController@store
# All with middleware: web, auth, verified, account.locked, rank:1
```

---

### Step 6: Run Syntax Checks
```bash
# Check PHP syntax
php -l app/Policies/EvidencePolicy.php

# Check Blade syntax (compile views)
php artisan view:cache

# If no errors, clear the cache
php artisan view:clear

echo "✅ Syntax checks passed"
```

---

### Step 7: Create Test User

**Option A: Using Tinker (Recommended)**
```bash
php artisan tinker
```

Then run:
```php
$user = \App\Models\User::create([
    'name' => 'Test Field Officer',
    'email' => 'field.officer@test.com',
    'password' => bcrypt('password'),
    'rank' => 1,
    'is_active' => true,
    'email_verified_at' => now(),
]);

echo "User created: {$user->email} (Rank {$user->rank})\n";
exit;
```

**Option B: Via Admin Panel**
1. Login as admin
2. Navigate to `/admin/users/create`
3. Fill in:
   - Name: Test Field Officer
   - Email: field.officer@test.com
   - Password: password
   - Rank: 1
   - Active: Yes
4. Click "Create User"

---

### Step 8: Test the Changes

#### Test 1: Login as Rank 1 User
```
URL: /login
Email: field.officer@test.com
Password: password
```

#### Test 2: Verify Navigation
- ✅ Check "Evidence" link appears in top navigation
- ✅ Check "Evidence" link appears in mobile menu (click hamburger icon)

#### Test 3: Verify Dashboard
- ✅ Check "Upload Evidence" card appears on dashboard
- ✅ Click the card to access upload page

#### Test 4: Test Upload
1. Navigate to `/evidence/upload`
2. Fill in the form:
   - **Case Number:** TEST-001
   - **Title:** Test Evidence Upload
   - **Category:** Digital Media
   - **Description:** Testing Rank 1 upload access
   - **File:** Upload any test file (image, PDF, document)
3. Click "Upload Evidence"
4. ✅ Should successfully upload and redirect to evidence detail page

#### Test 5: Verify Restrictions
Try accessing these URLs (should all fail with 403):
- `/custody` → ❌ 403 Forbidden (requires Rank 3)
- `/integrity` → ❌ 403 Forbidden (requires Rank 5)
- `/audit-logs` → ❌ 403 Forbidden (requires Rank 5)
- `/admin/dashboard` → ❌ 403 Forbidden (requires Rank 8)

---

### Step 9: Verify Activity Logs
```bash
php artisan tinker
```

```php
// Check latest evidence upload
$activity = \Spatie\Activitylog\Models\Activity::where('description', 'Evidence uploaded (batch)')
    ->latest()
    ->first();

// Display properties
print_r($activity->properties->toArray());

// Should show:
// 'user_rank' => 1
// 'case_number' => 'TEST-001'
// 'title' => 'Test Evidence Upload'
// etc.

exit;
```

---

### Step 10: Test Auditor Protection

#### Create Rank 5 User (Auditor)
```bash
php artisan tinker
```

```php
$auditor = \App\Models\User::create([
    'name' => 'Test Auditor',
    'email' => 'auditor@test.com',
    'password' => bcrypt('password'),
    'rank' => 5,
    'is_active' => true,
    'email_verified_at' => now(),
]);

echo "Auditor created: {$auditor->email} (Rank {$auditor->rank})\n";
exit;
```

#### Test Auditor Block
1. Logout
2. Login as auditor (auditor@test.com / password)
3. Try to access `/evidence/upload`
4. ✅ Should receive **403 Forbidden** error
5. Check activity log:
   ```bash
   php artisan tinker
   ```
   ```php
   \Spatie\Activitylog\Models\Activity::where('description', 'like', '%BLOCKED%')
       ->latest()
       ->first()
       ->properties;
   // Should show: 'reason' => 'Auditor (rank 5–7) is read-only'
   ```

---

## ✅ Success Checklist

- [ ] All 4 files modified successfully
- [ ] All caches cleared
- [ ] Routes verified with `php artisan route:list`
- [ ] Syntax checks passed
- [ ] Test Rank 1 user created
- [ ] Rank 1 user can see Evidence link in navigation
- [ ] Rank 1 user can see Upload Evidence card on dashboard
- [ ] Rank 1 user can access `/evidence/upload`
- [ ] Rank 1 user can successfully upload evidence
- [ ] Rank 1 user receives 403 on `/custody`, `/integrity`, `/audit-logs`, `/admin`
- [ ] Activity log shows upload with `user_rank: 1`
- [ ] Rank 5 auditor blocked from upload
- [ ] Activity log shows auditor block with reason

---

## 🐛 Troubleshooting

### Issue: Changes not visible after applying

**Solution:**
```bash
# Clear all caches again
php artisan optimize:clear

# Restart web server (if using php artisan serve)
# Press Ctrl+C to stop, then:
php artisan serve

# Or restart PHP-FPM (if using Nginx/Apache)
sudo systemctl restart php8.2-fpm
```

---

### Issue: 403 Forbidden on upload page for Rank 1

**Check 1: Verify user rank**
```bash
php artisan tinker
```
```php
$user = \App\Models\User::where('email', 'field.officer@test.com')->first();
echo "Rank: {$user->rank}\n";
// Should be: 1
```

**Check 2: Verify policy change**
```bash
grep "hasMinimumRank" app/Policies/EvidencePolicy.php | grep "create" -A 2
# Should show: return $user->hasMinimumRank(1);
```

**Check 3: Clear caches again**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

### Issue: Navigation link not appearing

**Check 1: Verify blade file**
```bash
grep -A 1 "evidence.create" resources/views/layouts/navigation.blade.php | grep "@if"
# Should show: @if(Auth::user()->rank >= 1)
```

**Check 2: Clear view cache**
```bash
php artisan view:clear
```

**Check 3: Hard refresh browser**
- Windows/Linux: `Ctrl + Shift + R`
- Mac: `Cmd + Shift + R`

---

### Issue: Upload works but no activity log

**Check 1: Verify Spatie Activity Log is installed**
```bash
php artisan tinker
```
```php
\Spatie\Activitylog\Models\Activity::count();
// Should return a number (not an error)
```

**Check 2: Check database**
```bash
php artisan tinker
```
```php
\DB::table('activity_log')->latest()->first();
```

---

## 🔄 Rollback Instructions

If you need to revert the changes:

### Option A: Using Git (if changes are committed)
```bash
# Revert the commit
git revert <commit-hash>

# Or reset to previous state
git reset --hard HEAD~1

# Clear caches
php artisan optimize:clear
```

### Option B: Manual Rollback (using backups)
```bash
# Restore from backups
cp backups/rank1-changes/EvidencePolicy.php app/Policies/
cp backups/rank1-changes/navigation.blade.php resources/views/layouts/
cp backups/rank1-changes/dashboard.blade.php resources/views/
cp backups/rank1-changes/index.blade.php resources/views/custody/

# Clear caches
php artisan optimize:clear

echo "✅ Rollback complete"
```

### Option C: Manual Revert (change code back)

**In `app/Policies/EvidencePolicy.php`:**
```php
// Change line in create() method:
return $user->hasMinimumRank(3); // Back to 3
```

**In all 3 view files:**
```blade
// Change all instances back to:
@if(Auth::user()->rank >= 3)
// or
@if($user->rank >= 3)
// or
@if(auth()->user()->rank >= 3)
```

Then clear caches:
```bash
php artisan optimize:clear
```

---

## 📊 Verification Commands

### Quick Status Check
```bash
# Check policy
grep -A 2 "public function create" app/Policies/EvidencePolicy.php | grep "hasMinimumRank"

# Check navigation
grep -B 1 "evidence.create" resources/views/layouts/navigation.blade.php | grep "@if" | head -1

# Check dashboard
grep -B 1 "Upload Evidence" resources/views/dashboard.blade.php | grep "@if" | head -1

# Check custody
grep -B 1 "evidence.create" resources/views/custody/index.blade.php | grep "@if" | head -1
```

**Expected output:**
```
return $user->hasMinimumRank(1);
@if(Auth::user()->rank >= 1)
@if($user->rank >= 1)
@if(auth()->user()->rank >= 1)
```

---

## 📚 Additional Resources

- **Full Implementation Details:** `RANK_1_EVIDENCE_UPLOAD_IMPLEMENTATION.md`
- **Quick Testing Guide:** `RANK_1_TESTING_GUIDE.md`
- **Changes Summary:** `RANK_1_CHANGES_SUMMARY.md`

---

## 🎯 Final Verification

Run this complete verification script:

```bash
#!/bin/bash

echo "🔍 Verifying Rank 1 Evidence Upload Changes..."
echo ""

# Check files exist
echo "✓ Checking files..."
test -f app/Policies/EvidencePolicy.php && echo "  ✅ EvidencePolicy.php exists"
test -f resources/views/layouts/navigation.blade.php && echo "  ✅ navigation.blade.php exists"
test -f resources/views/dashboard.blade.php && echo "  ✅ dashboard.blade.php exists"
test -f resources/views/custody/index.blade.php && echo "  ✅ index.blade.php exists"
echo ""

# Check policy change
echo "✓ Checking policy..."
grep -q "hasMinimumRank(1)" app/Policies/EvidencePolicy.php && echo "  ✅ Policy allows Rank 1" || echo "  ❌ Policy still requires Rank 3"
echo ""

# Check view changes
echo "✓ Checking views..."
grep -q "rank >= 1" resources/views/layouts/navigation.blade.php && echo "  ✅ Navigation updated" || echo "  ❌ Navigation not updated"
grep -q "rank >= 1" resources/views/dashboard.blade.php && echo "  ✅ Dashboard updated" || echo "  ❌ Dashboard not updated"
grep -q "rank >= 1" resources/views/custody/index.blade.php && echo "  ✅ Custody index updated" || echo "  ❌ Custody index not updated"
echo ""

echo "✅ Verification complete!"
```

Save as `verify-rank1-changes.sh`, make executable, and run:
```bash
chmod +x verify-rank1-changes.sh
./verify-rank1-changes.sh
```

---

**Status:** ✅ **Ready to Apply**  
**Estimated Time:** 10-15 minutes  
**Difficulty:** Easy  
**Risk Level:** Low (no database changes, easily reversible)

---

*Last Updated: April 29, 2026*  
*Version: 1.0*
