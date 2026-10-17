-- =====================================================================
-- PUB CampusMate — Complete Database Schema
-- Institution: Pundra University of Science & Technology (PUB)
-- Engine: MySQL 8+ / MariaDB 10.4+ (InnoDB, utf8mb4)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS pub_campusmate;

USE pub_campusmate;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. Departments Table
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `faculty_name` VARCHAR(150) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_dept_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Users Table (Core Authentication & Role-Based Identity)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'teacher', 'academic_advisor', 'department_coordinator') NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `avatar_url` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Teachers Table (Faculty Member Details)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `department_id` INT NOT NULL,
    `teacher_id_card` VARCHAR(50) NOT NULL UNIQUE,
    `designation` VARCHAR(100) NOT NULL,
    `office_room` VARCHAR(50) DEFAULT NULL,
    `joining_date` DATE DEFAULT NULL,
    `max_credit_load` DECIMAL(4,2) NOT NULL DEFAULT 15.00,
    `status` ENUM('active', 'on_leave', 'retired') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_teachers_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_teachers_dept` (`department_id`),
    INDEX `idx_teachers_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Semesters Table (Academic Terms & Timeline Control)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `semesters`;
CREATE TABLE `semesters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(30) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `academic_year` YEAR NOT NULL,
    `term` ENUM('Spring', 'Summer', 'Fall') NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `registration_start` DATETIME NOT NULL,
    `registration_end` DATETIME NOT NULL,
    `add_drop_deadline` DATETIME NOT NULL,
    `is_current` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('upcoming', 'registration_open', 'active', 'grading', 'closed') NOT NULL DEFAULT 'upcoming',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_semesters_current` (`is_current`),
    INDEX `idx_semesters_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. Students Table (Enrolled Student Academic Profiles)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `department_id` INT NOT NULL,
    `student_id_card` VARCHAR(50) NOT NULL UNIQUE,
    `batch_no` VARCHAR(20) NOT NULL,
    `admission_semester_id` INT DEFAULT NULL,
    `current_semester_level` INT NOT NULL DEFAULT 1,
    `cgpa` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `completed_credits` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `academic_status` ENUM('regular', 'probation', 'graduated', 'suspended') NOT NULL DEFAULT 'regular',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_students_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_students_admission_sem` FOREIGN KEY (`admission_semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_students_dept` (`department_id`),
    INDEX `idx_students_status` (`academic_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. Academic Advisors Table (Student to Advisor Mapping)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `academic_advisors`;
CREATE TABLE `academic_advisors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT NOT NULL,
    `student_id` INT NOT NULL UNIQUE,
    `assigned_semester_id` INT DEFAULT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_advisor_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_advisor_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_advisor_semester` FOREIGN KEY (`assigned_semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_advisor_teacher` (`teacher_id`),
    INDEX `idx_advisor_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. Curricula Table (Department Academic Programs & Versions)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `curricula`;
CREATE TABLE `curricula` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `version` VARCHAR(20) NOT NULL,
    `total_credits_required` DECIMAL(5,2) NOT NULL DEFAULT 148.00,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_curricula_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_curricula_dept` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. Courses Table (Master Course Catalog)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `department_id` INT NOT NULL,
    `course_code` VARCHAR(20) NOT NULL UNIQUE,
    `title` VARCHAR(150) NOT NULL,
    `credit` DECIMAL(3,2) NOT NULL,
    `course_type` ENUM('theory', 'lab', 'project', 'thesis', 'internship') NOT NULL DEFAULT 'theory',
    `description` TEXT DEFAULT NULL,
    `syllabus_url` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'archived') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_courses_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_courses_dept` (`department_id`),
    INDEX `idx_courses_code` (`course_code`),
    INDEX `idx_courses_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. Curriculum Courses Table (Course Matrix in Curriculum)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `curriculum_courses`;
CREATE TABLE `curriculum_courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `curriculum_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `recommended_semester` INT NOT NULL,
    `is_elective` TINYINT(1) NOT NULL DEFAULT 0,
    `elective_group` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cc_curriculum` FOREIGN KEY (`curriculum_id`) REFERENCES `curricula` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cc_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_curriculum_course` (`curriculum_id`, `course_id`),
    INDEX `idx_cc_rec_sem` (`recommended_semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. Course Prerequisites Table (Structured Dependency Rules)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `course_prerequisites`;
CREATE TABLE `course_prerequisites` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` INT NOT NULL,
    `prerequisite_course_id` INT NOT NULL,
    `min_grade` VARCHAR(5) NOT NULL DEFAULT 'D',
    `is_mandatory` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cp_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cp_prereq_course` FOREIGN KEY (`prerequisite_course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_course_prereq` (`course_id`, `prerequisite_course_id`),
    INDEX `idx_cp_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. Rooms Table (Classroom, Lab, and Hall Scheduling Facilities)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `rooms`;
CREATE TABLE `rooms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_number` VARCHAR(30) NOT NULL UNIQUE,
    `building` VARCHAR(100) NOT NULL,
    `floor` INT NOT NULL DEFAULT 1,
    `room_type` ENUM('lecture', 'computer_lab', 'electronics_lab', 'seminar_hall', 'multimedia_room') NOT NULL DEFAULT 'lecture',
    `capacity` INT NOT NULL DEFAULT 40,
    `has_projector` TINYINT(1) NOT NULL DEFAULT 1,
    `has_ac` TINYINT(1) NOT NULL DEFAULT 1,
    `status` ENUM('available', 'maintenance', 'inactive') NOT NULL DEFAULT 'available',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rooms_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. Course Offerings Table (Courses Available in a Semester)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `course_offerings`;
CREATE TABLE `course_offerings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `semester_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    `coordinator_id` INT DEFAULT NULL,
    `status` ENUM('draft', 'offered', 'cancelled', 'completed') NOT NULL DEFAULT 'offered',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_co_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_co_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_co_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_co_coordinator` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE KEY `uk_semester_course` (`semester_id`, `course_id`),
    INDEX `idx_co_sem_dept` (`semester_id`, `department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. Sections Table (Course Sections & Seat Capacity)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_offering_id` INT NOT NULL,
    `section_name` VARCHAR(10) NOT NULL,
    `capacity` INT NOT NULL DEFAULT 40,
    `enrolled_count` INT NOT NULL DEFAULT 0,
    `status` ENUM('open', 'closed', 'cancelled') NOT NULL DEFAULT 'open',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sections_offering` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_offering_section` (`course_offering_id`, `section_name`),
    INDEX `idx_sections_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 14. Teacher Assignments Table (Instructors Assigned to Sections)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `teacher_assignments`;
CREATE TABLE `teacher_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `section_id` INT NOT NULL,
    `teacher_id` INT NOT NULL,
    `role` ENUM('lead_instructor', 'co_instructor', 'lab_instructor', 'teaching_assistant') NOT NULL DEFAULT 'lead_instructor',
    `assigned_credits` DECIMAL(3,2) NOT NULL DEFAULT 3.00,
    `assigned_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ta_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ta_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ta_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE KEY `uk_section_teacher` (`section_id`, `teacher_id`),
    INDEX `idx_ta_teacher` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 15. Teacher Availability Table (Time Preferences & Unavailability)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `teacher_availability`;
CREATE TABLE `teacher_availability` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT NOT NULL,
    `semester_id` INT NOT NULL,
    `day_of_week` ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `reason` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tavail_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_tavail_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_tavail_lookup` (`teacher_id`, `semester_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 16. Student Course History Table (Past Academic Record & Transcripts)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `student_course_history`;
CREATE TABLE `student_course_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `semester_id` INT NOT NULL,
    `grade_letter` VARCHAR(5) DEFAULT NULL,
    `grade_point` DECIMAL(3,2) DEFAULT NULL,
    `credits_attempted` DECIMAL(3,2) NOT NULL,
    `credits_earned` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('in_progress', 'completed', 'failed', 'withdrawn', 'incomplete') NOT NULL DEFAULT 'completed',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sch_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_sch_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_sch_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_sch_student_course` (`student_id`, `course_id`),
    INDEX `idx_sch_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 17. Registration Requests Table (Course Registration Batches)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `registration_requests`;
CREATE TABLE `registration_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `semester_id` INT NOT NULL,
    `request_type` ENUM('regular', 'add_drop', 'retake', 'special_approval') NOT NULL DEFAULT 'regular',
    `total_credits` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('draft', 'submitted', 'advisor_approved', 'advisor_rejected', 'coordinator_approved', 'cancelled') NOT NULL DEFAULT 'draft',
    `submission_notes` TEXT DEFAULT NULL,
    `advisor_id` INT DEFAULT NULL,
    `advisor_remarks` TEXT DEFAULT NULL,
    `advisor_action_at` DATETIME DEFAULT NULL,
    `coordinator_id` INT DEFAULT NULL,
    `coordinator_remarks` TEXT DEFAULT NULL,
    `coordinator_action_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_rr_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rr_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_rr_advisor` FOREIGN KEY (`advisor_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_rr_coordinator` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_rr_student_sem` (`student_id`, `semester_id`),
    INDEX `idx_rr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 18. Registration Request Items Table (Individual Requested Courses)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `registration_request_items`;
CREATE TABLE `registration_request_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `section_id` INT NOT NULL,
    `action_type` ENUM('add', 'drop', 'retake') NOT NULL DEFAULT 'add',
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `rejection_reason` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_rri_request` FOREIGN KEY (`request_id`) REFERENCES `registration_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_rri_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY `uk_req_section` (`request_id`, `section_id`),
    INDEX `idx_rri_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 19. Registrations Table (Final Enrolled Student Course Sections)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `registrations`;
CREATE TABLE `registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `section_id` INT NOT NULL,
    `semester_id` INT NOT NULL,
    `registration_request_id` INT DEFAULT NULL,
    `status` ENUM('enrolled', 'dropped', 'withdrawn', 'cancelled') NOT NULL DEFAULT 'enrolled',
    `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `dropped_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reg_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_reg_request` FOREIGN KEY (`registration_request_id`) REFERENCES `registration_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE KEY `uk_student_section` (`student_id`, `section_id`),
    INDEX `idx_reg_student_sem` (`student_id`, `semester_id`),
    INDEX `idx_reg_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 20. Class Schedules Table (Weekly Academic Routine)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `class_schedules`;
CREATE TABLE `class_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `section_id` INT NOT NULL,
    `room_id` INT NOT NULL,
    `teacher_id` INT NOT NULL,
    `semester_id` INT NOT NULL,
    `day_of_week` ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `class_type` ENUM('lecture', 'lab', 'tutorial') NOT NULL DEFAULT 'lecture',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cs_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cs_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_cs_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_cs_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_cs_lookup` (`semester_id`, `day_of_week`, `start_time`, `end_time`),
    INDEX `idx_cs_room_conflict` (`room_id`, `day_of_week`, `start_time`, `end_time`),
    INDEX `idx_cs_teacher_conflict` (`teacher_id`, `day_of_week`, `start_time`, `end_time`),
    INDEX `idx_cs_section_conflict` (`section_id`, `day_of_week`, `start_time`, `end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 21. Schedule Revisions Table (Schedule Change Audits & Tracking)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `schedule_revisions`;
CREATE TABLE `schedule_revisions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `class_schedule_id` INT NOT NULL,
    `previous_day` ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    `previous_start_time` TIME NOT NULL,
    `previous_end_time` TIME NOT NULL,
    `previous_room_id` INT NOT NULL,
    `new_day` ENUM('Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') NOT NULL,
    `new_start_time` TIME NOT NULL,
    `new_end_time` TIME NOT NULL,
    `new_room_id` INT NOT NULL,
    `revision_reason` TEXT NOT NULL,
    `revised_by` INT NOT NULL,
    `effective_date` DATE NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sr_schedule` FOREIGN KEY (`class_schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_sr_prev_room` FOREIGN KEY (`previous_room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_sr_new_room` FOREIGN KEY (`new_room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_sr_user` FOREIGN KEY (`revised_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_sr_schedule` (`class_schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 22. Makeup Classes Table (Rescheduled & Special Class Sessions)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `makeup_classes`;
CREATE TABLE `makeup_classes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `section_id` INT NOT NULL,
    `teacher_id` INT NOT NULL,
    `room_id` INT NOT NULL,
    `makeup_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    `scheduled_by` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mc_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_mc_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_mc_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_mc_user` FOREIGN KEY (`scheduled_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_mc_date_room` (`makeup_date`, `room_id`, `start_time`, `end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 23. Exams Table (Midterms, Finals, and Quizzes Schedule)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `exams`;
CREATE TABLE `exams` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_offering_id` INT NOT NULL,
    `section_id` INT DEFAULT NULL,
    `exam_type` ENUM('midterm', 'final', 'quiz', 'lab_viva') NOT NULL,
    `exam_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `room_id` INT NOT NULL,
    `invigilator_teacher_id` INT DEFAULT NULL,
    `status` ENUM('scheduled', 'in_progress', 'completed', 'postponed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    `instructions` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_exams_offering` FOREIGN KEY (`course_offering_id`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_exams_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_exams_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_exams_teacher` FOREIGN KEY (`invigilator_teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_exam_room_time` (`room_id`, `exam_date`, `start_time`, `end_time`),
    INDEX `idx_exam_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 24. Notices Table (Broadcast Announcements & Updates)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `notices`;
CREATE TABLE `notices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `category` ENUM('academic', 'exam', 'registration', 'events', 'general', 'emergency') NOT NULL DEFAULT 'general',
    `department_id` INT DEFAULT NULL,
    `semester_id` INT DEFAULT NULL,
    `target_role` ENUM('all', 'student', 'teacher', 'academic_advisor', 'department_coordinator') NOT NULL DEFAULT 'all',
    `attachment_url` VARCHAR(255) DEFAULT NULL,
    `published_by` INT NOT NULL,
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `publish_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expiry_date` DATETIME DEFAULT NULL,
    `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notices_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_notices_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_notices_author` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_notices_filter` (`department_id`, `target_role`, `status`),
    INDEX `idx_notices_pinned` (`is_pinned`, `publish_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 25. Notice Recipients Table (Read/Unread Tracking for Users)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `notice_recipients`;
CREATE TABLE `notice_recipients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `notice_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_nr_notice` FOREIGN KEY (`notice_id`) REFERENCES `notices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_nr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `uk_notice_user` (`notice_id`, `user_id`),
    INDEX `idx_nr_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 26. Notifications Table (Individual Alerts & Status Updates)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'danger', 'registration', 'schedule', 'system') NOT NULL DEFAULT 'info',
    `action_url` VARCHAR(255) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_notif_user_unread` (`user_id`, `is_read`),
    INDEX `idx_notif_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 27. Faculty Profiles Table (Teacher Bio, Directory & Office Hours)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `faculty_profiles`;
CREATE TABLE `faculty_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `teacher_id` INT NOT NULL UNIQUE,
    `bio` TEXT DEFAULT NULL,
    `research_interests` TEXT DEFAULT NULL,
    `education` TEXT DEFAULT NULL,
    `publications` TEXT DEFAULT NULL,
    `office_hours` VARCHAR(255) DEFAULT NULL,
    `personal_website` VARCHAR(255) DEFAULT NULL,
    `google_scholar` VARCHAR(255) DEFAULT NULL,
    `linkedin_url` VARCHAR(255) DEFAULT NULL,
    `is_public` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_fp_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_fp_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 28. Academic Documents Table (Official University PDFs & Ordinances)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `academic_documents`;
CREATE TABLE `academic_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `document_code` VARCHAR(50) NOT NULL UNIQUE,
    `category` ENUM('academic_ordinance', 'course_catalog', 'grading_policy', 'registration_rules', 'exam_rules', 'disciplinary_code', 'general') NOT NULL,
    `department_id` INT DEFAULT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NOT NULL,
    `file_size_kb` INT NOT NULL,
    `version` VARCHAR(20) NOT NULL DEFAULT '1.0',
    `uploaded_by` INT NOT NULL,
    `processing_status` ENUM('pending', 'chunked', 'indexed', 'failed') NOT NULL DEFAULT 'pending',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ad_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_ad_author` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_ad_cat_status` (`category`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 29. Document Chunks Table (RAG Segments with Citations & Fulltext)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `document_chunks`;
CREATE TABLE `document_chunks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `document_id` INT NOT NULL,
    `chunk_index` INT NOT NULL,
    `chunk_title` VARCHAR(255) DEFAULT NULL,
    `content` TEXT NOT NULL,
    `token_count` INT NOT NULL DEFAULT 0,
    `embedding_id` VARCHAR(100) DEFAULT NULL,
    `metadata_json` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_dc_document` FOREIGN KEY (`document_id`) REFERENCES `academic_documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_dc_lookup` (`document_id`, `chunk_index`),
    FULLTEXT INDEX `ft_chunk_content` (`content`, `chunk_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 30. Helpdesk Conversations Table (AI Chat Sessions)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `helpdesk_conversations`;
CREATE TABLE `helpdesk_conversations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `session_id` VARCHAR(100) NOT NULL UNIQUE,
    `topic` VARCHAR(255) NOT NULL DEFAULT 'General Academic Query',
    `status` ENUM('active', 'closed', 'flagged') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_hc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_hc_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 31. Helpdesk Messages Table (Chat History & Grounded Citations)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `helpdesk_messages`;
CREATE TABLE `helpdesk_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT NOT NULL,
    `sender_type` ENUM('user', 'assistant', 'system') NOT NULL,
    `message` TEXT NOT NULL,
    `sources_cited_json` JSON DEFAULT NULL,
    `confidence_score` DECIMAL(3,2) DEFAULT NULL,
    `feedback_rating` TINYINT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_hm_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `helpdesk_conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_hm_conv_created` (`conversation_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 32. Audit Logs Table (Administrative & Security Event Logging)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `role` VARCHAR(50) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT DEFAULT NULL,
    `old_values_json` JSON DEFAULT NULL,
    `new_values_json` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
