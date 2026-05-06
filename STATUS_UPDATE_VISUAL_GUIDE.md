# Visual Guide: Status Updates

## Before vs After

### ❌ BEFORE (Broken)

```
┌─────────────────────────────────────────────────┐
│  Upload Evidence                                 │
│  Status: PENDING 🟡                             │
│  ↓                                               │
│  [Queue job queued but never runs]              │
│  ↓                                               │
│  Status: PENDING 🟡 (stuck forever)             │
│  ↓                                               │
│  User clicks "Checkout for Court"               │
│  ↓                                               │
│  Status: PENDING 🟡 (still stuck!)              │
│                                                  │
│  ❌ PROBLEM: Status never changes!              │
└─────────────────────────────────────────────────┘
```

### ✅ AFTER (Fixed)

```
┌─────────────────────────────────────────────────┐
│  Upload Evidence                                 │
│  Status: PENDING 🟡                             │
│  ↓ (< 2 seconds)                                │
│  [Hash job runs immediately - sync queue]       │
│  ↓                                               │
│  Status: ACTIVE 🟢                              │
│  ↓                                               │
│  User clicks "Checkout for Court"               │
│  Purpose: "Court Presentation"                  │
│  ↓                                               │
│  Status: ADMITTED 🟣                            │
│                                                  │
│  ✅ SUCCESS: Status updates correctly!          │
└─────────────────────────────────────────────────┘
```

## UI Changes

### Pending Evidence (New)

```
┌────────────────────────────────────────────────────┐
│  Evidence Details                                   │
│  ┌──────────────────────────────────────────────┐  │
│  │  ⟳ Hash Calculation in Progress              │  │
│  │                                               │  │
│  │  Evidence is being processed. Custody        │  │
│  │  actions will be available once status       │  │
│  │  changes to Active.                          │  │
│  └──────────────────────────────────────────────┘  │
│                                                     │
│  ❌ Transfer button (hidden)                       │
│  ❌ Checkout button (hidden)                       │
│  ❌ Checkin button (hidden)                        │
└────────────────────────────────────────────────────┘
```

### Active Evidence

```
┌────────────────────────────────────────────────────┐
│  Evidence Details                                   │
│  ┌──────────────────────────────────────────────┐  │
│  │  Custody Actions                              │  │
│  │                                               │  │
│  │  ✅ Transfer Custody                         │  │
│  │     Assign to another officer                │  │
│  │                                               │  │
│  │  ✅ Check Out                                │  │
│  │     For court, lab, or review                │  │
│  │                                               │  │
│  │  ✅ Check In                                 │  │
│  │     Return evidence to storage               │  │
│  └──────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────┘
```

### Checkout Modal (Updated)

```
┌────────────────────────────────────────────────────┐
│  Check Out Evidence                          [X]    │
├────────────────────────────────────────────────────┤
│                                                     │
│  Purpose *                                          │
│  ┌──────────────────────────────────────────────┐  │
│  │ Select purpose...                        ▼  │  │
│  └──────────────────────────────────────────────┘  │
│  Options:                                           │
│  • Court Presentation → Status: Admitted            │
│  • Lab Analysis → Status: In Review                 │
│  • Review/Investigation → Status: In Review         │
│  • Other → Status: In Review                        │
│                                                     │
│  Court: Status → "Admitted" | Other: Status → "In Review"
│                                                     │
│  Location *                                         │
│  ┌──────────────────────────────────────────────┐  │
│  │ e.g. Court Room 3, Forensic Lab             │  │
│  └──────────────────────────────────────────────┘  │
│                                                     │
│  Notes                                              │
│  ┌──────────────────────────────────────────────┐  │
│  │ Additional details...                        │  │
│  │                                              │  │
│  └──────────────────────────────────────────────┘  │
│                                                     │
│              [Cancel]  [Check Out]                  │
└────────────────────────────────────────────────────┘
```

## Status Badge Colors

| Status | Badge | When |
|--------|-------|------|
| Pending | 🟡 PENDING | Just uploaded, hash calculating |
| Active | 🟢 ACTIVE | Ready for custody actions |
| In Review | 🔵 IN REVIEW | Checked out for lab/review |
| Admitted | 🟣 ADMITTED | Presented in court (LOCKED) |
| Rejected | 🔴 REJECTED | Inadmissible (LOCKED) |
| Archived | ⚪ ARCHIVED | Long-term storage (LOCKED) |

## Complete Workflow Example

```
┌─────────────────────────────────────────────────────────────┐
│  STEP 1: UPLOAD                                              │
│  Officer Mike uploads USB drive evidence                     │
│  Status: PENDING 🟡                                         │
│  Time: 0 seconds                                             │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 2: HASH CALCULATION (Automatic)                        │
│  System calculates SHA-256 hash                              │
│  Status: PENDING 🟡 → ACTIVE 🟢                            │
│  Time: 1-2 seconds                                           │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 3: TRANSFER                                            │
│  Officer Mike → Detective Sarah                              │
│  Status: ACTIVE 🟢 (unchanged)                              │
│  Time: 2 hours later                                         │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 4: CHECKOUT FOR LAB                                    │
│  Detective Sarah checks out                                  │
│  Purpose: "Lab Analysis"                                     │
│  Location: "Forensic Lab - Building B"                       │
│  Status: ACTIVE 🟢 → IN REVIEW 🔵                          │
│  Time: Next day                                              │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 5: CHECKIN FROM LAB                                    │
│  Detective Sarah checks in                                   │
│  Notes: "3 fingerprints found"                               │
│  Status: IN REVIEW 🔵 → ACTIVE 🟢                          │
│  Time: Same day, 7 hours later                               │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 6: TRANSFER TO LEGAL                                   │
│  Detective Sarah → Attorney John                             │
│  Status: ACTIVE 🟢 (unchanged)                              │
│  Time: Next day                                              │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 7: CHECKOUT FOR COURT ⚖️                              │
│  Attorney John checks out                                    │
│  Purpose: "Court Presentation" ⭐                           │
│  Location: "District Court - Room 3"                         │
│  Status: ACTIVE 🟢 → ADMITTED 🟣                           │
│  Time: Trial day                                             │
│                                                              │
│  ✅ EVIDENCE NOW LOCKED - NO FURTHER CHANGES ALLOWED        │
└─────────────────────────────────────────────────────────────┘
```

## Key Points

### ⚡ Immediate Processing
- Hash calculation runs **immediately** (< 2 seconds)
- No need to wait for queue worker
- Status changes from pending to active automatically

### 🔒 Pending Protection
- Cannot checkout pending evidence
- Action buttons hidden until active
- Clear message explains why

### 🎯 Purpose-Based Status
- **Court** → Admitted (permanent)
- **Lab/Review/Other** → In Review (temporary)

### 🛡️ Locked States
Once admitted, evidence is **permanently locked**:
- ❌ Cannot transfer
- ❌ Cannot checkout
- ❌ Cannot checkin
- ❌ Cannot modify
- ✅ Can view
- ✅ Can download

## Troubleshooting

### Problem: Status stuck on pending
**Check**: Is `QUEUE_CONNECTION=sync` in `.env`?
**Fix**: 
```bash
# Update .env
QUEUE_CONNECTION=sync

# Clear cache
php artisan config:clear
```

### Problem: Can't checkout evidence
**Check**: Is status "active"? (not "pending")
**Fix**: Wait 1-2 seconds after upload for hash to complete

### Problem: Status doesn't change to admitted
**Check**: Did you select "Court Presentation" as purpose?
**Fix**: Purpose field is now required - must select correct option
