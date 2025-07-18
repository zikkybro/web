<?php
header('Content-Type: application/json; charset=utf-8');
include("../include/ConnDB.php");

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
    echo json_encode([]);
    exit;
}

// ดึงประวัติการจองคิว (booking) ของผู้ใช้
// ตาราง bookings: id, user_id, booking_date, booking_time, name, ...
$sql = "SELECT booking_date, booking_time, name AS patient_name FROM bookings WHERE user_id = ? ORDER BY booking_date DESC, booking_time DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'booking_date' => $row['booking_date'],
        'booking_time' => $row['booking_time'],
        'patient_name' => $row['patient_name'],
    ];
}
echo json_encode($data);
