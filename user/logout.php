<?php
session_start();
// ลบ session ทั้งหมด
session_unset();
// ทำลาย session
session_destroy();
// redirect ไปหน้า login หรือหน้าแรก
header('Location: login.php');
exit;
