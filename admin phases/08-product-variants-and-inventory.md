# Phase 8: Product Variants and Inventory

## Goal
Add SKU-level management without making the UI feel technical or spreadsheet-heavy.

## Deliverables
- Variant management inside product edit
- Add/edit/remove variant rows
- Default variant selection
- Fields for:
  - name
  - SKU
  - barcode
  - price
  - compare-at price
  - cost price
  - stock quantity
  - active status
- Low-stock indicators

## UX requirements
- Variants should appear as compact cards or rows with clear labels
- Prevent confusing states visually
- Clearly mark which variant is default
- Use plain labels like “Selling price” and “Stock”

## Technical tasks
- Variant CRUD nested under product
- Guard against multiple default variants
- Enforce unique SKU
- Add low-stock query helper for dashboard/widgets

## Tests
- Add variant to product
- Update variant
- Duplicate SKU is rejected
- Only one default variant remains active per product
- Low-stock behavior works

## Done when
- Product selling data can be managed safely at variant level
