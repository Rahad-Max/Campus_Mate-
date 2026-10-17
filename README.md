# PUB CampusMate — Smart Campus Companion

> **Smart Campus Companion with Academic Planning, Course Registration, and AI Helpdesk**  
> Designed for **Pundra University of Science & Technology (PUB)**

---

## 1. Project Overview

**PUB CampusMate** is a web application engineered for Pundra University of Science & Technology to modernize and streamline undergraduate academic planning, course registration, timetable coordination, and student inquiry handling.

Built using an MVC architecture in **PHP 8.2+** and **MySQL 8+**, the platform replaces fragmented manual registration and paper-based routines with an integrated system supporting role-based workflows for **Students**, **Teachers**, **Academic Advisors**, and **Department Coordinators**.

---

## 2. Technology Stack

### Frontend
- **Markup & Styling**: HTML5, Vanilla CSS3 with customized modern design tokens, and **Bootstrap 5.3**.
- **Icons & Typography**: Bootstrap Icons, Google Fonts (*Plus Jakarta Sans* & *JetBrains Mono*).
- **Interactivity & Dialogs**: Vanilla JavaScript (ES6+), **SweetAlert2** for interactive confirmations, **Chart.js** for academic CGPA charts.

### Backend
- **Core Runtime**: PHP 8.2+ structured MVC architecture.
- **Persistence Layer**: PHP Data Objects (**PDO**) with prepared statements, strict error handling, and UTF-8 charset.
- **Authentication**: Native PHP sessions with secure cookies and server-side role validation.
- **Dependency Management**: Composer (PSR-4 autoloading).

### Database
- **RDBMS**: MySQL 8+ / MariaDB 10.4+.
- **Engine & Charset**: InnoDB storage engine, `utf8mb4` character set, `utf8mb4_unicode_ci` collation.
- **Data Integrity**: Foreign key constraints with cascade behaviors, unique indexes, and MySQL native Full-Text search for AI Helpdesk chunks.

---

## 3. Mandatory Roles & Access Control

| Role | Responsibility | Default Dashboard |
| :--- | :--- | :--- |
| **Student** | Course registration, timetable viewing, transcript inspection, AI Helpdesk queries. | `/dashboard/student` |
| **Teacher** | Assigned section rosters, routine management, faculty directory profile. | `/dashboard/teacher` |
| **Academic Advisor** | Advisee oversight, registration request approval/rejection with prerequisite checks. | `/dashboard/advisor` |
| **Department Coordinator** | Curriculum planning, semester course offerings, scheduling conflict management. | `/dashboard/coordinator` |

Role boundaries are strictly enforced server-side through `RoleMiddleware`. A student or teacher cannot access another role's functionality simply by editing URL endpoints.

---

## 4. Prerequisites

1. **PHP 8.2+** with extensions: `pdo`, `pdo_mysql`, `mbstring`, `json`, `session`.
2. **MySQL 8+** or **MariaDB 10.4+** (e.g., via XAMPP).
3. **Composer 2+** (optional for standalone mode; built-in PSR-4 fallback is included).
4. Modern web browser (Chrome, Firefox, Edge).

---

## 5. Local Setup & Installation (XAMPP Guide)

### Step 1: Clone or Place Project into `htdocs`
Ensure the repository is placed in your local web root:
```text
C:\xampp\htdocs\pub-campusmate
```

### Step 2: Configure Environment (`.env`)
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Open `.env` in your editor and configure your database port and credentials:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307       # Use 3306 or 3307 based on your XAMPP MySQL configuration
DB_DATABASE=pub_campusmate
DB_USERNAME=root
DB_PASSWORD=""
```

### Step 3: Create Database and Import Schema & Seed
You can create the database and import schema and seed demonstration data using MySQL CLI or phpMyAdmin:

#### Option A: Via Command Line (MySQL / MariaDB)
```bash
# 1. Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS pub_campusmate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import Schema (32 tables)
mysql -u root -p pub_campusmate < database/schema.sql

# 3. Import Demonstration Seed Data
mysql -u root -p pub_campusmate < database/seed.sql
```

#### Option B: Via Built-In Verification Script
```bash
php scripts/verify_database.php
```

### Step 4: Install Composer Dependencies (Optional)
```bash
composer dump-autoload -o
```
*(Note: If Composer is not installed on your system, the application includes a standalone PSR-4 autoloader in `public/index.php` that works immediately).*

---

## 6. How to Run the Application

### Option A: Using XAMPP Apache
Start Apache and MySQL from the **XAMPP Control Panel**, then open:
```text
http://localhost/pub-campusmate/public/
```
*(Or `http://localhost/pub-campusmate/` — the root `.htaccess` will automatically redirect to `public/`).*

### Option B: Using PHP Built-In Development Server
You can also run directly from the project directory:
```bash
php -S localhost:8000 -t public
```
Then navigate to:
```text
http://localhost:8000
```

---

## 7. Verification & Diagnostic Commands

Run the built-in diagnostic scripts to ensure your environment and database are functioning correctly:

```bash
# 1. Verify PHP version, required extensions, and directory write permissions:
php scripts/check_environment.php

# 2. Verify MySQL database connectivity, all 32 tables, and sample row counts:
php scripts/verify_database.php
```

---

## 8. Seed Demonstration Accounts

All demonstration accounts use the default password: **`Password@123`**

| Role | Username | Email | Sample Name |
| :--- | :--- | :--- | :--- |
| **Student** | `student21` | `student@pub.ac.bd` | Rahat Rahman (ID: 210101001) |
| **Student (Alt)** | `tamanna22` | `tamanna@pub.ac.bd` | Tamanna Akter (ID: 220101002) |
| **Teacher** | `prof_tanvir` | `teacher@pub.ac.bd` | Engr. Tanvir Ahmed (T-CSE-001) |
| **Academic Advisor** | `advisor_kazi` | `advisor@pub.ac.bd` | Dr. Kazi Nahid (T-CSE-002) |
| **Coordinator** | `coord_hasan` | `coordinator@pub.ac.bd` | Dr. Mahmudul Hasan (T-CSE-003) |

*(Tip: The sign-in page features 1-click quick-fill buttons for instant evaluation without manual typing).*

---

## 9. AI Helpdesk (RAG Foundation)

The AI Academic Helpdesk integrates Retrieval-Augmented Generation (RAG) principles:
1. **Document Storage**: Uploaded academic ordinances are chunked into `document_chunks` with metadata (article number, page reference).
2. **Hybrid Retrieval**: Powered by MySQL `FULLTEXT` natural language searching as a native zero-dependency engine, with support for external vector embeddings (Gemini Embeddings / ChromaDB).
3. **Citations**: The assistant cites specific articles from official university documentation.

---

## 10. Known Limitations & Next Steps

This repository represents **Phase C1: Complete Architecture & Database Foundation**.
- Core routing, controllers, services, models, database schemas, seed data, and role dashboards are established and verifiable.
- Future phases will add:
  - Automated dynamic schedule clash solvers.
  - Multi-semester degree audit calculators.
  - PDF generation for grade reports and registration receipts.
  - Vector embedding ingestion pipelines via external vector databases.
