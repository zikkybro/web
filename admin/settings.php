<?php
include("../system_urls.php");
include("../include/ConnDB.php");
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
// ====== AJAX toggle booking_enabled ต้องอยู่บนสุดก่อน output ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_enabled']) && !isset($_POST['closed_dates'])) {
    header('Content-Type: application/json');
    $enabled = ($_POST['booking_enabled'] === '1') ? '1' : '0';
    $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES ('booking_enabled', ?) ON DUPLICATE KEY UPDATE setting_value=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $enabled, $enabled);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo json_encode([
        'success' => $ok,
        'message' => $ok ? ($enabled === '1' ? 'เปิดรับจองคิวแล้ว' : 'ปิดรับจองคิวเรียบร้อย') : 'เกิดข้อผิดพลาดในการบันทึก'
    ]);
    exit;
}
// ====== END AJAX toggle ======

// ====== AJAX สำหรับ available_dates_range ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['available_date_from']) && isset($_POST['available_date_to'])) {
    header('Content-Type: application/json');
    $from = $_POST['available_date_from'];
    $to = $_POST['available_date_to'];
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) && $from <= $to) {
        // บันทึกลง system_settings (หรือสร้างตารางใหม่ถ้าต้องการ)
        $range = $from . ',' . $to;
        $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES ('available_dates_range', ?) ON DUPLICATE KEY UPDATE setting_value=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $range, $range);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'บันทึกช่วงวันที่เปิดจองเรียบร้อย' : 'เกิดข้อผิดพลาดในการบันทึก'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'รูปแบบวันที่ไม่ถูกต้อง']);
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_available_dates'])) {
    header('Content-Type: application/json');
    $sql = "DELETE FROM system_settings WHERE setting_key='available_dates_range'";
    $ok = mysqli_query($conn, $sql);
    echo json_encode([
        'success' => $ok,
        'message' => $ok ? 'รีเซตช่วงวันที่เปิดจองเรียบร้อย' : 'เกิดข้อผิดพลาดในการรีเซต'
    ]);
    exit;
}

$msg = null;
// อ่านสถานะ booking_enabled
$booking_enabled = '1';
$sql = "SELECT setting_value FROM system_settings WHERE setting_key='booking_enabled' LIMIT 1";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $booking_enabled = $row['setting_value'];
}
// --- Handle add/remove closed dates ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['closed_dates'])) {
    $dates = array_filter(array_map('trim', explode(',', $_POST['closed_dates'])));
    if ($_POST['closed_dates'] === '') {
        // ถ้า input ว่าง (รีเซตวันปิดรับจองทั้งหมด)
        mysqli_query($conn, "DELETE FROM booking_closed_dates");
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'รีเซตวันปิดรับจองเรียบร้อย'
        ]);
        exit;
    } elseif (empty($dates)) {
        // ถ้า input ไม่ว่างแต่แปลงแล้วไม่มีวัน (เช่นใส่ ,,,)
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'กรุณาเลือกวันปิดรับจองอย่างน้อย 1 วัน'
        ]);
        exit;
    } else {
        // ลบวันเดิมทั้งหมด
        mysqli_query($conn, "DELETE FROM booking_closed_dates");
        // เพิ่มวันใหม่
        $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO booking_closed_dates (closed_date) VALUES (?)");
        foreach ($dates as $d) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                mysqli_stmt_bind_param($stmt, "s", $d);
                mysqli_stmt_execute($stmt);
            }
        }
        mysqli_stmt_close($stmt);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'บันทึกวันปิดรับจองคิวเรียบร้อย'
        ]);
        exit;
    }
}
// อ่านวันปิดรับจองทั้งหมด
$closed_dates = [];
$result = mysqli_query($conn, "SELECT closed_date FROM booking_closed_dates ORDER BY closed_date");
while ($row = mysqli_fetch_assoc($result)) {
    $closed_dates[] = $row['closed_date'];
}

