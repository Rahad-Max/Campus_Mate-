<?php
$user = auth_user();
$role = $user['role'] ?? 'guest';
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

// Normalize current URI by removing script directory if in subfolder
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptName !== '/' && !empty($scriptName) && str_starts_with($currentUri, $scriptName)) {
    $currentUri = substr($currentUri, strlen($scriptName));
}
$currentUri = '/' . trim($currentUri, '/');
?>
<aside class="app-sidebar">
    <div class="sidebar-header">
        <a href="<?= url('/') ?>" class="sidebar-brand">
            <span class="rounded-3 bg-pub-primary text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </span>
            <span>CampusMate</span>
        </a>
    </div>

    <div class="sidebar-nav">
        <!-- Main Section -->
        <div class="nav-section-title">Navigation</div>
        <a href="<?= url('dashboard') ?>" class="sidebar-link <?= ($currentUri === '/dashboard' || $currentUri === '/') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($role === 'student'): ?>
            <!-- Student Links -->
            <div class="nav-section-title">Academic Portal</div>
            <a href="<?= url('student/semester') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/student/semester') ? 'active' : '' ?>">
                <i class="bi bi-calendar-check"></i>
                <span>Current Semester</span>
            </a>
            <a href="<?= url('student/courses') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/student/courses') ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i>
                <span>Available Courses</span>
            </a>
            <a href="<?= url('student/registration') ?>" class="sidebar-link <?= (str_starts_with($currentUri, '/student/registration') && !str_starts_with($currentUri, '/student/registration/history')) || $currentUri === '/registration' ? 'active' : '' ?>">
                <i class="bi bi-check2-circle"></i>
                <span>Course Registration</span>
            </a>
            <a href="<?= url('student/registration/history') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/student/registration/history') ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i>
                <span>Registration History</span>
            </a>
            <a href="<?= url('timetable') ?>" class="sidebar-link <?= $currentUri === '/timetable' ? 'active' : '' ?>">
                <i class="bi bi-calendar-week"></i>
                <span>Class Timetable</span>
            </a>
            <a href="<?= url('exams') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/exams') ? 'active' : '' ?>">
                <i class="bi bi-journal-bookmark"></i>
                <span>Exam Routine</span>
            </a>
            <a href="<?= url('makeup-classes') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/makeup-classes') ? 'active' : '' ?>">
                <i class="bi bi-calendar-plus"></i>
                <span>Make-up Classes</span>
            </a>
            <a href="<?= url('student/history') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/student/history') ? 'active' : '' ?>">
                <i class="bi bi-award"></i>
                <span>Academic History</span>
            </a>
            <a href="<?= url('student/profile') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/student/profile') ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i>
                <span>Student Profile</span>
            </a>
        <?php endif; ?>

        <?php if ($role === 'teacher'): ?>
            <!-- Teacher Links -->
            <div class="nav-section-title">Faculty Portal</div>
            <a href="<?= url('teacher/classes') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/teacher/classes') ? 'active' : '' ?>">
                <i class="bi bi-journal-bookmark"></i>
                <span>Assigned Classes</span>
            </a>
            <a href="<?= url('teacher/courses') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/teacher/courses') ? 'active' : '' ?>">
                <i class="bi bi-book"></i>
                <span>My Courses</span>
            </a>
            <a href="<?= url('teacher/assignments') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/teacher/assignments') ? 'active' : '' ?>">
                <i class="bi bi-person-workspace"></i>
                <span>Workload & Hours</span>
            </a>
            <a href="<?= url('timetable') ?>" class="sidebar-link <?= $currentUri === '/timetable' ? 'active' : '' ?>">
                <i class="bi bi-calendar-week"></i>
                <span>Teaching Schedule</span>
            </a>
            <a href="<?= url('exams') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/exams') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>Exam Duties</span>
            </a>
            <a href="<?= url('makeup-classes') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/makeup-classes') ? 'active' : '' ?>">
                <i class="bi bi-calendar-plus"></i>
                <span>Make-up Classes</span>
            </a>
            <a href="<?= url('teacher/profile') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/teacher/profile') ? 'active' : '' ?>">
                <i class="bi bi-person-lines-fill"></i>
                <span>Faculty Profile</span>
            </a>
        <?php endif; ?>

        <?php if ($role === 'academic_advisor'): ?>
            <!-- Advisor Links -->
            <div class="nav-section-title">Advising Portal</div>
            <a href="<?= url('advisor/registration-requests') ?>" class="sidebar-link <?= (str_starts_with($currentUri, '/advisor/registration-requests') || str_starts_with($currentUri, '/advisor/requests')) ? 'active' : '' ?>">
                <i class="bi bi-inbox"></i>
                <span>Registration Queue</span>
            </a>
            <a href="<?= url('advisor/advisees') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/advisor/advisees') ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>My Advisees</span>
            </a>
            <a href="<?= url('advisor/courses') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/advisor/courses') ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Curriculum Explorer</span>
            </a>
            <a href="<?= url('timetable') ?>" class="sidebar-link <?= $currentUri === '/timetable' ? 'active' : '' ?>">
                <i class="bi bi-calendar-week"></i>
                <span>Master Timetable</span>
            </a>
        <?php endif; ?>

        <?php if ($role === 'department_coordinator'): ?>
            <!-- Coordinator Links -->
            <div class="nav-section-title">Academic Administration</div>
            <a href="<?= url('coordinator/departments') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/departments') ? 'active' : '' ?>">
                <i class="bi bi-building"></i>
                <span>Departments</span>
            </a>
            <a href="<?= url('coordinator/semesters') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/semesters') ? 'active' : '' ?>">
                <i class="bi bi-calendar-event"></i>
                <span>Semesters</span>
            </a>
            <a href="<?= url('coordinator/curricula') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/curricula') ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Curricula</span>
            </a>
            <a href="<?= url('coordinator/courses') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/courses') ? 'active' : '' ?>">
                <i class="bi bi-journal-code"></i>
                <span>Courses & Prereqs</span>
            </a>
            <a href="<?= url('coordinator/course-offerings') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/course-offerings') ? 'active' : '' ?>">
                <i class="bi bi-collection"></i>
                <span>Course Offerings</span>
            </a>
            <a href="<?= url('coordinator/sections') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/sections') ? 'active' : '' ?>">
                <i class="bi bi-layout-three-columns"></i>
                <span>Sections & Seats</span>
            </a>
            <a href="<?= url('coordinator/rooms') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/rooms') ? 'active' : '' ?>">
                <i class="bi bi-door-open"></i>
                <span>Rooms & Facilities</span>
            </a>
            <a href="<?= url('coordinator/teacher-assignments') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/teacher-assignments') ? 'active' : '' ?>">
                <i class="bi bi-person-workspace"></i>
                <span>Teacher Assignments</span>
            </a>

            <div class="nav-section-title">Schedule & Routine Management</div>
            <a href="<?= url('coordinator/timetable') ?>" class="sidebar-link <?= (str_starts_with($currentUri, '/coordinator/timetable') && !str_contains($currentUri, 'revisions')) ? 'active' : '' ?>">
                <i class="bi bi-calendar3"></i>
                <span>Class Timetable</span>
            </a>
            <a href="<?= url('coordinator/exams') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/exams') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>Examination Routines</span>
            </a>
            <a href="<?= url('coordinator/makeup-classes') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/makeup-classes') ? 'active' : '' ?>">
                <i class="bi bi-calendar-plus"></i>
                <span>Make-up Classes</span>
            </a>
            <a href="<?= url('coordinator/timetable/revisions') ?>" class="sidebar-link <?= str_contains($currentUri, 'revisions') ? 'active' : '' ?>">
                <i class="bi bi-arrow-repeat"></i>
                <span>Schedule Revisions</span>
            </a>
        <?php endif; ?>

        <!-- University-Wide Utilities -->
        <div class="nav-section-title">Campus Directory & AI</div>
        <a href="<?= url('courses') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/courses') ? 'active' : '' ?>">
            <i class="bi bi-book"></i>
            <span>Course Catalog</span>
        </a>
        <a href="<?= url('faculty') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/faculty') ? 'active' : '' ?>">
            <i class="bi bi-person-video3"></i>
            <span>Faculty Directory</span>
        </a>
        <a href="<?= url('notices') ?>" class="sidebar-link <?= str_starts_with($currentUri, '/notices') ? 'active' : '' ?>">
            <i class="bi bi-megaphone"></i>
            <span>Notices & Events</span>
        </a>
        <a href="<?= url('helpdesk') ?>" class="sidebar-link text-primary fw-semibold <?= str_starts_with($currentUri, '/helpdesk') ? 'active' : '' ?>">
            <i class="bi bi-stars text-accent"></i>
            <span>AI Helpdesk (RAG)</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <span class="badge-role <?= $role ?>">
                <?= ucwords(str_replace('_', ' ', $role)) ?>
            </span>
            <a href="<?= url('logout') ?>" class="text-danger small text-decoration-none" title="Sign out">
                <i class="bi bi-box-arrow-right fs-6"></i>
            </a>
        </div>
    </div>
</aside>
