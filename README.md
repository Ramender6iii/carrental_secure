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
