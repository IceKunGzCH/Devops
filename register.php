<?php
// ไฟล์นี้ควรถูกเปลี่ยนชื่อเป็น register.php และรันบน PHP Server

// ตรวจสอบและรวมไฟล์เชื่อมต่อฐานข้อมูล
// ไฟล์ connect_db.php ต้องมีตัวแปร $conn สำหรับการเชื่อมต่อ MySQLi
include 'connect_db.php'; 

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. รับข้อมูลจากฟอร์ม
    $username = $_POST["username"];
    $email = $_POST["email"];
    $dob = $_POST["dob"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // 2. ตรวจสอบรหัสผ่านตรงกัน (Server-side validation)
    if ($password !== $confirm_password) {
        $error = "❌ รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        // 3. ตรวจสอบความซ้ำซ้อนในตาราง User
        
        // ตรวจสอบ Username ซ้ำ
        $check_user = $conn->prepare("SELECT user_id FROM User WHERE username = ?");
        $check_user->bind_param("s", $username);
        $check_user->execute();
        $result_user = $check_user->get_result();

        // ตรวจสอบ Email ซ้ำ
        $check_email = $conn->prepare("SELECT user_id FROM User WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $result_email = $check_email->get_result();

        if ($result_user->num_rows > 0) {
            $error = "❌ Username นี้ถูกใช้แล้ว กรุณาเลือกชื่อใหม่";
        } elseif ($result_email->num_rows > 0) {
            $error = "❌ อีเมลนี้ถูกใช้แล้ว กรุณาใช้อีเมลอื่น";
        } else {
            // 4. Hashing รหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 5. บันทึกข้อมูลลงในตาราง User
            $stmt = $conn->prepare("INSERT INTO User (username, email, date_of_birth, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $dob, $hashed_password);
            
            if ($stmt->execute()) {
                $success = "✅ สมัครสมาชิกสำเร็จ! ไปที่ <a href='login.php'>หน้าเข้าสู่ระบบ</a>";
            } else {
                // ถ้าเกิดข้อผิดพลาดในการ INSERT
                $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียนสมาชิกใหม่ | Lantip จำลอง</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap');

        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f5f7fa; /* เปลี่ยนพื้นหลังหลักเป็นสีอ่อนเพื่อให้ Navbar เข้ากัน */
            min-height: 100vh;
            display: flex;
            flex-direction: column; /* จัดองค์ประกอบหลักให้เรียงลง */
        }
        
        /* Navbar Style */
        .navbar-custom {
            background-color: #ff6600; /* สีส้ม Pantip */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: 700;
            color: white !important;
            font-size: 1.5rem;
        }

        /* Content Area (Register Box) Style */
        .main-content {
            flex-grow: 1; /* ทำให้ส่วนนี้ขยายเต็มพื้นที่ที่เหลือ */
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #ff6600, #ff9900, #ffcc66); /* พื้นหลัง Gradient เฉพาะส่วน Content */
            position: relative;
            overflow: hidden; /* ป้องกัน Scrollbar จาก Animation */
        }
        
        /* --- โค้ด Dot Animation --- */
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
             z-index: 1;
        }
        
        @keyframes moveDots {
             from { background-position: 0 0; }
             to { background-position: 100% 100%; }
        }
        /* -------------------------- */
        
        .register-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 35px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            z-index: 2; /* ให้อยู่เหนือพื้นหลัง Animation */
        }
        .register-container h2 {
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
            transition: all 0.2s ease;
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
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 102, 0, 0.3);
        }
        .btn-custom:hover {
            background: linear-gradient(90deg, #ff6600, #e55c00);
            box-shadow: 0 6px 15px rgba(255, 102, 0, 0.4);
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
        .form-check-input:checked {
            background-color: #ff6600;
            border-color: #ff6600;
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
            <a href="login.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
            </a>
        </div>
    </div>
</nav>
<div class="main-content">
    <div class="register-container">
        <h2 class="mb-4">สมัครสมาชิก Lantip จำลอง</h2>
        
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

        <form id="registerForm" method="POST" action="">
            
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> ชื่อผู้ใช้
                </label>
                <input type="text" class="form-control" id="username" name="username" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i> อีเมล
                </label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="mb-3">
                <label for="dob" class="form-label">
                    <i class="fas fa-calendar-alt"></i> วันเดือนปีเกิด (DOB)
                </label>
                <input type="date" class="form-control" id="dob" name="dob" value="<?= isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : '' ?>">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> รหัสผ่าน
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">
                    <i class="fas fa-lock"></i> ยืนยันรหัสผ่าน
                </label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <small id="password-error" class="text-danger mt-2" style="display:none;">
                    <i class="fas fa-exclamation-circle"></i> รหัสผ่านไม่ตรงกัน โปรดตรวจสอบอีกครั้ง
                </small>
            </div>

            <div class="mb-3 form-check d-flex align-items-center justify-content-between">
                <div>
                    <input type="checkbox" class="form-check-input" id="showPassword" onclick="togglePasswordVisibility()">
                    <label class="form-check-label" for="showPassword">แสดงรหัสผ่าน</label>
                </div>
                <a href="#" class="link-text">ลืมรหัสผ่าน?</a>
            </div>


            <button type="submit" class="btn btn-custom w-100 mt-3">
                <i class="fas fa-user-plus"></i> ลงทะเบียน
            </button>

            <p class="text-center mt-4">
                มีบัญชีแล้ว? <a href="login.php" class="link-text">เข้าสู่ระบบที่นี่</a>
            </p>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript สำหรับตรวจสอบรหัสผ่านในฝั่ง Client
    document.getElementById('registerForm').onsubmit = function(event) {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const errorElement = document.getElementById('password-error');

        // 1. ล้างสถานะ Error เดิมออกก่อนเสมอ
        passwordInput.classList.remove('is-invalid');
        confirmPasswordInput.classList.remove('is-invalid');
        errorElement.style.display = 'none';

        if (password !== confirmPassword) {
            // 2. ถ้ารหัสผ่านไม่ตรงกัน
            errorElement.style.display = 'block'; // แสดงข้อความผิดพลาด
            passwordInput.classList.add('is-invalid'); // เพิ่มขอบแดงด้วย Bootstrap class
            confirmPasswordInput.classList.add('is-invalid');
            event.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งข้อมูล
            return false;
        } else {
            // 3. ถ้ารหัสผ่านตรงกัน (Client-side ผ่าน)
            return true; 
        }
    };

    // JavaScript สำหรับปุ่ม "แสดงรหัสผ่าน"
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const showPasswordCheckbox = document.getElementById('showPassword');

        if (showPasswordCheckbox.checked) {
            passwordInput.type = 'text';
            confirmPasswordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
            confirmPasswordInput.type = 'password';
        }
    }
</script>

</body>
</html>