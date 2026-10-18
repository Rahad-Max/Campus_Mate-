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

Run the built-in diagnostic scripts to ensure your environment, database, authentication, and security layers are functioning correctly:

```bash
# 1. Verify PHP version, required extensions, and directory write permissions:
php scripts/check_environment.php

# 2. Verify MySQL database connectivity, all 32 tables, and sample row counts:
php scripts/verify_database.php

# 3. Seed / Verify demonstration accounts (including inactive test user):
php scripts/seed_demo_accounts.php

# 4. Run the full automated test suite (25 Unit & Integration tests):
php tests/run_tests.php
```

---

## 8. Seed Demonstration Accounts

All demonstration accounts use the default password: **`Password@123`**  
*(Passwords are securely hashed using `password_hash()` with `PASSWORD_BCRYPT` cost 10).*

| Role | Username | Email | University ID | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Student** | `student21` | `student@pub.ac.bd` | 210101001 | Regular enrolled student, Batch 21 CSE |
| **Student (Alt)** | `tamanna22` | `tamanna@pub.ac.bd` | 220101002 | Regular student, Batch 22 CSE |
| **Teacher** | `prof_tanvir` | `teacher@pub.ac.bd` | T-CSE-001 | Assistant Professor, teaches algorithms & discrete math |
| **Academic Advisor** | `advisor_kazi` | `advisor@pub.ac.bd` | T-CSE-002 | Associate Professor, oversees advisees & registration queue |
| **Coordinator** | `coord_hasan` | `coordinator@pub.ac.bd` | T-CSE-003 | Department Head, curriculum & schedule management |
| **Inactive Demo** | `inactive_demo` | `inactive@pub.ac.bd` | 200101099 | Suspended student account (verifies login rejection) |

*(Tip: The sign-in page features 1-click quick-fill buttons for all demo accounts including the inactive account test).*

---

## 9. Authentication & Security Highlights

1. **Role-Based Routing & Access Control**:
   - `/student/dashboard` (also `/dashboard/student`): Enforces `role:student`.
   - `/teacher/dashboard` (also `/dashboard/teacher`): Enforces `role:teacher`.
   - `/advisor/dashboard` (also `/dashboard/advisor`): Enforces `role:academic_advisor`.
   - `/coordinator/dashboard` (also `/dashboard/coordinator`): Enforces `role:department_coordinator`.
   - Base `/dashboard` automatically evaluates the authenticated session and redirects to the role-specific dashboard.
   - Unauthorized attempts return an HTTP 403 Forbidden status and branded error view.
2. **Session Security & CSRF Protection**:
   - Session fixation prevention: Session ID is regenerated via `session_regenerate_id(true)` upon successful authentication.
   - Secure cookie settings (`httponly`, `samesite=Lax`).
   - CSRF tokens are injected on all state-changing POST forms (`login`, `logout`, `registration`, `approval`).
3. **Information Disclosure Prevention**:
   - Generic credentials error prevents user/email enumeration.
   - Inactive/suspended accounts are blocked from accessing protected resources even with a valid password.
   - Open-redirect protection validates the `intended_url` parameter, disallowing external and malformed URLs.
4. **Audit Logging**:
   - Authentication events (`auth.login`, `auth.logout`) are recorded in the `audit_logs` table without recording passwords or session tokens.

---

## 10. Academic Management Module (Phase 3)

The Academic Management Module equips the Department Coordinator with comprehensive administration tools while offering role-tailored academic visibility for students, teachers, and advisors:

### 1. Department Administration (`/coordinator/departments`)
- Department catalog with active status filtering, building office location, and contact information.
- Server-side validation of unique department codes (`CSE`, `EEE`, `BBA`).
- Referential integrity guards preventing deactivation or orphan deletion when academic history exists.

### 2. Semester & Term Planning (`/coordinator/semesters`)
- Semester lifecycle management (`upcoming`, `registration_open`, `active`, `grading`, `closed`).
- Strict date validation: term start/end date logic, registration window deadlines, and add/drop cutoffs.
- Atomic transactional enforcement of a single official active semester across the institution.

