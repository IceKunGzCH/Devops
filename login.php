<?php
// ไฟล์นี้ควรบันทึกเป็น login.php

// เริ่มต้น Session เพื่อเก็บบันทึกสถานะการเข้าสู่it[[]]
session_start();

// รวมไฟล์เชื่อมต่อฐานข้อมูล
include 'connect_db.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = $_POST["username_or_email"]; 
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM User WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            header("Location: index.php");
            exit();

        } else {
            $error = "❌ ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error = "❌ ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | Lantip จำลอง</title>
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
        
        /* Navbar Style */
        .navbar-custom {
            background-color: #ff6600; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.5rem;
        }

        /* Content Area (Login Box) Style */
        .main-content {
            flex-grow: 1; 
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #ff6600, #ff9900, #ffcc66); 
            position: relative;
            overflow: hidden; /* ป้องกัน Scrollbar จาก Animation */
        }
        
        /* --- โค้ด Dot Animation ที่ถูกเพิ่มกลับมา --- */
        .main-content::before { 
             content: '';
             position: absolute;
             top: 0;
             left: 0;
             width: 100%;
             height: 100%;
             background-image: radial-gradient(#ffffff33 1px, transparent 1px);
             background-size: 20px 20px;
             animation: moveDots 60s linear infinite; /* เรียกใช้ Animation */
        }
        
        @keyframes moveDots {
             from { background-position: 0 0; }
             to { background-position: 100% 100%; }
        }
        /* ------------------------------------------- */
        
        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 35px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            z-index: 2;
        }
        .login-container h2 {
            text-align: center;
            color: #ff6600;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }
        .form-control:focus {
            border-color: #ff9900;
            box-shadow: 0 0 0 0.25rem rgba(255, 153, 0, 0.25);
        }
        .btn-custom {
            background: linear-gradient(90deg, #ff8000, #ff6600);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 102, 0, 0.3);
        }
        .btn-custom:hover {
            background: linear-gradient(90deg, #ff6600, #e55c00);
            transform: translateY(-2px);
        }
        .link-text {
            color: #ff6600;
            font-weight: 500;
            text-decoration: none;
        }
        .link-text:hover {
            color: #e55c00;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-comments"></i> Lantip จำลอง
        </a>
        <div class="d-flex">
            <a href="register.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-user-plus"></i> สมัครสมาชิก
            </a>
        </div>
    </div>
</nav>
<div class="main-content">
    <div class="login-container">
        <h2 class="mb-4">เข้าสู่ระบบ</h2>
        
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            
            <div class="mb-3">
                <label for="username_or_email" class="form-label">
                    <i class="fas fa-user-circle"></i> ชื่อผู้ใช้ หรือ อีเมล
                </label>
                <input type="text" class="form-control" id="username_or_email" name="username_or_email" required 
                       value="<?= isset($_POST['username_or_email']) ? htmlspecialchars($_POST['username_or_email']) : '' ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-key"></i> รหัสผ่าน
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3 d-flex justify-content-between">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">จำฉันไว้</label>
                </div>
                <a href="#" class="link-text">ลืมรหัสผ่าน?</a>
            </div>


            <button type="submit" class="btn btn-custom w-100 mt-3">
                <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
            </button>

            <p class="text-center mt-4">
                ยังไม่มีบัญชี? <a href="register.php" class="link-text">สมัครสมาชิกที่นี่</a>
            </p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>