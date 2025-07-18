<?php
include("../system_urls.php");
include("../include/ConnDB.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// --- Check booking_enabled setting ---
$booking_enabled = '1';
$sql = "SELECT setting_value FROM system_settings WHERE setting_key='booking_enabled' LIMIT 1";
$result = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($result)) {
    $booking_enabled = $row['setting_value'];
}

// --- Get closed booking dates ---
$closed_dates = [];
$result = mysqli_query($conn, "SELECT closed_date FROM booking_closed_dates");
while ($row = mysqli_fetch_assoc($result)) {
    $closed_dates[] = $row['closed_date'];
}

// --- Get available booking date range ---
$available_from = null;
$available_to = null;
$result = mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key='available_dates_range' LIMIT 1");
if ($row = mysqli_fetch_assoc($result)) {
    $range = explode(',', $row['setting_value']);
    if (count($range) === 2) {
        $available_from = $range[0];
        $available_to = $range[1];
    }
}

// --- CSRF Token พร้อมหมดอายุ ---
if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_expire']) || time() > $_SESSION['csrf_token_expire']) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_expire'] = time() + 900; // อายุ 15 นาที
}
// --- Handle booking submission ---
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['bookingDate'], $_POST['bookingTime'], $_POST['bookingName'])
) {
    // --- CSRF Token Check พร้อมหมดอายุ ---
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'] || time() > $_SESSION['csrf_token_expire']) {
        die('CSRF validation failed or token expired');
    }
    $user_id = $_SESSION['user'];
    $booking_date = $_POST['bookingDate'];
    $booking_time = $_POST['bookingTime'];
    $name = trim($_POST['bookingName']);
    $id_card = trim($_POST['bookingIdCard'] ?? '');
    $phone = trim($_POST['bookingPhone'] ?? '');
    $email = trim($_POST['bookingEmail'] ?? '');
    $service_type = trim($_POST['bookingServiceType'] ?? '');
    $gender = trim($_POST['bookingGender'] ?? '');
    $age = intval($_POST['bookingAge'] ?? 0);
    $note = trim($_POST['bookingNote'] ?? '');
    // คำนวณ queue_number จากลำดับเวลา
    $all_times = ["08:30", "08:50", "09:10", "09:30", "09:50", "10:10", "10:30", "10:50", "11:10", "11:30", "11:50",
                  "12:10", "12:30", "12:50", "13:10", "13:30", "13:50", "14:10", "14:30", "14:50", "15:10", "15:30", "15:50", "16:10"];
    $queue_number = array_search($booking_time, $all_times) + 1;
    // ตรวจสอบว่าอยู่ในช่วงวันที่เปิดจองหรือไม่
    $allow = true;
    if ($available_from && $available_to) {
        if ($booking_date < $available_from || $booking_date > $available_to) {
            $allow = false;
        }
    }
    if (!$allow) {
        header("Location: booking-calendar.php?booking=fail&reason=out_of_range");
        exit;
    }
    // ตรวจสอบว่าคิวนี้ถูกจองไปแล้วหรือยัง
    $sql_check = "SELECT id FROM bookings WHERE booking_date=? AND booking_time=?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ss", $booking_date, $booking_time);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    if (mysqli_stmt_num_rows($stmt_check) == 0) {
        // ยังไม่มีการจอง slot นี้
        $sql_insert = "INSERT INTO bookings (user_id, booking_date, booking_time, queue_number, name, id_card, phone, email, service_type, gender, age, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $sql_insert);
        // รูปแบบ: user_id (i), booking_date (s), booking_time (s), queue_number (i), name (s), id_card (s), phone (s), email (s), service_type (s), gender (s), age (i), note (s)
        mysqli_stmt_bind_param($stmt_insert, "ississssssss", $user_id, $booking_date, $booking_time, $queue_number, $name, $id_card, $phone, $email, $service_type, $gender, $age, $note);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
        // --- Log booking ---
        $log_msg = date('Y-m-d H:i:s') . " | user_id: $user_id | booked: $booking_date $booking_time | name: $name | id_card: $id_card | phone: $phone | email: $email | service_type: $service_type | gender: $gender | age: $age\n";
        file_put_contents(__DIR__ . '/../logs/booking.log', $log_msg, FILE_APPEND | LOCK_EX);
        // redirect พร้อมแจ้งเตือน
        header("Location: booking-calendar.php?booking=success");
        exit;
    } else {
        // slot นี้ถูกจองไปแล้ว
        header("Location: booking-calendar.php?booking=fail");
        exit;
    }
    mysqli_stmt_close($stmt_check);

