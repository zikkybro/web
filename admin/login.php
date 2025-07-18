<?php
include("../system_urls.php");
include("../include/ConnDB.php");

session_start();
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = '';
    } else {
        $sql = "SELECT id, password, name FROM admins WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            die('Prepare failed: ' . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $admin_id, $hash, $name);
        if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $hash)) {
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_name'] = $name;
                // แสดง Swal ที่หน้านี้ แล้ว redirect ไป index.php หลัง Swal ปิด (ให้ใช้ธีม/พื้นหลังเหมือนหน้า login จริง)
                echo '<!DOCTYPE html>';
                echo '<html lang="en">';
                echo '<head>';
                echo '<meta charset="UTF-8">';
                echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                include_once("../link.php");
                echo '<link rel="stylesheet" href="../css/login-register.css">';
                echo '<title>เข้าสู่ระบบผู้ดูแลระบบ</title>';
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
                echo '                  <span class="h1 fw-bold mb-0">Admin</span>';
                echo '                </div>';
                echo '                <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">เข้าสู่ระบบผู้ดูแลระบบ</h5>';
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
                echo '    title: "เข้าสู่ระบบผู้ดูแลระบบสำเร็จ!",';
                echo '    text: "ยินดีต้อนรับสู่แดชบอร์ด",';
                echo '    showConfirmButton: false,';
                echo '    timer: 1400';
                echo '  }).then(() => { window.location = "index.php"; });';
                echo '  setTimeout(function(){ window.location = "index.php"; }, 1600);';
                echo '} else {';
                echo '  document.getElementById("swal-root").innerHTML = "<div style=\'padding:2em;text-align:center;color:green;font-size:1.2em\'>เข้าสู่ระบบสำเร็จ กำลังไปหน้าแดชบอร์ด...</div>";';
                echo '  setTimeout(function(){ window.location = "index.php"; }, 1200);';
                echo '}';
                echo '</script>';
                echo '</body></html>';
                exit;
            } else {
                $error = 'รหัสผ่านไม่ถูกต้อง';
            }
        } else {
            $error = 'ไม่พบผู้ดูแลระบบนี้';
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
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>เข้าสู่ระบบผู้ดูแลระบบ</title>
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
                    <div class="alert alert-danger py-2"><?php echo $error; ?></div>
                  <?php endif; ?>
                  <div class="d-flex align-items-center mb-3 pb-1">
                    <img src="../img/logo.png" alt="Logo" style="height:48px;width:auto;object-fit:contain;margin-right:12px;">
                    <span class="h1 fw-bold mb-0">Admin</span>
                  </div>
                  <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">เข้าสู่ระบบผู้ดูแลระบบ</h5>
                  <div class="form-outline mb-4">
                    <input type="text" id="username" name="username" class="form-control form-control-lg" placeholder=" " />
                    <label class="form-label" for="username">ชื่อผู้ใช้</label>
                  </div>
                  <div class="form-outline mb-4">
                    <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder=" " />
                    <label class="form-label" for="password">รหัสผ่าน</label>
                  </div>
                  <div class="pt-1 mb-4">
                    <button class="btn btn-dark btn-lg btn-block w-100" type="submit">เข้าสู่ระบบ</button>
                  </div>
                  <a class="small text-muted" href="#">ลืมรหัสผ่าน?</a>
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
<?php include("../script.php") ?>
<script src="../vendor/sweetalert2/js/sweetalert2.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // เฉพาะหน้า login: แสดง Swal เฉพาะกรณี error เท่านั้น ไม่ต้องแสดงเมื่อ sessionStorage adminLoginSuccess = 1
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($username === '' || $password === '')): ?>
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน',
        confirmButtonColor: '#d33'
      });
      var username = document.getElementById('username');
      var password = document.getElementById('password');
      if (username) username.classList.add('is-invalid');
      if (password) password.classList.add('is-invalid');
    <?php elseif (!empty($error)): ?>
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: '<?php echo addslashes($error); ?>',
        confirmButtonColor: '#d33'
      });
      var username = document.getElementById('username');
      var password = document.getElementById('password');
      if (username) username.classList.add('is-invalid');
      if (password) password.classList.add('is-invalid');
    <?php endif; ?>
    // ไม่ต้องแสดง Swal สำเร็จที่หน้า login (ให้แสดงที่ index.php เท่านั้น)
    sessionStorage.removeItem('adminLoginSuccess');
  });
</script>
</body>
</html>
