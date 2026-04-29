# Module 7 - Manual Testing Script

## 🧪 Quick Verification Test

Follow these steps to verify Module 7 is working correctly:

---

## Step 1: Verify Routes

```bash
cd SDEMS
php artisan route:list --name=search
```

**Expected Output:**
```
GET|HEAD  search              search.index
GET|HEAD  search/suggestions  search.suggestions
```

✅ **Pass Criteria:** Both routes are listed

---

## Step 2: Start Development Server

```bash
php artisan serve
```

**Expected Output:**
```
INFO  Server running on [http://127.0.0.1:8000]
```

---

## Step 3: Test Search Page Access

### Test 3A: Login as Admin (Rank 8)
1. Open browser: `http://127.0.0.1:8000`
2. Login with admin credentials
3. Navigate to: `http://127.0.0.1:8000/search`

**Expected:**
- ✅ Search page loads without errors
- ✅ Search bar is visible
- ✅ "Show Advanced Filters" button is present
- ✅ Navigation shows "Search" link highlighted
- ✅ All evidence is visible (if any exists)

### Test 3B: Test Basic Search
1. Type "TEST" in the search bar
2. Press Enter or click "Search"

**Expected:**
- ✅ Page reloads with search results
- ✅ URL contains `?search=TEST`
- ✅ Results are filtered (or "No evidence found" message)
- ✅ Search term is highlighted in yellow in results

### Test 3C: Test Real-Time Suggestions
1. Clear the search bar
2. Type at least 2 characters slowly
3. Wait 300ms

**Expected:**
- ✅ Dropdown appears below search bar
- ✅ Suggestions show case number, title, category
- ✅ Clicking a suggestion navigates to that evidence

### Test 3D: Test Advanced Filters
1. Click "Show Advanced Filters"
2. Set "Date From" to 1 month ago
3. Select a category (e.g., "Document")
4. Click "Search"

**Expected:**
- ✅ Filters panel expands
- ✅ URL contains filter parameters
- ✅ Results are filtered correctly
- ✅ Results count updates

### Test 3E: Test Clear Filters
1. With filters applied, click "Clear Filters"

**Expected:**
- ✅ All filters are reset
- ✅ URL returns to `/search`
- ✅ All accessible evidence is shown

---

## Step 4: Test Access Control

### Test 4A: Create Test Users (if not exist)

```bash
php artisan tinker
```

```php
// Create Rank 1 user
$rank1 = User::create([
    'name' => 'Test Field Officer',
    'email' => 'field@test.com',
    'password' => bcrypt('password'),
    'rank' => 1,
    'is_active' => true,
    'email_verified_at' => now(),
]);

// Create Rank 3 user
$rank3 = User::create([
    'name' => 'Test Investigator',
    'email' => 'investigator@test.com',
    'password' => bcrypt('password'),
    'rank' => 3,
    'is_active' => true,
    'email_verified_at' => now(),
]);

// Create Rank 5 user
$rank5 = User::create([
    'name' => 'Test Auditor',
    'email' => 'auditor@test.com',
    'password' => bcrypt('password'),
    'rank' => 5,
    'is_active' => true,
    'email_verified_at' => now(),
]);

exit
```

### Test 4B: Test Rank 1 Access
1. Logout
2. Login as: `field@test.com` / `password`
3. Navigate to: `/search`

**Expected:**
- ✅ Search page loads
- ✅ Only sees evidence they uploaded or are assigned to
- ✅ Cannot see other users' evidence

### Test 4C: Test Rank 3 Access
1. Logout
2. Login as: `investigator@test.com` / `password`
3. Navigate to: `/search`

**Expected:**
- ✅ Search page loads
- ✅ Only sees evidence from assigned cases
- ✅ Cannot see evidence from unassigned cases

### Test 4D: Test Rank 5 Access
1. Logout
2. Login as: `auditor@test.com` / `password`
3. Navigate to: `/search`

**Expected:**
- ✅ Search page loads
- ✅ Sees **ALL evidence** (global access)
- ✅ Can view but not edit evidence
- ✅ "View Integrity" button is visible

