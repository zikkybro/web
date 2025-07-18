<?php
// api_attach_result.php : รับ POST จากฟอร์มแนบผลตรวจ (admin)
include("../include/ConnDB.php");
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$doctor_comment = isset($_POST['doctor_comment']) ? trim($_POST['doctor_comment']) : '';
$detail_name = $_POST['detail_name'] ?? [];
$detail_target = $_POST['detail_target'] ?? [];
$detail_result = $_POST['detail_result'] ?? [];
$detail_unit = $_POST['detail_unit'] ?? [];
$detail_status = $_POST['detail_status'] ?? [];
if ($booking_id <= 0 || count($detail_name) == 0) {
    header('Location: queue_manage.php?error=missing_data');
    exit;
}
// ลบผลตรวจเดิมของ booking_id นี้ก่อน (ถ้ามี)
$sql_del = "DELETE FROM booking_results WHERE booking_id=?";
$stmt_del = mysqli_prepare($conn, $sql_del);
mysqli_stmt_bind_param($stmt_del, "i", $booking_id);
mysqli_stmt_execute($stmt_del);
mysqli_stmt_close($stmt_del);
// เพิ่มผลตรวจใหม่
$sql = "INSERT INTO booking_results (booking_id, detail_name, detail_target, detail_result, detail_unit, detail_status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $sql);
for ($i = 0; $i < count($detail_name); $i++) {
    $name = trim($detail_name[$i]);
    $target = trim($detail_target[$i]);
    $result = trim($detail_result[$i]);
    $unit = trim($detail_unit[$i]);
    $status = trim($detail_status[$i]);
    if ($name === '' && $result === '') continue;
    mysqli_stmt_bind_param($stmt, "isssss", $booking_id, $name, $target, $result, $unit, $status);
    mysqli_stmt_execute($stmt);
}
mysqli_stmt_close($stmt);
// อัปเดต doctor_comment ในตาราง bookings
if ($booking_id > 0) {
    $sql_comment = "UPDATE bookings SET doctor_comment=? WHERE id=?";
    $stmt_comment = mysqli_prepare($conn, $sql_comment);
    mysqli_stmt_bind_param($stmt_comment, "si", $doctor_comment, $booking_id);
    mysqli_stmt_execute($stmt_comment);
    mysqli_stmt_close($stmt_comment);
}
// Redirect กลับหน้าจัดการคิว
header('Location: queue_manage.php?success=1');
exit;