### 3. Degree Curricula & Matrix Roadmaps (`/coordinator/curricula`)
- Program curriculum configuration (OBE syllabus versioning, target graduation credit hours).
- Dynamic course matrix table with semester-by-semester recommended terms (Semester 1 to 8).
- Real-time aggregation of matrix credit totals derived directly from associated course units.

### 4. Course Catalog & Graph Prerequisite Validation (`/coordinator/courses`)
- Master course registry with credit weights, course types (Theory, Lab, Project, Thesis), and syllabi.
- Unique course code validation (`CSE-1101`, `CSE-2101`, etc.).
- **Graph-based Cycle Detection**: Utilizes Breadth-First Search (BFS) graph traversal to detect and prevent circular prerequisite loops (e.g. A &rarr; B &rarr; C &rarr; A), as well as direct self-prerequisite assignment.

### 5. Semester Course Offerings (`/coordinator/course-offerings`)
- Schedule catalog courses for student registration in active academic terms.
- Duplicate offering prevention per semester with status control (`offered`, `cancelled`, `completed`).

### 6. Course Sections & Seat Capacities (`/coordinator/sections`)
- Class section partitioning (`Sec A`, `Sec B`, etc.) with positive capacity limits.
- Available seat computation (`capacity - enrolled_count`), preventing negative capacity.
- Duplicate section code prevention within the same course offering.

### 7. Classrooms & Lab Facilities (`/coordinator/rooms`)
- Lecture gallery, computer lab, electronics lab, and seminar room facility management.
- Capacity tracking, building/floor mapping, and maintenance status toggling.
- Referential integrity guards: prevents room deletion if referenced by scheduled classes or exams.

### 8. Teacher Assignments & Workload Limits (`/coordinator/teacher-assignments`)
- Faculty allocation to course sections with Lead Teacher / Co-Instructor roles.
- Workload capacity checking: computes semester credit hours against `max_credit_load` to prevent faculty burnout and policy violations.

### 9. Role-Specific Read-Only Academic Views
- **Student**: Browse active courses (`/student/courses`) and current term offerings with section seat availability (`/student/semester`).
- **Teacher**: Inspect assigned syllabus outlines (`/teacher/courses`) and workload quota progress (`/teacher/assignments`).
- **Advisor**: Review curriculum matrices and prerequisite requirements (`/advisor/courses`) to guide advisees during registration.

---

## 11. Smart Course Registration & Advisor Approval (Phase 4)

Phase 4 delivers an end-to-end, transaction-safe course registration engine and an Academic Advisor approval suite:

### 1. Student Course Registration Portal (`/student/registration`)
- Real-time catalog search and dynamic filtering by course code, title, and seat availability.
- Course cards showing credits, course type, assigned teacher, routine schedule, room number, and vacancies.
- Live credit meter tracking selected credits against configured minimum (9.0 cr) and maximum limits (regular 18.0 cr, probation 12.0 cr).
- Real-time client-side preview validation and server-side `/student/registration/validate` checking.
- Submission modal preventing duplicate concurrent requests while a prior submission is under review (`RegistrationRequest::hasPendingRequest()`).

### 2. Registration Request History & Tracking (`/student/registration/history`, `/student/registration/{id}`)
- Batch history tracking with status badges (`submitted`, `advisor_approved`, `advisor_rejected`, `cancelled`).
- Itemized request inspection displaying routine schedules, room numbers, instructor details, and advisor decision remarks.
- Safe request cancellation modal allowing students to withdraw pending submissions prior to advisor action.

