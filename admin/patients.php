<?php
include("../system_urls.php");
include("../include/ConnDB.php");
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// ดึงข้อมูลผู้ป่วยทั้งหมด (ตัวอย่าง)
$sql = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>ข้อมูลผู้ป่วย</title>
    <link rel="icon" type="image/png" href="../img/logo.png">
    <!-- admin.css (patients.php) -->
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
        <li class="nav-item"><a class="nav-link active" href="patients.php"><i class="bi bi-person-lines-fill"></i>ข้อมูลผู้ป่วย</a></li>
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
          <h4 class="mb-0">ข้อมูลผู้ป่วย</h4>
        </div>
        <div class="d-flex align-items-center gap-3">
          <form class="d-flex" role="search">
            <input class="form-control form-control-sm me-2" type="search" placeholder="ค้นหาผู้ป่วย" aria-label="Search">
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
        <h3 class="mb-4">ข้อมูลผู้ป่วย</h3>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ชื่อผู้ใช้</th>
                    <th>อีเมล</th>
                    <th>วันที่สมัคร</th>
                    <th>เช็คประวัติการตรวจ</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary view-history-btn" 
                          data-userid="<?php echo $row['id']; ?>" 
                          data-username="<?php echo htmlspecialchars($row['username']); ?>"
                        ><i class="bi bi-clipboard-check"></i> เช็คประวัติการตรวจ</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-secondary">กลับแดชบอร์ด</a>
      </div>
    </main>

    <!-- Modal แนบผลตรวจ -->
    <!-- Modal ประวัติการตรวจ -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="historyModalLabel">ประวัติการตรวจของ <span id="historyModalUsername"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="historyModalContent">
              <div class="text-center text-muted">กำลังโหลดข้อมูล...</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
          </div>
        </div>
      </div>
    </div>
   
    
  </div>
</div>
<?php include("../script.php")?>
<script src="../js/admin-patients.js"></script>
</body>
</html>
