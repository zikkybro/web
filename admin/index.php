<?php
include("../system_urls.php");
include("../include/ConnDB.php");
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$today = date('Y-m-d');

// จำนวนคิววันนี้
$sqlQueue = "SELECT COUNT(*) AS total FROM bookings WHERE booking_date = ?";
$stmtQueue = mysqli_prepare($conn, $sqlQueue);
mysqli_stmt_bind_param($stmtQueue, "s", $today);
mysqli_stmt_execute($stmtQueue);
$resultQueue = mysqli_stmt_get_result($stmtQueue);
$rowQueue = mysqli_fetch_assoc($resultQueue);
$queueToday = $rowQueue['total'] ?? 0;
mysqli_stmt_close($stmtQueue);

// จำนวนผู้ป่วยใหม่วันนี้
$sqlPatient = "SELECT COUNT(*) AS total FROM users WHERE DATE(created_at) = ?";
$stmtPatient = mysqli_prepare($conn, $sqlPatient);
mysqli_stmt_bind_param($stmtPatient, "s", $today);
mysqli_stmt_execute($stmtPatient);
$resultPatient = mysqli_stmt_get_result($stmtPatient);
$rowPatient = mysqli_fetch_assoc($resultPatient);
$newPatients = $rowPatient['total'] ?? 0;
mysqli_stmt_close($stmtPatient);

// คิวจองรวมของสัปดาห์นี้
$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));
$sqlWeek = "SELECT COUNT(*) AS total FROM bookings WHERE booking_date BETWEEN ? AND ?";
$stmtWeek = mysqli_prepare($conn, $sqlWeek);
mysqli_stmt_bind_param($stmtWeek, "ss", $monday, $sunday);
mysqli_stmt_execute($stmtWeek);
$resultWeek = mysqli_stmt_get_result($stmtWeek);
$rowWeek = mysqli_fetch_assoc($resultWeek);
$queueWeek = $rowWeek['total'] ?? 0;
mysqli_stmt_close($stmtWeek);

// ดึงรายการคิววันนี้

// --- Pagination for queue table ---
$selected_date = isset($_GET['date']) ? $_GET['date'] : $today;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;
$sqlCount = "SELECT COUNT(*) AS total FROM bookings WHERE booking_date = ?";
$stmtCount = mysqli_prepare($conn, $sqlCount);
mysqli_stmt_bind_param($stmtCount, "s", $selected_date);
mysqli_stmt_execute($stmtCount);
$resCount = mysqli_stmt_get_result($stmtCount);
$total_rows = mysqli_fetch_assoc($resCount)['total'] ?? 0;
mysqli_stmt_close($stmtCount);
$total_pages = ceil($total_rows / $per_page);

$sqlList = "SELECT b.queue_number, b.booking_time, b.name AS patient_name, u.username AS username 
            FROM bookings b 
            LEFT JOIN users u ON b.user_id = u.id 
            WHERE b.booking_date = ? 
            ORDER BY b.booking_time ASC LIMIT $per_page OFFSET $offset";
$stmtList = mysqli_prepare($conn, $sqlList);
mysqli_stmt_bind_param($stmtList, "s", $selected_date);
mysqli_stmt_execute($stmtList);
$resultList = mysqli_stmt_get_result($stmtList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="icon" type="image/png" href="../img/logo.png">
  <?php include("../link.php"); ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="container-fluid">
  <div class="row">
   <nav class="col-md-2 d-none d-md-block sidebar py-4 bg-light border-end">
  <div class="text-center mb-4">
    <img src="../img/logo.png" alt="Logo" style="height:48px;">
    <h5 class="mt-2">Admin</h5>
  </div>
  <ul class="nav flex-column">
    <li class="nav-item"><a class="nav-link active" href="#"><i class="bi bi-speedometer2"></i> แดชบอร์ด</a></li>
    <li class="nav-item"><a class="nav-link" href="queue_manage.php"><i class="bi bi-calendar-check"></i> การจัดการคิว</a></li>
    <li class="nav-item"><a class="nav-link" href="patients.php"><i class="bi bi-person-lines-fill"></i> ข้อมูลผู้ป่วย</a></li>
    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-box2-heart"></i> แพ็คเกจตรวจสุขภาพ</a></li>
    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-clipboard2-pulse"></i> บริการ/รายการตรวจ</a></li>
    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="bi bi-bar-chart-line"></i> รายงาน</a></li>
    <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> การตั้งค่า</a></li>
  </ul>
</nav>

    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4">
     <div class="topbar d-flex align-items-center justify-content-between py-3 px-3 border-bottom">
  <div>
    <h4 class="mb-0">แดชบอร์ดผู้ดูแลระบบ</h4>
  </div>
  <div class="d-flex align-items-center gap-3">
    <form class="d-flex" role="search">
      <input class="form-control form-control-sm me-2" type="search" placeholder="ค้นหาคิว/ผู้ป่วย" aria-label="Search">
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

      <div class="main-content">
        <div class="row mb-4 align-items-end">
          <div class="col-md-3 mb-2">
            <label for="dashboardDate">เลือกวันที่</label>
            <form method="get" class="d-flex align-items-center gap-2">
              <input type="date" id="dashboardDate" name="date" class="form-control" value="<?php echo htmlspecialchars($selected_date); ?>" onchange="this.form.submit()">
            </form>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm border-0">
              <div class="card-body">
                <h6 class="card-title"><i class="bi bi-calendar-event text-primary"></i> คิววันนี้</h6>
                <h2 class="fw-bold" id="queueTodayVal"><?php echo $queueToday; ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm border-0">
              <div class="card-body">
                <h6 class="card-title"><i class="bi bi-person-plus text-success"></i> ผู้ป่วยใหม่</h6>
                <h2 class="fw-bold" id="newPatientsVal"><?php echo $newPatients; ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm border-0">
              <div class="card-body">
                <h6 class="card-title"><i class="bi bi-calendar-week text-info"></i> คิวจองรวมสัปดาห์นี้</h6>
                <h2 class="fw-bold" id="queueWeekVal"><?php echo $queueWeek; ?></h2>
              </div>
            </div>
          </div>
        </div>
        <!-- ตารางคิว -->
        <div class="card shadow-sm border-0">
          <div class="card-header bg-primary text-white">
            <span id="queueListTitle">รายการคิววันนี้</span>
          </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th>คิวที่</th>
                  <th>เวลา</th>
                  <th>ชื่อผู้ป่วย</th>
                  <th>ชื่อผู้ใช้</th>
                </tr>
              </thead>
              <tbody id="queueTableBody">
              <?php while($row = mysqli_fetch_assoc($resultList)): ?>
                <tr>
                  <td><?= htmlspecialchars($row['queue_number']) ?></td>
                  <td><?= htmlspecialchars($row['booking_time']) ?></td>
                  <td><?= htmlspecialchars($row['patient_name']) ?></td>
                  <td><?= htmlspecialchars($row['username']) ?></td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
              <ul class="pagination justify-content-center my-2">
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
        </div>
      </div>
    </main>
  </div>
</div>
<?php include("../script.php") ?>
<script src="../js/admin.js"></script>
</body>
</html>
