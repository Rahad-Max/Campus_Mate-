<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Room;

class RoomController extends Controller
{
    private Room $roomModel;

    public function __construct()
    {
        $this->roomModel = new Room();
    }

    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');
        $building = trim($_GET['building'] ?? '');

        $allRooms = $this->roomModel->all('building ASC, room_number ASC');
        $buildings = array_values(array_unique(array_filter(array_column($allRooms, 'building'))));

        $rooms = array_filter($allRooms, function($r) use ($search, $building) {
            if (!empty($search)) {
                $q = strtolower($search);
                if (!str_contains(strtolower($r['room_number']), $q) && !str_contains(strtolower($r['building']), $q)) {
                    return false;
                }
            }
            if (!empty($building) && ($r['building'] ?? '') !== $building) {
                return false;
            }
            return true;
        });

        $this->render('coordinator/rooms/index', [
            'title' => 'Classroom & Facility Management — PUB CampusMate',
            'rooms' => $rooms,
            'buildings' => $buildings,
            'search' => $search,
            'building' => $building
        ]);
    }

    public function create(): void
    {
        $this->render('coordinator/rooms/create', [
            'title' => 'Add Classroom / Lab — PUB CampusMate'
        ]);
    }

    public function store(): void
    {
        $data = $this->validate($_POST, [
            'room_number' => 'required',
            'building' => 'required',
            'capacity' => 'required|numeric'
        ]);

        $roomNumber = trim($data['room_number'] ?? '');
        $building = trim($data['building'] ?? '');
        $floor = (int)($_POST['floor'] ?? 1);
        $capacity = (int)($data['capacity'] ?? 0);
        $type = in_array($_POST['room_type'] ?? '', ['lecture', 'computer_lab', 'electronics_lab', 'seminar_hall', 'multimedia_room'])
            ? $_POST['room_type']
            : 'lecture';
        $hasProjector = isset($_POST['has_projector']) ? 1 : 0;
        $hasAc = isset($_POST['has_ac']) ? 1 : 0;

        if ($capacity <= 0) {
            $this->setFlash('danger', 'Capacity must be greater than zero.');
            $this->redirect('/coordinator/rooms/create');
            return;
        }

        if ($this->roomModel->findByRoomNumber($roomNumber)) {
            $this->setFlash('danger', "Room '{$roomNumber}' already exists.");
            $this->redirect('/coordinator/rooms/create');
            return;
        }

        $id = $this->roomModel->create([
            'room_number' => $roomNumber,
            'building' => $building,
            'floor' => $floor,
            'room_type' => $type,
            'capacity' => $capacity,
            'has_projector' => $hasProjector,
            'has_ac' => $hasAc,
            'status' => 'available'
        ]);

        if ($id) {
            $this->setFlash('success', "Room '{$roomNumber}' registered successfully.");
            $this->redirect('/coordinator/rooms');
        } else {
            $this->setFlash('danger', 'Failed to register room.');
            $this->redirect('/coordinator/rooms/create');
        }
    }

    public function edit(string $id): void
    {
        $room = $this->roomModel->find((int)$id);
        if (!$room) {
            $this->setFlash('danger', 'Room not found.');
            $this->redirect('/coordinator/rooms');
            return;
        }

        $this->render('coordinator/rooms/edit', [
            'title' => "Edit Room: {$room['room_number']} — PUB CampusMate",
            'room' => $room
        ]);
    }

    public function update(string $id): void
    {
        $roomId = (int)$id;
        $room = $this->roomModel->find($roomId);
        if (!$room) {
            $this->setFlash('danger', 'Room not found.');
            $this->redirect('/coordinator/rooms');
            return;
        }

        $data = $this->validate($_POST, [
            'room_number' => 'required',
            'building' => 'required',
            'capacity' => 'required|numeric'
        ]);

        $roomNumber = trim($data['room_number'] ?? '');
        $building = trim($data['building'] ?? '');
        $floor = (int)($_POST['floor'] ?? 1);
        $capacity = (int)($data['capacity'] ?? 0);
        $type = in_array($_POST['room_type'] ?? '', ['lecture', 'computer_lab', 'electronics_lab', 'seminar_hall', 'multimedia_room'])
            ? $_POST['room_type']
            : $room['room_type'];
        $status = in_array($_POST['status'] ?? '', ['available', 'maintenance', 'inactive'])
            ? $_POST['status']
            : $room['status'];
        $hasProjector = isset($_POST['has_projector']) ? 1 : 0;
        $hasAc = isset($_POST['has_ac']) ? 1 : 0;

        if ($capacity <= 0) {
            $this->setFlash('danger', 'Capacity must be positive.');
            $this->redirect("/coordinator/rooms/{$roomId}/edit");
            return;
        }

        $existing = $this->roomModel->findByRoomNumber($roomNumber);
        if ($existing && (int)$existing['id'] !== $roomId) {
            $this->setFlash('danger', "Room number '{$roomNumber}' is already in use.");
            $this->redirect("/coordinator/rooms/{$roomId}/edit");
            return;
        }

        $this->roomModel->update($roomId, [
            'room_number' => $roomNumber,
            'building' => $building,
            'floor' => $floor,
            'room_type' => $type,
            'capacity' => $capacity,
            'has_projector' => $hasProjector,
            'has_ac' => $hasAc,
            'status' => $status
        ]);

        $this->setFlash('success', "Room '{$roomNumber}' updated successfully.");
        $this->redirect('/coordinator/rooms');
    }

    public function toggleStatus(string $id): void
    {
        $roomId = (int)$id;
        $room = $this->roomModel->find($roomId);
        if (!$room) {
            $this->setFlash('danger', 'Room not found.');
            $this->redirect('/coordinator/rooms');
            return;
        }

        $newStatus = ($room['status'] === 'available') ? 'maintenance' : 'available';
        $this->roomModel->update($roomId, ['status' => $newStatus]);

        $this->setFlash('success', "Room {$room['room_number']} status changed to {$newStatus}.");
        $this->redirect('/coordinator/rooms');
    }

    public function delete(string $id): void
    {
        $roomId = (int)$id;
        $room = $this->roomModel->find($roomId);
        if (!$room) {
            $this->setFlash('danger', 'Room not found.');
            $this->redirect('/coordinator/rooms');
            return;
        }

        // Safety: Prevent deleting rooms referenced by schedules or exams
        if ($this->roomModel->isReferencedBySchedules($roomId)) {
            $this->setFlash('danger', "Cannot delete room '{$room['room_number']}': It is referenced by published class schedules or exams.");
            $this->redirect('/coordinator/rooms');
            return;
        }

        $this->roomModel->delete($roomId);
        $this->setFlash('success', "Room '{$room['room_number']}' deleted successfully.");
        $this->redirect('/coordinator/rooms');
    }
}
