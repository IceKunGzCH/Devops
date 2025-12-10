<?php
$servername = "localhost";
$username = "root";   // ปกติของ XAMPP/MAMP
$password = "";       // ถ้ามีรหัสผ่านให้ใส่ตรงนี้
$dbname = "lantip_db"; // ตามภาพ

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// รับค่าจากฟอร์ม
$name = $_POST['name_report'];
$email = $_POST['email_report'];
$head = $_POST['report_hand'];
$detail = $_POST['report'];

$sql = "INSERT INTO report_admin (name_report, email_report, report_hand, report)
        VALUES ('$name', '$email', '$head', '$detail')";

if ($conn->query($sql) === TRUE) {
    echo "<h3>ส่งข้อความสำเร็จ!</h3>";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
