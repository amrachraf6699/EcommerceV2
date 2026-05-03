# Phase 6: Category Management

## Goal
Allow admins to manage product categories with simple, safe CRUD flows.

## Deliverables
- Category index page
- Search/filter/sort on categories
- Create category page
- Edit category page
- Soft delete action

## UX requirements
- Small and simple category form
- Slug can auto-fill from name but still be editable
- Status and order fields should be easy to understand
- Empty list should suggest creating the first category

## Technical tasks
- Add request validation
- Add slug uniqueness rules
- Add category query filters
- Use soft deletes consistently

## Tests
- Create category
- Update category
- Soft delete category
- Duplicate slug is rejected
- Permission enforcement works

## Done when
- Admins can organize products into categories without technical confusion
