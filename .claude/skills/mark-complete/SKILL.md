---
name: mark-complete
description: >-
  Automatically marks a feature/phase as complete and updates all project documentation.
  This happens automatically when implementing numbered features (e.g., "implement Phase 3.5").
user_invocable: false
auto_trigger: true
---

# Mark Feature Complete (Automatic)

This skill defines the documentation updates that MUST happen automatically when any numbered feature implementation is completed.

## When This Triggers Automatically

This process triggers automatically when:
- User asks to "implement Phase X.X" or "implement Feature X.X"
- User asks to "complete Phase X.X"
- Any task involving a numbered feature from the Implementation Plan

**DO NOT** wait for the user to ask for documentation updates. After the code is written and tests pass, immediately perform all updates below.

## Required Updates

### 1. Feature Document (`Project Design/Implementation Plan/Features/X.X-*.md`)

Update the feature document:
- Set `**Status:** Complete`
- Add `**Completed:** [Current Month Year]`
- Mark all tasks as `[x]` complete

### 2. Phase Document (`Project Design/Implementation Plan/Phase-XX-*.md`)

Update the parent phase document:
- Update the feature status in the feature list
- Update the phase completion count if all features are done

### 3. README (`Project Design/README.md`)

Update all relevant sections:

1. **Quick Status section** - Update counts and "Currently Active" status
2. **Fully Implemented section** - Add the feature to the list with `**Feature Name (description)**`
3. **Partially Implemented section** - Update or remove the phase entry
4. **Phase Index table** - Update the status column (e.g., `🔵 5/8` or `✅ Complete`)
5. **Feature Summary tables** - Change feature from `⬜ Pending` to `✅ Done`
6. **Recommended Implementation Order** - Move from "Up Next" to "Completed" with strikethrough

### 4. UAT Document (`Project Design/UAT/X.X-*-UAT.md`)

Verify UAT document exists. If not, create it with test cases.

### 5. HIGH_LEVEL_DESIGN.md (`Project Design/HIGH_LEVEL_DESIGN.md`)

If the feature added new:
- User-facing functionality
- Business workflows
- Feature descriptions
- User roles/permissions

Update the relevant sections in HIGH_LEVEL_DESIGN.md to reflect the completed feature.

### 6. LOW_LEVEL_DESIGN.md (`Project Design/LOW_LEVEL_DESIGN.md`)

If the feature added new:
- Database tables/columns
- API endpoints
- Services/Classes

Update the relevant sections in LOW_LEVEL_DESIGN.md.

## Checklist Output

After updates, output a checklist confirming:

```
## Documentation Updated

- [x] Feature document: Status set to Complete
- [x] Phase document: Feature marked complete
- [x] README: Quick Status counts updated
- [x] README: Fully Implemented list updated
- [x] README: Phase Index table updated
- [x] README: Feature Summary table updated
- [x] README: Recommended Order updated
- [x] UAT document: Verified/created
- [x] HIGH_LEVEL_DESIGN.md: Updated (or no changes needed)
- [x] LOW_LEVEL_DESIGN.md: Updated (or no changes needed)
```

## Important Notes

- Always read the current state of documents before editing
- Preserve formatting and style of existing documents
- Use consistent date format: "Month Year" (e.g., "January 2026")
- Increment document version numbers where applicable
