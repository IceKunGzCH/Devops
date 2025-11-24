<?php
// ไฟล์นี้ควรบันทึกเป็น index.php
session_start();

// *** 1. เพิ่มไฟล์เชื่อมต่อฐานข้อมูล ***
include 'connect_db.php'; 

$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : 'ผู้เยี่ยมชม';

// *** 2. โค้ดดึงกระทู้ล่าสุด ***
$topics = [];
$sql = "SELECT 
            t.topic_id, 
            t.title, 
            t.content, 
            t.tags, 
            t.created_at, 
            t.views,
            u.username,
        -- นับจำนวนความคิดเห็นจากตาราง Comment
        COUNT(c.comment_id) AS comment_count 
        FROM Topic t
        JOIN User u ON t.user_id = u.user_id
    -- ใช้ LEFT JOIN เพื่อให้กระทู้ที่ไม่มี Comment ก็ยังแสดงอยู่
        LEFT JOIN Comment c ON t.topic_id = c.topic_id
        GROUP BY t.topic_id
        ORDER BY t.created_at DESC
        LIMIT 15";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
}
// ปิดการเชื่อมต่อ (เป็นทางเลือกที่ดีเมื่อเสร็จสิ้นการทำงานกับ DB)
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรก | Lantip จำลอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f7fa; /* สีพื้นหลังอ่อน */
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
        .btn-post {
            background-color: #009933; /* สีเขียวสำหรับปุ่มตั้งกระทู้ */
            color: white;
            border-radius: 20px;
            padding: 8px 18px;
            font-weight: 500;
        }
        .btn-post:hover {
            background-color: #00802b;
            color: white;
        }
        .welcome-text {
            color: white;
            margin-right: 15px;
            font-weight: 400;
        }
        .topic-card {
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            border: 1px solid #eee;
        }
        .topic-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .topic-title {
            color: #333;
            font-weight: 600;
            font-size: 1.15rem;
        }
        .tag-badge {
            background-color: #f0f0f0;
            color: #888;
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 5px;
            margin-right: 5px;
        }
        .sidebar {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-comments"></i> Lantip จำลอง
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">ห้องรวม</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">แนะนำ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">กระทู้เด่น</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                
                <?php if ($is_logged_in): ?>
                    <span class="welcome-text">
                        สวัสดี, **<?= $username ?>**
                    </span>
                    <a href="post.php" class="btn btn-post me-3">
                        <i class="fas fa-plus-circle"></i> ตั้งกระทู้
                    </a>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light me-2">เข้าสู่ระบบ</a>
                    <a href="register.php" class="btn btn-light">สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        
       <div class="col-lg-8">
    <h4 class="mb-4 text-secondary"><i class="fas fa-list-alt"></i> กระทู้ล่าสุด</h4>
    
    <?php if (!empty($topics)): ?>
        
        <?php foreach ($topics as $topic): 
            // เตรียมข้อมูล
            $title = htmlspecialchars($topic['title']);
            $username_post = htmlspecialchars($topic['username']);
            $created_at_format = date('d/m/Y H:i', strtotime($topic['created_at'])); 
            $topic_link = "topic_view.php?id=" . $topic['topic_id']; 
            // ตัดเนื้อหาย่อ 150 ตัวอักษร
            $summary = mb_substr(strip_tags($topic['content']), 0, 150, 'UTF-8') . '...'; 
            // ดึงค่าจำนวนความคิดเห็นจริงที่นับมาได้จาก SQL
            $replies = $topic['comment_count']; 
            ?>
        
        <div class="card p-3 topic-card">
            <p class="mb-1">
                <?php
                    $tags = explode(',', htmlspecialchars($topic['tags']));
                    foreach ($tags as $tag) {
                        $tag = trim($tag);
                        if (!empty($tag)) {
                            // ใช้ link ไปหาหน้า tag search ในอนาคต
                            echo '<span class="tag-badge">#' . $tag . '</span>'; 
                        }
                    }
                ?>
            </p>
            <a href="<?= $topic_link ?>" class="topic-title link-text text-dark text-decoration-none">
                <?= $title ?>
            </a>
            <p class="mt-2 text-muted small">
                <?= $summary ?>
            </p>
            <small class="text-muted mt-2">
                <i class="fas fa-clock"></i> <?= $created_at_format ?> โดย **<?= $username_post ?>** | 
                <i class="fas fa-eye"></i> <?= number_format($topic['views']) ?> ครั้ง |
                <i class="fas fa-comment"></i> <?= number_format($replies) ?> ความคิดเห็น 
            </small>
        </div>

        <?php endforeach; ?>
    
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            <i class="fas fa-info-circle"></i> ยังไม่มีกระทู้ในระบบเลย ลอง <a href="post.php" class="alert-link">ตั้งกระทู้ใหม่</a> สิ!
        </div>
    <?php endif; ?>
    </div>

        <div class="col-lg-4">
            <div class="sidebar mb-4">
                <h5 class="text-primary mb-3"><i class="fas fa-fire"></i> แท็กยอดนิยมวันนี้</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="link-secondary">#เที่ยวทะเล (25K)</a></li>
                    <li><a href="#" class="link-secondary">#มังงะน่าอ่าน (18K)</a></li>
                    <li><a href="#" class="link-secondary">#หุ้นกู้ (15K)</a></li>
                    <li><a href="#" class="link-secondary">#ห้องสินธร (12K)</a></li>
                </ul>
            </div>
            
            <div class="sidebar">
                <h5 class="text-success mb-3"><i class="fas fa-users"></i> แนะนำสมาชิก</h5>
                <p class="text-muted small">โปรดล็อกอินเพื่อดูเพื่อนและห้องแนะนำเฉพาะคุณ</p>
                <?php if (!$is_logged_in): ?>
                    <a href="login.php" class="btn btn-outline-primary w-100">เข้าสู่ระบบ / สมัครสมาชิก</a>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <li><a href="#" class="link-secondary">@DoctorPants</a></li>
                        <li><a href="#" class="link-secondary">@TravelGuru</a></li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>