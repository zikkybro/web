<?php
include("../system_urls.php");
include("../include/ConnDB.php");
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Sanitize and validate GET variables
$per_page = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;
$show_all = isset($_GET['show_all']) && $_GET['show_all'] == '1';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!$show_all) {
    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
        die('Invalid date format.');
    }
}

if ($show_all) {
    // Count
    $sql_count = "SELECT COUNT(*) AS total FROM bookings";
    $res_count = mysqli_query($conn, $sql_count);
    if (!$res_count) { die("Query failed: " . mysqli_error($conn)); }
    $row_count = mysqli_fetch_assoc($res_count);
    $total_rows = $row_count['total'] ?? 0;
    // Data (prepared statement for LIMIT/OFFSET)
    $sql = "SELECT b.id, b.queue_number, b.booking_date, b.booking_time, b.name AS patient_name, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id ORDER BY b.booking_date DESC, b.booking_time ASC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { die("Prepare failed: " . mysqli_error($conn)); }
    mysqli_stmt_bind_param($stmt, "ii", $per_page, $offset);
    if (!mysqli_stmt_execute($stmt)) { die("Execute failed: " . mysqli_stmt_error($stmt)); }
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    // Count
    $sql_count = "SELECT COUNT(*) AS total FROM bookings WHERE booking_date = ?";
    $stmt_count = mysqli_prepare($conn, $sql_count);
    if (!$stmt_count) { die("Prepare failed: " . mysqli_error($conn)); }
    mysqli_stmt_bind_param($stmt_count, "s", $selected_date);
    if (!mysqli_stmt_execute($stmt_count)) { die("Execute failed: " . mysqli_stmt_error($stmt_count)); }
    $res_count = mysqli_stmt_get_result($stmt_count);
    $row_count = mysqli_fetch_assoc($res_count);
    $total_rows = $row_count['total'] ?? 0;
    mysqli_stmt_close($stmt_count);
    // Data
    $sql = "SELECT b.id, b.queue_number, b.booking_date, b.booking_time, b.name AS patient_name, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE b.booking_date = ? ORDER BY b.booking_time ASC LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { die("Prepare failed: " . mysqli_error($conn)); }
    mysqli_stmt_bind_param($stmt, "sii", $selected_date, $per_page, $offset);
    if (!mysqli_stmt_execute($stmt)) { die("Execute failed: " . mysqli_stmt_error($stmt)); }
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}
$total_pages = ceil($total_rows / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>การจัดการคิว</title>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <!-- admin.css (queue_manage.php) -->
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/queue-manage.css">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav class="col-md-2 d-none d-md-block sidebar py-4">
      <div class="text-center mb-4">
        <img src="../img/logo.png" alt="Logo" style="height:48px;">
        <h5 class="mt-2">Admin</h5>
      </div>
      <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i>แดชบอร์ด</a></li>
        <li class="nav-item"><a class="nav-link active" href="queue_manage.php"><i class="bi bi-calendar-check"></i>การจัดการคิว</a></li>
        <li class="nav-item"><a class="nav-link" href="patients.php"><i class="bi bi-person-lines-fill"></i>ข้อมูลผู้ป่วย</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-box2-heart"></i>แพ็คเกจตรวจสุขภาพ</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-clipboard2-pulse"></i>บริการ/รายการตรวจ</a></li>
        <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-bar-chart-line"></i>รายงาน</a></li>
        <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i>การตั้งค่า</a></li>
      </ul>
    </nav>
    <!-- Main Content -->
    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4">
      <!-- Topbar -->
      <div class="topbar d-flex align-items-center justify-content-between py-3 px-3">
        <div>
          <h4 class="mb-0">การจัดการคิว</h4>
        </div>
        <div class="d-flex align-items-center gap-3">
          <form class="d-flex" role="search">
            <input class="form-control form-control-sm me-2" type="search" placeholder="ค้นหาคิว" aria-label="Search">
            <button class="btn btn-outline-primary btn-sm" type="submit"><i class="bi bi-search"></i></button>
          </form>
          <!-- Bell notification badge (เหมือนหน้า index.php) -->
          <a href="#" id="notiBtn" class="text-dark position-relative">
            <i class="bi bi-bell fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notiBadge"></span>
          </a>
          <!-- END Bell -->
          <script src="../js/admin.js"></script>
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
        <h3 class="mb-4">การจัดการคิว</h3>
        <form method="get" class="mb-3 d-flex align-items-center gap-2 flex-wrap">
          <label for="date" class="form-label mb-0">เลือกวันที่:</label>
          <input type="date" id="date" name="date" class="form-control form-control-sm" value="<?php echo $show_all ? '' : htmlspecialchars($selected_date); ?>" style="max-width:160px;" <?php if($show_all) echo 'disabled'; ?>>
          <button type="submit" class="btn btn-primary btn-sm" <?php if($show_all) echo 'disabled'; ?>>แสดงคิว</button>
          <a href="?show_all=1" class="btn btn-secondary btn-sm ms-2<?php if($show_all) echo ' active'; ?>">ดูคิวทั้งหมด</a>
          <?php if($show_all): ?>
            <a href="?date=<?php echo htmlspecialchars(date('Y-m-d')); ?>" class="btn btn-outline-primary btn-sm ms-2">กลับดูตามวัน</a>
          <?php endif; ?>
          <a href="export_bookings.php?<?php echo $show_all ? 'show_all=1' : 'date=' . urlencode($selected_date); ?>" class="btn btn-outline-success ms-auto" target="_blank">
            <i class="bi bi-file-earmark-excel"></i> ดาวน์โหลด Excel
          </a>
        </form>
        <div class="table-responsive">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
              <span id="queueListTitle">
                <?php if ($show_all): ?>
                  รายการคิวทั้งหมด
                <?php else: ?>
                  รายการคิววันที่ <?php echo htmlspecialchars($selected_date); ?>
                <?php endif; ?>
              </span>
            </div>
            <div class="card-body p-0">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>คิวที่</th>
                    <?php if ($show_all): ?>
                      <th>วันที่</th>
                    <?php endif; ?>
                    <th>เวลา</th>
                    <th>ชื่อผู้ป่วย</th>
                    <th>ชื่อผู้ใช้</th>
                    <th>แนบผลตรวจ</th>
                  </tr>
                </thead>
                <tbody id="queueTableBody">
                <?php
                // Reset result pointer for show_all (fetch_assoc หมดรอบแรกจะว่าง)
                if ($show_all && isset($result) && gettype($result) === 'object') {
                  mysqli_data_seek($result, 0);
                }
                while($row = mysqli_fetch_assoc($result)):
                ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['queue_number']); ?></td>
                    <?php if ($show_all): ?>
                      <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                      <button type="button" class="btn btn-sm btn-primary attach-result-btn"
                        data-bookingid="<?php echo $row['id']; ?>"
                        data-patientname="<?php echo htmlspecialchars($row['patient_name']); ?>"
                        data-bookingdate="<?php echo htmlspecialchars($row['booking_date']); ?>"
                      ><i class="bi bi-pencil-square"></i> แนบผลตรวจ</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>

        </table>
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li class="page-item<?php if($page<=1) echo ' disabled'; ?>">
              <a class="page-link" href="?<?php 
                $params = $_GET; $params['page'] = $page-1; 
                echo http_build_query($params); 
              ?>" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
            </li>
            <?php for($i=1;$i<=$total_pages;$i++): ?>
              <li class="page-item<?php if($i==$page) echo ' active'; ?>">
                <a class="page-link" href="?<?php $params = $_GET; $params['page']=$i; echo http_build_query($params); ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item<?php if($page>=$total_pages) echo ' disabled'; ?>">
              <a class="page-link" href="?<?php 
                $params = $_GET; $params['page'] = $page+1; 
                echo http_build_query($params); 
              ?>" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>
        
      </div>
    </main>


      <!-- Modal แนบผลตรวจ (ตารางเพิ่มแถว) -->
      <div class="modal fade" id="attachResultModal" tabindex="-1" aria-labelledby="attachResultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <form class="modal-content" id="attachResultForm" method="post" action="api_attach_result.php">
            <div class="modal-header">
              <h5 class="modal-title" id="attachResultModalLabel">
                แนบผลตรวจให้ <span id="modalPatientName"></span>
                <br>
                <small class="text-muted">วันที่ตรวจ: <span id="modalBookingDate"></span></small>
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <!-- booking_id จะถูก set ด้วย JS ตอนกดปุ่มแนบผลตรวจ -->
              <input type="hidden" name="booking_id" id="modalBookingId" required>
              <div class="mb-3">
               
              </div>
              <div class="mb-3">
                <label class="form-label">รายละเอียดผลตรวจ (สามารถเพิ่มแถวได้)</label>
                <div class="table-responsive">
                  <table class="table table-bordered mb-2" id="resultDetailsTable">
                    <thead>
                      <tr>
                        <th>รายการตรวจ</th>
                        <th>ค่าเป้าหมาย (ปกติ)</th>
                        <th>ผลตรวจ</th>
                        <th>หน่วย</th>
                        <th>สถานะ</th>
                        <th>ลบ</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>
                          <select name="detail_name[]" class="form-select detail-name-select" >
                            <option value="" data-target="" data-unit="" selected disabled>เลือกรายการตรวจ</option>
                            <option value="ความดันโลหิต (Blood Pressure)" data-target="90–120 / 60–80" data-unit="mmHg">ความดันโลหิต (Blood Pressure)</option>
                            <option value="ดัชนีมวลกาย (BMI)" data-target="18.5 – 24.9" data-unit="kg/m²">ดัชนีมวลกาย (BMI)</option>
                            <option value="อุณหภูมิร่างกาย" data-target="36.5 – 37.5" data-unit="°C">อุณหภูมิร่างกาย</option>
                            <option value="ชีพจร (Pulse)" data-target="60 – 100" data-unit="ครั้ง/นาที">ชีพจร (Pulse)</option>
                            <option value="ส่วนสูง / น้ำหนัก" data-target="ตามเกณฑ์ส่วนบุคคล" data-unit="ซม./กก.">ส่วนสูง / น้ำหนัก</option>
                            <option value="น้ำตาลในเลือด (FBS)" data-target="< 100" data-unit="mg/dL">น้ำตาลในเลือด (FBS)</option>
                            <option value="ไขมันในเลือด LDL" data-target="< 100" data-unit="mg/dL">ไขมันในเลือด LDL</option>
                            <option value="ไตรกลีเซอไรด์ (TG)" data-target="< 150" data-unit="mg/dL">ไตรกลีเซอไรด์ (TG)</option>
                            <option value="คอเลสเตอรอลรวม (Total Cholesterol)" data-target="< 200" data-unit="mg/dL">คอเลสเตอรอลรวม (Total Cholesterol)</option>
                            <option value="HDL (ไขมันดี)" data-target="> 40" data-unit="mg/dL">HDL (ไขมันดี)</option>
                            <option value="Creatinine" data-target="0.6 – 1.2" data-unit="mg/dL">Creatinine</option>
                            <option value="น้ำตาลในปัสสาวะ" data-target="ไม่พบ" data-unit="-">น้ำตาลในปัสสาวะ</option>
                            <option value="โปรตีนในปัสสาวะ" data-target="ไม่พบ" data-unit="-">โปรตีนในปัสสาวะ</option>
                            <option value="เอกซเรย์ทรวงอก (Chest X-ray)" data-target="ปกติ" data-unit="-">เอกซเรย์ทรวงอก (Chest X-ray)</option>
                            <option value="ตรวจคลื่นไฟฟ้าหัวใจ (EKG)" data-target="ปกติ" data-unit="-">ตรวจคลื่นไฟฟ้าหัวใจ (EKG)</option>
                          </select>
                        </td>
                        <td><input type="text" name="detail_target[]" class="form-control detail-target-input"></td>
                        <td><input type="text" name="detail_result[]" class="form-control"></td>
                        <td><input type="text" name="detail_unit[]" class="form-control detail-unit-input"></td>
                        <td>
                          <select name="detail_status[]" class="form-select" >
                            <option value="" selected disabled>เลือกสถานะ</option>
                            <option value="🟢 ปกติ">🟢 ปกติ</option>
                            <option value="🟡 ค่าผิดเล็กน้อย">🟡 ค่าผิดเล็กน้อย</option>
                            <option value="🔴 ค่าผิดมาก (ต้องพบแพทย์)">🔴 ค่าผิดมาก (ต้องพบแพทย์)</option>
                          </select>
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-trash"></i> </button></td>
                        <!-- booking_id จะถูกส่งในฟอร์มหลัก ไม่ต้องซ่อนในแต่ละแถว -->
                      </tr>
                    </tbody>
                  </table>
                  <button type="button" class="btn btn-outline-success btn-sm" id="addResultRow" ><i class="bi bi-plus-square"></i></button></button>
                </div>
              </div>
              <div class="mb-3">
                <label for="doctor_comment" class="form-label">ความเห็นแพทย์</label>
                <textarea class="form-control" name="doctor_comment" id="doctor_comment" rows="2"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" class="btn btn-success">บันทึกผลตรวจ</button>
            </div>
          </form>
        </div>
      </div>

      <!-- JS เฉพาะของ queue_manage.php ด้านล่างนี้ -->
      <script src="../js/admin-attach-result.js"></script>
      <script src="../js/queue-manage.js"></script>
      <?php include("../script.php")?>
    </body>
</html>
