<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Rooms</li>
            </ol>
        </nav>
        <h2 class="fw-bold mb-1">Classrooms & Lab Facilities</h2>
        <p class="text-muted mb-0">Manage university lecture galleries, computer labs, and facility seating capacities.</p>
    </div>
    <div>
        <a href="/coordinator/rooms/create" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-door-open"></i>
            <span>Add Room</span>
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-pub border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/coordinator/rooms" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search room number or building..." value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="building" class="form-select">
                    <option value="">All Buildings</option>
                    <?php foreach ($buildings as $bld): ?>
                        <option value="<?= e($bld) ?>" <?= ($building ?? '') === $bld ? 'selected' : '' ?>>
                            <?= e($bld) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                <?php if (!empty($search) || !empty($building)): ?>
                    <a href="/coordinator/rooms" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Rooms Table -->
<div class="card card-pub border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Room Number</th>
                        <th>Campus Building / Complex</th>
                        <th>Facility Type</th>
                        <th>Seating Capacity</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-door-closed fs-1 d-block mb-2 text-secondary"></i>
                                <div>No rooms found matching your criteria.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $r): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold fs-6 text-primary">
                                        <i class="bi bi-door-closed me-1"></i><?= e($r['room_number']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($r['building'] ?? 'Academic Building') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= ucfirst($r['type'] ?? 'lecture') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold fs-6"><?= (int)$r['capacity'] ?></span> <span class="text-muted small">students</span>
                                </td>
                                <td>
                                    <?php
                                    $roomStatus = match($r['status'] ?? 'active') {
                                        'active' => 'success',
                                        'maintenance' => 'warning',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $roomStatus ?>-subtle text-<?= $roomStatus ?> px-2 py-1">
                                        <?= ucfirst($r['status'] ?? 'active') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/coordinator/rooms/<?= $r['id'] ?>/edit" class="btn btn-outline-primary" title="Edit Room">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="/coordinator/rooms/<?= $r['id'] ?>/toggle-status" class="d-inline" onsubmit="return confirm('Toggle active/maintenance status for <?= e($r['room_number']) ?>?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-warning" title="Toggle Maintenance">
                                                <i class="bi bi-tools"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="/coordinator/rooms/<?= $r['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete room <?= e($r['room_number']) ?>? Cannot be deleted if scheduled.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger" title="Delete Room">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
