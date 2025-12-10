<?php
session_start();

// -----------------------------
// 1) เชื่อมต่อฐานข้อมูล
// -----------------------------
// *ต้องแน่ใจว่าไฟล์ connect_db.php ใช้งานได้
include 'connect_db.php'; 

$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : 'ผู้เยี่ยมชม';

// -----------------------------------------------------------------
// 2) Query กระทู้แนะนำ (ต้องมี is_featured = 1 ในตาราง Topic)
// -----------------------------------------------------------------
$topics = [];
$sql = "
SELECT 
    t.topic_id,
    t.title,
    t.content,
    t.tags,
    t.created_at,
    t.views,
    t.image_url,
    u.username,
    COUNT(c.comment_id) AS comment_count
FROM Topic t
JOIN User u ON t.user_id = u.user_id
LEFT JOIN Comment c ON t.topic_id = c.topic_id
WHERE 
    t.is_featured = 1       -- *** เงื่อนไขสำคัญ: ต้องถูกตั้งค่าเป็น Featured ***
GROUP BY 
    t.topic_id, t.title, t.content, t.tags, 
    t.created_at, t.views, t.image_url, u.username
ORDER BY 
    t.created_at DESC       -- เรียงตามเวลาที่สร้าง (ให้กระทู้ใหม่ของ Featured ขึ้นก่อน)
LIMIT 20
";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
}
$conn->close();

// *******************************************************************
// ! คำเตือน: ก่อนใช้โค้ดนี้ อย่าลืมรันคำสั่ง SQL นี้ในฐานข้อมูลของคุณ
// ALTER TABLE Topic ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0;
// *******************************************************************

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กระทู้แนะนำ | Lantip จำลอง</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* CSS Style */
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        body { font-family: 'Kanit', sans-serif; background-color: #f5f7fa; }
        .navbar-custom { background-color: #ff6600; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .navbar-brand { font-weight: 700; color: white !important; font-size: 1.5rem; }
        .btn-post { background-color: #009933; color: white; border-radius: 20px; padding: 8px 18px; font-weight: 500; }
        .btn-post:hover { background-color: #00802b; }
        .topic-card { 
            border-radius: 10px; margin-bottom: 20px; padding: 15px; background-color: #fff; 
            border: 1px solid #eee; display: flex; align-items: flex-start; gap: 15px; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); transition: 0.2s;
        }
        .topic-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); }
        .topic-thumbnail { 
            width: 100px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; 
        }
        .tag-badge { 
            background-color: #f0f0f0; color: #888; font-size: 0.75rem; padding: 4px 8px; 
            border-radius: 5px; margin-right: 5px; display: inline-block; 
        }
        .sidebar { 
            background-color: #fff; border-radius: 10px; padding: 20px; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); 
        }
        a.topic-title { font-weight: 600; font-size: 1.15rem; }
        a.topic-title:hover { color: #ff6600 !important; }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-comments"></i> Lantip จำลอง
        </a>

        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link text-white" href="index.php">ห้องรวม</a></li>
                <li class="nav-item"><a class="nav-link text-white active" href="featured.php">แนะนำ</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="notable.php">กระทู้เด่น</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="contact.php">ติดต่อเรา</a></li>
            </ul>

            <div class="d-flex align-items-center">
                <?php if ($is_logged_in): ?>
                    <span class="text-white me-3">สวัสดี, <strong><?= $username ?></strong></span>
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
            <h4 class="mb-4 text-secondary"><i class="fas fa-magic"></i> กระทู้แนะนำ</h4> 
            
            <?php if (!empty($topics)): ?>
                <?php foreach ($topics as $topic): ?>

                    <?php
                        $title = htmlspecialchars($topic['title']);
                        $username_post = htmlspecialchars($topic['username']);
                        $created_at_format = date('d/m/Y H:i', strtotime($topic['created_at']));
                        $topic_link = "topic_view.php?id=" . $topic['topic_id'];

                        // สรุปเนื้อหา
                        $clean = strip_tags($topic['content']);
                        $summary = mb_strlen($clean, 'UTF-8') > 150 
                                       ? mb_substr($clean, 0, 150) . "..."
                                       : $clean;

                        // รูปภาพ (แก้ให้โหลดจาก uploads/)
                        $image_url = !empty($topic['image_url']) 
                                           ? 'uploads/' . htmlspecialchars($topic['image_url'])
                                           : "https://placehold.co/100x80/cccccc/333333?text=No+Image";
                    ?>

                    <div class="topic-card">
                        
                        <a href="<?= $topic_link ?>">
                            <img src="<?= $image_url ?>" 
                                 class="topic-thumbnail"
                                 loading="lazy"
                                 onerror="this.src='https://placehold.co/100x80/cccccc/333333?text=No+Image';">
                        </a>

                        <div>
                            <p class="mb-1">
                                <?php
                                    $tags = array_filter(array_map('trim', explode(',', $topic['tags'])));
                                    foreach ($tags as $tag) {
                                        echo "<span class='tag-badge'>#$tag</span>";
                                    }
                                ?>
                            </p>

                            <a href="<?= $topic_link ?>" class="topic-title text-dark text-decoration-none">
                                <?= $title ?>
                            </a>

                            <p class="mt-2 text-muted small"><?= $summary ?></p>

                            <small class="text-muted">
                                <i class="fas fa-clock"></i> <?= $created_at_format ?>  
                                โดย <strong><?= $username_post ?></strong> |
                                <i class="fas fa-eye"></i> <?= number_format($topic['views']) ?> ครั้ง |
                                <i class="fas fa-comment"></i> <?= number_format($topic['comment_count']) ?> ความคิดเห็น
                            </small>
                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> ยังไม่มีกระทู้แนะนำในขณะนี้
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">

            <div class="sidebar mb-4">
            <h5 class="text-primary mb-3"><i class="fas fa-fire"></i> แท็กยอดนิยมวันนี้</h5>

            <?php if (empty($tag_count)): ?>
                <p class="text-muted small">ยังไม่มีการใช้แท็กในวันนี้</p>

            <?php else: ?>
                <ul class="list-unstyled">
                    <?php 
                    $i = 0;
                    foreach ($tag_count as $tag => $count): 
                        if ($i++ >= 10) break; // จำกัด 10 อันดับ
                    ?>
                    <li>
                        <a href="search.php?tag=<?= urlencode($tag) ?>" class="link-secondary">
                            #<?= htmlspecialchars($tag) ?> (<?= $count ?>)
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

            <div class="sidebar">
                <h5 class="text-success mb-3"><i class="fas fa-users"></i> แนะนำสมาชิก</h5>

                <?php if (!$is_logged_in): ?>
                    <p class="text-muted small">โปรดล็อกอินเพื่อดูห้องแนะนำเฉพาะคุณ</p>
                    <a href="login.php" class="btn btn-outline-primary w-100">เข้าสู่ระบบ / สมัครสมาชิก</a>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <li><a href="#" class="link-secondary">@DoctorPants</a></li>
                        <li><a href="#" class="link-secondary">@TravelerJane</a></li>
                    </ul>
                <?php endif; ?>

            </div>

        </div>

    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>