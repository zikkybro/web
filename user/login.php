<?php
    include("../system_urls.php");
    include("../include/ConnDB.php");

    // ถ้ามีการ submit ฟอร์ม
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $error = '';
        if ($username === '' || $password === '') {
            $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
        } else {
            $sql = "SELECT id, password FROM users WHERE username=? OR email=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $username, $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $uid, $hash);
            if (mysqli_stmt_fetch($stmt)) {
                if (password_verify($password, $hash)) {
                    session_start();
                    $_SESSION['user'] = $uid;
                    // แสดง Swal ที่หน้านี้ แล้ว redirect ไป booking-calendar.php หลัง Swal ปิด (ให้ใช้ธีม/พื้นหลังเหมือนหน้า login จริง)
                    echo '<!DOCTYPE html>';
                    echo '<html lang="en">';
                    echo '<head>';
                    echo '<meta charset="UTF-8">';
                    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                    include_once("../link.php");
                    echo '<link rel="stylesheet" href="../css/login-register.css">';
                    echo '<title>เข้าสู่ระบบ</title>';
                    echo '</head>';
                    echo '<body>';
                    echo '<section class="vh-100" style="background-color: #f7ecd7;">';
                    echo '  <div class="container py-5 h-100">';
                    echo '    <div class="row d-flex justify-content-center align-items-center h-100">';
                    echo '      <div class="col col-xl-10">';
                    echo '        <div class="card" style="border-radius: 1rem;">';
                    echo '          <div class="row g-0">';
                    echo '            <div class="col-md-6 col-lg-5 d-none d-md-block">';
                    echo '              <img src="../img/hospital.jpg" alt="login form" class="img-fluid h-100 w-100 object-fit-cover" style="border-radius: 1rem 0 0 1rem; min-height:350px; height:100%; max-height:none; object-fit:cover;" />';
                    echo '            </div>';
                    echo '            <div class="col-md-6 col-lg-7 d-flex align-items-center">';
                    echo '              <div class="card-body p-4 p-lg-5 text-black">';
                    echo '                <div class="d-flex align-items-center mb-3 pb-1">';
                    echo '                  <img src="../img/logo.png" alt="Logo" style="height:48px;width:auto;object-fit:contain;margin-right:12px;">';
                    echo '                  <span class="h1 fw-bold mb-0">NU</span>';
                    echo '                </div>';
                    echo '                <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">เข้าสู่ระบบ</h5>';
                    echo '                <div id="swal-root"></div>';
                    echo '              </div>';
                    echo '            </div>';
                    echo '          </div>';
                    echo '        </div>';
                    echo '      </div>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '</section>';
                    include_once("../script.php");
                    echo '<script src="../vendor/sweetalert2/js/sweetalert2.js"></script>';
                    echo '<script>';
                    echo 'if (typeof Swal !== "undefined") {';
                    echo '  Swal.fire({';
                    echo '    icon: "success",';
                    echo '    title: "เข้าสู่ระบบสำเร็จ!",';
                    echo '    text: "ยินดีต้อนรับ",';
                    echo '    showConfirmButton: false,';
                    echo '    timer: 1400';
                    echo '  }).then(() => { window.location = "booking-calendar.php"; });';
                    echo '  setTimeout(function(){ window.location = "booking-calendar.php"; }, 1600);';
                    echo '} else {';
                    echo '  document.getElementById("swal-root").innerHTML = "<div style=\'padding:2em;text-align:center;color:green;font-size:1.2em\'>เข้าสู่ระบบสำเร็จ กำลังไปหน้าหลัก...</div>";';
                    echo '  setTimeout(function(){ window.location = "booking-calendar.php"; }, 1200);';
                    echo '}';
                    echo '</script>';
                    echo '</body></html>';
                    exit;
                } else {
                    $error = 'รหัสผ่านไม่ถูกต้อง';
                }
            } else {
                $error = 'ไม่พบผู้ใช้งานนี้';
            }
            mysqli_stmt_close($stmt);
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <link rel="stylesheet" href="../css/login-register.css">
    <title>เข้าสู่ระบบ</title>
</head>
<body>
<section class="vh-100" style="background-color: #f7ecd7;">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col col-xl-10">
        <div class="card" style="border-radius: 1rem;">
          <div class="row g-0">
            <div class="col-md-6 col-lg-5 d-none d-md-block">
              <img src="../img/hospital.jpg"
                alt="login form" class="img-fluid h-100 w-100 object-fit-cover" style="border-radius: 1rem 0 0 1rem; min-height:350px; height:100%; max-height:none; object-fit:cover;" />
            </div>
            <div class="col-md-6 col-lg-7 d-flex align-items-center">
              <div class="card-body p-4 p-lg-5 text-black">
                <form id="loginForm" method="post" autocomplete="off">
                  <?php if (!empty($error)): ?>
                    <input type="hidden" id="loginErrorMsg" value="<?php echo htmlspecialchars($error, ENT_QUOTES); ?>">
                  <?php endif; ?>
                  <div class="d-flex align-items-center mb-3 pb-1">
                    <img src="../img/logo.png" alt="Logo" style="height:48px;width:auto;object-fit:contain;margin-right:12px;">
                    <span class="h1 fw-bold mb-0">NU</span>
                  </div>
                  <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">เข้าสู่ระบบ</h5>
                  <div class="form-outline mb-4">
                    <input type="text" id="username" name="username" class="form-control form-control-lg<?php if (!empty($error)) echo ' is-invalid'; ?>" placeholder=" " />
                    <label class="form-label" for="username">ชื่อผู้ใช้หรืออีเมล</label>
                  </div>
                  <div class="form-outline mb-4">
                    <input type="password" id="password" name="password" class="form-control form-control-lg<?php if (!empty($error)) echo ' is-invalid'; ?>" placeholder=" " />
                    <label class="form-label" for="password">รหัสผ่าน</label>
                  </div>
                  <div class="pt-1 mb-4">
                    <button class="btn btn-dark btn-lg btn-block w-100" type="submit">เข้าสู่ระบบ</button>
                  </div>
                  <a class="small text-muted" href="#">ลืมรหัสผ่าน?</a>
                  <p class="mb-5 pb-lg-2" style="color: #393f81;">ยังไม่มีบัญชี? <a href="register.php" style="color: #393f81;">สมัครสมาชิก</a></p>
                  <a href="#" class="small text-muted">ข้อตกลงการใช้งาน</a>
                  <a href="#" class="small text-muted">นโยบายความเป็นส่วนตัว</a>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include(__DIR__ . "/../script.php") ?>
<script src="../vendor/sweetalert2/js/sweetalert2.js"></script>
<script src="../js/login.js?v=1"></script>
</body>
</html>
