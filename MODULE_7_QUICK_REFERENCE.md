# Module 7: Search & Access Control - Quick Reference

## 🚀 Quick Start

```bash
# 1. Run migrations
cd SDEMS
php artisan migrate

# 2. Clear cache
php artisan optimize:clear

# 3. Access search page
# Navigate to: http://your-domain/search
```

---

## 🔐 Access Control Summary

| Rank | Can Search? | Sees What? |
|------|-------------|------------|
| 1-2  | ✅ Yes | Only their own evidence |
| 3-4  | ✅ Yes | Evidence in assigned cases |
| 5-7  | ✅ Yes | **All evidence** (read-only) |
| 8+   | ✅ Yes | **All evidence** (full access) |

---

## 🔍 Search Features

### Basic Search
- Search bar at top of page
- Searches: case number, title, description
- Real-time suggestions (2+ characters)
- Results highlighted in yellow

### Advanced Filters
- **Date Range**: Filter by upload date
- **Category**: Multi-select (document, image, video, etc.)
- **Tags**: Comma-separated (e.g., `evidence, critical`)
- **Uploaded By**: Filter by user
- **Current Custodian**: Filter by assigned user
- **Status**: Multi-select (pending, active, in_review, etc.)
- **Integrity Status**: Verified or Pending

---

## 📋 Testing Checklist

### Quick Test (5 minutes)
- [ ] Login as different ranks (1, 3, 5, 8)
- [ ] Verify each rank sees correct evidence
- [ ] Perform basic search
- [ ] Test one advanced filter
- [ ] Verify search is logged

### Full Test (15 minutes)
- [ ] Test all advanced filters
- [ ] Test real-time suggestions
- [ ] Test pagination
- [ ] Test Clear Filters button
- [ ] Verify action buttons work
- [ ] Test on mobile device
- [ ] Test dark mode

---

## 🐛 Common Issues

| Issue | Solution |
|-------|----------|
| No results | Check user rank and evidence access |
| Suggestions not working | Verify Alpine.js is loaded |
| Filters not applying | Check URL has query parameters |
| Access denied | Verify user has minimum rank 1 |

---

## 📊 Key Files

```
Controllers:
├── EvidenceSearchController.php    # Main search logic

Models:
├── Evidence.php                    # Search scopes added

Views:
├── evidence/search.blade.php       # Search UI

Routes:
├── web.php                         # /search routes

Migrations:
└── *_add_fulltext_indexes_to_evidence_table.php
```

---

## 🔒 Security Features

✅ Rank-based access control enforced  
✅ SQL injection prevention  
✅ All searches logged for audit  
✅ No evidence leakage across ranks  
✅ Policy-based authorization  

---

## 📞 Quick Commands

```bash
# View search routes
php artisan route:list --name=search

# Check recent searches
php artisan tinker
Activity::where('log_name', 'evidence_search')->latest()->take(5)->get();

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed --class=EvidenceSeeder
```

---

## ✅ Success Indicators

- ✅ Search page loads at `/search`
- ✅ Different ranks see different evidence
- ✅ All filters work correctly
- ✅ Suggestions appear in real-time
- ✅ Searches are logged in activity log
- ✅ No unauthorized access possible

---

**Need detailed instructions? See `MODULE_7_INTEGRATION_GUIDE.md`**
