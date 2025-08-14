## Box Cricket Booking (PHP + MySQL)

### Prerequisites
- PHP 7.4+ with PDO MySQL
- MySQL 5.7+/MariaDB 10.4+
- Web server (XAMPP on Windows is fine)

### Setup
1. Create a database `box_cricket` and import `schema.sql`.
2. Update `config.php` DB credentials and `BASE_URL` according to your folder path (e.g., `/box_cricket`).
3. Put the project folder under your web root (e.g., `D:/xampp/htdocs/box_cricket`).
4. Visit `/box_cricket/index.php`.

### Default Access
- Super admin (seeded):
  - Email: `superadmin@example.com`
  - Password: `Super@123` (change immediately)

### Roles
- User: Search and book slots.
- Admin: Manage grounds, slots, and view bookings.
- Superadmin: Manage users (create admins, toggle active, change roles).

### Key Files
- `config.php`: DB connection, session, helpers.
- `lib/Auth.php`: Authentication.
- `partials/header.php`, `partials/footer.php`: Layout.
- Public pages: `index.php`, `grounds.php`, `ground.php`, `make_booking.php`, `login.php`, `register.php`, `my_bookings.php`.
- Admin: `admin/index.php`, `admin/grounds.php`, `admin/slots.php`, `admin/bookings.php`.
- Super Admin: `superadmin/index.php`, `superadmin/users.php`.

### Notes
- CSRF protection for all forms.
- Passwords are hashed using bcrypt.
- Bookings use transactions and row locks to prevent double booking.

