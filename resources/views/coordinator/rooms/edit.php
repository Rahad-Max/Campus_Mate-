<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/coordinator/rooms">Rooms</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Room</li>
        </ol>
    </nav>
    <h2 class="fw-bold mb-1">Edit Room: <?= e($room['room_number']) ?></h2>
    <p class="text-muted mb-0">Update facility details, seating limits, and maintenance condition.</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pub border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/coordinator/rooms/<?= $room['id'] ?>/edit">
                    <?= csrf_field() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room Number / Identifier <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" required maxlength="20" value="<?= e(old('room_number', $room['room_number'])) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Campus Building / Complex <span class="text-danger">*</span></label>
                            <input type="text" name="building" class="form-control" required maxlength="100" value="<?= e(old('building', $room['building'])) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Seating Capacity <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" class="form-control" required min="1" max="1000" value="<?= e(old('capacity', $room['capacity'])) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Facility Type</label>
                            <?php $currentType = $room['room_type'] ?? $room['type'] ?? 'lecture'; ?>
                            <select name="room_type" class="form-select">
                                <option value="lecture" <?= old('room_type', $currentType) === 'lecture' ? 'selected' : '' ?>>Lecture Gallery</option>
                                <option value="computer_lab" <?= in_array(old('room_type', $currentType), ['computer_lab', 'lab']) ? 'selected' : '' ?>>Computer Lab</option>
                                <option value="electronics_lab" <?= old('room_type', $currentType) === 'electronics_lab' ? 'selected' : '' ?>>Electronics Lab</option>
                                <option value="seminar_hall" <?= in_array(old('room_type', $currentType), ['seminar_hall', 'seminar']) ? 'selected' : '' ?>>Seminar Hall</option>
                                <option value="multimedia_room" <?= in_array(old('room_type', $currentType), ['multimedia_room', 'auditorium']) ? 'selected' : '' ?>>Multimedia / Auditorium</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Operational Status</label>
                            <?php $currentStatus = $room['status'] ?? 'available'; ?>
                            <select name="status" class="form-select">
                                <option value="available" <?= in_array(old('status', $currentStatus), ['available', 'active']) ? 'selected' : '' ?>>Available</option>
                                <option value="maintenance" <?= old('status', $currentStatus) === 'maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                                <option value="inactive" <?= old('status', $currentStatus) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Room
                        </button>
                        <a href="/coordinator/rooms" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
