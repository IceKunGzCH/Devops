<?php
session_start();
include 'connect_db.php';

$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : 'ผู้เยี่ยมชม';

// รับแท็กจาก URL
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

if ($tag === '') {
    die("ไม่พบแท็กที่ต้องการค้นหา");
}

// Query หากระทู้ที่มีแท็กนี้
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
WHERE FIND_IN_SET(?, t.tags)
GROUP BY 
    t.topic_id, t.title, t.content, t.tags, 
    t.created_at, t.views, t.image_url, u.username
ORDER BY t.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tag);
$stmt->execute();
$result = $stmt->get_result();

$topics = [];
while ($row = $result->fetch_assoc()) {
    $topics[] = $row;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ผลการค้นหาแท็ก #<?= htmlspecialchars($tag) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-4">

    <h3 class="mb-4">
        <span class="text-primary">#<?= htmlspecialchars($tag) ?></span>  
        - ผลลัพธ์ <?= count($topics) ?> รายการ
    </h3>

    <?php if (empty($topics)): ?>
        <div class="alert alert-warning">ไม่พบโพสต์ที่มีแท็กนี้</div>
    <?php endif; ?>

    <?php foreach ($topics as $topic): ?>

        <?php
            $title = htmlspecialchars($topic['title']);
            $clean = strip_tags($topic['content']);
            $summary = mb_strlen($clean) > 180 ? mb_substr($clean, 0, 180) . "..." : $clean;
            $created_at_format = date('d/m/Y H:i', strtotime($topic['created_at']));
            $topic_link = "topic_view.php?id=" . $topic['topic_id'];
            $image_url = !empty($topic['image_url']) 
                         ? 'uploads/' . htmlspecialchars($topic['image_url']) 
                         : "https://placehold.co/100x80";
        ?>

        <div class="card mb-3 p-3">
            <div class="row g-2">

                <div class="col-md-3">
                    <a href="<?= $topic_link ?>">
                        <img src="<?= $image_url ?>" class="img-fluid rounded" />
                    </a>
                </div>

                <div class="col-md-9">
                    <a href="<?= $topic_link ?>" class="h5 text-dark"><?= $title ?></a>
                    <p class="text-muted small"><?= $summary ?></p>

                    <div class="small text-muted">
                        โดย <?= htmlspecialchars($topic['username']) ?>
                        | <i class="fas fa-clock"></i> <?= $created_at_format ?>
                        | <i class="fas fa-eye"></i> <?= $topic['views'] ?> ครั้ง
                        | <i class="fas fa-comment"></i> <?= $topic['comment_count'] ?> ความคิดเห็น
                    </div>

                    <!-- Show tags clickable -->
                    <p class="mt-1">
                        <?php
                        $tags = explode(',', $topic['tags']);
                        foreach ($tags as $t) {
                            $t = trim($t);
                            echo "<a href='search.php?tag=" . urlencode($t) . "' class='badge bg-secondary me-1'>#$t</a>";
                        }
                        ?>
                    </p>

                </div>
            </div>
        </div>

    <?php endforeach; ?>

</div>

</body>
</html>