// --- แนะนำการปรับสิทธิ์ไฟล์/ฐานข้อมูล ---
// 1. ตั้ง permission logs/booking.log เป็น 600 (เจ้าของอ่าน/เขียนเท่านั้น)
// 2. ตั้ง permission ไฟล์ config/database เป็น 600
// 3. จำกัดสิทธิ์ user ฐานข้อมูลให้เฉพาะสิทธิ์ที่จำเป็น (SELECT, INSERT, UPDATE, DELETE เฉพาะตารางที่ใช้)
}

// ดึงข้อมูลการจองทั้งหมด (ทุก user)
$bookings = [];
$booking_counts = [];
$user_id = $_SESSION['user'];
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
    if (!isset($booking_counts[$date])) $booking_counts[$date] = 0;
    $booking_counts[$date]++;
}
// จำนวน slot ต่อวัน (ต้องตรงกับ allTimes ใน JS)
$max_per_day = 24;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/booking-calendar.css">
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>NU</title>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="../img/logo.png" alt="logo" class="text-white" >
      ระบบจองคิว
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link " href="#">หน้าหลัก</a></li>
        <li class="nav-item"><a class="nav-link active" href="#features">จองคิวตรวจสุขภาพออนไลน์</a></li>
        <li class="nav-item"><a class="nav-link btn" href="../admin/login.php">Admin</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
      <div class="card shadow-lg border-0 rounded-4 p-0 mb-4">
        <div class="card-body p-4">
          <h2 class="mb-4 text-center">กรุณาเลือกวันตรวจสุขภาพ</h2>
          <div class="calendar-controls mb-3 justify-content-center d-flex flex-wrap gap-2">
            <select id="monthSelect" class="form-select d-inline-block w-auto"></select>
            <select id="yearSelect" class="form-select d-inline-block w-auto"></select>
          </div>
          <?php if ($booking_enabled !== '1'): ?>
            <div class="alert alert-warning text-center my-4" style="font-size:1.15rem;">ขออภัย ขณะนี้ระบบปิดรับจองคิวชั่วคราว</div>
          <?php else: ?>
            <div id="calendar-wrapper" class="d-flex justify-content-center mb-4">
              <table class="calendar-table" id="calendar-table">
                <!-- Calendar will be rendered here by JS -->
              </table>
            </div>
            <div class="d-flex justify-content-center gap-3 mb-4">
              <button type="button" class="btn btn-lg px-4 fw-bold shadow-sm" style="background-color: #ff8f00; color: white;" id="openCombinedBtn">
                ดูประวัติ/ผลตรวจสุขภาพ
              </button>
            </div>
            <!-- Modal: Booking History -->
            <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">ประวัติการจองของฉัน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-0">
                    <iframe id="historyIframe" src="" style="width:100%;height:70vh;border:none;"></iframe>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary me-auto" id="historyBackBtn"><i class="bi bi-arrow-left me-2"></i>ย้อนกลับ</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal: Health Results -->
            <div class="modal fade" id="resultsModal" tabindex="-1" aria-labelledby="resultsModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="resultsModalLabel">ผลการตรวจสุขภาพ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-0">
                    <iframe id="resultsIframe" src="" style="width:100%;height:70vh;border:none;"></iframe>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary me-auto" id="resultsBackBtn"><i class="bi bi-arrow-left me-2"></i>ย้อนกลับ</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal: Verify Info (id_card & phone) -->
            <div class="modal fade" id="verifyInfoModal" tabindex="-1" aria-labelledby="verifyInfoModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="verifyInfoModalLabel">ยืนยันตัวตนก่อนเข้าดูข้อมูล</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <form id="verifyInfoForm">
                      <div class="mb-3">
                        <label for="verifyIdCard" class="form-label">รหัสประจำตัว 13 หลัก</label>
                        <input type="text" class="form-control" id="verifyIdCard" maxlength="13" autocomplete="off" >
                        <div class="invalid-feedback">กรุณากรอกรหัสประจำตัว 13 หลักให้ถูกต้อง</div>
                      </div>
                      <div class="mb-3">
                        <label for="verifyPhone" class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" class="form-control" id="verifyPhone" maxlength="15" autocomplete="off" >
                        <div class="invalid-feedback">กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง</div>
                      </div>
                      <button type="submit" class="btn w-100" style="background-color: #ff8f00; color: #fff;">ยืนยัน</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <!-- Modal: Choose Action (History/Results) -->
            <div class="modal fade" id="chooseActionModal" tabindex="-1" aria-labelledby="chooseActionModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="chooseActionModalLabel">เลือกข้อมูลที่ต้องการดู</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body d-flex flex-column gap-3 align-items-center justify-content-center py-4">
                    <button type="button" class="btn btn-outline-primary btn-lg w-75" id="chooseHistoryBtn"><i class="bi bi-clock-history me-2"></i>ดูประวัติการจอง</button>
                    <button type="button" class="btn btn-outline-success btn-lg w-75" id="chooseResultsBtn"><i class="bi bi-clipboard2-pulse me-2"></i>ดูผลตรวจสุขภาพ</button>
                    <button type="button" class="btn btn-outline-secondary btn-lg w-75 mt-2" id="chooseBackBtn"><i class="bi bi-arrow-left me-2"></i>ย้อนกลับ</button>
                  </div>
                </div>
              </div>
            </div>
            <!-- ต้องกำหนดตัวแปร JS ก่อนโหลด booking-calendar.js -->
            <script>
              var currentUserId = <?php echo json_encode($user_id); ?>;
              var closedDates = <?php echo json_encode($closed_dates); ?>;
              var availableFrom = <?php echo $available_from ? ('"' . $available_from . '"') : 'null'; ?>;
              var availableTo = <?php echo $available_to ? ('"' . $available_to . '"') : 'null'; ?>;
              var bookingCounts = <?php echo json_encode($booking_counts); ?>;
              var maxBookingPerDay = <?php echo $max_per_day; ?>;
            </script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <!-- moved all modal/validation JS to booking-calendar.js -->
          <?php endif; ?>
        </div>

        </div> <!-- .card-body -->
      </div> <!-- .card -->
    </div> <!-- .col -->
  </div> <!-- .row -->
