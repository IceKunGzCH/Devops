<?php
// ไฟล์นี้ควรบันทึกเป็น post.php

session_start();

// 1. ตรวจสอบสถานะการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    // ถ้ายังไม่ล็อกอิน ให้เปลี่ยนเส้นทางไปหน้า login
    header("Location: login.php");
    exit();
}

// รวมไฟล์เชื่อมต่อฐานข้อมูล
include 'connect_db.php'; 

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. รับข้อมูลจากฟอร์ม
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $tags_raw = trim($_POST["tags"]);
    $image_url = NULL; // กำหนดค่าเริ่มต้นเป็น NULL สำหรับคอลัมน์รูปภาพ

    // 3. ตรวจสอบความถูกต้องของข้อมูลหลัก
    if (empty($title) || empty($content)) {
        $error = "❌ กรุณากรอกหัวข้อและเนื้อหาของกระทู้ให้ครบถ้วน";
    } 
    
    // === ส่วนใหม่: การจัดการไฟล์อัปโหลด (ถ้าไม่มี error หลัก) ===
    if (empty($error) && isset($_FILES['topic_image']) && $_FILES['topic_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        // ตรวจสอบและสร้างโฟลเดอร์ uploads ถ้ายังไม่มี
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_info = $_FILES['topic_image'];
        
        if ($file_info['size'] > $max_size) {
            $error = "❌ ขนาดไฟล์รูปภาพต้องไม่เกิน 5MB";
        } elseif (!in_array($file_info['type'], $allowed_types)) {
            $error = "❌ ชนิดไฟล์รูปภาพไม่ถูกต้อง รองรับเฉพาะ JPG, PNG, GIF";
        } else {
            // สร้างชื่อไฟล์ที่ไม่ซ้ำกัน
            $file_ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('img_', true) . '.' . strtolower($file_ext);
            $target_file = $target_dir . $new_file_name;
            
            // ย้ายไฟล์จาก temp ไปยังโฟลเดอร์ uploads
            if (move_uploaded_file($file_info['tmp_name'], $target_file)) {
                $image_url = $new_file_name; // บันทึกชื่อไฟล์ (path สัมพัทธ์) ลง DB
            } else {
                $error = "❌ เกิดข้อผิดพลาดในการย้ายไฟล์ โปรดตรวจสอบสิทธิ์โฟลเดอร์ 'uploads'";
            }
        }
    }
    // === สิ้นสุดการจัดการไฟล์อัปโหลด ===


    // 4. บันทึกกระทู้ลงในฐานข้อมูล ถ้าไม่มี Error เลย
    if (empty($error)) {
        // จัดการ Tags
        $tags = preg_replace('/\s*,\s*/', ',', $tags_raw); 

        // ใช้ Prepared Statement โดยเพิ่มคอลัมน์ image_url
        $sql = "INSERT INTO Topic (user_id, title, content, tags, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // ผูกตัวแปร: i, s, s, s, s (integer, string, string, string, string สำหรับ image_url)
        $stmt->bind_param("issss", $user_id, $title, $content, $tags, $image_url);
        
        if ($stmt->execute()) {
            // บันทึกสำเร็จ
            $success = "✅ ตั้งกระทู้สำเร็จ! หัวข้อ: " . htmlspecialchars($title);
            $new_topic_id = $conn->insert_id;
            header("Location: topic_view.php?id=" . $new_topic_id);
            exit();
        } else {
            // บันทึกไม่สำเร็จ
            $error = "เกิดข้อผิดพลาดในการบันทึกกระทู้: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งกระทู้ใหม่ | Lantip จำลอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f7fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-custom {
            background-color: #ff6600; /* สีส้ม Pantip */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.5rem;
        }
        .post-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 40px;
        }
        .btn-post-submit {
            background-color: #009933; /* สีเขียวสำหรับปุ่มตั้งกระทู้ */
            color: white;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .btn-post-submit:hover {
            background-color: #00802b;
            color: white;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #ff9900;
            box-shadow: 0 0 0 0.25rem rgba(255, 153, 0, 0.25);
        }
        textarea.form-control {
            min-height: 250px;
            resize: vertical;
        }
    </style>
</head>
<body>

<!-- Navbar (แบบเรียบง่ายสำหรับหน้า Post) -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-comments"></i> Lantip จำลอง
        </a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">
                <i class="fas fa-user-circle"></i> **<?= $username ?>**
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="post-container">
        <h2 class="mb-4 text-center text-secondary"><i class="fas fa-feather-alt"></i> ตั้งกระทู้ใหม่</h2>
        <hr>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- **สำคัญ:** เพิ่ม enctype="multipart/form-data" เพื่อรองรับการอัปโหลดไฟล์ -->
        <form method="POST" action="" enctype="multipart/form-data"> 
            
            <div class="mb-4">
                <label for="title" class="form-label fw-bold"><i class="fas fa-heading"></i> หัวข้อกระทู้ (Title)</label>
                <input type="text" class="form-control form-control-lg" id="title" name="title" required 
                        placeholder="กรอกหัวข้อที่น่าสนใจ" 
                        value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
            </div>

            <div class="mb-4">
                <label for="content" class="form-label fw-bold"><i class="fas fa-pencil-alt"></i> เนื้อหากระทู้</label>
                <textarea class="form-control" id="content" name="content" required 
                          placeholder="รายละเอียดของกระทู้..."><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                <small class="form-text text-muted">รองรับการใช้ Markdown/HTML พื้นฐาน (สำหรับการพัฒนาในภายหลัง)</small>
            </div>

            <div class="mb-4">
                <label for="tags" class="form-label fw-bold"><i class="fas fa-tags"></i> แท็ก (Tags)</label>
                <input type="text" class="form-control" id="tags" name="tags" 
                        placeholder="คั่นด้วยคอมมา เช่น การเมือง, ห้องบลูแพลเน็ต, หุ้น"
                        value="<?= isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : '' ?>">
            </div>
            
            <!-- ส่วนเพิ่มสำหรับอัปโหลดรูปภาพ -->
            <div class="mb-4">
                <label for="topicImage" class="form-label fw-bold"><i class="fas fa-image"></i> รูปภาพประกอบ (ไม่บังคับ)</label>
                <input class="form-control" type="file" id="topicImage" name="topic_image" accept="image/*">
                <div class="form-text text-muted">รองรับไฟล์ JPG, PNG, GIF ขนาดไม่เกิน 5MB.</div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='index.php'">
                    <i class="fas fa-arrow-left"></i> ยกเลิก
                </button>
                <button type="submit" class="btn btn-post-submit">
                    <i class="fas fa-paper-plane"></i> โพสต์กระทู้
                </button>
            </div>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>