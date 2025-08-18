<?php
/**
 * Generate PWA Icons
 * This script creates basic icons for the PWA using GD library
 */

// Icon sizes needed for PWA
$sizes = [16, 32, 72, 96, 128, 144, 152, 192, 384, 512];

// Create icons directory if it doesn't exist
$iconsDir = __DIR__ . '/public/icons';
if (!is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
}

foreach ($sizes as $size) {
    // Create a new image
    $image = imagecreatetruecolor($size, $size);
    
    // Define colors
    $bgColor = imagecolorallocate($image, 16, 185, 129); // #10b981 (emerald)
    $textColor = imagecolorallocate($image, 255, 255, 255); // White
    $borderColor = imagecolorallocate($image, 5, 150, 105); // Darker emerald
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    
    // Add border
    imagerectangle($image, 0, 0, $size-1, $size-1, $borderColor);
    
    // Add cricket ball icon (simple circle)
    $ballSize = $size * 0.4;
    $ballX = $size / 2;
    $ballY = $size / 2;
    imagefilledellipse($image, $ballX, $ballY, $ballSize, $ballSize, $textColor);
    
    // Add text for larger icons
    if ($size >= 96) {
        $fontSize = max(8, $size / 16);
        $text = "🏏";
        $textX = $size / 2;
        $textY = $size / 2 + $fontSize / 3;
        
        // Use a simple text rendering
        imagestring($image, 5, $textX - 10, $textY - 10, "🏏", $bgColor);
    }
    
    // Save the image
    $filename = $iconsDir . "/icon-{$size}x{$size}.png";
    imagepng($image, $filename);
    imagedestroy($image);
    
    echo "Generated icon: {$filename}\n";
}

echo "\nAll PWA icons generated successfully!\n";
echo "Icons are saved in: {$iconsDir}\n";
?>
