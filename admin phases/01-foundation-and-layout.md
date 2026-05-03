# Phase 1: Foundation and Layout

## Goal
Create the base admin shell and shared frontend foundation that all later phases depend on.

## Why this phase exists
Without a stable layout, route group, asset pipeline, and reusable UI patterns, every later admin screen will be inconsistent and expensive to maintain.

## Deliverables
- Admin route group under `/admin`
- Shared Blade layout for all admin pages
- Shared partials for sidebar, topbar, breadcrumbs, alerts, empty states, and error summary
- Tailwind CSS setup
- Vanilla JS admin bootstrap file for shared interactions
- RTL-first base styling
- Theme tokens for colors, spacing, typography, shadows, radius, and field states
- Installation and initialization structure for:
  - Tom Select
  - FilePond
  - Flatpickr

## Pages in this phase
- Admin layout shell only
- Placeholder dashboard page
- Unauthorized page / message handling

## UI/UX requirements
- Arabic-first navigation labels
- RTL sidebar and content flow
- Large click targets
- Obvious primary vs secondary buttons
- Friendly flash messages
- Clear empty-state cards
- Clear validation summary block at top of forms

## Technical tasks
- Define admin route prefix and route name prefix
- Define admin middleware stack
- Create `admin` Blade layout
- Create reusable Blade components/partials for:
  - page header
  - card
  - table wrapper
  - form field label/help/error
  - confirmation modal shell
- Create admin Vite entrypoint and JS initializer by `data-*` hooks
- Configure Tailwind for RTL-aware utility usage

## Tests
- Guest requesting `/admin` is redirected to login
- Authenticated admin can access `/admin`
- Unauthorized admin cannot access a protected admin route
- Shared admin layout renders without errors
- `npm run build` succeeds after adding the admin asset stack

## Done when
- Any future admin page can extend one layout and look consistent immediately
- The shell is readable and usable even before business modules exist
