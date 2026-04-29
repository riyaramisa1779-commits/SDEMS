# Module 7: Search & Access Control - Integration Guide

## Overview
Module 7 implements advanced evidence search with strict rank-based access control. Every search query is audited, and users can only see evidence they are authorized to access based on their rank and role.

---

## 🚀 Installation Steps

### 1. Run Database Migrations
```bash
cd SDEMS
php artisan migrate
```

This will add full-text search indexes to the evidence table for improved performance.

### 2. Clear Application Cache
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 3. Verify Routes
```bash
php artisan route:list --name=search
```

You should see:
- `GET /search` → `search.index`
- `GET /search/suggestions` → `search.suggestions`

---

## 🔐 Access Control Matrix

| Rank | Role | Search Access | Visible Evidence |
|------|------|---------------|------------------|
| 1-2 | Field Officer | ✅ Yes | Only evidence they uploaded or are assigned to |
| 3-4 | Senior Investigator | ✅ Yes | Evidence within their assigned cases |
| 5-7 | Legal/Auditor | ✅ Yes | **All evidence** (read-only, global access) |
| 8+ | Admin | ✅ Yes | **All evidence** (full access) |

---

## 🧪 Testing Instructions

### Test 1: Rank 1 (Field Officer) - Limited Access
```bash
# Login as a Rank 1 user
# Navigate to: /search
```

**Expected Behavior:**
- Can only see evidence they uploaded
- Can only see evidence assigned to them
- Cannot see other users' evidence
- Search filters work within their limited scope

**Test Steps:**
1. Login as Field Officer (Rank 1)
2. Go to Search page
3. Verify you only see your own evidence
4. Try searching for case numbers you don't have access to
5. Confirm no unauthorized evidence appears

---

### Test 2: Rank 3 (Senior Investigator) - Case-Scoped Access
```bash
# Login as a Rank 3 user
# Navigate to: /search
```

**Expected Behavior:**
- Can see all evidence within cases they are assigned to (primary or secondary investigator)
- Cannot see evidence from unassigned cases
- All search filters work within their case scope

**Test Steps:**
1. Login as Senior Investigator (Rank 3)
2. Go to Search page
3. Verify you see evidence from your assigned cases
4. Search for evidence from a case you're NOT assigned to
5. Confirm it doesn't appear in results
6. Test advanced filters (date range, category, tags)

---

### Test 3: Rank 5 (Auditor) - Global Read-Only Access
```bash
# Login as a Rank 5 user
# Navigate to: /search
```

**Expected Behavior:**
- Can see **ALL evidence** across all cases
- Read-only access (no create/edit/delete buttons)
- Can use all search filters
- Can view integrity status for all evidence

**Test Steps:**
1. Login as Auditor (Rank 5)
2. Go to Search page
3. Verify you see evidence from ALL cases
4. Test search with various filters
5. Confirm you can view evidence details but cannot edit
6. Check that "View Integrity" link is available

---

### Test 4: Rank 8 (Admin) - Full Access
```bash
# Login as a Rank 8 user
# Navigate to: /search
```

**Expected Behavior:**
- Can see **ALL evidence** across all cases
- Full access (can view, edit, delete)
- All search filters work
- Can perform all actions on evidence

**Test Steps:**
1. Login as Admin (Rank 8)
2. Go to Search page
3. Verify you see all evidence
4. Test all search filters
5. Confirm all action buttons are available

---

## 🔍 Feature Testing

### A. Basic Search
1. Enter text in the search bar
2. Press Enter or click "Search"
3. Verify results match the search term in:
   - Case number
   - Title
   - Description

### B. Real-Time Suggestions
1. Start typing in the search bar (at least 2 characters)
2. Wait 300ms (debounce delay)
3. Verify dropdown appears with suggestions
4. Click a suggestion to navigate to that evidence

### C. Advanced Filters

#### Date Range Filter
1. Click "Show Advanced Filters"
2. Set "Date From" and "Date To"
3. Click "Search"
4. Verify only evidence uploaded within that date range appears

#### Category Filter
1. Open Advanced Filters
2. Select one or more categories (hold Ctrl/Cmd)
3. Click "Search"
4. Verify only evidence with selected categories appears

#### Tags Filter
1. Open Advanced Filters
2. Enter tags (comma-separated): `evidence, forensic, critical`
3. Click "Search"
4. Verify only evidence with matching tags appears

#### Uploaded By Filter
1. Open Advanced Filters
2. Select a user from "Uploaded By" dropdown
3. Click "Search"
4. Verify only evidence uploaded by that user appears

#### Current Custodian Filter
1. Open Advanced Filters
2. Select a user from "Current Custodian" dropdown
3. Click "Search"
4. Verify only evidence assigned to that user appears

#### Status Filter
1. Open Advanced Filters
2. Select one or more statuses (pending, active, in_review, etc.)
3. Click "Search"
4. Verify only evidence with selected statuses appears

#### Integrity Status Filter
1. Open Advanced Filters
2. Select "Verified" or "Pending"
3. Click "Search"
4. Verify only evidence with matching integrity status appears

### D. Clear Filters
1. Apply multiple filters
2. Click "Clear Filters" button
3. Verify all filters are reset
4. Verify all accessible evidence is shown

### E. Search Highlighting
1. Perform a search with a specific term
2. Verify the search term is highlighted in yellow in the results
3. Check that highlighting works in the title column

