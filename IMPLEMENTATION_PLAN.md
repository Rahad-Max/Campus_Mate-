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
    Phase 2: Authentication & RBAC             :active,  p2, 2026-05-16, 2026-05-28
    Phase 3: Profiles & Organization           :         p3, 2026-05-29, 2026-06-08
    Phase 4: Curriculum & Catalog              :         p4, 2026-06-09, 2026-06-20
    section Academic Engine
    Phase 5: Course Registration Engine        :         p5, 2026-06-21, 2026-07-05
    Phase 6: Advising & Add/Drop Workflows     :         p6, 2026-07-06, 2026-07-16
    Phase 7: Timetable & Collision Engine      :         p7, 2026-07-17, 2026-07-30
    section Operations & AI
    Phase 8: Examination & Notifications       :         p8, 2026-07-31, 2026-08-10
    Phase 9: Faculty Directory                 :         p9, 2026-08-11, 2026-08-17
    Phase 10: AI Academic Helpdesk (RAG)       :         p10, 2026-08-18, 2026-08-31
    Phase 11: Security Audit & Demonstration   :         p11, 2026-09-01, 2026-09-15
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

### Phase 2: Authentication and Role-Based Access Control (RBAC)
- Multi-role session management with cryptographically secure session cookies.
- Password hashing with `PASSWORD_BCRYPT` and strict verification.
- Server-side role boundaries (`RoleMiddleware`) preventing unauthorized URL parameter manipulation.
- Account lockout throttling after 5 consecutive failed login attempts.
- Password reset token generation with time-bound expiration.

### Phase 3: Student and Teacher Profiles
- Detailed student transcripts, semester-wise GPA calculation, and CGPA audit.
- Faculty workload analytics and credit load tracking.
- Advisor-to-student assignment and batch mapping.
- Profile update forms with secure avatar upload handling outside the web root.

### Phase 4: Course, Curriculum, and Semester Management
- Department curriculum versioning and total credit tracking.
- Master course catalog with theoretical and lab distinctions.
- Directed acyclic graph (DAG) representation for course prerequisites.
- Semester lifecycle control (Upcoming -> Registration Open -> Active -> Grading -> Closed).

### Phase 5: Course Registration Engine
- Automated prerequisite validation against student academic history.
- Dynamic credit minimum and maximum validation (including academic probation rules).
- Atomic seat reservation with row-level locking (`SELECT ... FOR UPDATE`) to prevent overbooking.
- Live cart conflict checker detecting overlapping class hours.
- Duplicate course enrollment prevention.

### Phase 6: Advisor Approval and Add/Drop
- Registration review queues for assigned academic advisors.
- 1-click batch approvals and granular course-by-course approvals.
- Rejection reason logging and student notification triggering.
- Formal add/drop window enforcement with automatic audit logs.

### Phase 7: Timetable Conflict Detection and Scheduling
- Conflict detection across Room, Teacher, and Section slots.
- Teacher availability preference tracking during routine generation.
- Schedule revision tracking with historical records of reasons and changes.
- Makeup class scheduling and room booking validation.

### Phase 8: Exams, Notices, and Notifications
- Midterm and final exam routine generator with invigilator assignment.
- Role-targeted announcements and department-specific notice boards.
- In-app asynchronous notification drawer with read/unread tracking.
- PDF attachment downloads stored with verified MIME-types.

### Phase 9: Faculty Directory
- Public and authenticated faculty directory with search and filtering by department.
- Faculty research interests, education history, and office consultation hours.
- Direct academic contact links.

### Phase 10: AI Helpdesk and Document Retrieval (RAG)
- University ordinance PDF upload and text chunking engine.
- Chunk token counting and metadata tagging (article number, page reference).
- Hybrid search: MySQL Full-Text search with fallback and dense vector embedding integration via external vector service (e.g. Gemini Embeddings / ChromaDB).
- Conversational chat interface displaying grounded source citations.

### Phase 11: Testing, Security Review, and Demonstration Preparation
- Comprehensive unit and integration test suite execution.
- Security penetration testing (SQL injection, XSS escaping, CSRF verification, session fixation).
- Performance indexing optimization on queries with high cardinality.
- Preparation of end-to-end demo scenarios for university stakeholders.
