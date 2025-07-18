<?php
// รับ booking_id แล้วคืนผลตรวจ (JSON)
header('Content-Type: application/json; charset=utf-8');
include("../include/ConnDB.php");
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
if (!$booking_id) {
    echo json_encode(['success'=>false, 'error'=>'booking_id required']);
    exit;
}
// ดึงผลตรวจสุขภาพ
$sql = "SELECT * FROM booking_results WHERE booking_id = ? ORDER BY id ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$results = [];
while ($row = mysqli_fetch_assoc($result)) {
    $results[] = $row;
}
mysqli_stmt_close($stmt);
// ดึง doctor_comment (ถ้ามี)
$doctor_comment = '';
$sql2 = "SELECT doctor_comment FROM bookings WHERE id = ?";
$stmt2 = mysqli_prepare($conn, $sql2);
mysqli_stmt_bind_param($stmt2, "i", $booking_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
if ($row2 = mysqli_fetch_assoc($result2)) {
    $doctor_comment = $row2['doctor_comment'] ?? '';
}
mysqli_stmt_close($stmt2);
echo json_encode([
    'success' => true,
    'results' => $results,
    'doctor_comment' => $doctor_comment
]);
