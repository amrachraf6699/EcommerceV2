# Phase 2: Authentication and Admin Bootstrap

## Goal
Enable admin login/logout and seed the first usable admin accounts, roles, and permissions.

## Deliverables
- Admin login page
- Login controller/actions
- Logout action
- Default seeded `super-admin`
- Base seeded `admin` role
- Profile basics for the logged-in admin:
  - update name
  - update email
  - change password

## UX requirements
- Simple login page with no clutter
- Large input fields and clear validation errors
- Password field with show/hide toggle using vanilla JS
- Obvious success confirmation after password/profile updates

## Technical tasks
- Use existing `users` table as admin auth source
- Keep dashboard auth separate by route prefix and middleware, not by separate table
- Apply Spatie roles to `User`
- Seed first admin users in a safe, environment-aware way
- Restrict admin area to users with admin role/permission

## Tests
- Valid admin credentials log in successfully
- Invalid credentials fail with validation message
- Authenticated admin can log out
- Authenticated admin can update profile
- Authenticated admin can change password
- Non-admin user cannot access admin routes even if authenticated

## Done when
- A seeded admin can sign in and land inside the dashboard safely
