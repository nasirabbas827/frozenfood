# frozenfood-final

A lightweight PHP application for managing frozen‑food inventory, complete with email notifications powered by **PHPMailer**.

---

## Overview

`frozenfood-final` provides a simple web interface to:

- Store and retrieve frozen‑food items in a MySQL database.
- Send automated email alerts (e.g., low‑stock warnings) using PHPMailer.
- Export data for reporting or integration with other systems.

The project is built with pure PHP and includes a ready‑to‑import SQL dump (`Database/frozenfood_db.sql`) for quick setup.

---

## Features

| ✅ | Feature |
|---|---------|
| 📦 | **SQL schema** – pre‑populated `frozenfood_db.sql` for immediate use |
| 📧 | **Email notifications** via PHPMailer (multilingual language packs included) |
| 🗂️ | **Modular structure** – clear separation of database, mailer, and business logic |
| 🔧 | **Composer integration** – manage PHPMailer dependencies effortlessly |
| 🌐 | **Configurable** – all sensitive values (API keys, DB credentials) are loaded from environment variables |

---

## Tech Stack

| Component | Technology |
|-----------|------------|
| Language | PHP (≥7.4) |
| Database | MySQL / MariaDB |
| Mailer | PHPMailer (bundled, see `PHPMailer/`) |
| Dependency Management | Composer |
| Front‑end | HTML5 + CSS3 (optional) |
| Hosting | Any LAMP/LEMP stack |

---

## Installation

> **Prerequisites**  
> - PHP 7.4+ with PDO MySQL extension  
> - Composer installed globally (`composer --version`)  
> - MySQL server (or compatible)  

### 1. Clone the repository

```bash
git clone https://github.com/yourusername/frozenfood-final.git
cd frozenfood-final
```

### 2. Install PHP dependencies

```bash
composer install
```

> The `composer.json` located in `PHPMailer/` pulls in the PHPMailer library and its autoloader.

### 3. Set up the database

```bash
# Create a new database (replace <db_name> as desired)
mysql -u root -p -e "CREATE DATABASE frozenfood CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import the schema and sample data
mysql -u root -p frozenfood < Database/frozenfood_db.sql
```

### 4. Configure environment variables

Create a `.env` file in the project root (or configure your server’s environment) with the following keys:

```dotenv
DB_HOST=localhost
DB_NAME=frozenfood
DB_USER=your_db_user
DB_PASS=your_db_password

# PHPMailer settings
MAIL_HOST=smtp.example.com
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=YOUR_OWN_API_KEY
MAIL_PORT=587
MAIL_FROM=your_email@example.com
MAIL_FROM_NAME="FrozenFood Alerts"
```

> **Never** commit the `.env` file to version control.

### 5. (Optional) Set up a virtual host

If you’re using Apache:

```apache
<VirtualHost *:80>
    ServerName frozenfood.local
    DocumentRoot /path/to/frozenfood-final/public

    <Directory /path/to/frozenfood-final/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Restart Apache and add `frozenfood.local` to your hosts file.

---

##