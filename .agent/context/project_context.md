# Project Context: LeaveBoard

## Overview
LeaveBoard is an internal leave-planning application for visualising team availability and processing absence requests. The product centers on a multi-month planner, a profile workspace for the active session user, and a lightweight admin page for operational controls.

## User-Facing Areas

### Planner
- Multi-month timeline covering the selected month and the following two months
- Drag/range selection for request creation
- Department grouping with expandable user lists
- Department, site, and personnel filtering
- Current-user row spotlight and jump-back behavior
- Holiday markers driven by the active user's holiday country

### Profile
- Dedicated `/profile` page for the active session user
- Holiday-country preference management
- Light/dark theme preference management
- Request summaries, request history, and current-month snapshot

### Admin
- Session-based user impersonation
- Application name updates through settings
- Absence-option creation and editing
- User and manager overview
- Request log browsing

## Workflow Rules
- Requests from users with a manager are submitted as pending.
- Requests from users without a manager are approved immediately.
- Multi-day requests are grouped by a shared request UUID.
- Pending requests can be edited or deleted by the request owner before approval.
- Managers can approve or reject requests from direct reports.
- Rejections require a manager decision reason.

## Holidays and Preferences
- Holiday resolution is country-aware.
- `users.holiday_country` controls planner holidays per user.
- `holidays.country_code` scopes stored holiday overrides.
- `users.theme_preference` persists light/dark mode across pages.

## Seeded Environment
- Seeders provide departments, users, manager assignments, absence options, holidays, and sample absence history.
- First visit stores the first available user in session as the current active user.
