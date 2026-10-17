-- =====================================================================
-- PUB CampusMate — Seed Demonstration Data
-- Department: Computer Science & Engineering (CSE)
-- Semester: Summer 2026
-- Demo Password for all seeded accounts: Password@123
-- Hash: $2y$10$mLOb2b5E/43ww.KwJOnhP.gkkPLMBEkkbw5p5zMobz/ziN41AWUjq
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. Departments
-- ---------------------------------------------------------------------
INSERT INTO `departments` (`id`, `code`, `name`, `faculty_name`, `description`, `status`) VALUES
(1, 'CSE', 'Department of Computer Science and Engineering', 'Faculty of Science and Engineering', 'Pioneering modern computing, AI research, and software engineering at Pundra University of Science & Technology.', 'active'),
(2, 'EEE', 'Department of Electrical and Electronic Engineering', 'Faculty of Science and Engineering', 'Advancing electronics, telecommunications, and sustainable power engineering.', 'active');

-- ---------------------------------------------------------------------
-- 2. Semesters
-- ---------------------------------------------------------------------
INSERT INTO `semesters` (`id`, `code`, `name`, `academic_year`, `term`, `start_date`, `end_date`, `registration_start`, `registration_end`, `add_drop_deadline`, `is_current`, `status`) VALUES
(1, 'SPRING-2026', 'Spring 2026', 2026, 'Spring', '2026-01-05', '2026-04-30', '2025-12-20 09:00:00', '2026-01-10 17:00:00', '2026-01-20 17:00:00', 0, 'closed'),
(2, 'SUMMER-2026', 'Summer 2026', 2026, 'Summer', '2026-05-10', '2026-08-31', '2026-04-25 09:00:00', '2026-05-18 23:59:00', '2026-05-25 17:00:00', 1, 'registration_open'),
(3, 'FALL-2026', 'Fall 2026', 2026, 'Fall', '2026-09-15', '2026-12-31', '2026-08-25 09:00:00', '2026-09-20 17:00:00', '2026-09-30 17:00:00', 0, 'upcoming');

-- ---------------------------------------------------------------------
-- 3. Users (Password: Password@123)
-- ---------------------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `phone`, `avatar_url`, `is_active`) VALUES
(1, 'student21', 'student@pub.ac.bd', '$2y$10$mLOb2b5E/43ww.KwJOnhP.gkkPLMBEkkbw5p5zMobz/ziN41AWUjq', 'student', 'Rahat', 'Rahman', '+8801710000001', 'assets/images/student.png', 1),
(2, 'tamanna22', 'tamanna@pub.ac.bd', '$2y$10$mLOb2b5E/43ww.KwJOnhP.gkkPLMBEkkbw5p5zMobz/ziN41AWUjq', 'student', 'Tamanna', 'Akter', '+8801710000002', 'assets/images/student2.png', 1),
(3, 'prof_tanvir', 'teacher@pub.ac.bd', '$2y$10$mLOb2b5E/43ww.KwJOnhP.gkkPLMBEkkbw5p5zMobz/ziN41AWUjq', 'teacher', 'Tanvir', 'Ahmed', '+8801720000001', 'assets/images/teacher.png', 1),
(4, 'advisor_kazi', 'advisor@pub.ac.bd', '$2y$10$mLOb2b5E/43ww.KwJOnhP.gkkPLMBEkkbw5p5zMobz/ziN41AWUjq', 'academic_advisor', 'Dr. Kazi', 'Nahid', '+8801730000001', 'assets/images/advisor.png', 1),
(5, 'coord_hasan', 'coordinator@pub.ac.bd', '$2y$10$mLOb2b5E/43ww.KwJOnhP.gkkPLMBEkkbw5p5zMobz/ziN41AWUjq', 'department_coordinator', 'Dr. Mahmudul', 'Hasan', '+8801740000001', 'assets/images/coordinator.png', 1);