### 3. Dedicated Server-Side Validation Engine (`RegistrationService.php`)
- **Prerequisite Validation**: Cross-checks student's verified `student_course_history` for completed prerequisite courses with passing grades; provides explicit error messages identifying unsatisfied prerequisite courses.
- **Credit Limit Policy**: Enforces institutional workload policies without hard-coding; dynamically respects academic probation thresholds.
- **Seat Availability & Atomic Row Locking**: Immediate vacancies feedback in cart; performs row-level locking via `SELECT ... FOR UPDATE` (`Section::lockForUpdate()`) at approval time inside a database transaction to eliminate race conditions and overbooking.
- **Duplicate Registration Prevention**: Blocks registering for multiple sections of the same course offering or duplicate submissions for the same academic semester.
- **Timetable Conflict Detection**: Minute-accurate routine overlap detection between selected course sections and existing enrolled schedules on the same day.

### 4. Academic Advisor Review & Approval Suite (`/advisor/registration-requests`)
- Advisee request queue filtered strictly by authorized advisor mapping (`academic_advisors`).
- Pre-audit review screen (`/advisor/registration-requests/{id}`) evaluating:
  - Advisee profile: Department, Batch, CGPA, Academic Status (Regular/Probation).
  - Pre-audit checks: Prerequisite completion status, credit workload compliance, schedule collisions, and section capacities.
  - Complete historical transcript of previous semester grades.
- **Atomic Approval**: Locks sections, reserves seats, increments `enrolled_count`, generates confirmed `registrations` records (`status = 'enrolled'`), and dispatches notifications inside a single database transaction.
- **Rejection with Documented Reason**: Requires non-empty academic remarks and transitions status to `advisor_rejected`.
- **Idempotency Guard**: Prevents already approved or rejected requests from being reprocessed.

### 5. Transactional Notifications
- Integrated with the notification system: sends alerts to students upon request submission, approval, and rejection; alerts advisors upon new advisee submissions.

---

## 12. AI Helpdesk (RAG Foundation)

The AI Academic Helpdesk integrates Retrieval-Augmented Generation (RAG) principles:
1. **Document Storage**: Uploaded academic ordinances are chunked into `document_chunks` with metadata (article number, page reference).
2. **Hybrid Retrieval**: Powered by MySQL `FULLTEXT` natural language searching as a native zero-dependency engine, with support for external vector embeddings (Gemini Embeddings / ChromaDB).
3. **Citations**: The assistant cites specific articles from official university documentation.

---

## 13. Automated Test Suite

A standalone test runner verifies all foundation, authentication, academic management, registration, and timetable/exam scheduling features:
```bash
php tests/run_tests.php
```

Results: **65 Passed, 0 Failed (100% Pass Rate)**
- **Foundation Tests (1-14)**: Database singleton, PDO, 32 tables, seeds, bcrypt, routing, XSS escaping, CSRF tokens, services, Helpdesk RAG search.
- **Phase 2 Tests (1-12)**: Authentication, credential checks, inactive account blocks, session invalidation, role boundaries, CGPA calculation, open-redirect protection.
- **Phase 3 Tests (1-12)**: Course creation, unique codes, self-prerequisite rejection, graph cycle detection, semester date validation, single active semester transactions, section capacity, duplicate section prevention, teacher workload enforcement, student/teacher access boundaries, and academic referential integrity.
- **Phase 4 Tests (1-12)**: Eligible registration submission, prerequisite rejection with explicit errors, credit limit enforcement, full section capacity rejection, duplicate course in cart detection, timetable clash detection, student request isolation, advisor authorization, atomic approval & seat reservation, idempotent double-approval prevention, rejection with reason, and student cancellation.
- **Phase 5 Tests (1-15)**: Timetable slot creation, teacher conflict rejection, classroom overlap rejection, section clash rejection, invalid time range validation, examination routine creation, room & invigilator clash checks, enrolled student exam clash checks, non-destructive make-up class creation linked to original routines, mandatory revision audit logging with documented reasons upon modifying published schedules, targeted notification dispatching to affected students and teachers, strict role boundary enforcement blocking student modifications, draft slot hiding from unauthorized students, complete preservation of academic management & Phase 4 enrollments, and full integration verification ensuring all four role dashboards render cleanly.

---

## 14. Phase 5: Smart Timetable & Examination Scheduling Features

