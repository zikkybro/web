<?php
include("../include/ConnDB.php");
$labels = [];
$queue = [];
$patients = [];
$days = isset($_GET['days']) && is_numeric($_GET['days']) && $_GET['days'] > 0 ? intval($_GET['days']) : 7;
if ($days > 60) $days = 60; // จำกัดสูงสุด 60 วันเพื่อป้องกันโหลดหนัก
for ($i = $days - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = $date;
    // จำนวนคิวแต่ละวัน
    $sqlQ = "SELECT COUNT(*) AS total FROM bookings WHERE booking_date = ?";
    $stmtQ = mysqli_prepare($conn, $sqlQ);
    mysqli_stmt_bind_param($stmtQ, "s", $date);
    mysqli_stmt_execute($stmtQ);
    $resultQ = mysqli_stmt_get_result($stmtQ);
    $rowQ = mysqli_fetch_assoc($resultQ);
    $queue[] = (int)($rowQ['total'] ?? 0);
    mysqli_stmt_close($stmtQ);
    // ผู้ป่วยใหม่แต่ละวัน
    $sqlP = "SELECT COUNT(*) AS total FROM users WHERE DATE(created_at) = ?";
    $stmtP = mysqli_prepare($conn, $sqlP);
    mysqli_stmt_bind_param($stmtP, "s", $date);
    mysqli_stmt_execute($stmtP);
    $resultP = mysqli_stmt_get_result($stmtP);
    $rowP = mysqli_fetch_assoc($resultP);
    $patients[] = (int)($rowP['total'] ?? 0);
    mysqli_stmt_close($stmtP);
}
header('Content-Type: application/json');
echo json_encode([
    'labels' => $labels,
    'queue' => $queue,
    'patients' => $patients
]);
