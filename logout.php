<?php
// ไฟล์นี้ควรบันทึกเป็น logout.php

// 1. เริ่มต้น Session
// ต้องเรียก session_start() เสมอ เพื่อให้ PHP รู้จักและจัดการ Session ที่มีอยู่ได้
session_start();

// 2. ทำลาย Session Data ทั้งหมด
// ลบตัวแปร Session ทั้งหมดใน script ปัจจุบัน
$_SESSION = array();

// ถ้าต้องการทำลาย Session บน Client (ลบคุกกี้ Session) ด้วย
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// สุดท้าย ทำลาย Session บน Server
session_destroy();

// 3. เปลี่ยนเส้นทางไปยังหน้า Login
// เมื่อออกจากระบบแล้ว ควรส่งผู้ใช้กลับไปหน้าเข้าสู่ระบบ หรือหน้าหลักที่ไม่ได้ล็อกอิน
header("Location: login.php");
exit();
?>