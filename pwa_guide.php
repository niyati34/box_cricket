<?php require_once __DIR__ . '/config.php';
include __DIR__ . '/partials/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">📱 Install Box Cricket App</h1>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-blue-800 mb-4">Why Install the App?</h2>
            <ul class="list-disc list-inside space-y-2 text-blue-700">
                <li>Quick access from your home screen</li>
                <li>Works offline - browse grounds without internet</li>
                <li>Faster loading and better performance</li>
                <li>Push notifications for booking updates</li>
                <li>App-like experience on your device</li>
            </ul>
        </div>

        <div class="grid gap-8 md:grid-cols-2">
            <!-- Chrome/Edge Instructions -->
            <div class="card">
                <div class="text-4xl mb-4">🌐</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Chrome / Edge (Android)</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Open this website in Chrome or Edge</li>
                    <li>Tap the menu button (⋮) in the top right</li>
                    <li>Select "Add to Home screen" or "Install app"</li>
                    <li>Tap "Install" to confirm</li>
                    <li>The app will appear on your home screen</li>
                </ol>
            </div>

            <!-- Safari Instructions -->
            <div class="card">
                <div class="text-4xl mb-4">🍎</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Safari (iPhone/iPad)</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Open this website in Safari</li>
                    <li>Tap the Share button (📤) at the bottom</li>
                    <li>Scroll down and tap "Add to Home Screen"</li>
                    <li>Tap "Add" to confirm</li>
                    <li>The app will appear on your home screen</li>
                </ol>
            </div>

            <!-- Desktop Instructions -->
            <div class="card">
                <div class="text-4xl mb-4">💻</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Desktop (Chrome/Edge)</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Open this website in Chrome or Edge</li>
                    <li>Look for the install icon (📱) in the address bar</li>
                    <li>Click "Install" when prompted</li>
                    <li>The app will open in a new window</li>
                    <li>You can also find it in your Start Menu</li>
                </ol>
            </div>

            <!-- Firefox Instructions -->
            <div class="card">
                <div class="text-4xl mb-4">🦊</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Firefox (Mobile)</h3>
                <ol class="list-decimal list-inside space-y-2 text-gray-700">
                    <li>Open this website in Firefox</li>
                    <li>Tap the menu button (☰) in the top right</li>
                    <li>Select "Add to Home Screen"</li>
                    <li>Tap "Add" to confirm</li>
                    <li>The app will appear on your home screen</li>
                </ol>
            </div>
        </div>

        <!-- Manual Install Button -->
        <div class="mt-8 text-center">
            <button id="manual-install-btn" class="btn text-lg px-8 py-4">
                📱 Install App Now
            </button>
            <p class="text-sm text-gray-600 mt-2">Click this button if you don't see the install prompt</p>
        </div>

        <!-- App Features -->
        <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-green-800 mb-4">🎯 App Features</h3>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <h4 class="font-semibold text-green-700 mb-2">Offline Capabilities:</h4>
                    <ul class="list-disc list-inside space-y-1 text-green-600 text-sm">
                        <li>Browse previously loaded grounds</li>
                        <li>View your booking history</li>
                        <li>Check ground details and prices</li>
                        <li>Access 3D maps (if previously loaded)</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-green-700 mb-2">Enhanced Experience:</h4>
                    <ul class="list-disc list-inside space-y-1 text-green-600 text-sm">
                        <li>Faster loading times</li>
                        <li>Push notifications</li>
                        <li>App-like navigation</li>
                        <li>Home screen shortcuts</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <h3 class="text-xl font-semibold text-yellow-800 mb-4">🔧 Troubleshooting</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="font-semibold text-yellow-700 mb-2">Install button not showing?</h4>
                    <ul class="list-disc list-inside space-y-1 text-yellow-600 text-sm">
                        <li>Make sure you're using a supported browser</li>
                        <li>Try refreshing the page</li>
                        <li>Check if you're in incognito/private mode</li>
                        <li>Ensure you have a stable internet connection</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-yellow-700 mb-2">App not working offline?</h4>
                    <ul class="list-disc list-inside space-y-1 text-yellow-600 text-sm">
                        <li>Visit the pages you want offline first</li>
                        <li>Wait for the app to cache the content</li>
                        <li>Check your browser's storage settings</li>
                        <li>Try clearing cache and reinstalling</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Browser Support -->
        <div class="mt-8 card">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">🌍 Browser Support</h3>
            <div class="grid gap-4 md:grid-cols-3">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl mb-2">✅</div>
                    <h4 class="font-semibold">Chrome</h4>
                    <p class="text-sm text-gray-600">Full support</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl mb-2">✅</div>
                    <h4 class="font-semibold">Edge</h4>
                    <p class="text-sm text-gray-600">Full support</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl mb-2">✅</div>
                    <h4 class="font-semibold">Safari</h4>
                    <p class="text-sm text-gray-600">Full support</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl mb-2">⚠️</div>
                    <h4 class="font-semibold">Firefox</h4>
                    <p class="text-sm text-gray-600">Limited support</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl mb-2">❌</div>
                    <h4 class="font-semibold">Internet Explorer</h4>
                    <p class="text-sm text-gray-600">Not supported</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl mb-2">❌</div>
                    <h4 class="font-semibold">Opera Mini</h4>
                    <p class="text-sm text-gray-600">Not supported</p>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="grounds.php" class="btn secondary">← Back to Grounds</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const manualInstallBtn = document.getElementById('manual-install-btn');
    
    if (manualInstallBtn) {
        manualInstallBtn.addEventListener('click', function() {
            if (window.pwa && window.pwa.deferredPrompt) {
                window.pwa.installPWA();
            } else {
                // Show instructions based on platform
                const platform = navigator.platform;
                const userAgent = navigator.userAgent;
                
                let message = 'Installation instructions:\n\n';
                
                if (/Android/i.test(userAgent)) {
                    message += '1. Tap the menu button (⋮)\n2. Select "Add to Home screen"\n3. Tap "Add" to confirm';
                } else if (/iPhone|iPad|iPod/i.test(userAgent)) {
                    message += '1. Tap the Share button (📤)\n2. Select "Add to Home Screen"\n3. Tap "Add" to confirm';
                } else {
                    message += '1. Look for the install icon (📱) in your browser\n2. Click "Install" when prompted';
                }
                
                alert(message);
            }
        });
    }
    
    // Check if app is already installed
    if (window.pwa && window.pwa.isInstalled()) {
        const installBtn = document.getElementById('manual-install-btn');
        if (installBtn) {
            installBtn.textContent = '✅ App Already Installed';
            installBtn.disabled = true;
            installBtn.classList.add('bg-green-500');
        }
    }
});
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>
