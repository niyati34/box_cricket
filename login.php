<?php require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	[$ok, $res] = AuthService::login($email, $password);
	if ($ok) {
		flash('success', 'Welcome back, ' . $res['name']);
		header('Location: ' . BASE_URL . '/index.php');
		exit;
	}
	flash('error', $res);
}

include __DIR__ . '/partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Login</h2>
	<div class="card max-w-md">
		<form method="post" class="space-y-3">
			<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
			<div class="field">
				<label for="email">Email</label>
				<input type="email" id="email" name="email" required>
			</div>
			<div class="field">
				<label for="password">Password</label>
				<input type="password" id="password" name="password" required>
			</div>
			<button class="btn w-full" type="submit">Login</button>
		</form>
	</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