-- ---------------------------------------------------------------------
-- 4. Teachers
-- ---------------------------------------------------------------------
INSERT INTO `teachers` (`id`, `user_id`, `department_id`, `teacher_id_card`, `designation`, `office_room`, `joining_date`, `max_credit_load`, `status`) VALUES
(1, 3, 1, 'T-CSE-001', 'Assistant Professor', 'Room-402', '2020-02-01', 15.00, 'active'),
(2, 4, 1, 'T-CSE-002', 'Associate Professor', 'Room-405', '2017-08-15', 12.00, 'active'),
(3, 5, 1, 'T-CSE-003', 'Professor & Head', 'Room-401', '2014-01-10', 9.00, 'active');

-- ---------------------------------------------------------------------
-- 5. Students
-- ---------------------------------------------------------------------
INSERT INTO `students` (`id`, `user_id`, `department_id`, `student_id_card`, `batch_no`, `admission_semester_id`, `current_semester_level`, `cgpa`, `completed_credits`, `academic_status`) VALUES
(1, 1, 1, '210101001', 'Batch 21', 1, 3, 3.65, 36.00, 'regular'),
(2, 2, 1, '220101002', 'Batch 22', 1, 2, 3.82, 19.50, 'regular');

-- ---------------------------------------------------------------------
-- 6. Academic Advisors Mapping
-- ---------------------------------------------------------------------
INSERT INTO `academic_advisors` (`id`, `teacher_id`, `student_id`, `assigned_semester_id`, `status`, `notes`) VALUES
(1, 2, 1, 2, 'active', 'Assigned academic advisor for Batch 21 CSE students.'),
(2, 2, 2, 2, 'active', 'Assigned academic advisor for Batch 22 CSE students.');

-- ---------------------------------------------------------------------
-- 7. Curricula
-- ---------------------------------------------------------------------
INSERT INTO `curricula` (`id`, `department_id`, `name`, `version`, `total_credits_required`, `is_active`) VALUES
(1, 1, 'B.Sc. in Computer Science and Engineering (Outcome-Based Education)', '2022.1', 148.00, 1);

-- ---------------------------------------------------------------------
-- 8. Courses
-- ---------------------------------------------------------------------
INSERT INTO `courses` (`id`, `department_id`, `course_code`, `title`, `credit`, `course_type`, `description`, `status`) VALUES
(1, 1, 'CSE-1101', 'Structured Programming Language', 3.00, 'theory', 'Fundamentals of C programming, control structures, arrays, pointers, functions, dynamic memory.', 'active'),
(2, 1, 'CSE-1102', 'Structured Programming Language Sessional', 1.50, 'lab', 'Hands-on programming laboratory in C, algorithmic problem solving and debugging.', 'active'),
(3, 1, 'MATH-1101', 'Differential and Integral Calculus', 3.00, 'theory', 'Functions, limits, continuity, differential calculus, applications of derivatives, integration.', 'active'),
(4, 1, 'CSE-1201', 'Object Oriented Programming', 3.00, 'theory', 'Java and C++ OOP principles: classes, objects, encapsulation, inheritance, polymorphism, and exceptions.', 'active'),
(5, 1, 'CSE-1202', 'Object Oriented Programming Sessional', 1.50, 'lab', 'Practical implementation of OOP designs, GUI programming, and file handling.', 'active'),
(6, 1, 'CSE-2101', 'Data Structures', 3.00, 'theory', 'Linear and non-linear data structures: stacks, queues, linked lists, trees, graphs, and hashing.', 'active'),
(7, 1, 'CSE-2102', 'Data Structures Sessional', 1.50, 'lab', 'Lab implementation and performance profiling of foundational data structures.', 'active'),
(8, 1, 'CSE-2201', 'Algorithms', 3.00, 'theory', 'Divide and conquer, greedy algorithms, dynamic programming, graph traversal, and complexity analysis.', 'active'),
(9, 1, 'CSE-3101', 'Database Management Systems', 3.00, 'theory', 'Relational database model, SQL, normalization, transactions, ACID properties, indexing, and recovery.', 'active'),
(10, 1, 'CSE-3102', 'Database Management Systems Sessional', 1.50, 'lab', 'Practical relational database design, query optimization, indexing, and full-stack DB integration.', 'active');

