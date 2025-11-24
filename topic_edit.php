<?php
// ไฟล์นี้ควรบันทึกเป็น topic_edit.php
session_start();
include 'connect_db.php'; 

// 1. ตรวจสอบสถานะการล็อกอินและ ID กระทู้
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($topic_id == 0) {
    die("❌ ไม่พบหมายเลขกระทู้");
}

// 2. ดึงข้อมูลกระทู้และตรวจสอบสิทธิ์
$stmt = $conn->prepare("SELECT title, content, tags, user_id FROM Topic WHERE topic_id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("❌ ไม่พบกระทู้ที่ต้องการแก้ไข");
}

$topic = $result->fetch_assoc();
$original_user_id = $topic['user_id'];

// ตรวจสอบสิทธิ์: ถ้า ID ผู้ใช้ที่ล็อกอินไม่ตรงกับ ID ผู้ตั้งกระทู้
if ($user_id != $original_user_id) {
    die("⛔ คุณไม่มีสิทธิ์แก้ไข/ลบกระทู้นี้");
}

$stmt->close();

// 3. ประมวลผลการลบ (เมื่อส่งฟอร์ม POST ด้วย action=delete)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $stmt = $conn->prepare("DELETE FROM Topic WHERE topic_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $topic_id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?status=deleted");
        exit();
    } else {
        $error = "เกิดข้อผิดพลาดในการลบกระทู้: " . $stmt->error;
    }
    $stmt->close();

} 
// 4. ประมวลผลการแก้ไข (เมื่อส่งฟอร์ม POST ด้วยการบันทึก)
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $tags_raw = trim($_POST["tags"]);
    $tags = preg_replace('/\s*,\s*/', ',', $tags_raw); 

    if (empty($title) || empty($content)) {
        $error = "❌ กรุณากรอกหัวข้อและเนื้อหาของกระทู้ให้ครบถ้วน";
    } else {
        // บรรทัด 65: 5 ตำแหน่ง (?)
        $stmt = $conn->prepare("UPDATE Topic SET title = ?, content = ?, tags = ? WHERE topic_id = ? AND user_id = ?");
        
        // บรรทัด 66: แก้ไข Type String เป็น "sssii" (5 ตัว)
        $stmt->bind_param("sssii", $title, $content, $tags, $topic_id, $user_id);
        
        if ($stmt->execute()) {
            $success = "✅ แก้ไขกระทู้สำเร็จ!";
            // เปลี่ยนเส้นทางกลับไปหน้าแสดงกระทู้
            header("Location: topic_view.php?id=" . $topic_id . "&status=updated");
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกการแก้ไข: " . $stmt->error;
        }
        $stmt->close();
    }
}

// ถ้าไม่ได้ส่งฟอร์ม ให้กำหนดค่าเริ่มต้นสำหรับฟอร์ม (อาจถูกอัปเดตถ้ามี $error)
$current_title = isset($title) ? htmlspecialchars($title) : htmlspecialchars($topic['title']);
$current_content = isset($content) ? htmlspecialchars($content) : htmlspecialchars($topic['content']);
$current_tags = isset($tags_raw) ? htmlspecialchars($tags_raw) : htmlspecialchars($topic['tags']);

// ... (ต่อด้วย HTML/Form)
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขกระทู้: <?= $current_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ... (คัดลอก CSS ที่เกี่ยวข้องจาก post.php มาใส่ที่นี่) ... */
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        body { font-family: 'Kanit', sans-serif; background-color: #f5f7fa; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar-custom { background-color: #ff6600; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .navbar-brand { font-weight: 700; color: white !important; font-size: 1.5rem; }
        .post-container { background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); margin-top: 20px; margin-bottom: 40px; }
        .btn-post-submit { background-color: #009933; color: white; border-radius: 8px; padding: 10px 25px; font-weight: 500; transition: background-color 0.3s; }
        .btn-post-submit:hover { background-color: #00802b; color: white; }
        .form-control, .form-select { border-radius: 8px; padding: 10px 15px; }
        textarea.form-control { min-height: 250px; resize: vertical; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-comments"></i> Lantip จำลอง
        </a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">
                <i class="fas fa-user-circle"></i> **<?= htmlspecialchars($_SESSION['username']) ?>**
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="post-container">
        <h2 class="mb-4 text-center text-secondary"><i class="fas fa-edit"></i> แก้ไขกระทู้</h2>
        <hr>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger" role="alert"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            
            <div class="mb-4">
                <label for="title" class="form-label fw-bold">หัวข้อกระทู้ (Title)</label>
                <input type="text" class="form-control form-control-lg" id="title" name="title" required 
                       value="<?= $current_title ?>">
            </div>

            <div class="mb-4">
                <label for="content" class="form-label fw-bold">เนื้อหากระทู้</label>
                <textarea class="form-control" id="content" name="content" required><?= $current_content ?></textarea>
            </div>

            <div class="mb-4">
                <label for="tags" class="form-label fw-bold">แท็ก (Tags)</label>
                <input type="text" class="form-control" id="tags" name="tags" 
                       value="<?= $current_tags ?>">
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="topic_view.php?id=<?= $topic_id ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> ยกเลิก
                </a>
                <button type="submit" class="btn btn-post-submit">
                    <i class="fas fa-save"></i> บันทึกการแก้ไข
                </button>
            </div>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>