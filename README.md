# Student Information System (SIS) – PHP & MySQL

A web-based Student Information System built with PHP and MySQL. An administrator can manage students, academic records, departments, courses, and system users from one admin panel.

**Live site:** https://sims.qd.je  
**Admin login:** https://sims.qd.je/admin/login.php  
**Repository:** https://github.com/adriancabusas364-prog/student-information-system

## Features

- Administrator login with session handling
- Dashboard with totals for departments, courses, students, and student academics
- Student management (add, edit, view, delete, update status)
- Student academic history (semester, year, school year, status)
- Department and course maintenance
- User list and account management
- System settings (system name, logo, and cover image)

## Tech Stack

| Layer | Technology |
|---|---|
| Server-side | PHP (mysqli) |
| Database | MySQL / MariaDB |
| Front end | AdminLTE, Bootstrap 4, jQuery, DataTables, Font Awesome |
| Web server | Apache (`.htaccess` enabled) |
| Hosting | ByetHost free hosting (VistaPanel) |
| Domain | sims.qd.je, registered with DigitalPlat Domain |
| HTTPS | ZeroSSL 90-day certificate (renew before 2027-01-20) |

## Project Structure

```
admin/        Admin pages and login
build/        Build assets
classes/      PHP classes (DBConnection, Login, Master, Users, SystemSettings)
database/     SQL script for the database
dist/         AdminLTE CSS and JavaScript assets
inc/          Shared header and layout includes
libs/         Third-party libraries
plugins/      Front-end plugins (jQuery, Bootstrap, DataTables, etc.)
uploads/      Logo, cover, and avatar images
user/         Public-facing pages
initialize.php   Base URL and database settings
config.php       Application bootstrap
```

## Local Installation (XAMPP)

1. Copy the project folder into `C:\xampp\htdocs\sis`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. In phpMyAdmin, create a database named `sis_db` and import the SQL script from the `database` folder.
4. Check `initialize.php`:
   ```php
   define('base_url','http://localhost/sis/');
   define('DB_SERVER',"localhost");
   define('DB_USERNAME',"root");
   define('DB_PASSWORD',"");
   define('DB_NAME',"sis_db");
   ```
5. Open http://localhost/sis/admin/ in your browser.

## Deployment (ByetHost + custom domain)

1. Create a MySQL database in the hosting panel and import the SQL script with phpMyAdmin.
2. Edit `initialize.php` with the live `base_url` (`https://sims.qd.je/`) and the host's database server, username, password, and database name.
3. Upload all project files into the `htdocs` folder so that `index.php` sits directly inside it.
4. Make sure the `uploads` folder is writable.
5. Register the domain, set its nameservers to `ns1.byet.org` through `ns5.byet.org`, and add it under **Parked Domains** in VistaPanel.
6. Install an SSL certificate under **SSL/TLS** in VistaPanel, then open the site over HTTPS.

> **Security note:** never commit real database passwords to a public repository. Keep production credentials only on the server.

## Demo Credentials (for evaluators)

| Field | Value |
|---|---|
| Login URL | https://sims.qd.je/admin/login.php |
| Username | `admin` |
| Password | `admin123` |

The account holds demo data only. Change the password after evaluation.

## Credits

- Base application: an open-source PHP/MySQL Student Information System template (original developer tag `dev_oretnom` in `initialize.php`). Credit for the base code belongs to its original author.
- Deployment, domain, DNS, SSL, and documentation: Adrian Cabusas, with AI assistance from Claude (Anthropic) for troubleshooting and drafting.

## Author

Cabusas, Adrian – Capstone Project, Web Development 2
