<?php
include("../include/ConnDB.php");
header('Content-Type: application/json');
$bookings = [];
$sql = "SELECT booking_date, booking_time, name, note, user_id FROM bookings";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['booking_date'];
    $time = $row['booking_time'];
    if (!isset($bookings[$date])) $bookings[$date] = [];
    $bookings[$date][$time] = [
        'name' => $row['name'],
        'note' => $row['note'],
        'user_id' => $row['user_id']
    ];
}
echo json_encode($bookings);
