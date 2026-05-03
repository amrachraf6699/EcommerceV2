# Phase 10: Orders Management

## Goal
Provide an easy order back-office for viewing and updating customer orders.

## Deliverables
- Orders index page
- Filters:
  - order status
  - payment status
  - fulfillment status
  - date range
  - text search
- Order detail page
- Status update actions

## UX requirements
- Order list should be scannable first, detailed second
- Detail page should show:
  - customer info
  - addresses
  - item list
  - totals
  - notes
  - statuses
- Use strong visual grouping, not raw dumps of database fields

## Technical tasks
- Build index/detail controllers and views
- Add status update validation
- Preserve order item snapshot display even if product data changes later

## Tests
- Orders index renders
- Filters work as expected
- Order detail renders all stored snapshot info
- Status updates persist correctly
- Permission checks block unauthorized admins

## Done when
- Admins can process and review orders without database access
