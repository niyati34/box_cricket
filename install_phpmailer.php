<?php
/**
 * Simple PHPMailer Installation Script
 * Run this if you don't have Composer installed
 */

echo "Installing PHPMailer...\n";

// Create lib directory if it doesn't exist
if (!is_dir('lib')) {
    mkdir('lib', 0755, true);
}

// Download PHPMailer from GitHub
$phpmailerUrl = 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php';
$smtpUrl = 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php';
$exceptionUrl = 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php';

$files = [
    'lib/PHPMailer.php' => $phpmailerUrl,
    'lib/SMTP.php' => $smtpUrl,
    'lib/Exception.php' => $exceptionUrl
];

foreach ($files as $file => $url) {
    echo "Downloading $file...\n";
    $content = file_get_contents($url);
    if ($content !== false) {
        file_put_contents($file, $content);
        echo "✓ Downloaded $file\n";
    } else {
        echo "✗ Failed to download $file\n";
    }
}

// Create autoloader
$autoloader = '<?php
// Simple autoloader for PHPMailer
spl_autoload_register(function ($class) {
    $prefix = "PHPMailer\\PHPMailer\\";
    $base_dir = __DIR__ . "/";
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace("\\", "/", $relative_class) . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
});
';

file_put_contents('lib/autoload.php', $autoloader);
echo "✓ Created autoloader\n";

echo "\nInstallation complete! Include 'lib/autoload.php' in your config.php\n";
echo "Or better yet, use Composer: composer require phpmailer/phpmailer\n";
?>
