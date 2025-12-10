<?php
// ไฟล์นี้ควรบันทึกเป็น topic_view.php

session_start();
include 'connect_db.php'; 

// 1. รับ ID กระทู้จาก URL (Query String)
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($topic_id == 0) {
    die("❌ ไม่พบหมายเลขกระทู้");
}

// 2. ดึงข้อมูลกระทู้และผู้เขียน
$stmt = $conn->prepare("
    SELECT 
        t.topic_id, /* เพิ่ม topic_id สำหรับใช้ใน logic แนะนำ */
        t.title, 
        t.content, 
        t.tags, 
        t.created_at, 
        t.views,
        t.user_id, 
        t.image_url, 
        u.username
    FROM Topic t
    JOIN User u ON t.user_id = u.user_id
    WHERE t.topic_id = ?
");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("❌ ไม่พบกระทู้ที่คุณต้องการ");
}

$topic = $result->fetch_assoc();
$stmt->close();

$topic_user_id = $topic['user_id']; 

// 3. (Optional) อัพเดทจำนวน Views
// ต้องเชื่อมต่อ DB ใหม่ถ้ามีการปิดไปแล้ว แต่ในโค้ดนี้ยังไม่ได้ปิด conn จึงใช้ได้เลย
$conn->query("UPDATE Topic SET views = views + 1 WHERE topic_id = $topic_id");

// กำหนดตัวแปรสำหรับแสดงผล
$title = htmlspecialchars($topic['title']);
$content = nl2br(htmlspecialchars($topic['content']));
$image_url = htmlspecialchars($topic['image_url']); 
$username = htmlspecialchars($topic['username']);
$created_at = date('d/m/Y H:i:s', strtotime($topic['created_at']));
$tags = htmlspecialchars($topic['tags']);
$views = number_format($topic['views'] + 1);

// ตรวจสอบสิทธิ์การแก้ไข/ลบ
$can_edit = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $topic_user_id;


// =================================================================
// 5. [ส่วนใหม่] Logic การแนะนำ Content-Based (กระทู้ที่เกี่ยวข้อง)
// =================================================================
$related_topics = [];
// ตรวจสอบว่ากระทู้หลักมี Tags หรือไม่
if (!empty($topic['tags'])) {
    
    // 5.1 เตรียม Tags สำหรับ Query
    $current_tags_array = array_filter(array_map('trim', explode(',', $topic['tags'])));
    
    $tag_conditions = [];
    foreach ($current_tags_array as $tag) {
        // ใช้ LIKE เพื่อค้นหา Tags ในฐานข้อมูลอย่างปลอดภัย
        $escaped_tag = $conn->real_escape_string($tag);
        // เงื่อนไข: t.tags มี tag นี้อยู่ข้างใน
        $tag_conditions[] = "t.tags LIKE '%" . $escaped_tag . "%'";
    }
    
    $where_clause = implode(' OR ', $tag_conditions);

    // 5.2 Query เพื่อค้นหากระทู้ที่เกี่ยวข้อง
    $sql_related = "
    SELECT 
        t.topic_id,
        t.title,
        t.views
    FROM Topic t
    WHERE 
        t.topic_id != {$topic_id}   -- ไม่รวมกระทู้ปัจจุบัน
        AND ({$where_clause})       -- ต้องมี Tags ร่วมกันอย่างน้อย 1 Tag
    ORDER BY 
        t.views DESC,               -- เรียงตาม Views
        t.created_at DESC
    LIMIT 5;
    ";
    
    $result_related = $conn->query($sql_related);

    if ($result_related && $result_related->num_rows > 0) {
        while ($row = $result_related->fetch_assoc()) {
            $related_topics[] = $row;
        }
    }
}
// =================================================================

