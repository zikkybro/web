<?php
include("../system_urls.php");
include("../include/ConnDB.php");
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "unauthorized"]);
    exit;
}

$today = isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date']) ? $_GET['date'] : date('Y-m-d');

// จำนวนคิววันนี้
$sqlQueue = "SELECT COUNT(*) AS total FROM bookings WHERE booking_date = ?";
$stmtQueue = mysqli_prepare($conn, $sqlQueue);
mysqli_stmt_bind_param($stmtQueue, "s", $today);
mysqli_stmt_execute($stmtQueue);
$resultQueue = mysqli_stmt_get_result($stmtQueue);
$rowQueue = mysqli_fetch_assoc($resultQueue);
$queueToday = $rowQueue['total'] ?? 0;
mysqli_stmt_close($stmtQueue);

// จำนวนผู้ป่วยใหม่
$sqlPatient = "SELECT COUNT(*) AS total FROM users WHERE DATE(created_at) = ?";
$stmtPatient = mysqli_prepare($conn, $sqlPatient);
mysqli_stmt_bind_param($stmtPatient, "s", $today);
mysqli_stmt_execute($stmtPatient);
$resultPatient = mysqli_stmt_get_result($stmtPatient);
$rowPatient = mysqli_fetch_assoc($resultPatient);
$newPatients = $rowPatient['total'] ?? 0;
mysqli_stmt_close($stmtPatient);

// คิวจองรวมสัปดาห์นี้
$monday = date('Y-m-d', strtotime('monday this week', strtotime($today)));
$sunday = date('Y-m-d', strtotime('sunday this week', strtotime($today)));
$sqlWeek = "SELECT COUNT(*) AS total FROM bookings WHERE booking_date BETWEEN ? AND ?";
$stmtWeek = mysqli_prepare($conn, $sqlWeek);
mysqli_stmt_bind_param($stmtWeek, "ss", $monday, $sunday);
mysqli_stmt_execute($stmtWeek);
$resultWeek = mysqli_stmt_get_result($stmtWeek);
$rowWeek = mysqli_fetch_assoc($resultWeek);
$queueWeek = $rowWeek['total'] ?? 0;
mysqli_stmt_close($stmtWeek);

// รายการคิว
$sqlList = "SELECT b.booking_time, b.name AS patient_name, u.username AS username 
            FROM bookings b 
            LEFT JOIN users u ON b.user_id = u.id 
            WHERE b.booking_date = ? 
            ORDER BY b.booking_time ASC";
$stmtList = mysqli_prepare($conn, $sqlList);
mysqli_stmt_bind_param($stmtList, "s", $today);
mysqli_stmt_execute($stmtList);
$resultList = mysqli_stmt_get_result($stmtList);
$list = [];
while ($row = mysqli_fetch_assoc($resultList)) {
    $list[] = [
        "time" => $row['booking_time'],
        "name" => $row['patient_name'],
        "username" => $row['username']
    ];
}
mysqli_stmt_close($stmtList);

header('Content-Type: application/json');
echo json_encode([
    "queueToday" => $queueToday,
    "newPatients" => $newPatients,
    "queueWeek" => $queueWeek,
    "list" => $list
], JSON_UNESCAPED_UNICODE);
