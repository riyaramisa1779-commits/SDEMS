# Checkout Status Update - Implementation Complete

## Problem
Evidence status was stuck on "Pending" and didn't update to "Admitted" when checked out for court presentation.

## Root Causes Identified

### 1. Queue Worker Not Running
- Evidence uploads create jobs to calculate hashes and update status from "pending" to "active"
- The `CalculateEvidenceHash` job was queued but not processed
- **Solution**: Run `php artisan queue:work` or set `QUEUE_CONNECTION=sync` in `.env`

### 2. Missing Court Checkout Logic
- Checkout action only changed status to "in_review" regardless of purpose
- No way to distinguish between court presentation vs lab analysis
- **Solution**: Added "Purpose" field to checkout form with smart status updates

## Changes Made

### 1. Controller Update (`ChainOfCustodyController.php`)

**Added:**
- New `purpose` validation field (court, lab, review, other)
- Smart status logic:
  - **Court checkout** → Status changes to "admitted"
  - **Other checkouts** → Status changes to "in_review"

```php
// Update status based on checkout purpose
if ($evidence->status === 'active') {
    // If checking out for court, mark as admitted
    if (isset($validated['purpose']) && $validated['purpose'] === 'court') {
        $evidence->update(['status' => 'admitted']);
    } else {
        // Otherwise, mark as in_review (lab analysis, general review, etc.)
        $evidence->update(['status' => 'in_review']);
    }
}
```

### 2. View Updates

**Updated Files:**
- `resources/views/custody/show.blade.php` - Detail page checkout modal
- `resources/views/custody/index.blade.php` - Listing page checkout modal

**Added:**
- Purpose dropdown with 4 options:
  1. **Court Presentation** - Sets status to "Admitted"
  2. **Lab Analysis** - Sets status to "In Review"
  3. **Review/Investigation** - Sets status to "In Review"
  4. **Other** - Sets status to "In Review"
- Helper text explaining status changes
- Required field validation

## Workflow Now Works Correctly

### Complete Chain of Custody Example:

1. **Upload** (Rank 1 Officer)
   - Status: `pending` → Hash job queued

2. **Hash Calculation** (Background Job)
   - Status: `pending` → `active`

3. **Transfer** (Rank 1 → Rank 3 Detective)
   - Status: `active` (unchanged)

4. **Checkout for Lab** (Rank 3 Detective)
   - Purpose: "Lab Analysis"
   - Status: `active` → `in_review`

5. **Checkin from Lab** (Rank 3 Detective)
   - Status: `in_review` → `active`

6. **Transfer to Legal** (Rank 3 → Rank 5 Attorney)
   - Status: `active` (unchanged)

7. **Checkout for Court** (Rank 5 Attorney)
   - Purpose: "Court Presentation"
   - Status: `active` → `admitted` ✅

## Testing Instructions

1. **Ensure queue is processing:**
   ```bash
   # Option 1: Run queue worker
   php artisan queue:work
   
   # Option 2: Use sync driver (development only)
   # In .env: QUEUE_CONNECTION=sync
   ```

2. **Test the workflow:**
   - Upload evidence (status should be "pending")
   - Wait for queue to process (status becomes "active")
   - Checkout with purpose "Court Presentation"
   - Verify status changes to "Admitted"

3. **Verify in UI:**
   - Status badge should show "Admitted" in purple
   - Chain of custody timeline should show the checkout event
   - Evidence should be locked (no further custody actions allowed)

## Status Flow Chart

```
Upload → pending
  ↓ (hash job)
active
  ↓ (checkout: lab/review/other)
in_review
  ↓ (checkin)
active
  ↓ (checkout: court)
admitted (LOCKED - no further changes)
```

## Notes

- **Admitted** and **Archived** statuses are locked - no custody actions allowed
- The `isLocked()` method prevents modifications to locked evidence
- All custody actions are logged in the activity log
- Chain integrity verification still works with the new status flow
