# Phase 4: Admins, Roles, and Permissions

## Goal
Allow super-admins to manage other admins and control access safely.

## Deliverables
- Admin list page
- Create admin page
- Edit admin page
- Assign role to admin
- Role list/detail page
- Safe activation/deactivation workflow

## UX requirements
- Use role-based UI, not raw permission overload
- Show plain-language descriptions for roles
- Make destructive actions confirm explicitly
- Hide forbidden actions entirely when possible

## Technical tasks
- Build admin CRUD on `users`
- Enforce Spatie permissions on routes and controller actions
- Provide role assignment UI
- Prevent deleting/deactivating the last `super-admin`

## Tests
- Super-admin can create an admin
- Super-admin can assign a role
- Restricted admin cannot access admin-management screens
- Last super-admin cannot be removed or disabled

## Done when
- Admin access control is manageable without touching the database manually
