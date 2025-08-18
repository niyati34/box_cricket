<?php
require_once __DIR__ . '/config.php';
$pdo = get_pdo();

echo "<h1>Testing Ground ID 2</h1>";

// Check if ground exists
$stmt = $pdo->prepare('SELECT * FROM grounds WHERE id = 2');
$stmt->execute();
$ground = $stmt->fetch();

if ($ground) {
    echo "<p><strong>Ground found:</strong></p>";
    echo "<ul>";
    echo "<li>ID: " . $ground['id'] . "</li>";
    echo "<li>Name: " . htmlspecialchars($ground['name']) . "</li>";
    echo "<li>Location: " . htmlspecialchars($ground['location']) . "</li>";
    echo "<li>Maps Link: " . htmlspecialchars($ground['maps_link'] ?? 'NULL') . "</li>";
    echo "<li>Active: " . ($ground['is_active'] ? 'Yes' : 'No') . "</li>";
    echo "</ul>";
    
    if (!empty($ground['maps_link'])) {
        echo "<p><strong>Testing MapsHelper:</strong></p>";
        require_once __DIR__ . '/lib/MapsHelper.php';
        
        if (MapsHelper::isValidGoogleMapsUrl($ground['maps_link'])) {
            echo "<p>✅ Valid Google Maps URL</p>";
            $maps_url_3d = MapsHelper::convertTo3D($ground['maps_link']);
            echo "<p>3D URL: " . htmlspecialchars($maps_url_3d) . "</p>";
        } else {
            echo "<p>❌ Not a valid Google Maps URL</p>";
        }
    } else {
        echo "<p>❌ No maps_link configured for this ground</p>";
    }
} else {
    echo "<p>❌ Ground with ID 2 not found</p>";
}

// List all grounds
echo "<h2>All Grounds:</h2>";
$stmt = $pdo->prepare('SELECT id, name, location, maps_link FROM grounds ORDER BY id');
$stmt->execute();
$grounds = $stmt->fetchAll();

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Location</th><th>Maps Link</th></tr>";
foreach ($grounds as $g) {
    echo "<tr>";
    echo "<td>" . $g['id'] . "</td>";
    echo "<td>" . htmlspecialchars($g['name']) . "</td>";
    echo "<td>" . htmlspecialchars($g['location']) . "</td>";
    echo "<td>" . htmlspecialchars($g['maps_link'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
