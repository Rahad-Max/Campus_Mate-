# PUB CampusMate — Database Architecture & Operations

## Overview
This directory contains the database design, schema definition, migration files, and seed demonstration datasets for **PUB CampusMate** (Pundra University of Science & Technology).

## Files in This Directory
- `schema.sql`: Complete DDL schema script containing all 32 tables, primary keys, foreign key constraints with cascade behaviors, indexes, and fulltext search definitions.
- `seed.sql`: Realistic, production-style fictional demonstration dataset for the Department of Computer Science & Engineering (Summer 2026).
- `er_diagram.md`: Entity-Relationship diagrams rendered in Mermaid, along with relationship constraints.
- `migrations/`: Reserved directory for versioned database migration scripts in subsequent development phases.

## Schema Highlights
- **Character Set & Collation**: `utf8mb4` / `utf8mb4_unicode_ci`
- **Storage Engine**: `InnoDB`
- **Tables Count**: 32 relational tables organized into 8 functional clusters.
- **Strict Data Types**: Precise decimals for credit points (`DECIMAL(3,2)` and `DECIMAL(4,2)`), strict ENUM definitions for statuses and days of the week, and `BIGINT` for audit logs.

## Demo Accounts in `seed.sql`
All demo accounts use the standard password: `Password@123`

| Role | Username | Email | Sample Name | Notes |
| :--- | :--- | :--- | :--- | :--- |
| **Student** | `student21` | `student@pub.ac.bd` | Rahat Rahman | 3rd Semester, Batch 21, CGPA 3.65 |
| **Student (Alt)** | `tamanna22` | `tamanna@pub.ac.bd` | Tamanna Akter | 2nd Semester, Batch 22, CGPA 3.82 |
| **Teacher** | `prof_tanvir` | `teacher@pub.ac.bd` | Engr. Tanvir Ahmed | Assistant Professor, CSE |
| **Academic Advisor** | `advisor_kazi` | `advisor@pub.ac.bd` | Dr. Kazi Nahid | Associate Professor & Batch 21 Advisor |
| **Coordinator** | `coord_hasan` | `coordinator@pub.ac.bd` | Dr. Mahmudul Hasan | Professor & Head of Department |

## How to Import
You can import the schema and seed data using MySQL CLI, phpMyAdmin, or the built-in verification script.

### Using Built-in Verification Script:
```bash
php scripts/verify_database.php
```

### Using MySQL Command Line:
```bash
# 1. Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS pub_campusmate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import Schema
mysql -u root -p pub_campusmate < database/schema.sql

# 3. Import Seed Data
mysql -u root -p pub_campusmate < database/seed.sql
```

## Phase 4: Course Registration Policy & Seat Allocation Model

### 1. Registration Request Lifecycle
The course registration system implements a strict state-machine workflow across `registration_requests` and `registration_request_items`:
- `draft`: Course sections selected in browser session; not yet officially filed.
- `submitted`: Request formally submitted by the student and awaiting assigned Academic Advisor review. Active validation ensures only one pending request per student per semester (`RegistrationRequest::hasPendingRequest()`).
- `advisor_approved`: Reviewed and approved by the assigned advisor. Seats are locked and consumed atomically, creating confirmed `registrations` records.
- `advisor_rejected`: Rejected by the advisor with documented remarks. No seats consumed.
- `cancelled`: Withdrawn by the student prior to advisor decision.

### 2. Seat Reservation & Capacity Policy
- **Timing of Seat Consumption**: Seats are evaluated during cart validation for immediate feedback, and reserved atomically at **approval time** inside a database transaction (`BEGIN TRANSACTION ... COMMIT`).
- **Concurrency & Overbooking Prevention**: `SELECT ... FOR UPDATE` row-level locking on `sections` (`Section::lockForUpdate()`) ensures concurrent advisor approvals cannot overbook a section past its `capacity`.
- **Atomic Counting**: `enrolled_count` is incremented upon approval (`Section::incrementEnrolled()`) and linked to confirmed `registrations` rows (`status = 'enrolled'`). Rejected and cancelled requests NEVER count toward enrolled seats.
- **Referential Integrity**: A foreign key links `registrations.registration_request_id` to `registration_requests.id`. Partial registrations are rolled back automatically if any section is full or fails validation during the batch transaction.

