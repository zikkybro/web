<?php
// export_bookings.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    die('Unauthorized');
}
include("../include/ConnDB.php");

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="queue-export.xlsx"');
header('Cache-Control: max-age=0');

require_once __DIR__ . '/../vendor/autoload.php'; // phpspreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$show_all = isset($_GET['show_all']) && $_GET['show_all'] == '1';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if (!$show_all) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
        die('Invalid date format.');
    }
}

if ($show_all) {
    $sql = "SELECT b.booking_date, b.booking_time, b.name AS patient_name, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id ORDER BY b.booking_date DESC, b.booking_time ASC";
    $result = mysqli_query($conn, $sql);
    if (!$result) { die("Query failed: " . mysqli_error($conn)); }
} else {
    $sql = "SELECT b.booking_date, b.booking_time, b.name AS patient_name, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE b.booking_date = ? ORDER BY b.booking_time ASC";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { die("Prepare failed: " . mysqli_error($conn)); }
    mysqli_stmt_bind_param($stmt, "s", $selected_date);
    if (!mysqli_stmt_execute($stmt)) { die("Execute failed: " . mysqli_stmt_error($stmt)); }
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'วันที่');
$sheet->setCellValue('B1', 'เวลา');
$sheet->setCellValue('C1', 'ชื่อผู้ป่วย');
$sheet->setCellValue('D1', 'Username');

$rowNum = 2;
while($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A'.$rowNum, $row['booking_date']);
    $sheet->setCellValue('B'.$rowNum, $row['booking_time']);
    $sheet->setCellValue('C'.$rowNum, $row['patient_name']);
    $sheet->setCellValue('D'.$rowNum, $row['username']);
    $rowNum++;
}

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
