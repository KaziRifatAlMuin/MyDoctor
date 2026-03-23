<?php

$baseDir = 'c:\\Users\\USER\\OneDrive\\My Codes\\GitHub\\MyDoctor\\database\\migrations';

$oldMigrations = [
    '2026_03_07_052752_add_notification_preferences_to_users_table.php',
    '2026_03_09_211129_add_file_fields_to_posts_table.php',
    '2026_03_09_211148_add_file_fields_to_comments_table.php',
    '2026_03_10_100123_add_multiple_files_to_posts_table.php',
    '2026_03_13_011943_add_role_to_users_table.php',
];

echo "Deleting old alteration migrations...\n";

foreach ($oldMigrations as $file) {
    $path = $baseDir . '\\' . $file;
    if (file_exists($path)) {
        if (unlink($path)) {
            echo "✓ Deleted: " . $file . "\n";
        } else {
            echo "✗ Failed to delete: " . $file . "\n";
        }
    } else {
        echo "○ File not found: " . $file . "\n";
    }
}

echo "\n=== Remaining migrations ===\n";
$files = glob($baseDir . '\\*.php');
foreach ($files as $file) {
    echo basename($file) . "\n";
}

echo "\nCleanup complete!\n";
?>