1. **Timetable Management**:
   - Coordinator selects Semester, Course Offering, Section, assigned Teacher, and Classroom.
   - Sets Day of Week, Start Time, End Time, and publication status (`draft`, `published`, `revised`, `cancelled`).
   - Revision tracking: modifying a published routine prompts for a documented revision reason, which is committed to `schedule_revisions` and alerts affected users.
2. **Automatic Conflict Engine (`TimetableConflictService`)**:
   - Overlap formula: `new_start < existing_end AND new_end > existing_start`.
   - Prevents Teacher Overlaps, Room Overlaps, Section Overlaps, and Student Enrollment Overlaps.
3. **Examination Scheduling (`ExamController`, `Exam`)**:
   - Supports midterm, final, quiz, and practical examinations.
   - Detects room clashes, invigilator duty clashes, and student enrollment conflicts.
4. **Non-Destructive Make-up Classes (`MakeupClassController`, `MakeupClass`)**:
   - Links to original routine slot via `class_schedule_id` without overwriting historical records.
   - Validates teacher, room, and student clashes.
5. **Targeted In-App Notifications**:
   - Dispatches notifications only to affected students (via section enrollments) and assigned teachers.
6. **Visual UI Audit**:
   - Documented in `artifacts/phase5/visual-audit.md` with 12 high-resolution screenshots in `artifacts/phase5/screenshots/`.

---

## 15. Demo Accounts & Verification Steps

| Role | Username | Password | University ID | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Student** | `student21` | `Password@123` | 210101001 | Rahat Rahman (Batch 21, 3rd Sem) — View history, timetable & exams |
| **Student (Alt)** | `tamanna22` | `Password@123` | 220101002 | Tamanna Akter (Batch 22, 2nd Sem) — Submit registration requests |
| **Academic Advisor** | `advisor_kazi` | `Password@123` | T-CSE-002 | Dr. Kazi Nahid — Review advisee queues, approve/reject |
| **Teacher** | `prof_tanvir` | `Password@123` | T-CSE-001 | Engr. Tanvir Ahmed — View assigned sections, teaching schedule & exam duties |
| **Coordinator** | `coord_hasan` | `Password@123` | T-CSE-003 | Dr. Mahmudul Hasan — Manage timetables, exams, make-up classes & revisions |

### Timetable & Examination Walkthrough:
1. **Schedule a Class Routine**:
   - Login as `coord_hasan` / `Password@123`.
   - Navigate to `/coordinator/timetable`.
   - Click **+ Schedule Class Routine** (`/coordinator/timetable/create`).
   - Select Course Offering (e.g. `CSE-2101`), Section `A`, Faculty Member `Engr. Tanvir Ahmed`, Room `Room-301`, Day `Sunday`, Time `09:00 AM - 10:30 AM`.
   - Click **Save Schedule Routine**.
2. **Test Conflict Detection**:
   - Attempt to schedule another routine for the same instructor or room at an overlapping time (e.g., `10:00 AM - 11:30 AM`).
   - Observe immediate conflict rejection with specific clash details.
3. **Revise a Published Routine**:
   - Edit the routine, change the time or room, and provide a revision reason (e.g., *"Shifted for laboratory maintenance"*).
   - View the revision recorded in `/coordinator/timetable/revisions`.
4. **Student & Teacher Inspection**:
   - Login as `student21` or `prof_tanvir`.
   - Navigate to `/timetable` or `/exams` to view the published routines and targeted notifications.

---

## 16. Next Steps & Recommended Phase 6

With **Phases 1, 2, 3, 4, and 5** complete and verified:
- **Phase 6: FullCalendar & Interactive Visual Routine Matrix**: Drag-and-drop schedule adjustment with real-time collision warning modals.
- **Phase 7: Examination Seat Plan Allotment & Hall Ticket Generation**: Intelligent student roll seating generator preventing adjacent peers from the same department/semester.
- **Phase 8: AI Helpdesk Vector Embeddings Integration**: Seamless Gemini Embeddings ingestion replacing keyword fallback with semantic vector search.
