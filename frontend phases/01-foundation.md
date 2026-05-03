# Phase 1: Frontend Foundation

## Goal
Establish the customer-facing frontend shell and its dedicated design system without touching admin styling behavior.

## Scope
- Add public frontend route structure and a base storefront layout.
- Create dedicated storefront asset entry points, isolated from admin CSS/JS.
- Define the base visual tokens:
  - black/white palette
  - sharp edges only
  - depth and shadow system
  - typography scale
  - motion and hover rules
- Build shared storefront primitives:
  - buttons
  - inputs
  - cards
  - section wrappers
  - badges
  - empty states
- Expose shared storefront data:
  - brand settings
  - navigation categories
  - footer/contact data
  - cart summary

## Deliverables
- Storefront layout renders independently from admin.
- Frontend CSS and JS do not leak into admin pages.
- RTL base layout works on mobile and desktop.
- Shared storefront components are ready for later phases.

## Test Checklist
- Public layout route renders successfully.
- Frontend assets compile successfully.
- No admin page styling changes after frontend asset introduction.
- Mobile and desktop layout shell display correctly in RTL.

## Acceptance
- Frontend layout is usable as the base for all later pages.
- Storefront identity is clearly monochrome, sharp-edged, and modern.
- The foundation is stable enough for navigation and homepage work.