</div> <!-- .container -->

<footer class="footer text-center" >
  <div class="container py-4">
    <div class="row align-items-center mb-2 gy-4">
      <div class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center">
        <div class="d-flex flex-row align-items-center justify-content-center mb-2" style="gap: 18px;">
          <img src="../img/logo.png" alt="logo" style="height: 80px; width: 80px; object-fit: contain;">
          <img src="../img/logo2.png" alt="logo2" style="height: 120px; width: 100px; object-fit: contain;">
        </div>
        <div style="white-space: pre-line; font-size: 1.1rem; line-height: 1.2;">
          คณะแพทยศาสตร์มหาวิทยาลัยนเรศวร
        </div>
      </div>
      <div class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center">
        <div style="white-space: pre-line; font-size: 1.05rem;">
          เลขที่ 99 หมู่ 9 ตำบล ท่าโพธิ์ อำเภอ เมืองพิษณุโลก จังหวัด พิษณุโลก 65000
        </div>
      </div>
      <div class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center">
        <div style="white-space: pre-line; font-size: 1.15rem; font-weight: 500;">
          0 5596 5666
          0 5596 5777
        </div>
      </div>
    </div>
    <div class="text-center mt-3" style="font-size: 1rem; opacity: 0.85;">
      © 2025 คณะแพทยศาสตร์ มหาวิทยาลัยนเรศวร
    </div>
  </div>
