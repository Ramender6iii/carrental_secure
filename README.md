# Secure Car Rental Management System — v16 Simplified Public Pages + API Backend

## Setup
1. Start Apache and MySQL in XAMPP.
2. Import `sql/schema.sql` in phpMyAdmin. This recreates the `car_rental_secure` coursework database.
3. Copy the extracted `car_rental` folder to `C:\xampp\htdocs\car_rental`.
4. Open `http://localhost/car_rental/public/setup_admin.php` once.
5. Setup creates three demo staff accounts, 10 completed rental transactions using synthetic customer data, and 5 maintenance records (4 completed and 1 open).
6. Delete `public/setup_admin.php` after setup.
7. Open `http://localhost/car_rental/public/`.

## Demo staff accounts
- System Administrator: `admin` / `Admin@123` / `admin@example.test`
- Manager: `manager` / `Manager@123` / `manager@example.test`
- Rental Staff: `staff` / `Staff@123` / `staff@example.test`

The same demo account details, including email addresses, are displayed on both the Login and Forgot Password screens for localhost coursework demonstration.

## Forgot Password — local administrator-assisted workflow
No SMTP or external email service is required.

1. User opens Forgot Password.
2. User enters username and registered email.
3. The application creates a PENDING password-reset request.
4. System Administrator signs in and opens User Accounts.
5. The request appears under Password Reset Requests.
6. After organisational identity verification, the administrator selects Reset Password.
7. The application generates a cryptographically random temporary password and displays it once.
8. Only the password hash is stored.
9. The account is unlocked, failed attempts are cleared, and `must_change_password=1` is set.
10. The user signs in with the temporary password and is forced to choose a new password.
11. Reset request processing is recorded in the audit log.

An administrator cannot process their own reset request; administrator-account recovery requires another authorised administrator or an organisational fallback procedure.

## Security controls
PDO prepared statements, password hashing, CSRF tokens, output encoding, server-side RBAC, session-ID regeneration, account lockout, audit logging, administrator-assisted password recovery with one-time temporary passwords, transaction/state validation, and Manager approval for Rental Staff discounts above 10%.

All customer and account data in the demonstration build are synthetic coursework data.

## Seeded maintenance demonstration data
The fresh database contains five synthetic maintenance records:
1. Toyota Vios — engine oil and filter service — 185.00 — COMPLETED
2. Honda City — front brake pad replacement and inspection — 420.00 — COMPLETED
3. Proton X50 — tyre replacement and wheel alignment — 760.00 — COMPLETED
4. Toyota Innova — battery replacement and electrical system check — 295.00 — COMPLETED
5. Perodua Bezza (JRV1004) — engine diagnostic and cooling-system repair — 540.00 — OPEN

JRV1004 is seeded with car status `MAINTENANCE` because it has the OPEN maintenance record. Completing that record through the application should return the vehicle to `IDLE`, matching the application's vehicle-state model.

These records are demonstration data for coursework screenshots and testing.


## REST API v1 — actual application integration
This version introduces a real PHP JSON API rather than only describing an API in the report.

The authenticated Dashboard page calls `../api/dashboard.php` with JavaScript `fetch()` and renders the returned JSON statistics. The API therefore participates in the running application.

Available read APIs:
- `GET /car_rental/api/dashboard.php` — all authenticated staff
- `GET /car_rental/api/cars.php` — Manager and Rental Staff; Rental Staff receives a reduced field set
- `GET /car_rental/api/rentals.php` — Manager and Rental Staff
- `GET /car_rental/api/maintenance.php` — Manager only
- `GET /car_rental/api/revenue.php` — Manager only

The `API Architecture` page in the application provides a live JSON demonstration. API requests reuse the authenticated PHP session and enforce server-side role checks. The service layer uses PDO/prepared application database access.

This is a coursework-scale service-oriented design. An API improves separation, reuse and future extensibility, but does not by itself guarantee horizontal scalability.


## v10 state-changing API integration
The Transactions page is now a presentation client for the API for the core rental and maintenance workflow.

Actual API-driven actions:
- `POST /api/rentals.php` action `create` — creates a rental/customer transaction.
- `POST /api/rentals.php` action `approve` / `reject` — Manager authorization for exceptional discounts.
- `POST /api/rentals.php` action `complete` — returns the car and changes its state to IDLE.
- `POST /api/maintenance.php` action `create` — creates maintenance and changes an IDLE car to MAINTENANCE.
- `POST /api/maintenance.php` action `complete` — completes maintenance and returns the car to IDLE.

The browser uses JavaScript `fetch()` and receives JSON responses. State-changing API calls require the authenticated session plus a CSRF token supplied in the `X-CSRF-Token` header. Role authorization and business rules are enforced by the API server, not trusted to JavaScript.

The original `transactions.php` no longer performs these state-changing database writes directly.

## v11 interface cleanup
The REST API remains part of the implementation and continues to handle dashboard data and the core rental/maintenance transactions. The standalone API Architecture page and visible technical API labels were removed from the operational staff interface. Staff see business functions only; JavaScript calls the authenticated PHP API in the background.

## v12 UI cleanup
Decorative sidebar icons and the visible coursework footer/message were removed. Business navigation, API integration, security controls and application functions remain unchanged.

## v15 simplified operational interface
Rebuilt directly from v12. Vehicle availability, rental creation, rental records, returns, Manager approval and maintenance are presented through one Operations page. Rental Staff see only operational information permitted by their role; Manager-only controls remain protected by server-side RBAC.

Manager menu: Dashboard, Operations, Reports.
Rental Staff menu: Dashboard, Operations.
Administrator menu: Dashboard, Administration.

The API remains separated internally but the `v1` directory has been removed. Endpoints are now under `/api/`.

## v16 public-page simplification
Obsolete public pages were physically removed after their functions were consolidated. The user-facing application uses a small set of pages while backend responsibilities remain separated through the API.

Primary public pages:
- login.php
- forgot_password.php
- dashboard.php
- operations.php
- reports.php
- administration.php
- change_password.php
- logout.php

Additional support pages are retained only where required by an existing workflow. The API remains under `/api/` with separate dashboard, car, rental, maintenance and revenue services.


## Customer access update
Rental staff may view and complete only rentals they created (rentals.requested_by).
Managers may view and complete all rentals. This policy uses the existing creator
field; staff handover/reassignment would require a separate assignment workflow.
The operations page and GET rentals API apply the same prepared SQL scope.
Return requests validate ownership before any writes and return a generic 404
for inaccessible/missing rentals. Read access and denied return attempts are
recorded in the administrator audit log without copying customer details.
Names and contact details remain available for authorised operational use;
addresses are not returned in rental lists and only the last four licence
characters are retained. Existing output escaping and CSRF checks remain.
Rental creation validates customer lengths, phone characters and calendar dates
on the server as well as the browser. No schema change or reimport is required.

Validation: static path/reference and SQL fixture checks passed. PHP execution,
MySQL integration and browser checks were unavailable in the editing environment.
Before use, verify two separate staff accounts cannot list or return each other's
rentals, a manager can oversee both, and legitimate create/return flows succeed.
This targeted update is not a complete security assessment of the application.
