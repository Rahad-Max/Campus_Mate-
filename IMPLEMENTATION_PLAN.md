# PUB CampusMate — Master Implementation Plan (Phased Roadmap)

This document outlines the detailed roadmap for developing **PUB CampusMate** (Pundra University of Science & Technology) from foundation to full production readiness.

---

## Phase Summary & Milestone Overview

```mermaid
gantt
    title PUB CampusMate Development Roadmap
    dateFormat  YYYY-MM-DD
    section Foundation
    Phase 1: Architecture & DB Foundation       :done,    p1, 2026-05-01, 2026-05-15
    section Core Auth & Entities
    Phase 2: Authentication & RBAC Dashboards   :done,    p2, 2026-05-16, 2026-05-28
    Phase 3: Academic Management Module         :done,    p3, 2026-05-29, 2026-06-12
    Phase 4: Smart Registration & Advisor Approval :done, p4, 2026-06-13, 2026-06-25
    Phase 5: Add/Drop Period & Enrollment Changes:        p5, 2026-06-26, 2026-07-08
    Phase 6: Timetable & Collision Engine       :         p6, 2026-07-09, 2026-07-22
    section Operations & AI
    Phase 7: Examination & Notifications        :         p7, 2026-07-23, 2026-08-05
    Phase 8: Faculty Directory & Profiles       :         p8, 2026-08-06, 2026-08-15
    Phase 9: AI Academic Helpdesk (RAG)         :         p9, 2026-08-16, 2026-08-30
    Phase 10: Security Audit & Demonstration    :         p10, 2026-09-01, 2026-09-15
```

---

## Detailed Phase Breakdown

### Phase 1: Project Foundation & Database (Completed — C1)
- [x] Establish structured MVC directory layout with separation of concerns.
- [x] Implement PSR-4 autoloading, Front Controller, and singleton PDO wrapper.
- [x] Design 32-table relational database schema (`database/schema.sql`) with InnoDB, strict constraints, and indexes.
- [x] Prepare comprehensive seed dataset (`database/seed.sql`) covering the Department of CSE (Summer 2026).
- [x] Implement routing engine with route groups, parameter extraction, and middleware pipelines.
- [x] Create verification scripts (`check_environment.php`, `verify_database.php`).
- [x] Craft modern design tokens, Bootstrap 5 integration, and responsive layouts.

### Phase 2: Authentication and Role-Based Dashboards (Completed — C1 Phase 2)
- [x] Multi-role authentication (Student, Teacher, Academic Advisor, Department Coordinator) via email or username.
- [x] Secure password verification using `password_verify()` with `PASSWORD_BCRYPT`.
- [x] Generic error message for invalid credentials preventing email/account disclosure.
- [x] Inactive/suspended account blocking (`is_active = 0`) with descriptive status feedback.
- [x] Session fixation protection via `session_regenerate_id(true)`.
- [x] Secure session cookie settings (`httponly`, `samesite=Lax`) and logout session invalidation.
- [x] CSRF protection token generation and validation across authentication and state-changing forms.
- [x] Server-side role boundaries (`RoleMiddleware`) returning HTTP 403 Forbidden with branded error view.
- [x] Open-redirect validation preventing external and malformed redirect exploitation.
- [x] 4 distinct role-based dashboards (`/student/dashboard`, `/teacher/dashboard`, `/advisor/dashboard`, `/coordinator/dashboard`) with real database queries.
- [x] Automated test suite with 25 unit and integration tests passing (`tests/run_tests.php`).
- [x] Demonstration accounts verified across all 4 roles plus inactive test account (`scripts/seed_demo_accounts.php`).

### Phase 3: Academic Management Module (Completed — C1 Phase 3)
- [x] **Department Management (`/coordinator/departments`)**:
  - Full CRUD operations with department code uniqueness validation and active/inactive state management.
  - Safeguard preventing accidental deletion of departments associated with academic records.
- [x] **Semester Lifecycle Management (`/coordinator/semesters`)**:
  - Comprehensive date validation (start date, end date, registration window, add/drop deadline).
  - Single active semester rule with atomic transaction-safe status transitions.
  - Read-only active semester view for students (`/student/semester`).
