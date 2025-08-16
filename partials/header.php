<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?></title>
	
	<!-- PWA Meta Tags -->
	<meta name="description" content="Book box cricket grounds easily with 3D maps and real-time availability">
	<meta name="theme-color" content="#10b981">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta name="apple-mobile-web-app-title" content="Box Cricket">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="msapplication-TileColor" content="#10b981">
	<meta name="msapplication-tap-highlight" content="no">
	
	<!-- PWA Icons -->
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo BASE_URL; ?>/public/icons/icon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo BASE_URL; ?>/public/icons/icon-16x16.png">
	<link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/public/icons/icon-192x192.png">
	<link rel="manifest" href="<?php echo BASE_URL; ?>/manifest.json">
	
	<!-- Preconnect for performance -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/style.css">
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="<?php echo BASE_URL; ?>/public/pwa.js"></script>
</head>
<body class="bg-slate-50">
	<header class="site-header sticky top-0 z-40">
		<div class="container max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
			<a href="<?php echo BASE_URL; ?>/" class="logo text-white text-xl font-bold transition-transform hover:scale-[1.02]"><?php echo APP_NAME; ?></a>
			<nav>
				<ul class="flex items-center gap-3 md:gap-5 flex-wrap">
					<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/grounds">Grounds</a></li>
					<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/pwa_guide.php" title="Install App">📱</a></li>
					<?php if (is_logged_in()): $u = current_user(); ?>
						<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/my_bookings">My Bookings</a></li>
						<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/change_password.php">Change Password</a></li>
						<?php if ($u['role'] === 'admin' || $u['role'] === 'superadmin'): ?>
							<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/admin/index.php">Admin</a></li>
						<?php endif; ?>
						<?php if ($u['role'] === 'superadmin'): ?>
							<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/superadmin/index.php">Super Admin</a></li>
						<?php endif; ?>
						<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/logout">Logout</a></li>
					<?php else: ?>
						<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/login">Login</a></li>
						<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/register">Register</a></li>
					<?php endif; ?>
				</ul>
			</nav>
		</div>
	</header>
	<div class="toast">
		<?php if ($msg = flash('success')): ?><div class="alert success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
		<?php if ($msg = flash('error')): ?><div class="alert error"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
	</div>
	<main class="container max-w-7xl mx-auto px-4 py-6">
		<?php if ($msg = flash('success')): ?><div class="alert success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
		<?php if ($msg = flash('error')): ?><div class="alert error"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
		
		<!-- PWA Install Prompt -->
		<div id="pwa-install-prompt" class="hidden fixed bottom-4 left-4 right-4 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50">
			<div class="flex items-center justify-between">
				<div class="flex items-center space-x-3">
					<div class="text-2xl">📱</div>
					<div>
						<h3 class="font-semibold text-gray-900">Install App</h3>
						<p class="text-sm text-gray-600">Add to home screen for quick access</p>
					</div>
				</div>
				<div class="flex space-x-2">
					<button id="pwa-install-btn" class="btn btn-sm">Install</button>
					<button id="pwa-dismiss-btn" class="btn secondary btn-sm">Later</button>
				</div>
			</div>
		</div>
		
		<!-- Offline Indicator -->
		<div id="offline-indicator" class="hidden fixed top-20 left-4 right-4 bg-yellow-100 border border-yellow-300 rounded-lg p-3 z-50">
			<div class="flex items-center justify-center space-x-2">
				<div class="text-yellow-600">📶</div>
				<span class="text-yellow-800 text-sm font-medium">You're offline. Some features may be limited.</span>
			</div>
		</div>
<script>
  // Auto-dismiss alerts after 2 seconds
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
      document.querySelectorAll('.alert').forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(function() { alert.style.display = 'none'; }, 500);
      });
    }, 2000);
  });
</script>
