<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
$user = current_user();
include __DIR__ . '/../partials/header.php';
?>

	<h2 class="text-2xl md:text-3xl font-semibold mb-4">Admin Dashboard</h2>
	<div class="grid gap-4 grid-cols-1 md:grid-cols-2">
		<div class="card">
			<h3 class="text-lg font-semibold">Manage Grounds</h3>
			<p>Create and edit grounds and slots.</p>
			<a class="btn mt-2" href="grounds.php">Open</a>
		</div>
		<div class="card">
			<h3 class="text-lg font-semibold">Bookings</h3>
			<p>View and manage bookings.</p>
			<a class="btn mt-2" href="bookings.php">Open</a>
		</div>
	</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