-- ---------------------------------------------------------------------
-- 9. Curriculum Courses
-- ---------------------------------------------------------------------
INSERT INTO `curriculum_courses` (`id`, `curriculum_id`, `course_id`, `recommended_semester`, `is_elective`, `elective_group`) VALUES
(1, 1, 1, 1, 0, NULL),
(2, 1, 2, 1, 0, NULL),
(3, 1, 3, 1, 0, NULL),
(4, 1, 4, 2, 0, NULL),
(5, 1, 5, 2, 0, NULL),
(6, 1, 6, 3, 0, NULL),
(7, 1, 7, 3, 0, NULL),
(8, 1, 8, 4, 0, NULL),
(9, 1, 9, 5, 0, NULL),
(10, 1, 10, 5, 0, NULL);

-- ---------------------------------------------------------------------
-- 10. Course Prerequisites (Proper Relationship Table)
-- ---------------------------------------------------------------------
INSERT INTO `course_prerequisites` (`id`, `course_id`, `prerequisite_course_id`, `min_grade`, `is_mandatory`) VALUES
(1, 4, 1, 'D', 1), -- CSE-1201 OOP requires CSE-1101 Structured Programming
(2, 5, 2, 'D', 1), -- CSE-1202 OOP Lab requires CSE-1102 SPL Lab
(3, 6, 1, 'D', 1), -- CSE-2101 Data Structures requires CSE-1101 Structured Programming
(4, 7, 2, 'D', 1), -- CSE-2102 Data Structures Lab requires CSE-1102 SPL Lab
(5, 8, 6, 'D', 1), -- CSE-2201 Algorithms requires CSE-2101 Data Structures
(6, 9, 6, 'D', 1); -- CSE-3101 DBMS requires CSE-2101 Data Structures

-- ---------------------------------------------------------------------
-- 11. Rooms
-- ---------------------------------------------------------------------
INSERT INTO `rooms` (`id`, `room_number`, `building`, `floor`, `room_type`, `capacity`, `has_projector`, `has_ac`, `status`) VALUES
(1, 'Room-301', 'Academic Building 1', 3, 'lecture', 45, 1, 1, 'available'),
(2, 'Room-302', 'Academic Building 1', 3, 'lecture', 45, 1, 1, 'available'),
(3, 'Lab-305', 'Academic Building 1', 3, 'computer_lab', 40, 1, 1, 'available'),
(4, 'Seminar-Hall', 'Administrative Building', 1, 'seminar_hall', 120, 1, 1, 'available');

-- ---------------------------------------------------------------------
-- 12. Course Offerings (Summer 2026)
-- ---------------------------------------------------------------------
INSERT INTO `course_offerings` (`id`, `semester_id`, `course_id`, `department_id`, `coordinator_id`, `status`) VALUES
(1, 2, 6, 1, 5, 'offered'), -- CSE-2101 Data Structures
(2, 2, 7, 1, 5, 'offered'), -- CSE-2102 Data Structures Sessional
(3, 2, 9, 1, 5, 'offered'), -- CSE-3101 Database Management Systems
(4, 2, 10, 1, 5, 'offered'), -- CSE-3102 DBMS Sessional
(5, 2, 3, 1, 5, 'offered'); -- MATH-1101 Calculus

-- ---------------------------------------------------------------------
-- 13. Sections
-- ---------------------------------------------------------------------
INSERT INTO `sections` (`id`, `course_offering_id`, `section_name`, `capacity`, `enrolled_count`, `status`) VALUES
(1, 1, 'A', 40, 28, 'open'),
(2, 1, 'B', 40, 22, 'open'),
(3, 2, 'A', 35, 28, 'open'),
(4, 3, 'A', 40, 30, 'open'),
(5, 4, 'A', 35, 30, 'open'),
(6, 5, 'A', 45, 35, 'open');

