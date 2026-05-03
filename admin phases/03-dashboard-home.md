# Phase 3: Dashboard Home

## Goal
Provide a clean home screen that helps non-technical admins understand the store status and next actions.

## Deliverables
- Dashboard landing page
- Stats cards:
  - products
  - categories
  - orders
  - carts
  - low-stock variants
- Quick action buttons
- Recent orders widget
- Recent carts widget
- Empty states when there is no data

## UX requirements
- No dense charts in v1
- Use plain labels and plain numbers
- Prioritize quick actions over analytics complexity
- Show “what should I do next?” messaging in empty states

## Technical tasks
- Build dashboard query layer / view model
- Keep counts simple and fast
- Make widgets permission-aware
- Do not show actions the admin cannot use

## Tests
- Authorized admin sees dashboard page
- Empty database renders friendly empty states
- Seeded data produces correct counts
- Restricted admin only sees permitted widgets/actions

## Done when
- A first-time admin can open the dashboard and understand what to click next
