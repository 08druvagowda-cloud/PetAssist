# PetAssist - Setup Documentation

## Overview
PetAssist is a modern, responsive web application for managing pet health, vaccinations, and appointments. It features a custom glassmorphism design with a dark mode by default.

## Required Software
- PHP 7.4 or newer
- MySQL Server (e.g., via XAMPP, MAMP, WAMP)

## Setup Instructions

1. **Database Setup**:
   - Open phpMyAdmin or your MySQL client.
   - Run the full contents of `database.sql` to create the `petassist` database, tables, and seed data.
   - *Alternative*: Run `mysql -u root < database.sql` from the terminal.

2. **Configuration**:
   - Open `includes/db.php`
   - Adjust `$db_host`, `$db_user`, `$db_pass`, and `$db_name` if your local MySQL settings differ from default XAMPP settings (`localhost`, `root`, `""`).

3. **Running the App**:
   - Place the project folder in your web server's document root (e.g., `htdocs` for XAMPP).
   - *Alternative*: Start PHP's built-in server by running this command in the project root:
     `php -S localhost:8000`
   - Open a browser and navigate to `http://localhost/path/to/pet_web_page` (or `http://localhost:8000`).

## Notes on Features
- **Admin Access**: A default admin is created via the SQL seed.
  - Email: `admin@petassist.com`
  - Password: `admin123`
- **Password Hashing**: Uses PHP's `password_hash()` for security.
- **Rule Engine**: The Health & Mood Tracker assigns points (1-3) to Food, Activity, and Behavior.
- **Smart Vaccinations**: Select a vaccine type (e.g., Rabies) and provide the Last Date; the system automatically computes Next Due Date.

Enjoy managing your pets!