-- ---------------------------------------------------------------------
-- 14. Teacher Assignments
-- ---------------------------------------------------------------------
INSERT INTO `teacher_assignments` (`id`, `section_id`, `teacher_id`, `role`, `assigned_credits`, `assigned_by`) VALUES
(1, 1, 1, 'lead_instructor', 3.00, 5), -- Tanvir Ahmed -> CSE-2101 Sec A
(2, 2, 2, 'lead_instructor', 3.00, 5), -- Kazi Nahid -> CSE-2101 Sec B
(3, 3, 1, 'lab_instructor', 1.50, 5),  -- Tanvir Ahmed -> CSE-2102 Lab A
(4, 4, 3, 'lead_instructor', 3.00, 5), -- Mahmudul Hasan -> CSE-3101 Sec A
(5, 5, 2, 'lab_instructor', 1.50, 5);  -- Kazi Nahid -> CSE-3102 Lab A

-- ---------------------------------------------------------------------
-- 15. Teacher Availability
-- ---------------------------------------------------------------------
INSERT INTO `teacher_availability` (`id`, `teacher_id`, `semester_id`, `day_of_week`, `start_time`, `end_time`, `is_available`, `reason`) VALUES
(1, 1, 2, 'Sunday', '09:00:00', '16:00:00', 1, 'Regular Teaching Hours'),
(2, 1, 2, 'Tuesday', '09:00:00', '16:00:00', 1, 'Regular Teaching Hours'),
(3, 2, 2, 'Monday', '10:00:00', '16:00:00', 1, 'Office & Research Hours'),
(4, 2, 2, 'Wednesday', '10:00:00', '16:00:00', 1, 'Teaching Hours');

-- ---------------------------------------------------------------------
-- 16. Student Past Course History
-- ---------------------------------------------------------------------
INSERT INTO `student_course_history` (`id`, `student_id`, `course_id`, `semester_id`, `grade_letter`, `grade_point`, `credits_attempted`, `credits_earned`, `status`) VALUES
(1, 1, 1, 1, 'A', 3.75, 3.00, 3.00, 'completed'),  -- Rahat completed CSE-1101
(2, 1, 2, 1, 'A+', 4.00, 1.50, 1.50, 'completed'), -- Rahat completed CSE-1102
(3, 1, 3, 1, 'A-', 3.50, 3.00, 3.00, 'completed'), -- Rahat completed MATH-1101
(4, 2, 1, 1, 'A+', 4.00, 3.00, 3.00, 'completed'), -- Tamanna completed CSE-1101
(5, 2, 2, 1, 'A+', 4.00, 1.50, 1.50, 'completed'); -- Tamanna completed CSE-1102

-- ---------------------------------------------------------------------
-- 17. Registration Requests (Summer 2026)
-- ---------------------------------------------------------------------
INSERT INTO `registration_requests` (`id`, `student_id`, `semester_id`, `request_type`, `total_credits`, `status`, `submission_notes`, `advisor_id`, `advisor_remarks`, `advisor_action_at`, `coordinator_id`, `coordinator_remarks`, `coordinator_action_at`) VALUES
(1, 1, 2, 'regular', 9.00, 'advisor_approved', 'Applying for 3rd semester core courses.', 2, 'Prerequisites verified. Approved for registration.', '2026-05-12 11:30:00', 5, 'Registration confirmed.', '2026-05-12 15:00:00');

-- ---------------------------------------------------------------------
-- 18. Registration Request Items
-- ---------------------------------------------------------------------
INSERT INTO `registration_request_items` (`id`, `request_id`, `section_id`, `action_type`, `status`) VALUES
(1, 1, 1, 'add', 'approved'), -- CSE-2101 Sec A
(2, 1, 3, 'add', 'approved'), -- CSE-2102 Sec A
(3, 1, 4, 'add', 'approved'); -- CSE-3101 Sec A