// อ่านช่วงวันที่เปิดจองล่าสุด (ถ้ามี)
$available_dates_range = '';
$available_date_from = '';
$available_date_to = '';
$result = mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key='available_dates_range' LIMIT 1");
if ($row = mysqli_fetch_assoc($result)) {
    $available_dates_range = $row['setting_value'];
    $parts = explode(',', $available_dates_range);
    if (count($parts) === 2) {
        $available_date_from = $parts[0];
        $available_date_to = $parts[1];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>การตั้งค่า</title>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <!-- admin.css (settings.php) -->
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 d-none d-md-block sidebar py-4 bg-light border-end">
      <div class="text-center mb-4">
        <img src="../img/logo.png" alt="Logo" style="height:48px;">
        <h5 class="mt-2">Admin</h5>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> แดชบอร์ด</a></li>
        <li class="nav-item"><a class="nav-link" href="queue_manage.php"><i class="bi bi-calendar-check"></i> การจัดการคิว</a></li>
        <li class="nav-item"><a class="nav-link" href="patients.php"><i class="bi bi-person-lines-fill"></i> ข้อมูลผู้ป่วย</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-box2-heart"></i> แพ็คเกจตรวจสุขภาพ</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-clipboard2-pulse"></i> บริการ/รายการตรวจ</a></li>
        <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-bar-chart-line"></i> รายงาน</a></li>
        <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="bi bi-gear"></i> การตั้งค่า</a></li>
      </ul>
    </nav>
    <!-- Main Content -->
    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4">
      <!-- Topbar -->
      <div class="topbar d-flex align-items-center justify-content-between py-3 px-3">
        <div>
          <h4 class="mb-0">การตั้งค่า</h4>
        </div>
        <div class="d-flex align-items-center gap-3">
          <form class="d-flex" role="search">
            <input class="form-control form-control-sm me-2" type="search" placeholder="ค้นหาการตั้งค่า" aria-label="Search">
            <button class="btn btn-outline-primary btn-sm" type="submit"><i class="bi bi-search"></i></button>
          </form>
          <!-- Bell notification badge (same as queue_manage.php) -->
          <a href="#" id="notiBtn" class="text-dark position-relative">
            <i class="bi bi-bell fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notiBadge"></span>
          </a>
          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle fs-4"></i>
              <span class="ms-2">Admin</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#">โปรไฟล์</a></li>
              <li><a class="dropdown-item" href="logout.php">ออกจากระบบ</a></li>
            </ul>
          </div>
        </div>
      </div>
      <!-- Main Content Area -->
      <div class="main-content">
        <h3 class="mb-4">การตั้งค่า (Settings)</h3>
<?php
// ถ้า AJAX toggle booking_enabled
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_enabled']) && !isset($_POST['closed_dates'])) {
    header('Content-Type: application/json');
    $enabled = ($_POST['booking_enabled'] === '1') ? '1' : '0';
    $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES ('booking_enabled', ?) ON DUPLICATE KEY UPDATE setting_value=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $enabled, $enabled);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    echo json_encode([
        'success' => $ok,
        'message' => $ok ? ($enabled === '1' ? 'เปิดรับจองคิวแล้ว' : 'ปิดรับจองคิวเรียบร้อย') : 'เกิดข้อผิดพลาดในการบันทึก'
    ]);
    exit;
}
?>
        <div class="card mb-4" style="max-width:500px;">
          <div class="card-body">
            <form id="booking-enabled-form" method="post" class="d-flex align-items-center gap-3">
              <label class="form-label mb-0 me-3" for="booking_enabled">เปิดรับจองคิว</label>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="booking_enabled" name="booking_enabled" value="1" <?php if($booking_enabled==='1') echo 'checked'; ?>>
                <label class="form-check-label" for="booking_enabled" id="booking-enabled-label">
                  <?php echo ($booking_enabled==='1') ? 'เปิด' : 'ปิด'; ?>
                </label>
              </div>
            </form>
          </div>
        </div>

        <!-- ฟอร์มเลือกวันปิดรับจองคิว -->
        <div class="card mb-4" style="max-width:600px;">
          <div class="card-body">
            <form id="closed-dates-form" method="post" class="mb-3">
              <div class="row g-2 align-items-end">
                <div class="col-12 col-md-7">
                  <label class="form-label" for="closed_dates">เลือกวัน <span class="text-danger">(ปิดรับจองคิว)</span></label>
                  <input type="text" id="closed_dates" name="closed_dates" class="form-control mb-2" placeholder="คลิกเพื่อเลือกวัน (เลือกได้หลายวัน)" autocomplete="off" readonly value="<?php echo htmlspecialchars(implode(',', $closed_dates)); ?>">
                  <div class="form-text mb-2">คลิกที่ช่องเพื่อเลือกวันปิดรับจอง (เลือกได้หลายวัน, คลิกซ้ำเพื่อลบวัน)</div>
                </div>
                <div class="col-auto d-flex flex-column gap-2">
                  <button type="submit" class="btn btn-warning w-100">บันทึกวันปิดรับจอง</button>
                  <button type="button" class="btn btn-outline-secondary w-100" id="reset-closed-dates">รีเซต</button>
                </div>
              </div>
            </form>
            <?php if (count($closed_dates)): ?>
              <div class="mt-3">
                <strong>วันที่ปิดรับจอง:</strong>
                <ul class="mb-0">
                  <?php foreach ($closed_dates as $d): ?>
                    <li><?php echo htmlspecialchars($d); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ฟอร์มเลือกช่วงวันที่เปิดให้จอง (range) -->
        <div class="card mb-4" style="max-width:600px;">
          <div class="card-body">
            <form id="available-dates-range-form" method="post" class="mb-3">
              <div class="row g-2 align-items-end">
                <div class="col-12 col-md-7">
                  <label class="form-label">เลือกช่วงวันที่ <span class="text-success">(เปิดให้จองได้)</span></label>
                  <div class="d-flex gap-2 mb-2">
                    <input type="text" id="available_date_from" class="form-control" placeholder="วันที่เริ่มต้น" autocomplete="off" readonly style="max-width: 150px;" value="<?php echo htmlspecialchars($available_date_from); ?>">
                    <span class="align-self-center">ถึง</span>
                    <input type="text" id="available_date_to" class="form-control" placeholder="วันที่สิ้นสุด" autocomplete="off" readonly style="max-width: 150px;" value="<?php echo htmlspecialchars($available_date_to); ?>">
                  </div>
                  <input type="text" id="available_dates_range" class="form-control" placeholder="ช่วงวันที่เลือก" readonly value="<?php echo htmlspecialchars($available_dates_range); ?>">
                </div>
                <div class="col-auto d-flex flex-column gap-2">
                  <button type="button" class="btn btn-success w-100" id="save-available-dates">บันทึกช่วงวันที่เปิดจอง</button>
                  <button type="button" class="btn btn-outline-secondary w-100" id="reset-available-dates">รีเซต</button>
                </div>
              </div>
            </form>
            <div id="available-dates-range-result" class="mt-3"></div>
          </div>
        </div>
        
      </div>
    </main>
      <!-- SweetAlert2 (settings.php) -->
      <script src="../vendor/sweetalert2/js/sweetalert2.js"></script>
      <!-- jQuery UI Datepicker (สำหรับเลือกวันปิดรับจอง) -->
      <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
      <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
      <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
      <script src="../js/admin.js"></script>
      <script src="../js/admin-closed-dates-datepicker.js"></script>
      <script src="../js/admin-available-dates-range.js"></script>
      <script src="../js/admin-settings.js"></script>
  </div>
</div>
<?php include("../script.php")?>
</body>
</html>
