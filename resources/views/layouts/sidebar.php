<?php
$user = auth_user();
$role = $user['role'] ?? 'guest';
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<aside class="app-sidebar">
    <div class="sidebar-header">
        <a href="/" class="sidebar-brand">
            <span class="rounded-3 bg-pub-primary text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </span>
            <span>CampusMate</span>
        </a>
    </div>

    <div class="sidebar-nav">
        <!-- Main Section -->
        <div class="nav-section-title">Navigation</div>
        <a href="/dashboard" class="sidebar-link <?= str_starts_with($currentUri, '/dashboard') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($role === 'student'): ?>
            <!-- Student Links -->
            <div class="nav-section-title">Academic Portal</div>
            <a href="/registration" class="sidebar-link <?= str_starts_with($currentUri, '/registration') ? 'active' : '' ?>">
                <i class="bi bi-check2-circle"></i>
                <span>Course Registration</span>
            </a>
            <a href="/timetable" class="sidebar-link <?= $currentUri === '/timetable' ? 'active' : '' ?>">
                <i class="bi bi-calendar-week"></i>
                <span>Class Timetable</span>
            </a>
            <a href="/exams" class="sidebar-link <?= str_starts_with($currentUri, '/exams') ? 'active' : '' ?>">
                <i class="bi bi-journal-text"></i>
                <span>Exam Routine</span>
            </a>
            <a href="/student/history" class="sidebar-link <?= str_starts_with($currentUri, '/student/history') ? 'active' : '' ?>">
                <i class="bi bi-award"></i>
                <span>Academic History</span>
            </a>
            <a href="/student/profile" class="sidebar-link <?= str_starts_with($currentUri, '/student/profile') ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i>
                <span>Student Profile</span>
            </a>
        <?php endif; ?>

        <?php if ($role === 'teacher'): ?>
            <!-- Teacher Links -->
            <div class="nav-section-title">Faculty Portal</div>
            <a href="/teacher/classes" class="sidebar-link <?= str_starts_with($currentUri, '/teacher/classes') ? 'active' : '' ?>">
                <i class="bi bi-journal-bookmark"></i>
                <span>Assigned Classes</span>
            </a>
            <a href="/timetable" class="sidebar-link <?= $currentUri === '/timetable' ? 'active' : '' ?>">
                <i class="bi bi-calendar-week"></i>
                <span>Teaching Schedule</span>
            </a>
            <a href="/teacher/profile" class="sidebar-link <?= str_starts_with($currentUri, '/teacher/profile') ? 'active' : '' ?>">
                <i class="bi bi-person-lines-fill"></i>
                <span>Faculty Profile</span>
            </a>
        <?php endif; ?>

        <?php if ($role === 'academic_advisor'): ?>
            <!-- Advisor Links -->
            <div class="nav-section-title">Advising Portal</div>
            <a href="/advisor/requests" class="sidebar-link <?= str_starts_with($currentUri, '/advisor/requests') ? 'active' : '' ?>">
                <i class="bi bi-inbox"></i>
                <span>Registration Queue</span>
            </a>
            <a href="/advisor/advisees" class="sidebar-link <?= str_starts_with($currentUri, '/advisor/advisees') ? 'active' : '' ?>">
                <i class="bi bi-people"></i>
                <span>My Advisees</span>
            </a>
            <a href="/timetable" class="sidebar-link <?= $currentUri === '/timetable' ? 'active' : '' ?>">
                <i class="bi bi-calendar-week"></i>
                <span>Master Timetable</span>
            </a>
        <?php endif; ?>

        <?php if ($role === 'department_coordinator'): ?>
            <!-- Coordinator Links -->
            <div class="nav-section-title">Coordination</div>
            <a href="/coordinator/offerings" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/offerings') ? 'active' : '' ?>">
                <i class="bi bi-collection"></i>
                <span>Course Offerings</span>
            </a>
            <a href="/coordinator/curriculum" class="sidebar-link <?= str_starts_with($currentUri, '/coordinator/curriculum') ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i>
                <span>Curriculum Matrix</span>
            </a>
            <a href="/timetable" class="sidebar-link <?= $currentUri === '/timetable' ? 'active' : '' ?>">
                <i class="bi bi-calendar3"></i>
                <span>Schedule Management</span>
            </a>
        <?php endif; ?>

        <!-- University-Wide Utilities -->
        <div class="nav-section-title">Campus Directory & AI</div>
        <a href="/courses" class="sidebar-link <?= str_starts_with($currentUri, '/courses') ? 'active' : '' ?>">
            <i class="bi bi-book"></i>
            <span>Course Catalog</span>
        </a>
        <a href="/faculty" class="sidebar-link <?= str_starts_with($currentUri, '/faculty') ? 'active' : '' ?>">
            <i class="bi bi-person-video3"></i>
            <span>Faculty Directory</span>
        </a>
        <a href="/notices" class="sidebar-link <?= str_starts_with($currentUri, '/notices') ? 'active' : '' ?>">
            <i class="bi bi-megaphone"></i>
            <span>Notices & Events</span>
        </a>
        <a href="/helpdesk" class="sidebar-link text-primary fw-semibold <?= str_starts_with($currentUri, '/helpdesk') ? 'active' : '' ?>">
            <i class="bi bi-stars text-accent"></i>
            <span>AI Helpdesk (RAG)</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <span class="badge-role <?= $role ?>">
                <?= ucwords(str_replace('_', ' ', $role)) ?>
            </span>
            <a href="/logout" class="text-danger small text-decoration-none" title="Sign out">
                <i class="bi bi-box-arrow-right fs-6"></i>
            </a>
        </div>
    </div>
</aside>