---

## 📊 Audit Trail Verification

Every search query is logged. To verify:

```bash
# Check activity log in database
php artisan tinker
```

```php
// View recent search queries
Activity::where('log_name', 'evidence_search')
    ->latest()
    ->take(10)
    ->get(['description', 'properties', 'created_at']);
```

**Expected Log Properties:**
- `filters`: Array of applied filters
- `user_rank`: Rank of the user who performed the search
- `ip`: IP address of the request
- `user_agent`: Browser user agent

---

## 🐛 Troubleshooting

### Issue: No search results appear
**Solution:**
1. Check if user has proper rank (minimum rank 1)
2. Verify evidence exists in the database
3. Check access control - user may not have permission to see that evidence
4. Run: `php artisan optimize:clear`

### Issue: Suggestions not appearing
**Solution:**
1. Check browser console for JavaScript errors
2. Verify Alpine.js is loaded
3. Check network tab - `/search/suggestions` should return JSON
4. Ensure search term is at least 2 characters

### Issue: Filters not working
**Solution:**
1. Check that form method is GET (not POST)
2. Verify query parameters are in the URL
3. Check controller's `applySearchFilters()` method
4. Clear browser cache

### Issue: Access control not working
**Solution:**
1. Verify user's rank in database: `User::find($id)->rank`
2. Check if cases table exists and has proper relationships
3. Verify middleware is applied: `php artisan route:list --name=search`
4. Check EvidencePolicy methods

---

## 🔒 Security Verification

### Test Access Control Bypass Attempts

#### Test 1: Direct URL Access
```bash
# As Rank 1 user, try to access evidence you shouldn't see
# Navigate to: /evidence/{uuid-of-restricted-evidence}
```
**Expected:** 403 Forbidden or redirect

#### Test 2: Query Parameter Manipulation
```bash
# Try to inject SQL or manipulate filters
# Example: /search?search='; DROP TABLE evidence; --
```
**Expected:** No SQL injection, safe query execution

#### Test 3: Cross-Rank Evidence Leakage
```bash
# As Rank 3 user, search for evidence from unassigned cases
# Use advanced filters to try to access restricted evidence
```
**Expected:** No evidence from unassigned cases appears

---

## 📈 Performance Testing

### Test with Large Dataset
```bash
# Seed database with test evidence
php artisan db:seed --class=EvidenceSeeder
```

### Verify Search Performance
1. Perform a search with 1000+ evidence records
2. Verify results load in < 2 seconds
3. Check that pagination works correctly
4. Verify indexes are being used:

```sql
EXPLAIN SELECT * FROM evidence 
WHERE case_number LIKE '%TEST%' 
OR title LIKE '%TEST%' 
OR description LIKE '%TEST%';
```

---

## 🎨 UI/UX Verification

### Desktop View
- [ ] Search bar is prominent and easy to find
- [ ] Advanced filters are collapsible
- [ ] Results table is readable and well-formatted
- [ ] Pagination controls are visible
- [ ] Action buttons are clearly labeled
- [ ] Rank badges display correctly

### Mobile View
- [ ] Search bar is responsive
- [ ] Filters work on mobile
- [ ] Table scrolls horizontally if needed
- [ ] Action buttons are accessible
- [ ] Navigation menu includes Search link

### Dark Mode
- [ ] All elements are visible in dark mode
- [ ] Colors have proper contrast
- [ ] Hover states work correctly

---

## 📝 Manual Test Checklist

- [ ] Search page loads without errors
- [ ] Basic search returns correct results
- [ ] Real-time suggestions work
- [ ] Advanced filters can be toggled
- [ ] Date range filter works
- [ ] Category filter works (multi-select)
- [ ] Tags filter works (comma-separated)
- [ ] Uploaded By filter works
- [ ] Current Custodian filter works
- [ ] Status filter works (multi-select)
- [ ] Integrity Status filter works
- [ ] Clear Filters button resets all filters
- [ ] Search term is highlighted in results
- [ ] Pagination works correctly
- [ ] Results count is accurate
- [ ] Rank 1 sees only their evidence
- [ ] Rank 3 sees only assigned case evidence
- [ ] Rank 5 sees all evidence (read-only)
- [ ] Rank 8 sees all evidence (full access)
- [ ] Action buttons (View, Chain, Integrity) work
- [ ] Search queries are logged in activity log
- [ ] No SQL injection vulnerabilities
- [ ] No unauthorized access to evidence
- [ ] Performance is acceptable with large datasets

---

## 🎯 Success Criteria

✅ **Module 7 is successfully integrated when:**

1. All users (Rank 1+) can access the search page
2. Search results respect rank-based access control
3. All search filters work correctly
4. Real-time suggestions appear as user types
5. Search queries are logged for audit trail
6. No unauthorized evidence is visible to any user
7. Performance is acceptable (< 2s for search results)
8. UI is responsive and works on mobile
9. Dark mode is fully supported
10. All manual tests pass

---

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check browser console for JavaScript errors
4. Verify database migrations ran successfully
5. Ensure all dependencies are installed

---

## 🎉 Next Steps

After Module 7 is verified:
1. Train users on advanced search features
2. Monitor search performance in production
3. Review audit logs regularly
4. Consider adding saved search filters (future enhancement)
5. Implement search analytics dashboard (future enhancement)

---

**Module 7 Implementation Complete! 🚀**