// 6. ดึงความคิดเห็นทั้งหมดสำหรับกระทู้นี้
$comment_stmt = $conn->prepare("
    SELECT 
        c.content, 
        c.created_at, 
        u.username
    FROM Comment c
    JOIN User u ON c.user_id = u.user_id
    WHERE c.topic_id = ?
    ORDER BY c.created_at ASC
");
$comment_stmt->bind_param("i", $topic_id);
$comment_stmt->execute();
$comments_result = $comment_stmt->get_result();
$comment_count = $comments_result->num_rows; 
$comment_stmt->close();

// ปิดการเชื่อมต่อ DB เมื่อจบงานทั้งหมด
$conn->close(); 

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Lantip จำลอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');
        body { font-family: 'Kanit', sans-serif; background-color: #f5f7fa; }
        .navbar-custom { background-color: #ff6600; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .navbar-brand { font-weight: 700; color: white !important; font-size: 1.5rem; }
        .topic-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
            margin-bottom: 40px;
        }
        .topic-title {
            color: #ff6600;
            font-weight: 600;
            border-left: 5px solid #ff6600;
            padding-left: 15px;
        }
        .topic-meta {
            font-size: 0.9em;
            color: #777;
            border-bottom: 1px dashed #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .topic-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }
        .tag-badge {
            display: inline-block;
            background-color: #e0f2f1;
            color: #009688;
            padding: 5px 10px;
            border-radius: 5px;
            margin-right: 5px;
            font-size: 0.9em;
        }
        /* CSS สำหรับรูปภาพประกอบ */
        .topic-image img {
            max-height: 400px; /* จำกัดความสูง */
            width: auto;      /* ปรับความกว้างอัตโนมัติ */
            object-fit: contain;
            border: 1px solid #ddd;
        }
        .related-section {
            background-color: #f0f8ff; /* สีฟ้าอ่อน */
            border: 1px solid #d0e8ff;
            border-radius: 8px;
        }
        .related-section li {
            padding-top: 5px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-comments"></i> Lantip จำลอง
        </a>
        <?php if (isset($_SESSION['username'])): ?>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">
                <i class="fas fa-user-circle"></i> <span class="fw-bold"><?= htmlspecialchars($_SESSION['username']) ?></span>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
            </a>
        </div>
        <?php else: ?>
        <a href="login.php" class="btn btn-light btn-sm">เข้าสู่ระบบ</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="topic-container">
                <h1 class="topic-title mb-4"><?= $title ?></h1>

                <div class="topic-meta d-flex justify-content-between">
                    <div>
                        โพสต์โดย: <span class="fw-bold"><?= $username ?></span> | 
                        เมื่อ: <?= $created_at ?> | 
                        เข้าชม: <?= $views ?> ครั้ง
                    </div>
                    
                    <?php if (isset($can_edit) && $can_edit): ?>
                        <div class="topic-actions">
                            <a href="topic_edit.php?id=<?= $topic_id ?>" class="btn btn-sm btn-outline-primary me-2">
                                <i class="fas fa-edit"></i> แก้ไข
                            </a>
                            <form method="POST" action="topic_edit.php?id=<?= $topic_id ?>" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบกระทู้นี้? การกระทำนี้ไม่สามารถยกเลิกได้');">
                                    <i class="fas fa-trash-alt"></i> ลบ
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($image_url)): ?>
                    // โค้ดที่ถูกแก้ไข (ทำให้เป็นลิงก์)
                    <div class="mb-4">
                        <label class="fw-bold"><i class="fas fa-tags"></i> แท็ก:</label>
                    <?php 
                        $tag_list = explode(',', $tags);
                        foreach ($tag_list as $tag_item) {
                            $tag_item = trim($tag_item);
                            if (!empty($tag_item)) {
                            // *** เปลี่ยน <span> เป็น <a> และกำหนด href ไปที่ index.php?tag=... ***
                            echo '<a href="index.php?tag=' . urlencode($tag_item) . '" class="tag-badge text-decoration-none">' . htmlspecialchars($tag_item) . '</a>';
            }
        }
    ?>
</div>
                <?php endif; ?>

                <div class="topic-content mb-5">
                    <?= $content ?>
                </div>

                <div class="mb-4">
                    <label class="fw-bold"><i class="fas fa-tags"></i> แท็ก:</label>
                    <?php 
                        $tag_list = explode(',', $tags);
                        foreach ($tag_list as $tag_item) {
                            $tag_item = trim($tag_item);
                            if (!empty($tag_item)) {
                                echo '<a href="index.php?tag=' . urlencode($tag_item) . '" class="tag-badge text-decoration-none">' . htmlspecialchars($tag_item) . '</a>';
                            }
                        }
                    ?>
                </div>
                
                <?php if (!empty($related_topics)): ?>
                    <div class="related-section mt-5 pt-3 p-3">
                        <h5 class="text-primary mb-3"><i class="fas fa-link"></i> กระทู้ที่เกี่ยวข้อง</h5>
                        <ul class="list-unstyled">
                            <?php foreach ($related_topics as $rt): ?>
                                <li class="mb-2 pb-2">
                                    <a href="topic_view.php?id=<?= $rt['topic_id'] ?>" class="link-dark fw-bold text-decoration-none">
                                        <?= htmlspecialchars($rt['title']) ?>
                                    </a>
                                    <span class="text-muted small d-block">👁️ เข้าชม <?= number_format($rt['views']) ?> ครั้ง</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="mt-5 pt-3 border-top" id="comments">
                    <h4><i class="fas fa-reply"></i> ความคิดเห็น (<?= number_format($comment_count) ?>)</h4>
                    
                    <?php if ($comment_count > 0): ?>
                        <?php while ($comment = $comments_result->fetch_assoc()): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <p class="card-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                </div>
                                <div class="card-footer text-muted d-flex justify-content-between">
                                    <small>โดย: <span class="fw-bold"><?= htmlspecialchars($comment['username']) ?></span></small>
                                    <small>เมื่อ: <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">ยังไม่มีความคิดเห็น</p>
                    <?php endif; ?>
                </div>

                <div class="comment-form-section mt-5 pt-3 border-top">
                    <h4><i class="fas fa-comment-dots"></i> แสดงความคิดเห็น</h4>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="post_comment.php" method="POST">
                            <input type="hidden" name="topic_id" value="<?= $topic_id ?>">
                            <div class="mb-3">
                                <textarea class="form-control" name="content" rows="4" placeholder="พิมพ์ความคิดเห็นของคุณที่นี่..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> โพสต์ความคิดเห็น
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-lock"></i> กรุณา <a href="login.php">เข้าสู่ระบบ</a> เพื่อแสดงความคิดเห็น
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>