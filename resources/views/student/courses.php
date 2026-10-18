<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Course Catalog</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Explore Academic Courses</h2>
        <p class="text-muted mb-0">Browse university course offerings, prerequisite requirements, syllabus descriptions, and credits.</p>
    </div>
    <div>
        <a href="/student/semester" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-calendar-check"></i>
            <span>Current Term Offerings</span>
        </a>
    </div>
</div>

<!-- Search & Department Filter -->
<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/student/courses" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by course code or title..." value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= ($departmentId ?? '') == $dept['id'] ? 'selected' : '' ?>>
                            <?= e($dept['code']) ?> - <?= e($dept['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
                <?php if (!empty($search) || !empty($departmentId)): ?>
                    <a href="/student/courses" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Courses Grid / Table -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Course Code</th>
                        <th>Course Title & Outline</th>
                        <th>Department</th>
                        <th>Credit Hours</th>
                        <th>Type</th>
                        <th class="text-end" style="width: 120px;">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary"></i>
                                <div>No courses found matching your inquiry.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold fs-6 px-2 py-1">
                                        <?= e($c['course_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($c['title']) ?></div>
                                    <div class="text-muted small text-truncate" style="max-width: 400px;">
                                        <?= e($c['description'] ?: 'Syllabus outline available upon registration.') ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= e($c['dept_code'] ?? 'CSE') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fw-bold">
                                        <?= number_format((float)$c['credit'], 1) ?> cr
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= ucfirst($c['course_type']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="/courses/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