</footer>


<?php include("../script.php")?>
<script src="../js/booking-calendar.js"></script>

<!-- Modal Bootstrap 5 (ย้าย modal ไปไว้ท้าย body) -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="bookingForm" method="post">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
      <div class="modal-header">
        <h5 class="modal-title" id="bookingModalLabel">จองคิว</h5>
        <span id="queueNumber" class="ms-3 text-primary"></span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="bookingDate" name="bookingDate">
        <div class="mb-3">
          <label for="bookingName" class="form-label">ชื่อ-นามสกุล</label>
          <input type="text" class="form-control" id="bookingName" name="bookingName">
          <div class="invalid-feedback">กรุณากรอกชื่อ-นามสกุล</div>
        </div>
        <div class="mb-3">
          <label for="bookingIdCard" class="form-label">รหัสประจำตัว 13 หลัก</label>
          <input type="text" class="form-control" id="bookingIdCard" name="bookingIdCard" maxlength="13" pattern="\d{13}">
          <div class="invalid-feedback">กรุณากรอกรหัสประจำตัว 13 หลักให้ถูกต้อง</div>
        </div>
        <div class="mb-3">
          <label for="bookingPhone" class="form-label">เบอร์โทรศัพท์</label>
          <input type="tel" class="form-control" id="bookingPhone" name="bookingPhone" maxlength="15" pattern="[0-9]{9,15}">
          <div class="invalid-feedback">กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง</div>
        </div>
        <div class="mb-3">
          <label for="bookingEmail" class="form-label">อีเมล</label>
          <input type="email" class="form-control" id="bookingEmail" name="bookingEmail">
          <div class="invalid-feedback">กรุณากรอกอีเมลให้ถูกต้อง</div>
        </div>
        <div class="mb-3">
          <label for="bookingServiceType" class="form-label">การเข้ารับบริการตรวจสุขภาพ</label>
          <select class="form-select" id="bookingServiceType" name="bookingServiceType">
            <option value="">-- กรุณาเลือก --</option>
            <option value="ทั่วไป">ตรวจสุขภาพทั่วไป</option>
            <option value="ก่อนเข้าทำงาน">ตรวจสุขภาพก่อนเข้าทำงาน</option>
            <option value="ประจำปี">ตรวจสุขภาพประจำปี</option>
            <option value="อื่น ๆ">อื่น ๆ</option>
          </select>
          <div class="invalid-feedback">กรุณาเลือกประเภทการเข้ารับบริการ</div>
        </div>
        <div class="mb-3">
          <label for="bookingGender" class="form-label">เพศ</label>
          <select class="form-select" id="bookingGender" name="bookingGender">
            <option value="">-- กรุณาเลือก --</option>
            <option value="ชาย">ชาย</option>
            <option value="หญิง">หญิง</option>
            <option value="อื่น ๆ">อื่น ๆ</option>
          </select>
          <div class="invalid-feedback">กรุณาเลือกเพศ</div>
        </div>
        <div class="mb-3">
          <label for="bookingAge" class="form-label">อายุ</label>
          <input type="number" class="form-control" id="bookingAge" name="bookingAge" min="0" max="120">
          <div class="invalid-feedback">กรุณากรอกอายุให้ถูกต้อง</div>
        </div>
        <div class="mb-3">
          <label class="form-label">เลือกเวลานัดหมาย</label>
          <div id="bookingTimeGrid" class="d-flex flex-wrap gap-2"></div>
          <input type="hidden" id="bookingTime" name="bookingTime">
          <div class="invalid-feedback" id="bookingTimeFeedback" style="display:none;">กรุณาเลือกเวลานัดหมาย</div>
        </div>
        <div class="mb-3">
          <label for="bookingNote" class="form-label">หมายเหตุ (ถ้ามี)</label>
          <textarea class="form-control" id="bookingNote" name="bookingNote" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-success">ยืนยันจองคิว</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>