- [x] **Curriculum & Syllabus Management (`/coordinator/curricula`)**:
  - Curriculum versioning, cohort tracking, and dynamic curriculum credit calculation.
  - Many-to-many relationship mapping (`curriculum_courses`) with term configuration.
- [x] **Course Catalog & Prerequisites (`/coordinator/courses`)**:
  - Master course catalog with positive credit limits (0.5 to 6.0 credits).
  - Breadth-First Search (BFS) directed acyclic graph (DAG) cycle detection preventing circular prerequisites.
  - Prevention of self-prerequisites and duplicate prerequisite relationships.
- [x] **Course Offerings & Section Management (`/coordinator/course-offerings`, `/coordinator/sections`)**:
  - Offer courses per active semester with capacity boundaries.
  - Section creation (e.g. A, B) with uniqueness check within offering.
  - Accurate seat availability calculations (`enrolled_count` vs `capacity`).
- [x] **Classroom & Facility Management (`/coordinator/rooms`)**:
  - Classroom inventory with room code uniqueness, capacity constraints, and room type classification.
- [x] **Teacher Workload & Course Assignment (`/coordinator/teacher-assignments`)**:
  - Teacher course/section assignment with live workload calculation.
  - Strict prevention of teacher credit overloads exceeding configured `max_credit_load`.
- [x] **Role-Based Read-Only Academic Views**:
  - Students: Browse course catalog (`/student/courses`) and active semester schedule (`/student/semester`).
  - Teachers: View assigned courses (`/teacher/courses`) and teaching schedule (`/teacher/assignments`).
  - Advisors: Explore curricula, course catalogs, and prerequisite trees (`/advisor/courses`).
- [x] **Automated Integration Testing**:
  - 12 comprehensive Phase 3 tests verifying course creation, prerequisite DAG cycles, date ranges, single active semester transactions, section capacity, duplicate sections, teacher workload limits, role boundaries, and referential integrity.
  - 38/38 automated tests passing across the entire test suite.

### Phase 4: Smart Course Registration & Advisor Approval (Completed — C1 Phase 4)
- [x] **Student Course Registration Portal (`/student/registration`)**:
  - Live course search & filters by course code, title, section, and seat availability.
  - Interactive selection cards with real-time credit progress bar, prerequisite badges, routine schedules, and capacity indicators.
  - Client-side and server-side cart validation returning clear error states and warnings.
  - Active submission modal preventing duplicate concurrent requests.
- [x] **Registration History & Tracking (`/student/registration/history`, `/student/registration/{id}`)**:
  - Comprehensive registration batch history with status badges (`submitted`, `advisor_approved`, `advisor_rejected`, `cancelled`).
  - Itemized request breakdown with class schedules, room allocations, instructor names, and advisor remarks.
  - Student request cancellation modal for pending submissions.
- [x] **Core Validation Engine (`RegistrationService.php`)**:
  - **Prerequisite Validation**: Strict verification against student's verified `student_course_history` with passing letter grades; explicit rejection mentioning missing prerequisite course codes.
  - **Credit Limit Policy**: Dynamic enforcement of minimum (9.0 cr) and maximum limits (regular 18.0 cr, probation 12.0 cr).
  - **Capacity & Atomic Row Locking**: Immediate cart feedback and transactional `SELECT ... FOR UPDATE` row locking during approval to eliminate race conditions and overbooking.
  - **Duplicate Course Prevention**: Blocks duplicate selections of the same course offering or multiple sections within a semester.
  - **Timetable Conflict Detection**: Minute-accurate routine overlap checking across selected sections and already enrolled courses.
- [x] **Academic Advisor Review & Approval Suite (`/advisor/registration-requests`)**:
  - Advisee request queue filtered strictly by authorized advisor mapping (`academic_advisors`).
  - Detailed pre-approval audit dashboard (`/advisor/registration-requests/{id}`) showing student academic history, CGPA, credit workload, routine conflicts, and seat statuses.
  - Atomic approval action creating confirmed `registrations` records, incrementing `enrolled_count`, and updating request items inside a database transaction.
  - Rejection workflow requiring documented academic justification and updating request status to `advisor_rejected`.
- [x] **Transactional Notification Integration**:
  - Automatic student notifications upon request submission, advisor approval, and advisor rejection.
  - Advisor notification triggered upon advisee request submission with direct link to the review queue.