-- ---------------------------------------------------------------------
-- 19. Registrations (Active Confirmed Enrolment)
-- ---------------------------------------------------------------------
INSERT INTO `registrations` (`id`, `student_id`, `section_id`, `semester_id`, `registration_request_id`, `status`, `enrolled_at`) VALUES
(1, 1, 1, 2, 1, 'enrolled', '2026-05-12 15:00:00'),
(2, 1, 3, 2, 1, 'enrolled', '2026-05-12 15:00:00'),
(3, 1, 4, 2, 1, 'enrolled', '2026-05-12 15:00:00');

-- ---------------------------------------------------------------------
-- 20. Class Schedules (Timetable)
-- ---------------------------------------------------------------------
INSERT INTO `class_schedules` (`id`, `section_id`, `room_id`, `teacher_id`, `semester_id`, `day_of_week`, `start_time`, `end_time`, `class_type`) VALUES
(1, 1, 1, 1, 2, 'Sunday', '09:00:00', '10:30:00', 'lecture'),  -- CSE-2101 A (Sun 9:00-10:30, Room-301)
(2, 1, 1, 1, 2, 'Tuesday', '09:00:00', '10:30:00', 'lecture'), -- CSE-2101 A (Tue 9:00-10:30, Room-301)
(3, 3, 3, 1, 2, 'Tuesday', '11:00:00', '14:00:00', 'lab'),     -- CSE-2102 A Lab (Tue 11:00-14:00, Lab-305)
(4, 4, 2, 3, 2, 'Monday', '10:30:00', '12:00:00', 'lecture'),  -- CSE-3101 A (Mon 10:30-12:00, Room-302)
(5, 4, 2, 3, 2, 'Wednesday', '10:30:00', '12:00:00', 'lecture');-- CSE-3101 A (Wed 10:30-12:00, Room-302)

-- ---------------------------------------------------------------------
-- 21. Schedule Revisions
-- ---------------------------------------------------------------------
INSERT INTO `schedule_revisions` (`id`, `class_schedule_id`, `previous_day`, `previous_start_time`, `previous_end_time`, `previous_room_id`, `new_day`, `new_start_time`, `new_end_time`, `new_room_id`, `revision_reason`, `revised_by`, `effective_date`) VALUES
(1, 1, 'Saturday', '09:00:00', '10:30:00', 1, 'Sunday', '09:00:00', '10:30:00', 1, 'Shifted weekend timetable slot to Sunday to accommodate student requests.', 5, '2026-05-15');

-- ---------------------------------------------------------------------
-- 22. Makeup Classes
-- ---------------------------------------------------------------------
INSERT INTO `makeup_classes` (`id`, `section_id`, `teacher_id`, `room_id`, `makeup_date`, `start_time`, `end_time`, `reason`, `status`, `scheduled_by`) VALUES
(1, 1, 1, 1, '2026-06-06', '14:00:00', '15:30:00', 'Covering holiday lecture for Data Structures sorting techniques.', 'scheduled', 3);

-- ---------------------------------------------------------------------
-- 23. Exams
-- ---------------------------------------------------------------------
INSERT INTO `exams` (`id`, `course_offering_id`, `section_id`, `exam_type`, `exam_date`, `start_time`, `end_time`, `room_id`, `invigilator_teacher_id`, `status`, `instructions`) VALUES
(1, 1, 1, 'midterm', '2026-06-20', '10:00:00', '12:00:00', 1, 2, 'scheduled', 'Calculators permitted. No electronic smartwatches allowed.'),
(2, 3, 4, 'midterm', '2026-06-22', '10:00:00', '12:00:00', 2, 1, 'scheduled', 'All questions are compulsory. Bring student ID card.');

