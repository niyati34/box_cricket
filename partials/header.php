<?php require_once __DIR__ . '/../config.php';
// Prevent browser caching of logged-in state
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Expires: 0');
header('Pragma: no-cache'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo APP_NAME; ?></title>
	<meta name="description" content="Book box cricket grounds easily with 3D maps and real-time availability">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/style.css">
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50">
	<header class="site-header sticky top-0 z-40">
		<div class="container max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
			<a href="<?php echo BASE_URL; ?>/" class="logo text-white text-xl font-bold transition-transform hover:scale-[1.02]"><?php echo APP_NAME; ?></a>
			<nav>
				<ul class="flex items-center gap-3 md:gap-5 flex-wrap">
					<li><a class="hover:text-white transition-opacity hover:opacity-100 opacity-90" href="<?php echo BASE_URL; ?>/grounds">Grounds</a></li>
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
<script>
// Force reload after logout to clear cached UI
if (window.location.pathname.endsWith('/index.php') || window.location.pathname === '<?php echo BASE_URL; ?>/' || window.location.pathname === '<?php echo BASE_URL; ?>') {
  if (performance && performance.getEntriesByType('navigation')[0].type === 'reload') {
    // If the page was reloaded, do nothing
  } else if (document.referrer && document.referrer.includes('/logout')) {
    window.location.reload(true); // Force reload from server
  }
}
</script>
