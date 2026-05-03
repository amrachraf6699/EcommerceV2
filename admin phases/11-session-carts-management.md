# Phase 11: Session Carts Management

## Goal
Give admins visibility into active and abandoned carts for support and operational insight.

## Deliverables
- Carts index page
- Filters for:
  - active
  - expired/abandoned
  - item count
  - last activity
- Cart detail page

## UX requirements
- Read-only first
- Show activity recency clearly
- Make abandoned carts understandable without technical jargon
- Use concise subtotal and quantity summaries

## Technical tasks
- Query carts by activity timestamps
- Build cart detail with items and totals
- Define abandoned threshold in one reusable place

## Tests
- Carts index renders
- Active/expired filters work
- Cart detail renders item snapshot correctly
- Unauthorized access is blocked

## Done when
- Admins can inspect carts without needing customer accounts or developer help
