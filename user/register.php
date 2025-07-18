<?php
    include("../system_urls.php");
    include("../include/ConnDB.php");

    // ถ้ามีการ submit ฟอร์ม
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];
        if ($username === '' || $email === '' || $password === '') {
            $errors[] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        }
        // ตรวจสอบซ้ำ
        $sql = "SELECT id FROM users WHERE username=? OR email=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้แล้ว';
        }
        mysqli_stmt_close($stmt);
        if (!$errors) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hash);
            if (mysqli_stmt_execute($stmt)) {
                echo '<script>window.parent.postMessage({swalType:"success", title:"สมัครสมาชิกสำเร็จ!", text:"คุณสามารถเข้าสู่ระบบได้แล้ว"}, "*");</script>';
                exit;
            } else {
                $errors[] = 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
            }
            mysqli_stmt_close($stmt);
        }
    }
    // ถ้ามี error ให้แสดง Swal ผ่าน postMessage
    if (!empty($errors)) {
        $msg = implode("\n", $errors);
        echo '<script>window.parent.postMessage({swalType:"error", title:"เกิดข้อผิดพลาด", text:'.json_encode($msg).'}, "*");</script>';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include("../link.php")?>
    <title>สมัครสมาชิก</title>
    <link rel="stylesheet" href="../css/login-register.css">
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
                alt="register form" class="img-fluid h-100 w-100 object-fit-cover" style="border-radius: 1rem 0 0 1rem; min-height:350px; height:100%; max-height:none; object-fit:cover;" />
            </div>
            <div class="col-md-6 col-lg-7 d-flex align-items-center">
              <div class="card-body p-4 p-lg-5 text-black">
                <form id="registerForm" method="post" autocomplete="off" action="register.php">
                  <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2">
                      <?php foreach($errors as $e) echo $e.'<br>'; ?>
                    </div>
                  <?php endif; ?>
                  <div class="d-flex align-items-center mb-3 pb-1">
                    <img src="../img/logo.png" alt="Logo" style="height:48px;width:auto;object-fit:contain;margin-right:12px;">
                    <span class="h1 fw-bold mb-0">NU</span>
                  </div>
                  <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">สมัครสมาชิก</h5>
                  <div class="form-outline mb-4">
                    <input type="text" id="username" name="username" class="form-control form-control-lg" placeholder=" " />
                    <label class="form-label" for="username">ชื่อผู้ใช้</label>
                  </div>
                  <div class="form-outline mb-4">
                    <input type="email" id="email" name="email" class="form-control form-control-lg" placeholder=" " />
                    <label class="form-label" for="email">อีเมล</label>
                  </div>
                  <div class="form-outline mb-4">
                    <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder=" " />
                    <label class="form-label" for="password">รหัสผ่าน</label>
                  </div>
                  <div class="pt-1 mb-4">
                    <button class="btn btn-dark btn-lg btn-block w-100" type="submit">สมัครสมาชิก</button>
                  </div>
                  <p class="mb-5 pb-lg-2" style="color: #393f81;">มีบัญชีแล้ว? <a href="login.php" style="color: #393f81;">เข้าสู่ระบบ</a></p>
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
<script src="../js/register.js"></script>
</body>
</html>