-- ---------------------------------------------------------------------
-- 24. Notices
-- ---------------------------------------------------------------------
INSERT INTO `notices` (`id`, `title`, `content`, `category`, `department_id`, `semester_id`, `target_role`, `attachment_url`, `published_by`, `is_pinned`, `publish_date`, `status`) VALUES
(1, 'Course Registration Schedule for Summer 2026', 'All students of the Department of Computer Science & Engineering are advised to complete their course registration by May 18, 2026. Please consult your respective academic advisors prior to submission.', 'registration', 1, 2, 'all', 'documents/notice_reg_summer2026.pdf', 5, 1, '2026-05-01 09:00:00', 'published'),
(2, 'Midterm Examination Schedule Announcement', 'The Midterm Examinations for Summer 2026 will commence from June 20, 2026. Please verify your timetable and seat allotment in the student portal.', 'exam', 1, 2, 'student', NULL, 5, 0, '2026-05-10 10:00:00', 'published');

-- ---------------------------------------------------------------------
-- 25. Notice Recipients
-- ---------------------------------------------------------------------
INSERT INTO `notice_recipients` (`id`, `notice_id`, `user_id`, `is_read`, `read_at`) VALUES
(1, 1, 1, 1, '2026-05-02 10:15:00'),
(2, 1, 2, 0, NULL);

-- ---------------------------------------------------------------------
-- 26. Notifications
-- ---------------------------------------------------------------------
INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `action_url`, `is_read`) VALUES
(1, 1, 'Registration Approved', 'Your Summer 2026 course registration has been approved by your Academic Advisor Dr. Kazi Nahid.', 'success', '/registration', 1),
(2, 4, 'Pending Registration Requests', 'You have 2 pending student course registration requests awaiting review.', 'warning', '/advisor/advisees', 0);

-- ---------------------------------------------------------------------
-- 27. Faculty Profiles
-- ---------------------------------------------------------------------
INSERT INTO `faculty_profiles` (`id`, `teacher_id`, `bio`, `research_interests`, `education`, `publications`, `office_hours`, `personal_website`, `is_public`) VALUES
(1, 1, 'Assistant Professor specializing in High Performance Computing, Distributed Systems, and Parallel Algorithms.', 'Distributed Systems, Parallel Computing, Cloud Infrastructure', 'M.Sc. in Computer Science (BUET), B.Sc. in CSE (PUB)', '12 peer-reviewed conference publications in IEEE & ACM.', 'Sunday & Tuesday 2:00 PM - 4:00 PM', 'https://tanvir.pub.ac.bd', 1),
(2, 2, 'Associate Professor with research focus in Machine Learning, Natural Language Processing, and Educational Technology.', 'NLP, Deep Learning, Bengali Speech Recognition', 'Ph.D. in Computer Science (NUS), B.Sc. in CSE (DU)', '20+ international journal publications in Springer and Elsevier.', 'Monday & Wednesday 1:00 PM - 3:30 PM', 'https://kazi.pub.ac.bd', 1),
(3, 3, 'Professor and Head of Department leading curriculum modernization and research collaborations at PUB.', 'Database Systems, Data Mining, Information Retrieval', 'Ph.D. in Computer Engineering, M.Sc. in CSE', 'Over 35 international research articles and 2 textbook chapters.', 'Saturday & Thursday 11:00 AM - 1:00 PM', 'https://hasan.pub.ac.bd', 1);

-- ---------------------------------------------------------------------
-- 28. Academic Documents (Official University Handbook & Ordinance)
-- ---------------------------------------------------------------------
INSERT INTO `academic_documents` (`id`, `title`, `document_code`, `category`, `department_id`, `file_path`, `file_type`, `file_size_kb`, `version`, `uploaded_by`, `processing_status`, `is_active`) VALUES
(1, 'PUB Undergraduate Academic Ordinance and Examination Rules', 'PUB-ORD-2022-V1', 'academic_ordinance', 1, 'storage/documents/pub_ordinance_2022.pdf', 'pdf', 1240, '2022.1', 5, 'indexed', 1),
(2, 'Course Registration & Credit Transfer Guidelines', 'PUB-REG-GUIDE-2026', 'registration_rules', 1, 'storage/documents/pub_registration_guide.pdf', 'pdf', 850, '1.2', 5, 'indexed', 1);

