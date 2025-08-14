<?php require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verify_csrf();
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$password = $_POST['password'] ?? '';
	$confirm = $_POST['confirm_password'] ?? '';
	if ($password !== $confirm) {
		flash('error', 'Passwords do not match');
	} else {
		[$ok, $res] = AuthService::register($name, $email, $phone, $password);
		if ($ok) {
			flash('success', 'Registration successful. Please login.');
			header('Location: ' . BASE_URL . '/login.php');
			exit;
		} else {
			flash('error', $res);
		}
	}
}

include __DIR__ . '/partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Register</h2>
	<div class="card max-w-xl">
		<form method="post" class="space-y-3">
			<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
			<div class="grid gap-3 grid-cols-1 md:grid-cols-2">
				<div class="field md:col-span-2"><label for="name">Full Name</label><input type="text" id="name" name="name" required></div>
				<div class="field"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
				<div class="field"><label for="phone">Phone</label><input type="text" id="phone" name="phone"></div>
				<div class="field"><label for="password">Password</label><input type="password" id="password" name="password" required></div>
				<div class="field"><label for="confirm_password">Confirm Password</label><input type="password" id="confirm_password" name="confirm_password" required></div>
			</div>
			<button class="btn w-full" type="submit">Create Account</button>
		</form>
	</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
