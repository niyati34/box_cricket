# Box Cricket Booking System

[![Live Demo](https://img.shields.io/badge/Live-Demo-green?style=for-the-badge)](https://boxpro.infinityfreeapp.com/)

A modern, full-featured web application for booking box cricket grounds. Built with PHP and MySQL, it offers seamless booking, role-based access, QR check-ins, and a clean, responsive UI.

---

## 🚀 Live Demo

[https://boxpro.infinityfreeapp.com/](https://boxpro.infinityfreeapp.com/)

---

## 📸 Screenshots

| Home Page                                     | Booking Page                                     | Admin Dashboard                                | Mobile View                                     |
| --------------------------------------------- | ------------------------------------------------ | ---------------------------------------------- | ----------------------------------------------- |
| ![Home](uploads/grounds/1475e69250f1875f.jpg) | ![Booking](uploads/grounds/2d98014bd1c87e9a.jpg) | ![Admin](uploads/grounds/b7b104a879c50bd3.jpg) | ![Mobile](uploads/grounds/cd357621adddd1fa.jpg) |

---

## 📝 Description

Box Cricket Booking is a robust, secure, and user-friendly platform to manage ground bookings, users, and schedules. It supports multiple roles (User, Admin, Superadmin), real-time slot availability, QR-based check-ins, and email notifications. Designed for clubs, sports venues, and event organizers.

---

## ✨ Features

- User registration, login, and password hashing
- Browse grounds, view details, and book slots
- Real-time slot availability and conflict prevention
- Admin panel for managing grounds, slots, and bookings
- Superadmin panel for user/role management
- Booking confirmation emails with QR code for check-in
- Mobile-friendly, responsive design
- CSRF protection and secure session management

---

## ⚡ Quick Start

1. Clone the repo and place it in your web root (e.g., `htdocs/box_cricket`).
2. Create a MySQL database and import `schema.sql`.
3. Update `config.php` with your DB credentials and `BASE_URL`.
4. Access the app at `/box_cricket/index.php`.

Default Superadmin:

- Email: `superadmin@example.com`
- Password: `Super@123`

---

## 🛠️ Tech Stack

- PHP 7.4+
- MySQL 5.7+/MariaDB 10.4+
- PHPMailer (for email)
- HTML5, CSS3, JavaScript

---

## 🌐 Website

[https://boxpro.infinityfreeapp.com/](https://boxpro.infinityfreeapp.com/)

---

## 🏷️ Topics

box-cricket booking php mysql qr-code admin-panel sports ground-management webapp

---

## 📦 Releases / Packages / Deployments

- See [Releases](https://github.com/niyati34/box_cricket/releases) for versioned builds.
- Packages and deployments are managed manually or via your preferred CI/CD.

---

## 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

---

## 📄 License

MIT

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
