<?php
$servername = "localhost";
$username = "root"; // ใช้ "root" สำหรับ XAMPP/WAMP มาตรฐาน
$password = "";     // รหัสผ่านว่างเปล่าสำหรับ XAMPP/WAMP มาตรฐาน
$dbname = "lantip_db"; 

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    // ถ้าเชื่อมต่อล้มเหลว ให้แสดงข้อความแจ้งเตือน
    die("Connection failed: " . $conn->connect_error);
}

// **สำคัญ:** ตั้งค่า charset เพื่อรองรับภาษาไทยและอีโมจิ (utf8mb4)
$conn->set_charset("utf8mb4");

?>