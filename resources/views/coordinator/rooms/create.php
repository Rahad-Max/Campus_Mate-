<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/rooms">Rooms</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Room</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Add Classroom / Lab Facility</h2>
    <p class="text-muted mb-0">Register a new lecture hall, laboratory, or seminar facility.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/rooms">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room Number / Identifier <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" placeholder="e.g. Room 302 or CSE Lab 2" required maxlength="20" value="<?= e(old('room_number')) ?>">
                            <div class="form-text small">Must be unique across the campus.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Campus Building / Complex <span class="text-danger">*</span></label>
                            <input type="text" name="building" class="form-control" placeholder="e.g. Academic Complex - Wing B" required maxlength="100" value="<?= e(old('building')) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Seating Capacity <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" class="form-control" placeholder="e.g. 50" required min="1" max="1000" value="<?= e(old('capacity', '50')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Facility Type</label>
                            <select name="room_type" class="form-select">
                                <option value="lecture" <?= old('room_type', 'lecture') === 'lecture' ? 'selected' : '' ?>>Lecture Gallery</option>
                                <option value="computer_lab" <?= old('room_type') === 'computer_lab' ? 'selected' : '' ?>>Computer Lab</option>
                                <option value="electronics_lab" <?= old('room_type') === 'electronics_lab' ? 'selected' : '' ?>>Electronics Lab</option>
                                <option value="seminar_hall" <?= old('room_type') === 'seminar_hall' ? 'selected' : '' ?>>Seminar Hall</option>
                                <option value="multimedia_room" <?= old('room_type') === 'multimedia_room' ? 'selected' : '' ?>>Multimedia / Auditorium</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Operational Status</label>
                            <select name="status" class="form-select">
                                <option value="available" <?= old('status', 'available') === 'available' ? 'selected' : '' ?>>Available</option>
                                <option value="maintenance" <?= old('status') === 'maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                                <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Save Room
                        </button>
                        <a href="/coordinator/rooms" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
