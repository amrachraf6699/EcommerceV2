# Phase 9: Product Media Management

## Goal
Let admins upload and organize product images with a modern drag-and-drop experience.

## Deliverables
- FilePond integration for product images
- Multi-image upload
- Image preview
- Primary image selection
- Delete image action
- Optional variant-linked image association
- Drag-sort / reorder support if implemented in this phase

## UX requirements
- Drag/drop area must be obvious
- Upload errors must be readable in plain language
- Current images must be visible immediately
- Primary image must be visually highlighted

## Technical tasks
- File upload endpoint(s)
- File validation rules
- Product image CRUD
- Primary-image update flow
- Optional sort-order update endpoint

## Tests
- Upload product image
- Reject invalid file type/size
- Mark primary image
- Delete image
- Optional reorder test if ordering ships in this phase

## Done when
- Admins can manage product visuals without touching storage manually
