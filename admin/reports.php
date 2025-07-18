<?php
include("../system_urls.php");
include("../include/ConnDB.php");
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>รายงาน</title>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <!-- admin.css (reports.php) -->
    <link rel="stylesheet" href="../css/admin.css">
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
        <li class="nav-item"><a class="nav-link" href="queue_manage.php"><i class="bi bi-calendar-check"></i>การจัดการคิว</a></li>
        <li class="nav-item"><a class="nav-link" href="patients.php"><i class="bi bi-person-lines-fill"></i>ข้อมูลผู้ป่วย</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-box2-heart"></i>แพ็คเกจตรวจสุขภาพ</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-clipboard2-pulse"></i>บริการ/รายการตรวจ</a></li>
        <li class="nav-item"><a class="nav-link active" href="reports.php"><i class="bi bi-bar-chart-line"></i>รายงาน</a></li>
        <li class="nav-item"><a class="nav-link" href="settings.php"><i class="bi bi-gear"></i>การตั้งค่า</a></li>
      </ul>
    </nav>
    <!-- Main Content -->
    <main class="col-md-10 ms-sm-auto col-lg-10 px-md-4">
      <!-- Topbar -->
      <div class="topbar d-flex align-items-center justify-content-between py-3 px-3">
        <div>
          <h4 class="mb-0">รายงาน</h4>
        </div>
        <div class="d-flex align-items-center gap-3">
          <form class="d-flex" role="search">
            <input class="form-control form-control-sm me-2" type="search" placeholder="ค้นหารายงาน" aria-label="Search">
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
        <div class="row mb-4">
          <div class="col-12 mb-3">
            <div class="bg-gradient p-4 rounded shadow-sm" style="background: linear-gradient(90deg,#f1f5f9 0,#e0e7ef 100%); color:#222;">
              <h3 class="mb-1" style="color:#222;"><i class="bi bi-bar-chart-line"></i> รายงานภาพรวมระบบ</h3>
              <div style="color:#444;">สรุปสถิติการจองคิวและผู้ป่วยใหม่ <span id="daysLabel">7</span> วันล่าสุด</div>
              <div class="d-flex align-items-center gap-2 mt-2">
                <label for="daysSelect" class="form-label mb-0" style="font-size:1rem;color:#222;font-weight:500;">ช่วงวัน:</label>
                <select id="daysSelect" class="form-select form-select-sm w-auto" style="min-width:80px;">
                  <option value="7" selected>7 วัน</option>
                  <option value="14">14 วัน</option>
                  <option value="30">30 วัน</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card shadow border-0 mb-3">
              <div class="card-body">
                <h6 class="card-title text-primary"><i class="bi bi-calendar-event"></i> <span id="queueChartTitle">จำนวนคิว (7 วัน)</span></h6>
                <canvas id="queueChart" height="120"></canvas>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card shadow border-0 mb-3">
              <div class="card-body">
                <h6 class="card-title text-success"><i class="bi bi-person-plus"></i> <span id="patientChartTitle">ผู้ป่วยใหม่ (7 วัน)</span></h6>
                <canvas id="patientChart" height="120"></canvas>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="card shadow border-0">
              <div class="card-body">
                <h6 class="card-title"><i class="bi bi-table"></i> <span id="tableTitle">ตารางสรุป (7 วันล่าสุด)</span></h6>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover mb-0 align-middle">
                    <thead class="table-light">
                      <tr>
                        <th><i class="bi bi-calendar"></i> วันที่</th>
                        <th><i class="bi bi-calendar-event"></i> จำนวนคิว</th>
                        <th><i class="bi bi-person-plus"></i> ผู้ป่วยใหม่</th>
                      </tr>
                    </thead>
                    <tbody id="reportTableBody">
                      <tr><td colspan="3" class="text-center">กำลังโหลด...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script src="../js/admin.js"></script>
      <script src="../js/admin-reports.js"></script>
    </main>
  </div>
</div>
<?php include("../script.php")?>
</body>
</html>
