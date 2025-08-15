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

## Booking Confirmation Email + QR

- When an admin approves a booking, the system generates a unique `qr_token` and emails the user a confirmation with a QR code link.
- The QR encodes `verify_qr.php?token=...` that admins can open to validate and mark check-in.
- Admins/Superadmins can open `verify_qr.php` on mobile to scan/verify.

### Email Setup (PHPMailer)

**Option 1: Using Composer (Recommended)**
```bash
composer require phpmailer/phpmailer
```

**Option 2: Manual Installation**
```bash
php install_phpmailer.php
```

**Option 3: Download manually**
- Download PHPMailer files to `lib/` directory
- Create `lib/autoload.php` for autoloading

### Email Configuration

The system uses PHPMailer with SMTP. Update `lib/Mailer.php` with your SMTP settings:
- Host: Your SMTP server (e.g., smtp.gmail.com)
- Port: SMTP port (587 for TLS, 465 for SSL)
- Authentication: Username/password if required

Notes:
- Database changes: `bookings.qr_token` (unique) and `bookings.checked_in_at` were added. Run migrations if you already have a DB.
- QR codes are generated via Google Chart API for simplicity.

