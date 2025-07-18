<?php
session_start();
// ลบ session ทั้งหมด
session_unset();
// ทำลาย session
session_destroy();
// redirect ไปหน้า login
header('Location: login.php');
exit;
