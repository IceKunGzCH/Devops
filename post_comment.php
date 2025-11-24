<?php
// ไฟล์นี้ควรบันทึกเป็น post_comment.php

session_start();
include 'connect_db.php'; 

// 1. ตรวจสอบสถานะการล็อกอิน (อนุญาตให้โพสต์ได้เฉพาะผู้ใช้ที่ล็อกอินแล้ว)
if (!isset($_SESSION['user_id'])) {
    // หากไม่ได้ล็อกอิน ให้เปลี่ยนเส้นทางไปหน้าล็อกอิน
    header("Location: login.php");
    exit();
}

// 2. ตรวจสอบข้อมูลที่ส่งมาจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['topic_id'], $_POST['content'])) {
    
    $topic_id = (int)$_POST['topic_id'];
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);

    if (empty($content)) {
        // หากไม่มีเนื้อหาความคิดเห็น ให้เปลี่ยนเส้นทางกลับไปหน้ากระทู้เดิมพร้อม Error
        header("Location: topic_view.php?id=$topic_id&error=empty_comment");
        exit();
    }

    // 3. บันทึกความคิดเห็นลงในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO Comment (topic_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $topic_id, $user_id, $content);

    if ($stmt->execute()) {
        // บันทึกสำเร็จ เปลี่ยนเส้นทางกลับไปหน้ากระทู้เดิม
        header("Location: topic_view.php?id=" . $topic_id . "#comments");
    } else {
        // เกิดข้อผิดพลาด
        header("Location: topic_view.php?id=" . $topic_id . "&error=db_error");
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    // ไม่ได้ส่งข้อมูลแบบ POST หรือข้อมูลไม่ครบ
    header("Location: index.php");
    exit();
}