-- ---------------------------------------------------------------------
-- 29. Document Chunks (RAG Seed for AI Helpdesk)
-- ---------------------------------------------------------------------
INSERT INTO `document_chunks` (`id`, `document_id`, `chunk_index`, `chunk_title`, `content`, `token_count`, `embedding_id`, `metadata_json`) VALUES
(1, 1, 1, 'Article 4: Course Registration Policy', 'A student must register for a minimum of 9.0 credit hours and a maximum of 18.0 credit hours in a regular semester. Students on academic probation (CGPA below 2.50) are limited to a maximum of 12.0 credit hours unless granted special Dean approval.', 62, 'chunk_pub_ord_art4', '{"article": "4.1", "source": "Academic Ordinance 2022", "page": 8}'),
(2, 1, 2, 'Article 7: Prerequisite Course Requirements', 'No student shall be allowed to register for a course without having passed all prerequisite courses specified in the department curriculum with at least a minimum passing grade of D (Grade Point 2.00). In retake situations, priority is determined by the Academic Advisor.', 58, 'chunk_pub_ord_art7', '{"article": "7.3", "source": "Academic Ordinance 2022", "page": 14}'),
(3, 1, 3, 'Article 9: Add/Drop and Withdrawal Guidelines', 'Course add or drop requests must be submitted within 7 working days from the formal date of commencement of classes. Dropping a course within this period leaves no record on the official transcript. Withdrawals requested thereafter will reflect as W grade.', 54, 'chunk_pub_ord_art9', '{"article": "9.2", "source": "Academic Ordinance 2022", "page": 19}');

-- ---------------------------------------------------------------------
-- 30. Helpdesk Conversations
-- ---------------------------------------------------------------------
INSERT INTO `helpdesk_conversations` (`id`, `user_id`, `session_id`, `topic`, `status`) VALUES
(1, 1, 'sess_demo_663f10a8c4', 'Credit Limits and Course Prerequisites', 'active');

-- ---------------------------------------------------------------------
-- 31. Helpdesk Messages (Grounded with Citations)
-- ---------------------------------------------------------------------
INSERT INTO `helpdesk_messages` (`id`, `conversation_id`, `sender_type`, `message`, `sources_cited_json`, `confidence_score`) VALUES
(1, 1, 'user', 'What is the maximum number of credits I can take this semester if I am on academic probation?', NULL, NULL),
(2, 1, 'assistant', 'According to Article 4.1 of the PUB Undergraduate Academic Ordinance, students on academic probation (CGPA below 2.50) are restricted to a maximum of 12.0 credit hours in a regular semester, unless special approval is granted by the Dean.', '[{"document_title": "PUB Undergraduate Academic Ordinance and Examination Rules", "chunk_title": "Article 4: Course Registration Policy", "page": 8}]', 0.98);

-- ---------------------------------------------------------------------
-- 32. Audit Logs
-- ---------------------------------------------------------------------
INSERT INTO `audit_logs` (`id`, `user_id`, `role`, `ip_address`, `user_agent`, `action`, `entity_type`, `entity_id`, `old_values_json`, `new_values_json`) VALUES
(1, 5, 'department_coordinator', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'course_offering.published', 'course_offerings', 1, NULL, '{"semester_id": 2, "course_code": "CSE-2101", "status": "offered"}'),
(2, 4, 'academic_advisor', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'registration.advisor_approved', 'registration_requests', 1, '{"status": "submitted"}', '{"status": "advisor_approved", "advisor_id": 2}');

SET FOREIGN_KEY_CHECKS = 1;
