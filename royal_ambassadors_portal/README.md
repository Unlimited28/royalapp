# ROYAL AMBASSADORS OGUN BAPTIST CONFERENCE PORTAL

## 1. Project Overview

This project is a complete, multi-role portal for the Royal Ambassadors of Nigeria, Ogun Baptist Conference. It provides a robust platform for managing members, handling administrative tasks (including finance and event registration), and disseminating information. The system is production-ready and designed with four distinct user roles:

-   **Ambassadors (Members):** The primary users. They can register, log in, take exams, view results, and access site content.
-   **Association Presidents:** Admins with privileges to manage members within their specific association. They can approve new members, upload camp registration files, and handle payment proof uploads.
-   **Finance Panel:** A restricted-access role for managing the financial aspects of the organization, including issuing payment requests and managing vouchers.
-   **Super Admin:** Has full control over the entire system, including user management, all site content, exam creation, financial verification, and system settings.

## 2. Features

-   **Full Role-Based Access Control:** Secure login, registration, and permissions for all four user roles.
-   **Dynamic Public-Facing Website:** A professional and responsive public site (Home, About, Contact, Blog, Gallery) with content that can be fully managed from the admin panel.
-   **Complete Exam & Results System:**
    -   Admin creates exams and questions.
    -   Presidents approve members for participation.
    -   Ambassadors take timed exams.
    -   Automated grading and secure results publication.
-   **Finance & Payment System:**
    -   Presidents can upload proof of payment for various fees.
    -   A dedicated Finance Panel can issue payment requests and manage vouchers.
    -   Super Admins can review and verify all financial transactions.
-   **Camp Registration System:** Presidents can upload Excel files for camp registration, which are then available for Super Admin review.
-   **Content Management System:**
    -   Super Admins can manage all public site text content (e.g., mission, vision, contact details).
    -   A full-featured blog with a WYSIWYG editor.
    -   A dynamic gallery with server-side image optimization.
    -   A system for managing sponsored ads.
-   **Notification System:** Users receive alerts for important events like exam results, membership approval, and payment requests.

## 3. Installation and Setup

To run this project locally, you will need a web server environment that supports PHP and MySQL (e.g., XAMPP, WAMP, MAMP).

1.  **Database Setup:**
    -   Open your MySQL database management tool (e.g., phpMyAdmin).
    -   Create a new database named `royal_ambassadors`.
    -   Import the `database.sql` file located in the root of the project directory. This will create all necessary tables and default settings.

2.  **Configuration:**
    -   All configuration is handled in the `includes/config.php` file.
    -   Update the database credentials and role passcodes in this file if you need to change them from the defaults.
    ```php
    // In includes/config.php
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    // ... etc.
    ```

3.  **Running the Application:**
    -   Place the entire `royal_ambassadors_portal` folder into your web server's root directory (e.g., `htdocs` for XAMPP).
    -   Open your web browser and navigate to `http://localhost/royal_ambassadors_portal/`.

## 4. Login Details & Passcodes

-   **Ambassador:** Register via `register.php`, then log in at `login.php`.
-   **Admin & President:** Register via `admin-signup.php`. Login at `admin/login.php`.
    -   Super Admin Passcode: `ogbc/access/super`
    -   President Passcode: `ogbc/access/president`
-   **Finance Panel:** Register via `finance/signup.php`. Login at `finance/login.php`.
    -   Finance Passcode: `OGBC/ACCESS/FIN`

## 5. File Structure
```
/royal_ambassadors_portal/
│
├── index.php, about.php, contact.php, etc. # Public pages
│
├── admin/                    # Super Admin backend pages
│   ├── login.php
│   ├── blog-management.php
│   ├── gallery-management.php
│   ├── finance-management.php
│   └── ...
│
├── president/                # President-only pages
│   ├── dashboard.php
│   ├── member-approval.php
│   └── camp-registration.php
│
├── finance/                  # Finance Panel pages
│   ├── login.php
│   ├── dashboard.php
│   └── voucher-management.php
│
├── ambassador/               # Ambassador-only pages
│   └── dashboard.php
│
├── includes/                 # Core PHP files
├── assets/                   # CSS, JS, images
├── uploads/                  # User-uploaded content (ignored by git)
└── database.sql              # The database schema
```
