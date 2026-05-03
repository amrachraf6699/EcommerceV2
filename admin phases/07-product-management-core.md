# Phase 7: Product Management Core

## Goal
Create the base product management module before variants/media complexity is added.

## Deliverables
- Product index page
- Search/filter products by:
  - text
  - active status
  - featured status
  - category
- Create product page
- Edit product page
- Category assignment with Tom Select

## UX requirements
- Split long forms into clear sections:
  - basics
  - descriptions
  - categories
  - SEO
  - status
- Sticky save bar on long forms
- Avoid overwhelming technical field names

## Technical tasks
- Product CRUD
- Category multi-select with Tom Select
- Request validation
- Slug generation/editing support
- Filterable index queries

## Tests
- Create product with categories
- Update product
- Duplicate product slug is rejected
- Filters return expected products
- Unauthorized actions are blocked

## Done when
- Admins can create a product record cleanly even before variants and media are managed
