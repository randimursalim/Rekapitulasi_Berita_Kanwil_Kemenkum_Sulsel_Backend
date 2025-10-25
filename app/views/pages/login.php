<!DOCTYPE html>
<html lang="en">
<head>
	<title>Login - KEMENKUM SULSEL</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="/rekap-konten/public/Images/LOGO KEMENKUM.jpeg"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/rekap-konten/public/vendor/bootstrap/css/bootstrap.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/rekap-konten/public/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/rekap-konten/public/vendor/animate/animate.css">
<!--===============================================================================================-->	
	<link rel="stylesheet" type="text/css" href="/rekap-konten/public/vendor/css-hamburgers/hamburgers.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/rekap-konten/public/vendor/select2/select2.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="/rekap-konten/public/css/util.css">
	<link rel="stylesheet" type="text/css" href="/rekap-konten/public/css/main.css">
<!--===============================================================================================-->
</head>
<body>
	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="/rekap-konten/public/Images/LOGO KEMENKUM.jpeg" alt="LOGO KEMENKUM SULSEL">
				</div>

				<form class="login100-form validate-form" method="POST" action="index.php?page=proses-login">
					<span class="login100-form-title">
						Selamat Datang!
					</span>

					<?php if (isset($error)): ?>
						<div class="alert alert-danger" style="margin-bottom: 20px; padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;">
							<?= htmlspecialchars($error) ?>
						</div>
					<?php endif; ?>

					<?php if (isset($_GET['timeout']) && $_GET['timeout'] == '1'): ?>
						<div class="alert alert-warning" style="margin-bottom: 20px; padding: 10px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; border-radius: 4px;">
							<i class="fa fa-clock-o"></i> Sesi Anda telah berakhir karena tidak ada aktivitas selama 15 menit. Silakan login kembali.
						</div>
					<?php endif; ?>

					<div class="wrap-input100 validate-input" data-validate = "Username is required">
						<input class="input100" type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-user" aria-hidden="true"></i>
						</span>
					</div>

					<div class="wrap-input100 validate-input" data-validate = "Password is required">
						<input class="input100" type="password" name="password" id="password" placeholder="Password" required>
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</span>
						<span class="password-toggle-login" onclick="togglePasswordLogin()">
							<i class="fa fa-eye" id="password-eye-login"></i>
						</span>
					</div>
					
					<div class="container-login100-form-btn">
						<button class="login100-form-btn" type="submit">
							Login
						</button>
					</div>

					<div class="text-center p-t-20">
						<a href="landing.php" class="btn-back-to-landing">
							<i class="fa fa-home"></i> Kembali ke Beranda
						</a>
					</div>

					<div class="text-center p-t-12">
						<span class="txt1">
							<!-- Forgot -->
						</span>
						<a class="txt2" href="#">
							<!-- Username / Password? -->
						</a>
					</div>

					<div class="text-center p-t-136">
						<a class="txt2" href="#">
							<!-- Create your Account -->
							<!-- <i class="fa fa-long-arrow-right m-l-5" aria-hidden="true"></i> -->
							 <br>
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	

	
<!--===============================================================================================-->	
	<script src="/rekap-konten/public/vendor/jquery/jquery-3.2.1.min.js"></script>
<!--===============================================================================================-->
	<script src="/rekap-konten/public/vendor/bootstrap/js/popper.js"></script>
	<script src="/rekap-konten/public/vendor/bootstrap/js/bootstrap.min.js"></script>
<!--===============================================================================================-->
	<script src="/rekap-konten/public/vendor/select2/select2.min.js"></script>
<!--===============================================================================================-->
	<script src="/rekap-konten/public/vendor/tilt/tilt.jquery.min.js"></script>
<!--===============================================================================================-->
	<script src="/rekap-konten/public/js/main.js"></script>
	<script src="/rekap-konten/public/js/login.js"></script>

	<style>
		.password-toggle-login {
			position: absolute;
			right: 45px;
			top: 50%;
			transform: translateY(-50%);
			cursor: pointer;
			color: #999;
			font-size: 16px;
			transition: color 0.3s ease;
			z-index: 10;
		}

		.password-toggle-login:hover {
			color: #333;
		}

		.password-toggle-login i {
			pointer-events: none;
		}

		.wrap-input100 {
			position: relative;
		}

		.btn-back-to-landing {
			display: inline-block;
			padding: 12px 24px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			text-decoration: none;
			border-radius: 25px;
			font-weight: 500;
			font-size: 14px;
			transition: all 0.3s ease;
			box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
			border: none;
			cursor: pointer;
		}

		.btn-back-to-landing:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
			background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
			color: white;
			text-decoration: none;
		}

		.btn-back-to-landing i {
			margin-right: 8px;
		}
	</style>


</body>
</html>
