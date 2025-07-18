<?php
    include("../system_urls.php");
    // session_start();
    // if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <title>NU</title>
    <link rel="stylesheet" href="../css/landing.css">
    <link rel="icon" type="image/png" href="../img/logo.png">
  <link rel="stylesheet" href="../css/welcome-custom.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
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
        <li class="nav-item"><a class="nav-link active" href="welcome.php">หน้าหลัก</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-calendar.php">จองคิวตรวจสุขภาพออนไลน์</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-history.php">ประวัติการจองของฉัน</a></li>
        <li class="nav-item"><a class="nav-link" href="health-results.php">ผลการตรวจสุขภาพ</a></li>
        <li class="nav-item"><a class="nav-link btn" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>


<main class="main-content flex-grow-1">
  <div class="container pt-4 pb-5">
    <div class="row align-items-center justify-content-center" style="min-height: 60vh;">
      <div class="col-lg-6 mb-5 mb-lg-0">
        <h1 class="hero-title mb-3">ยินดีต้อนรับสู่ระบบจองคิวตรวจสุขภาพ</h1>
        <div class="hero-desc mb-4">
          ระบบจองคิวออนไลน์ที่ช่วยให้คุณ<br>
          จองคิวตรวจสุขภาพได้สะดวก รวดเร็ว และปลอดภัย<br>
          เริ่มต้นใช้งานได้ทันที เพียงไม่กี่คลิก
        </div>
        <a href="booking-calendar.php" class="hero-btn btn btn-primary">จองคิวตรวจสุขภาพ</a>
      </div>
      <div class="col-lg-6 text-center">
        <img src="../img/hospital.jpg" alt="hospital illustration" class="img-fluid" style="max-width: 420px; border-radius: 32% 68% 60% 40% / 40% 30% 70% 60%; object-fit: cover; background: #fff;">
      </div>
    </div>
  </div>
</main>

<footer class="footer text-center mt-5">
  <div class="container">
    <div class="row align-items-center mb-2">
      <div class="col-md-6 text-md-start mb-2 mb-md-0">
        <a class="navbar-brand d-inline-flex align-items-center" href="#">
          <img src="../img/logo.png" alt="logo" style="height: 28px; margin-right: 8px;"> NU
        </a>
      </div>
      <div class="col-md-6 text-md-end">
        <ul class="navbar-nav flex-row justify-content-end">
        <li class="nav-item"><a class="nav-link active" href="welcome.php">หน้าหลัก</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-calendar.php">จองคิวตรวจสุขภาพออนไลน์</a></li>
        <li class="nav-item"><a class="nav-link" href="booking-history.php">ประวัติการจองของฉัน</a></li>
        <li class="nav-item"><a class="nav-link" href="#prices">ผลการตรวจสุขภาพ</a></li>
        </ul>
      </div>
    </div>
    <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
      <a href="#" class="text-muted"><i class="bi bi-twitter"></i></a>
      <a href="#" class="text-muted"><i class="bi bi-facebook"></i></a>
    </div>
    <div>Copyright © 2025</div>
  </div>
</footer>

<?php include("../script.php")?>
</body>
</html>
