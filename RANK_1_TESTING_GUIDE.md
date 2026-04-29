# Quick Testing Guide - Rank 1 Evidence Upload

## 🚀 Quick Start

### 1. Create Test User (Choose One Method)

**Method A: Using Tinker (Fastest)**
```bash
cd SDEMS
php artisan tinker
```
```php
\App\Models\User::create([
    'name' => 'Field Officer Test',
    'email' => 'field@test.com',
    'password' => bcrypt('password'),
    'rank' => 1,
    'is_active' => true,
    'email_verified_at' => now(),
]);
exit;
```

**Method B: Via Admin Panel**
1. Login as admin
2. Go to `/admin/users/create`
3. Fill form with Rank = 1
4. Save

---

## ✅ Test Checklist

### Test 1: Upload Access (Should Work)
- [ ] Login as Rank 1 user (field@test.com / password)
- [ ] See "Evidence" link in navigation
- [ ] See "Upload Evidence" card on dashboard
- [ ] Click to access `/evidence/upload`
- [ ] Fill form and upload a test file
- [ ] Successfully upload and see evidence details

### Test 2: View Restrictions (Should Fail)
- [ ] Try to access `/custody` → **403 Error** ✅
- [ ] Try to access `/integrity` → **403 Error** ✅
- [ ] Try to access `/audit-logs` → **403 Error** ✅
- [ ] Try to access `/admin/dashboard` → **403 Error** ✅

### Test 3: Auditor Protection (Should Fail)
- [ ] Create Rank 5 user (auditor)
- [ ] Login as auditor
- [ ] Try to access `/evidence/upload` → **403 Error** ✅

---

## 🔍 Verify Activity Logs

```bash
php artisan tinker
```
```php
// Check latest upload
\Spatie\Activitylog\Models\Activity::where('description', 'Evidence uploaded (batch)')
    ->latest()
    ->first()
    ->properties;

// Should show: user_rank => 1
```

---

## 🧹 Cleanup Test Data

```bash
php artisan tinker
```
```php
// Delete test user
\App\Models\User::where('email', 'field@test.com')->delete();

// Delete test evidence
\App\Models\Evidence::where('case_number', 'TEST-001')->delete();
```

---

## 📊 Expected Results Summary

| Action | Rank 1 | Rank 3 | Rank 5 | Rank 8 |
|--------|--------|--------|--------|--------|
| Upload Evidence | ✅ | ✅ | ❌ | ✅ |
| View Own Evidence | ✅ | ✅ | ✅ | ✅ |
| View All Evidence | ❌ | ✅* | ✅ | ✅ |
| Custody Management | ❌ | ✅* | ❌ | ✅ |
| Integrity Verification | ❌ | ✅* | ✅ | ✅ |
| Audit Logs | ❌ | ❌ | ✅ | ✅ |
| Admin Panel | ❌ | ❌ | ❌ | ✅ |

*Rank 3-4: Scoped to assigned cases only

---

## 🐛 Troubleshooting

**Problem:** Changes not visible
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

**Problem:** 403 on upload page
- Check user rank: `SELECT rank FROM users WHERE email = 'field@test.com';`
- Should be `1`

**Problem:** Navigation link missing
- Hard refresh browser (Ctrl+Shift+R)
- Check blade file changes applied

---

## ✨ Success Criteria

✅ Rank 1 user can upload evidence  
✅ Rank 1 user cannot access custody/integrity/audit  
✅ Rank 5-7 auditors still blocked from upload  
✅ All uploads logged with correct rank  
✅ No breaking changes to existing functionality  

**Status:** Ready for Testing 🎯
