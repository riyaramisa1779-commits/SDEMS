# Quick Reference: Evidence Status Flow

## Status Transitions

```
┌─────────────────────────────────────────────────────────────┐
│                    EVIDENCE STATUS FLOW                      │
└─────────────────────────────────────────────────────────────┘

UPLOAD
  │
  ├─→ [PENDING] 🟡
  │     │
  │     │ (Hash calculation job completes)
  │     ↓
  ├─→ [ACTIVE] 🟢 ←──────────────────┐
  │     │                             │
  │     │ (Checkout: Lab/Review)      │ (Checkin)
  │     ↓                             │
  ├─→ [IN REVIEW] 🔵 ─────────────────┘
  │     │
  │     │ (Checkout: Court)
  │     ↓
  └─→ [ADMITTED] 🟣 (LOCKED - Terminal State)
```

## Checkout Purpose → Status Mapping

| Checkout Purpose | Status Change | Reversible? |
|-----------------|---------------|-------------|
| Court Presentation | Active → **Admitted** 🟣 | ❌ No (Locked) |
| Lab Analysis | Active → **In Review** 🔵 | ✅ Yes (Checkin) |
| Review/Investigation | Active → **In Review** 🔵 | ✅ Yes (Checkin) |
| Other | Active → **In Review** 🔵 | ✅ Yes (Checkin) |

## Action Permissions by Status

| Action | Pending | Active | In Review | Admitted | Archived |
|--------|---------|--------|-----------|----------|----------|
| Transfer | ❌ | ✅ | ✅ | ❌ | ❌ |
| Checkout | ❌ | ✅ | ❌ | ❌ | ❌ |
| Checkin | ❌ | ❌ | ✅ | ❌ | ❌ |
| View | ✅ | ✅ | ✅ | ✅ | ✅ |
| Download | ❌ | ✅ | ✅ | ✅ | ✅ |

## Rank Requirements

| Action | Minimum Rank | Notes |
|--------|--------------|-------|
| Upload | Rank 1 | Field Officers can upload |
| Transfer | Rank 3 | Must be current custodian or Rank 8+ |
| Checkout | Rank 3 | Must be current custodian or Rank 8+ |
| Checkin | Rank 3 | Must be current custodian or Rank 8+ |
| View Chain | Rank 3 | Rank 5+ can view all chains |

## Common Workflows

### 🔬 Lab Analysis Workflow
```
1. Evidence is ACTIVE 🟢
2. Checkout (Purpose: Lab Analysis)
   → Status: IN REVIEW 🔵
3. Lab completes analysis
4. Checkin
   → Status: ACTIVE 🟢
```

### ⚖️ Court Presentation Workflow
```
1. Evidence is ACTIVE 🟢
2. Checkout (Purpose: Court Presentation)
   → Status: ADMITTED 🟣
3. Evidence is now LOCKED
   ❌ No further actions allowed
```

### 🔄 Transfer Workflow
```
1. Evidence is ACTIVE 🟢
2. Transfer to another officer
   → Status: ACTIVE 🟢 (unchanged)
3. New custodian can perform actions
```

## Troubleshooting

### ❓ Status stuck on "Pending"?
**Cause:** Queue worker not running
**Solution:** 
```bash
# Option 1: Run queue worker
php artisan queue:work

# Option 2: Set sync queue (dev only)
# In .env: QUEUE_CONNECTION=sync
```

### ❓ Can't checkout evidence?
**Check:**
- ✅ Evidence status is "Active"
- ✅ You are the current custodian (or Rank 8+)
- ✅ Evidence is not locked (Admitted/Archived)

### ❓ Accidentally marked as "Admitted"?
**Solution:** Contact system administrator
- Admitted status is permanent by design
- Ensures court evidence integrity
- Cannot be reversed through normal UI

## Key Points to Remember

1. **Court = Permanent**: Selecting "Court Presentation" locks the evidence forever
2. **Lab = Temporary**: Lab analysis can be checked back in
3. **Pending → Active**: Happens automatically via background job
4. **Locked Statuses**: Admitted, Rejected, Archived cannot be modified
5. **Chain Integrity**: All actions are logged and linked in the chain of custody

## Status Badge Reference

| Badge | Status | Color | Meaning |
|-------|--------|-------|---------|
| 🟡 | Pending | Yellow | Hash calculation in progress |
| 🟢 | Active | Green | Ready for custody actions |
| 🔵 | In Review | Blue | Temporarily checked out |
| 🟣 | Admitted | Purple | Court evidence - LOCKED |
| 🔴 | Rejected | Red | Inadmissible - LOCKED |
| ⚪ | Archived | Gray | Long-term storage - LOCKED |
