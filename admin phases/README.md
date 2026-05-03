# Admin Dashboard Phases

This directory breaks the admin dashboard implementation into small, testable phases.

Goals of this plan:
- Keep each phase small enough to implement and verify safely.
- Prioritize an Arabic-first, RTL, low-tech-admin experience.
- Use Blade + vanilla JS only.
- Standardize on Tailwind CSS, Tom Select, and FilePond.
- Require tests in every phase before moving forward.

Implementation order:
1. `01-foundation-and-layout.md`
2. `02-authentication-and-admin-bootstrap.md`
3. `03-dashboard-home.md`
4. `04-admins-roles-permissions.md`
5. `05-settings-management.md`
6. `06-category-management.md`
7. `07-product-management-core.md`
8. `08-product-variants-and-inventory.md`
9. `09-product-media-management.md`
10. `10-orders-management.md`
11. `11-session-carts-management.md`
12. `12-ux-hardening-and-polish.md`

Cross-phase standards:
- Every admin route must be behind auth and permission checks.
- Every page must work in RTL first.
- Every CRUD phase must include:
  - index page
  - create page
  - edit page
  - request validation
  - success/error flash messaging
  - permission-aware buttons and actions
- Every phase must pass:
  - `php artisan test`
  - `npm run build`

Frontend library standards:
- Tailwind CSS: layout, spacing, visual system, responsive design
- Tom Select: searchable single/multi-select fields
- FilePond: file and image uploads
- Flatpickr: date filtering where useful

Do not move to the next phase unless:
- all tests of the current phase pass
- the phase routes are permission-protected
- empty states exist where needed
- validation messaging is understandable for non-technical admins
