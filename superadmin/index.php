<?php require_once __DIR__ . '/../config.php';
require_role('superadmin');
include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Super Admin</h2>
	<div class="grid gap-4 grid-cols-1 md:grid-cols-2">
		<div class="card">
			<h3 class="text-lg font-semibold">Admins</h3>
			<p>Create and manage admin accounts.</p>
			<a class="btn mt-2" href="users.php">Manage</a>
		</div>
		<div class="card">
			<h3 class="text-lg font-semibold">Grounds</h3>
			<p>Create grounds and assign to admins.</p>
			<a class="btn mt-2" href="grounds.php">Manage</a>
		</div>
	</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