- [x] **Automated Integration Testing (`tests/Integration/Phase4RegistrationTest.php`)**:
  - 12 comprehensive automated tests verifying eligible submissions, prerequisite failures, credit limit violations, full section rejections, duplicate courses, schedule clashes, request isolation, advisor authorization, atomic approval & seat reservation, idempotent double-approval blocking, rejection with reason, and student cancellation.
  - **50/50 automated tests passing across the entire application test suite** (14 Foundation + 12 Phase 2 + 12 Phase 3 + 12 Phase 4).

### Phase 5: Smart Timetable, Examination Scheduling & Full Integration (Completed — C1 Phase 5)
- [x] **Smart Timetable Management (`/coordinator/timetable`)**:
  - Full CRUD operations: select semester, course offering, section, assigned teacher, and classroom.
  - Set day of week, start and end times, class type, and publication status (`draft`, `published`, `revised`, `cancelled`).
  - Draft isolation: unpublished and draft schedules are strictly restricted to authorized staff and invisible to students.
- [x] **Automatic Schedule Conflict Engine (`TimetableConflictService.php`)**:
  - Teacher conflict detection: blocks overlapping sessions for the same faculty member on the same day.
  - Classroom conflict detection: blocks concurrent room double-booking.
  - Section conflict detection: ensures no section is scheduled for multiple simultaneous courses.
  - Student registration conflict detection: prevents scheduling clashes for students enrolled in multiple courses.
  - Time range validation: enforces `new_start < existing_end AND new_end > existing_start` with minimum 30-minute durations.
- [x] **Examination Management (`/coordinator/exams`)**:
  - Midterm, final, quiz, and practical exam scheduling with room allocation and invigilator assignment.
  - Comprehensive clash detection for exam halls, faculty invigilators, and enrolled student groups.
  - Role-specific student and teacher views (`/exams`) with invigilation badges and examination instructions.
- [x] **Non-Destructive Make-up Class Scheduling (`/coordinator/makeup-classes`)**:
  - Dedicated workflow linking replacement classes to original class schedule IDs without modifying historical records.
  - Validation for room, faculty, and student clashes against regular schedules, exams, and prior make-up classes.
  - Unified student and faculty make-up class dashboard (`/makeup-classes`).
- [x] **Schedule Publication & Revision Tracking (`/coordinator/timetable/revisions`)**:
  - Published schedules cannot be modified silently; mandatory revision reasons are prompted and stored in `schedule_revisions`.
  - Immutable audit trail recording previous/new slot parameters, modifier user ID, and timestamps.
- [x] **Targeted In-App Notifications**:
  - Targeted notification dispatching to affected students and assigned instructors upon routine publication, revision, cancellation, or exam scheduling.
- [x] **Visual UI & Glassmorphism Audit**:
  - Complete browser automation test suite using headless Chrome (`puppeteer-core`).
  - 12 high-resolution screenshots captured and documented in `artifacts/phase5/visual-audit.md`.
- [x] **Automated Integration Testing (`tests/Integration/Phase5TimetableExamTest.php`)**:
  - 15 comprehensive automated tests covering all schedule creations, conflict triggers, exam clashes, make-up non-destructive links, revision audit trails, notifications, and access boundaries.
  - **65/65 automated tests passing across the entire application test suite** (100% pass rate).

### Phase 8: Faculty Directory & Profiles
- Public and authenticated faculty directory with search and filtering by department.
- Detailed student transcripts, semester-wise GPA calculation, and CGPA audit.
- Faculty workload analytics, research interests, and office consultation hours.
- Direct academic contact links and secure avatar uploads.

### Phase 9: AI Helpdesk and Document Retrieval (RAG)
- University ordinance PDF upload and text chunking engine.
- Chunk token counting and metadata tagging (article number, page reference).
- Hybrid search: MySQL Full-Text search with fallback and dense vector embedding integration via external vector service (e.g. Gemini Embeddings / ChromaDB).
- Conversational chat interface displaying grounded source citations.

### Phase 10: Security Review, Load Testing & Demonstration Preparation
- Comprehensive unit and integration test suite execution.
- Security penetration testing (SQL injection, XSS escaping, CSRF verification, session fixation).
- Performance indexing optimization on queries with high cardinality.
- Preparation of end-to-end demo scenarios for university stakeholders.
