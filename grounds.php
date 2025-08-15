<?php require_once __DIR__ . '/config.php';
$pdo = get_pdo();

$q = trim($_GET['q'] ?? '');
$date = $_GET['date'] ?? '';

$sql = 'SELECT * FROM grounds WHERE is_active = 1';
$params = [];
if ($q !== '') {
	$sql .= ' AND (name LIKE ? OR location LIKE ?)';
	$params[] = "%$q%";
	$params[] = "%$q%";
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$grounds = $stmt->fetchAll();

include __DIR__ . '/partials/header.php';
?>

	<div class="text-center mb-8">
		<h1 class="text-4xl md:text-5xl font-bold mb-4 bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">Discover Amazing Turfs</h1>
		<p class="text-lg text-slate-600 max-w-2xl mx-auto">Find the perfect box cricket arena for your next match. Book with confidence and enjoy world-class facilities.</p>
	</div>

	<div class="card max-w-4xl mx-auto mb-8">
		<form method="get" action="grounds" class="space-y-4">
			<div class="grid gap-4 grid-cols-1 md:grid-cols-3">
				<div class="field">
					<label for="date" class="text-slate-700 font-semibold">📅 Play Date</label>
					<input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" class="border-2 border-slate-200 focus:border-emerald-500">
				</div>
				<div class="field md:col-span-2">
					<label for="q" class="text-slate-700 font-semibold">🔍 Search Grounds</label>
					<input type="text" id="q" name="q" placeholder="Search by name or location..." value="<?php echo htmlspecialchars($q); ?>" class="border-2 border-slate-200 focus:border-emerald-500">
				</div>
			</div>
			<button class="btn w-full md:w-auto" type="submit">🔍 Search Grounds</button>
		</form>
	</div>

	<?php if (empty($grounds)): ?>
		<div class="text-center py-12">
			<div class="text-6xl mb-4">🏏</div>
			<h3 class="text-xl font-semibold text-slate-600 mb-2">No grounds found</h3>
			<p class="text-slate-500">Try adjusting your search criteria or check back later.</p>
		</div>
	<?php else: ?>
		<div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
			<?php foreach ($grounds as $g): $img = !empty($g['image_path']) ? htmlspecialchars($g['image_path']) : null; ?>
				<div class="card group hover:shadow-2xl transition-all duration-300">
					<div class="relative mb-4">
						<?php if ($img): ?>
							<img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($g['name']); ?>" style="width:100%;height:192px;object-fit:cover;border-radius:10px;" />
						<?php else: ?>
							<div class="w-full h-48 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-lg flex items-center justify-center">
								<div class="text-6xl">🏟️</div>
							</div>
						<?php endif; ?>
						<div class="absolute top-3 right-3 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
							₹<?php echo number_format($g['price_per_hour'], 0); ?>/hr
						</div>
					</div>
					<h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-emerald-600 transition-colors"><?php echo htmlspecialchars($g['name']); ?></h3>
					<p class="text-slate-600 mb-3 flex items-center">
						<span class="mr-2">📍</span>
						<?php echo htmlspecialchars($g['location'] ?? 'Location not specified'); ?>
					</p>
					<?php if (!empty($g['description'])): ?>
						<p class="text-slate-500 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($g['description']); ?></p>
					<?php endif; ?>
					<a class="btn w-full text-center group-hover:scale-105 transition-transform" href="ground/<?php echo (int)$g['id']; ?><?php echo $date ? '?date=' . urlencode($date) : ''; ?>">
						🎯 View Available Slots
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
