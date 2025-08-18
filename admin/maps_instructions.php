<?php require_once __DIR__ . '/../config.php';
require_role(['admin','superadmin']);
include __DIR__ . '/../partials/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">🗺️ How to Add Google Maps Links</h1>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-blue-800 mb-4">Why Add Google Maps Links?</h2>
            <ul class="list-disc list-inside space-y-2 text-blue-700">
                <li>Help customers find your ground easily</li>
                <li>Provide 3D map view for better location understanding</li>
                <li>Enable customers to get directions to your ground</li>
                <li>Improve customer experience and reduce no-shows</li>
            </ul>
        </div>

        <div class="grid gap-8 md:grid-cols-2">
            <!-- Step 1 -->
            <div class="card">
                <div class="text-4xl mb-4">📍</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Step 1: Find Your Ground on Google Maps</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Go to <a href="https://maps.google.com" target="_blank" class="text-blue-600 hover:underline">maps.google.com</a></li>
                    <li>Search for your ground's address or name</li>
                    <li>Make sure the location is correct</li>
                    <li>Click on the location marker</li>
                </ol>
            </div>

            <!-- Step 2 -->
            <div class="card">
                <div class="text-4xl mb-4">🔗</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Step 2: Get the Share Link</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Click the "Share" button</li>
                    <li>Select "Copy link"</li>
                    <li>The link will look like: <code class="bg-gray-100 px-2 py-1 rounded text-sm">https://maps.google.com/...</code></li>
                    <li>Copy the entire link</li>
                </ol>
            </div>

            <!-- Step 3 -->
            <div class="card">
                <div class="text-4xl mb-4">📝</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Step 3: Add to Your Ground</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Go to Admin → Manage Grounds</li>
                    <li>Click "Edit" on your ground</li>
                    <li>Paste the Google Maps link in the "Google Maps Link" field</li>
                    <li>Save the changes</li>
                </ol>
            </div>

            <!-- Step 4 -->
            <div class="card">
                <div class="text-4xl mb-4">✅</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Step 4: Test the 3D View</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Go to the main grounds page</li>
                    <li>Find your ground in the list</li>
                    <li>Click the 🗺️ button next to your ground</li>
                    <li>Verify the 3D map loads correctly</li>
                </ol>
            </div>
        </div>

        <!-- Example URLs -->
        <div class="mt-8 card">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">📋 Example Google Maps URLs</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">These are examples of valid Google Maps URLs:</p>
                <div class="space-y-2 text-sm">
                    <code class="block bg-white p-2 rounded border">https://maps.google.com/?q=Box+Cricket+Ground+Mumbai</code>
                    <code class="block bg-white p-2 rounded border">https://maps.google.com/maps?q=19.0760,72.8777</code>
                    <code class="block bg-white p-2 rounded border">https://maps.google.com/maps/place/Box+Cricket/@19.0760,72.8777,15z</code>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-green-800 mb-4">💡 Pro Tips</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <h4 class="font-semibold text-green-700 mb-2">For Better 3D Experience:</h4>
                    <ul class="list-disc list-inside space-y-1 text-green-600 text-sm">
                        <li>Use satellite view for better 3D buildings</li>
                        <li>Set zoom level to 18-20 for detailed view</li>
                        <li>Include the ground name in the search</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-green-700 mb-2">For Customer Convenience:</h4>
                    <ul class="list-disc list-inside space-y-1 text-green-600 text-sm">
                        <li>Test the link from a mobile device</li>
                        <li>Verify street view is available</li>
                        <li>Check that directions work properly</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-yellow-800 mb-4">🔧 Troubleshooting</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="font-semibold text-yellow-700 mb-2">Map not loading?</h4>
                    <ul class="list-disc list-inside space-y-1 text-yellow-600 text-sm">
                        <li>Make sure the URL starts with "https://maps.google.com"</li>
                        <li>Check that the link is not truncated</li>
                        <li>Try copying the link again from Google Maps</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-yellow-700 mb-2">Wrong location showing?</h4>
                    <ul class="list-disc list-inside space-y-1 text-yellow-600 text-sm">
                        <li>Verify the address on Google Maps first</li>
                        <li>Use the exact ground name in the search</li>
                        <li>Check coordinates if using a coordinate-based link</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="grounds.php" class="btn">← Back to Manage Grounds</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
