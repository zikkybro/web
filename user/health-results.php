<?php
$id_card = isset($_GET['id_card']) ? trim($_GET['id_card']) : '';
$phone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
include("../system_urls.php");
include("../include/ConnDB.php");
$bookings = [];
$booking_results = [];
$doctor_comments = [];
if ($id_card !== '' && $phone !== '') {
    // ดึง booking ที่ตรงกับ id_card และ phone
    $sql = "SELECT id, booking_date, booking_time, name, note, doctor_comment, created_at FROM bookings WHERE id_card=? AND phone=? ORDER BY booking_date DESC, booking_time DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $id_card, $phone);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    mysqli_stmt_close($stmt);
    // ดึงผลตรวจสุขภาพของ booking_id เหล่านี้
    if (!empty($bookings)) {
        $booking_ids = array_column($bookings, 'id');
        $booking_ids_safe = array_map('intval', $booking_ids);
        $in = implode(',', $booking_ids_safe);
        // ดึงผลตรวจสุขภาพ
        $sql2 = "SELECT * FROM booking_results WHERE booking_id IN ($in) ORDER BY booking_id DESC, id ASC";
        $result2 = mysqli_query($conn, $sql2);
        if ($result2) {
            while ($row = mysqli_fetch_assoc($result2)) {
                $booking_results[$row['booking_id']][] = $row;
            }
            mysqli_free_result($result2);
        }
        // ดึง doctor_comment ของแต่ละ booking
        $sql3 = "SELECT booking_id, comment, doctor_name, created_at FROM doctor_comment WHERE booking_id IN ($in)";
        $result3 = mysqli_query($conn, $sql3);
        if ($result3) {
            while ($row = mysqli_fetch_assoc($result3)) {
                $doctor_comments[$row['booking_id']] = $row;
            }
            mysqli_free_result($result3);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <link rel="stylesheet" href="../css/landing.css">
    <!-- <link rel="stylesheet" href="../css/booking-calendar.css"> -->
    <link rel="icon" type="image/png" href="../img/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>ผลการตรวจสุขภาพ</title>
</head>
<body>
<!-- <nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../img/logo.png" alt="logo">
      NU
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link " href="welcome.php">หน้าหลัก</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-calendar.php">จองคิวตรวจสุขภาพออนไลน์</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-history.php">ประวัติการจองของฉัน</a></li>
        <li class="nav-item"><a class="nav-link active" href="health-results.php">ผลการตรวจสุขภาพ</a></li>
        <li class="nav-item"><a class="nav-link btn" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav> -->
<div class="container py-5 main-content">
  <h2 class="mb-4 text-center">ผลการตรวจสุขภาพของฉัน</h2>
  <div class="row justify-content-center">
    <div class="col-12" style="max-width:1400px;">
      <div class="mb-4">
        <div class="row g-2 justify-content-center align-items-end flex-wrap">
          <div class="col-auto">
            <label for="searchDateInput" class="form-label mb-1 fw-bold">ค้นหาตามวันที่</label>
            <input type="date" id="searchDateInput" class="form-control form-control-lg shadow-sm search-input" style="min-width:180px;">
          </div>
          <div class="col-auto">
            <label for="searchResultInput" class="form-label mb-1 fw-bold">ค้นหาข้อความ</label>
            <input type="text" id="searchResultInput" class="form-control form-control-lg shadow-sm search-input" placeholder="ชื่อรายการ, ผลตรวจ, สถานะ ฯลฯ" style="min-width:260px;">
          </div>
          <div class="col-auto d-flex align-items-end">
            <button id="showAllBtn" class="btn btn-lg btn-gradient ms-2 px-4"><i class="bi bi-list-ul"></i> ดูทั้งหมด</button>
          </div>
        </div>
      </div>
      <?php if (empty($bookings)): ?>
        <div class="alert alert-info text-center">ยังไม่มีประวัติการจองและผลตรวจ</div>
      <?php else: ?>
        <div id="healthResultCards">
        <?php foreach ($bookings as $b): ?>
          <div class="history-section-card mb-4 p-3 p-md-4 shadow-lg rounded-4 bg-glass health-result-card animate__animated animate__fadeIn">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2 gap-2">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="badge rounded-pill bg-primary bg-gradient px-3 py-2 fs-6"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars(date('d/m/Y', strtotime($b['booking_date']))); ?></span>
                <span class="badge rounded-pill bg-info bg-gradient px-3 py-2 fs-6"><i class="bi bi-clock"></i> <?= htmlspecialchars($b['booking_time']); ?></span>
              </div>
              <div>
                <span class="fw-bold me-2"><i class="bi bi-person"></i> ชื่อ:</span> <span class="text-primary-emphasis fw-semibold"><?= htmlspecialchars($b['name']); ?></span>
              </div>
            </div>
            <div class="mb-2">
              <span class="fw-bold text-secondary"><i class="bi bi-chat-left-text"></i> หมายเหตุ:</span>
              <span class="text-body-emphasis">
                <?php 
                  // ถ้ามี doctor_comment ให้แสดงแทน note
                  if (!empty($b['doctor_comment'])) {
                    echo nl2br(htmlspecialchars($b['doctor_comment']));
                  } else {
                    echo htmlspecialchars($b['note']);
                  }
                ?>
              </span>
              <?php if (!empty($doctor_comments[$b['id']]['comment'])): ?>
                <div class="mt-2 p-2 rounded bg-light border border-info">
                  <span class="fw-bold text-primary"><i class="bi bi-person-vcard"></i> ความเห็นแพทย์ (บันทึกแยก):</span>
                  <span><?= nl2br(htmlspecialchars($doctor_comments[$b['id']]['comment'])) ?></span>
                  <?php if (!empty($doctor_comments[$b['id']]['doctor_name'])): ?>
                    <span class="ms-2 text-secondary-emphasis">โดย <?= htmlspecialchars($doctor_comments[$b['id']]['doctor_name']) ?></span>
                  <?php endif; ?>
                  <?php if (!empty($doctor_comments[$b['id']]['created_at'])): ?>
                    <span class="ms-2 text-secondary-emphasis"><i class="bi bi-clock-history"></i> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($doctor_comments[$b['id']]['created_at']))) ?></span>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="mb-2"><span class="fw-bold text-secondary"><i class="bi bi-calendar-plus"></i> จองเมื่อ:</span> <span class="text-body-emphasis"><?= isset($b['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($b['created_at']))) : '-'; ?></span></div>
            <div class="mt-3">
              <?php if (empty($booking_results[$b['id']])): ?>
                <div class="alert alert-warning mb-0">ยังไม่มีผลการตรวจสุขภาพสำหรับคิวนี้</div>
              <?php else: ?>
                <div class="table-responsive animate__animated animate__fadeIn">
                  <table class="table table-hover table-borderless align-middle text-center mb-0 health-result-table">
                    <thead class="table-light border-bottom border-2">
                      <tr>
                        <th class="text-secondary">ลำดับ</th>
                        <th class="text-secondary">รายการตรวจ</th>
                        <th class="text-secondary">ค่ามาตรฐาน</th>
                        <th class="text-secondary">ผลตรวจ</th>
                        <th class="text-secondary">หน่วย</th>
                        <th class="text-secondary">สถานะ</th>
                        <th class="text-secondary">บันทึกเมื่อ</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($booking_results[$b['id']] as $i => $r): ?>
                        <tr class="result-row">
                          <td class="fw-bold text-primary-emphasis">#<?= $i+1 ?></td>
                          <td><?= htmlspecialchars($r['detail_name']) ?></td>
                          <td><span class="badge bg-light text-dark border border-1 border-info px-2 py-1"><?= htmlspecialchars($r['detail_target']) ?></span></td>
                          <td class="fw-semibold fs-6 text-result">
                            <?= htmlspecialchars($r['detail_result']) ?>
                          </td>
                          <td><?= htmlspecialchars($r['detail_unit']) ?></td>
                          <td>
                            <?php
                              $status = trim($r['detail_status']);
                              $statusIcon = 'bi-question-circle';
                              $statusClass = 'bg-secondary';
                              if (stripos($status, 'ปกติ') !== false || stripos($status, 'normal') !== false) {
                                $statusIcon = 'bi-check-circle';
                                $statusClass = 'bg-success';
                              } elseif (stripos($status, 'ผิดปกติ') !== false || stripos($status, 'abnormal') !== false) {
                                $statusIcon = 'bi-exclamation-triangle';
                                $statusClass = 'bg-danger';
                              } elseif (stripos($status, 'รอผล') !== false || stripos($status, 'pending') !== false) {
                                $statusIcon = 'bi-hourglass-split';
                                $statusClass = 'bg-warning text-dark';
                              }
                            ?>
                            <span class="badge rounded-pill <?= $statusClass ?> px-3 py-2"><i class="bi <?= $statusIcon ?> me-1"></i><?= htmlspecialchars($status) ?></span>
                          </td>
                          <td><span class="text-secondary-emphasis"><i class="bi bi-clock-history"></i> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($r['created_at']))) ?></span></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <!-- <div class="text-center mt-4">
        <a href="booking-calendar.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> กลับหน้าจองคิว</a>
      </div> -->
    </div>
  </div>
</div>
<link rel="stylesheet" href="../css/health-results.css?v=1">
<script src="../js/health-results.js?v=1"></script>
<!-- <footer class="footer text-center">
  <div class="container">
    <div class="row align-items-center mb-2">
      <div class="col-md-6 text-md-start mb-2 mb-md-0">
        <a class="navbar-brand d-inline-flex align-items-center" href="#">
          <img src="../img/logo.png" alt="logo" style="height: 28px; margin-right: 8px;"> NU
        </a>
      </div>
      <div class="col-md-6 text-md-end">
        <ul class="navbar-nav flex-row justify-content-end">
        <li class="nav-item"><a class="nav-link" href="welcome.php">หน้าหลัก</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-calendar.php">จองคิวตรวจสุขภาพออนไลน์</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-history.php">ประวัติการจองของฉัน</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">ผลการตรวจสุขภาพ</a></li>
        </ul>
      </div>
    </div>
    <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
     
    </div>
    <div>Copyright © 2025</div>
  </div>
</footer> -->
<?php include("../script.php")?>
</body>
</html>
