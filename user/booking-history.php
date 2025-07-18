<?php
include("../system_urls.php");
include("../include/ConnDB.php");
$id_card = trim($_GET['id_card'] ?? '');
$phone = trim($_GET['phone'] ?? '');
$bookings = [];
// --- Handle cancel booking ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'], $_POST['booking_id']) && $id_card && $phone) {
    $booking_id = intval($_POST['booking_id']);
    // ลบเฉพาะ booking ที่ตรงกับ id_card/phone นี้เท่านั้น
    $sql_del = "DELETE FROM bookings WHERE id=? AND id_card=? AND phone=?";
    $stmt_del = mysqli_prepare($conn, $sql_del);
    mysqli_stmt_bind_param($stmt_del, "iss", $booking_id, $id_card, $phone);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);
    // reload หน้าใหม่หลังลบ (ป้องกัน submit ซ้ำ) พร้อม query string แจ้งเตือน
    header("Location: booking-history.php?id_card=".urlencode($id_card)."&phone=".urlencode($phone)."&cancel=success");
    exit;
}
if ($id_card && $phone) {
    $sql = "SELECT id, queue_number, booking_date, booking_time, name, note, created_at, service_type, gender, age FROM bookings WHERE id_card=? AND phone=? ORDER BY booking_date DESC, booking_time DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $id_card, $phone);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <link rel="stylesheet" href="../css/landing.css">
    <!-- <link rel="stylesheet" href="../css/booking-calendar.css"> -->
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
    <link rel="stylesheet" href="../css/booking-history.css">
    <title>NU</title>
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
        <li class="nav-item"><a class="nav-link" href="welcome.php">หน้าหลัก</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-calendar.php">จองคิวตรวจสุขภาพออนไลน์</a></li>
        <li class="nav-item"><a class="nav-link active" href="booking-history.php">ประวัติการจองของฉัน</a></li>
        <li class="nav-item"><a class="nav-link" href="health-results.php">ผลการตรวจสุขภาพ</a></li>
        <li class="nav-item"><a class="nav-link btn" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav> -->

<div class="container py-5 main-content">
  <h2 class="mb-4 text-center">ประวัติการจองของฉัน</h2>
  <div class="row justify-content-center">
    <div class="col-12" style="max-width:1400px;">
      <?php if (empty($bookings)): ?>
        <div class="no-history">ยังไม่มีประวัติการจอง</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle shadow-sm rounded-4 overflow-hidden bg-white" id="history-table" style="min-width: 700px;">
          <thead class="table-light">
            <tr>
              <th scope="col" class="py-3 ps-4 text-dark fw-bold" style="font-size:1.08rem; letter-spacing:0.5px;">คิวที่</th>
              <th scope="col" class="py-3 text-dark fw-bold" style="font-size:1.08rem; letter-spacing:0.5px;">วันที่</th>
              <th scope="col" class="py-3 text-dark fw-bold" style="font-size:1.08rem; letter-spacing:0.5px;">เวลา</th>
              <th scope="col" class="py-3 text-dark fw-bold" style="font-size:1.08rem; letter-spacing:0.5px;">ชื่อผู้จอง</th>
              <th scope="col" class="py-3 text-dark fw-bold" style="font-size:1.08rem; letter-spacing:0.5px;">หมายเหตุ</th>
              <th scope="col" class="py-3 text-dark fw-bold" style="font-size:1.08rem; letter-spacing:0.5px;">จองเมื่อ</th>
              <th scope="col" class="py-3 pe-4 text-dark fw-bold" style="font-size:1.08rem; letter-spacing:0.5px;">ยกเลิก</th>
              <th scope="col" class="py-3 text-dark fw-bold text-center" style="font-size:1.08rem; letter-spacing:0.5px;">พิมพ์ใบจอง</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $b): ?>
              <tr class="align-middle">
                <td class="ps-4" style="font-size:1.05rem; color:#222; font-weight:500; background:#fff;">
                  <?php echo htmlspecialchars($b['queue_number']); ?>
                </td>
                <td style="font-size:1.05rem; color:#222; font-weight:500; background:#fff;">
                  <?php echo htmlspecialchars(date('d/m/Y', strtotime($b['booking_date']))); ?>
                </td>
                <td style="font-size:1.05rem; color:#222; font-weight:500; background:#fff;">
                  <?php echo htmlspecialchars($b['booking_time']); ?>
                </td>
                <td style="font-size:1.05rem; color:#222; font-weight:500; background:#fff;">
                  <?php echo htmlspecialchars($b['name']); ?>
                </td>
                <td style="font-size:1.05rem; color:#222; font-weight:400; background:#fff;">
                  <?php echo htmlspecialchars($b['note']); ?>
                </td>
                <td style="font-size:0.99rem; color:#222; background:#fff;">
                  <?php echo isset($b['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($b['created_at']))) : '-'; ?>
                </td>
                <td class="pe-4 cancel-cell" style="background:#fff;">
                  <form method="post" class="cancel-booking-form d-flex justify-content-center align-items-center m-0" action="">
                    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($b['id']); ?>">
                    <input type="hidden" name="cancel_booking" value="1">
                    <button type="submit" class="btn btn-sm btn-outline-danger btn-cancel-booking">ยกเลิก</button>
                  </form>
                </td>
                <td class="text-center" style="background:#fff;">
                  <button type="button" class="btn btn-sm btn-outline-primary btn-print-booking"
                    data-queue="<?php echo htmlspecialchars($b['queue_number']); ?>"
                    data-date="<?php echo htmlspecialchars($b['booking_date']); ?>"
                    data-time="<?php echo htmlspecialchars($b['booking_time']); ?>"
                    data-name="<?php echo htmlspecialchars($b['name']); ?>"
                    data-note="<?php echo htmlspecialchars($b['note']); ?>">
                    <i class="bi bi-printer"></i> พิมพ์
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
      <!-- <div class="text-center mt-4">
        <a href="booking-calendar.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> กลับหน้าจองคิว</a>
      </div> -->
    </div>
  </div>
</div>
<!-- Modal สำหรับแสดงใบจอง (อยู่นอกลูป foreach) -->
<div class="modal fade" id="printBookingModal" tabindex="-1" aria-labelledby="printBookingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="printBookingModalLabel">ใบจองคิวตรวจสุขภาพ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="print-preview-area">
          <div class="form-print" style="width: 100%; max-width: 794px; background: white; padding: 40px; margin: auto; border: 1px solid #ccc;">
            <div class="header" style="text-align: center; margin-bottom: 30px;">
              <img src="../img/logo.png" alt="โลโก้โรงพยาบาล" style="width: 80px;">
              <h2 style="margin: 5px 0 0; font-size: 24px;">ใบจองคิวตรวจสุขภาพ</h2>
              <p>โรงพยาบาล</p>
            </div>
            <div class="section" style="margin-bottom: 20px;">
              <p><span class="label" style="font-weight: bold; display: inline-block; width: 180px;">คิวที่:</span> <span class="value" id="print-queue" style="border-bottom: 1px dotted #333; display: inline-block; min-width: 80px; padding: 2px 5px;"></span></p>
              <p><span class="label" style="font-weight: bold; display: inline-block; width: 180px;">ชื่อ - สกุล:</span> <span class="value" id="print-name" style="border-bottom: 1px dotted #333; display: inline-block; min-width: 300px; padding: 2px 5px;"></span></p>
              <p><span class="label" style="font-weight: bold; display: inline-block; width: 180px;">วันที่ตรวจ:</span> <span class="value" id="print-date" style="border-bottom: 1px dotted #333; display: inline-block; min-width: 300px; padding: 2px 5px;"></span></p>
              <p><span class="label" style="font-weight: bold; display: inline-block; width: 180px;">เวลา:</span> <span class="value" id="print-time" style="border-bottom: 1px dotted #333; display: inline-block; min-width: 300px; padding: 2px 5px;"></span></p>
              <p><span class="label" style="font-weight: bold; display: inline-block; width: 180px;">หมายเหตุ:</span> <span class="value" id="print-note" style="border-bottom: 1px dotted #333; display: inline-block; min-width: 300px; padding: 2px 5px;"></span></p>
            </div>
            <div class="signature" style="margin-top: 50px; text-align: right;">
              <div class="signature-line" style="margin-top: 60px; border-top: 1px solid #000; width: 200px; text-align: center; margin-left: auto; padding-top: 5px;">ลายเซ็นผู้จอง</div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
        <button type="button" class="btn btn-primary" id="btn-print-modal">พิมพ์ใบจอง</button>
      </div>
    </div>
  </div>
</div>
<?php include("../script.php"); ?>
<script src="../js/booking-history.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // แจ้งเตือนเมื่อยกเลิกการจองสำเร็จ
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('cancel') === 'success') {
    Swal.fire({
      icon: 'success',
      title: 'ยกเลิกการจองสำเร็จ',
      text: 'รายการจองถูกยกเลิกเรียบร้อย',
      confirmButtonColor: '#198754',
      timer: 1600,
      showConfirmButton: false
    });
    // ลบ query string ออกจาก url (history.replaceState)
    window.history.replaceState({}, document.title, window.location.pathname + window.location.search.replace(/([&?])cancel=success(&|$)/, '$1').replace(/([&?])$/, ''));
  }
</script>
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
        <li class="nav-item"><a class="nav-link active" href="booking-history.php">ประวัติการจองของฉัน</a></li>
        <li class="nav-item"><a class="nav-link" href="#prices">ผลการตรวจสุขภาพ</a></li>
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
