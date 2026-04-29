# Checkout Form - User Guide

## New Checkout Form Fields

When checking out evidence, you'll now see three fields:

### 1. Purpose (Required) ⭐ NEW
A dropdown menu with four options:

| Purpose | When to Use | Status Change |
|---------|-------------|---------------|
| **Court Presentation** | Evidence is being presented in court proceedings | Active → **Admitted** 🟣 |
| **Lab Analysis** | Evidence needs forensic analysis (fingerprints, DNA, etc.) | Active → In Review 🔵 |
| **Review/Investigation** | Evidence is being reviewed by investigators | Active → In Review 🔵 |
| **Other** | Any other temporary checkout | Active → In Review 🔵 |

### 2. Location (Required)
Where the evidence is being taken:
- Examples: "District Court - Room 3", "Forensic Lab - Building B", "Detective Office 205"

### 3. Notes (Optional)
Additional details about the checkout:
- Examples: "Presenting as Exhibit A in State v. Smith", "Fingerprint analysis required"

## Status Badge Colors

| Status | Color | Meaning |
|--------|-------|---------|
| Pending | 🟡 Yellow | Hash calculation in progress |
| Active | 🟢 Green | Available for custody actions |
| In Review | 🔵 Blue | Currently checked out for analysis/review |
| Admitted | 🟣 Purple | Presented in court - LOCKED |
| Rejected | 🔴 Red | Inadmissible - LOCKED |
| Archived | ⚪ Gray | Long-term storage - LOCKED |

## Important Notes

### 🔒 Locked Statuses
Once evidence reaches **Admitted**, **Rejected**, or **Archived** status, it becomes **locked** and cannot be:
- Transferred
- Checked out
- Checked in
- Modified

### ⚖️ Court Presentation
When you select "Court Presentation" as the purpose:
1. Status immediately changes to **Admitted**
2. Evidence becomes **locked**
3. No further custody actions are allowed
4. This is **permanent** - choose carefully!

### 🔬 Lab Analysis
When you select "Lab Analysis":
1. Status changes to **In Review**
2. Evidence remains unlocked
3. You can check it back in when analysis is complete
4. Status returns to **Active** after check-in

## Workflow Example

### Scenario: Evidence Going to Court

**Step 1: Initial Upload**
- Officer uploads evidence
- Status: **Pending** 🟡

**Step 2: Hash Calculation**
- Background job processes
- Status: **Active** 🟢

**Step 3: Transfer to Detective**
- Officer transfers to Detective
- Status: **Active** 🟢 (unchanged)

**Step 4: Checkout for Lab**
- Detective checks out
- Purpose: "Lab Analysis"
- Location: "Forensic Lab - Building B"
- Status: **In Review** 🔵

**Step 5: Checkin from Lab**
- Detective checks in
- Status: **Active** 🟢

**Step 6: Transfer to Attorney**
- Detective transfers to Attorney
- Status: **Active** 🟢 (unchanged)

**Step 7: Checkout for Court** ⚖️
- Attorney checks out
- Purpose: "**Court Presentation**"
- Location: "District Court - Room 3"
- Status: **Admitted** 🟣 (LOCKED)

**Result:** Complete, unbroken chain with proper status tracking!

## Tips

✅ **DO:**
- Select "Court Presentation" only when actually presenting in court
- Use "Lab Analysis" for forensic testing
- Use "Review/Investigation" for general case work
- Add detailed notes for audit trail

❌ **DON'T:**
- Select "Court Presentation" for testing or practice
- Forget to check evidence back in after lab analysis
- Leave the notes field empty - always document your actions

## Questions?

**Q: Can I undo a court checkout?**
A: No. Once status is "Admitted", the evidence is permanently locked. This ensures court evidence integrity.

**Q: What if I selected the wrong purpose?**
A: If you haven't submitted yet, just change the dropdown. If already submitted and status is "In Review", you can check in and try again. If status is "Admitted", contact your system administrator.

**Q: Do I need to check in evidence after court?**
A: No. Court presentation (Admitted status) is a terminal state. The evidence remains locked for archival purposes.

**Q: Can I transfer locked evidence?**
A: No. Locked evidence cannot be transferred, checked out, or modified in any way.