---

## Step 5: Test Audit Logging

```bash
php artisan tinker
```

```php
// View recent search queries
Activity::where('log_name', 'evidence_search')
    ->latest()
    ->take(5)
    ->get(['description', 'properties', 'created_at']);
```

**Expected:**
- ✅ Search queries are logged
- ✅ Properties include filters, user_rank, ip
- ✅ Timestamp is accurate

---

## Step 6: Test Mobile Responsiveness

1. Open browser DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select "iPhone 12 Pro" or similar
4. Navigate to `/search`

**Expected:**
- ✅ Search bar is full width
- ✅ Filters are accessible
- ✅ Table scrolls horizontally if needed
- ✅ Action buttons are clickable
- ✅ Navigation menu works

---

## Step 7: Test Dark Mode

1. Click the moon/sun icon in navigation
2. Verify dark mode toggles

**Expected:**
- ✅ Background changes to dark
- ✅ Text is readable
- ✅ Search bar has proper contrast
- ✅ Filters panel is visible
- ✅ Results table is readable

---

## Step 8: Performance Test

### Test 8A: Search Speed
1. Open browser DevTools → Network tab
2. Perform a search
3. Check request timing

**Expected:**
- ✅ Search request completes in < 500ms
- ✅ Page renders quickly
- ✅ No console errors

### Test 8B: Suggestions Speed
1. Open browser DevTools → Network tab
2. Type in search bar
3. Check `/search/suggestions` request

**Expected:**
- ✅ Suggestions request completes in < 100ms
- ✅ Debounce works (only 1 request per 300ms)
- ✅ JSON response is valid

---

## Step 9: Security Test

### Test 9A: SQL Injection Attempt
1. In search bar, type: `'; DROP TABLE evidence; --`
2. Click Search

**Expected:**
- ✅ No error occurs
- ✅ Search treats it as literal text
- ✅ No database damage
- ✅ Results show "No evidence found" or safe results

### Test 9B: XSS Attempt
1. In search bar, type: `<script>alert('XSS')</script>`
2. Click Search

**Expected:**
- ✅ No alert popup
- ✅ Script is escaped in HTML
- ✅ Displayed as plain text

### Test 9C: Authorization Bypass Attempt
1. As Rank 1 user, try to access evidence you shouldn't see
2. Manually navigate to: `/evidence/{uuid-of-restricted-evidence}`

**Expected:**
- ✅ 403 Forbidden or redirect
- ✅ Cannot view restricted evidence
- ✅ Error is logged

---

## ✅ Test Results Summary

| Test | Status | Notes |
|------|--------|-------|
| Routes registered | ⬜ | |
| Search page loads | ⬜ | |
| Basic search works | ⬜ | |
| Real-time suggestions | ⬜ | |
| Advanced filters | ⬜ | |
| Clear filters | ⬜ | |
| Rank 1 access control | ⬜ | |
| Rank 3 access control | ⬜ | |
| Rank 5 access control | ⬜ | |
| Rank 8 access control | ⬜ | |
| Audit logging | ⬜ | |
| Mobile responsive | ⬜ | |
| Dark mode | ⬜ | |
| Performance | ⬜ | |
| SQL injection blocked | ⬜ | |
| XSS prevented | ⬜ | |
| Authorization enforced | ⬜ | |

**Legend:**
- ⬜ Not tested
- ✅ Passed
- ❌ Failed

---

## 🐛 Issue Reporting

If any test fails, document:
1. Test step that failed
2. Expected behavior
3. Actual behavior
4. Error messages (if any)
5. Browser console errors
6. Laravel log errors (`storage/logs/laravel.log`)

---

## 🎉 Success Criteria

**Module 7 is ready for production when:**
- ✅ All tests pass
- ✅ No console errors
- ✅ No Laravel log errors
- ✅ Performance is acceptable
- ✅ Security tests pass
- ✅ Access control works correctly

---

**Testing Date:** _____________  
**Tested By:** _____________  
**Overall Status:** ⬜ Pass / ⬜ Fail  
**Notes:** _____________________________________________
