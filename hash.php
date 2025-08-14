<?php
// Save as hash_string.php and open in your browser

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$input = trim($_POST['text'] ?? '');
	if ($input === '') {
		$error = 'Please enter a string.';
	} else {
		// Bcrypt hash (good for passwords; includes salt)
		$hashed = password_hash($input, PASSWORD_BCRYPT);
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>String Hasher</title>
	<style>
		body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; }
		input[type="text"] { width: 100%; padding: 8px; }
		button { padding: 8px 12px; margin-top: 10px; }
		pre { background: #f5f5f5; padding: 10px; border-radius: 6px; }
		.error { color: #b91c1c; }
	</style>
</head>
<body>
	<h2>Convert a String to a Hashed String</h2>
	<form method="post">
		<input type="text" name="text" placeholder="Enter any string" value="<?php echo htmlspecialchars($_POST['text'] ?? ''); ?>" required>
		<button type="submit">Hash</button>
	</form>

	<?php if (!empty($error)): ?>
		<p class="error"><?php echo htmlspecialchars($error); ?></p>
	<?php endif; ?>

	<?php if (!empty($hashed)): ?>
		<h3>Hashed (bcrypt):</h3>
		<pre><?php echo htmlspecialchars($hashed); ?></pre>
	<?php endif; ?>
</body>
</html>