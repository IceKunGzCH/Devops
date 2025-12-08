<?php
// contact.php — หน้าติดต่อเรา
session_start();

$is_logged_in = isset($_SESSION['user_id']);
$username = $is_logged_in ? htmlspecialchars($_SESSION['username']) : 'ผู้เยี่ยมชม';
?>
<!DOCTYPE html>
<html lang="th">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>ติดต่อเรา | Lantip จำลอง</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

	<!-- Leaflet CSS -->
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">

	<style>
		@import url('https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap');

		body {
			font-family: 'Kanit', sans-serif;
			background-color: #f5f7fa;
		}
		.navbar-custom {
			background-color: #ff6600;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}
		.navbar-brand {
			font-weight: 700;
			color: white !important;
		}
		.btn-post {
			background-color: #009933;
			color: #fff;
			border-radius: 20px;
			padding: 8px 18px;
		}
		.btn-post:hover {
			background-color: #007a29;
			color: #fff;
		}
		.welcome-text {
			color: white;
			margin-right: 15px;
			font-weight: 400;
		}
		.card-map {
			box-shadow: 0 4px 10px rgba(0,0,0,0.08);
			border-radius: 10px;
		}
		.text-orange {
			color: #ff6600 !important;
		}
		#mapid {
			height: 400px;
			border-radius: 8px;
			margin-top: 10px;
		}
	</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
	<div class="container">
		<a class="navbar-brand" href="index.php">
			<i class="fas fa-comments"></i> Lantip จำลอง
		</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav me-auto">
				<li class="nav-item"><a class="nav-link text-white" href="index.php">ห้องรวม</a></li>
				<li class="nav-item"><a class="nav-link text-white" href="#">แนะนำ</a></li>
				<li class="nav-item"><a class="nav-link text-white" href="#">กระทู้เด่น</a></li>
				<li class="nav-item"><a class="nav-link text-white active" href="contact.php">ติดต่อเรา</a></li>
			</ul>

			<div class="d-flex align-items-center">
				<?php if ($is_logged_in): ?>
					<span class="welcome-text">สวัสดี, <?= $username ?></span>
					<a href="post.php" class="btn btn-post me-3"><i class="fas fa-plus-circle"></i> ตั้งกระทู้</a>
					<a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
				<?php else: ?>
					<a href="login.php" class="btn btn-outline-light me-2">เข้าสู่ระบบ</a>
					<a href="register.php" class="btn btn-light">สมัครสมาชิก</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</nav>
<!-- End Navbar -->

<div class="container my-5">
	<h1 class="text-center text-orange mb-4"><i class="fas fa-headset"></i> ติดต่อเรา</h1>

	<div class="row g-4">

		<!-- ข้อมูลติดต่อ -->
		<div class="col-lg-5">
			<div class="card p-4 card-map h-100">
				<h5 class="text-orange"><i class="fas fa-info-circle"></i> ข้อมูลการติดต่อ</h5>
				<hr>

				<p class="mb-1"><i class="fas fa-map-marker-alt text-danger"></i> <b>ที่ตั้งสำนักงาน</b></p>
				<p class="ms-3">วิทยาลัยเทคโนโลยีพายัพและบริหารธุรกิจ</p>
				<p class="ms-3">262 อำเภอสันทราย เชียงใหม่ 50210</p>

				<p class="mb-1 mt-3"><i class="fas fa-phone text-success"></i> <b>โทรศัพท์</b></p>
				<p class="ms-3">053845010</p>

				<p class="mb-1 mt-3"><i class="fas fa-envelope text-info"></i> <b>อีเมล</b></p>
				<p class="ms-3">info@lantip-example.com</p>

				<hr>
				<h5 class="text-orange"><i class="fas fa-clock"></i> เวลาทำการ</h5>
				<p class="ms-3">จันทร์ - ศุกร์: 8:30 - 17:30 น.</p>
				<p class="ms-3">เสาร์ - อาทิตย์: ปิดทำการ</p>
			</div>
		</div>

		<!-- ฟอร์มติดต่อ -->
		<div class="col-lg-7">
			<div class="card p-4 card-map">
				<h5 class="text-orange"><i class="fas fa-paper-plane"></i> ส่งข้อความถึงเรา</h5>

				<form class="mt-3">
					<div class="mb-3">
						<label class="form-label">ชื่อของคุณ</label>
						<input type="text" class="form-control" required placeholder="เช่น สมชาย ใจดี">
					</div>
					<div class="mb-3">
						<label class="form-label">อีเมลติดต่อกลับ</label>
						<input type="email" class="form-control" required placeholder="example@email.com">
					</div>
					<div class="mb-3">
						<label class="form-label">หัวข้อ</label>
						<input type="text" class="form-control" required placeholder="สอบถามเรื่อง...">
					</div>
					<div class="mb-3">
						<label class="form-label">ข้อความ</label>
						<textarea class="form-control" rows="4" required placeholder="รายละเอียดที่ต้องการติดต่อ..."></textarea>
					</div>

					<button type="submit" class="btn w-100" style="background:#ff6600;color:#fff;">
						<i class="fas fa-paper-plane"></i> ส่งข้อความ
					</button>

					<div class="alert alert-warning mt-3">
						<b>หมายเหตุ:</b> ฟอร์มนี้เป็นตัวอย่าง ระบบยังไม่ได้ส่งข้อมูลจริง
					</div>
				</form>
			</div>
		</div>

	</div>

	<!-- แผนที่ -->
	<div class="card p-4 mt-5 card-map">
		<h5 class="text-center text-orange"><i class="fas fa-map"></i> แผนที่ตั้ง</h5>
		<div id="mapid"></div>
	</div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// พิกัดใหม่ที่คุณให้มา
var lat = 18.8516335428749;
var lng = 99.01035887769721;

// สร้างแผนที่ + โฟกัสให้หมุดอยู่กลาง
var map = L.map('mapid').setView([lat, lng], 17);

// โหลดแผนที่จาก OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// วางหมุดตรงกลาง
var marker = L.marker([lat, lng]).addTo(map);

// ใส่ popup ชื่อสถานที่
marker.bindPopup("วิทยาลัยเทคโนโลยีพายัพและบริหารธุรกิจ").openPopup();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
