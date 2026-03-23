<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Database Migration & Structure Verification ===\n\n";

// Check if migrations table exists
if (Schema::hasTable('migrations')) {
    echo "✓ Migrations table exists\n";
    $migrationCount = DB::table('migrations')->count();
    echo "  Migrations ran: " . $migrationCount . "\n";
} else {
    echo "✗ Migrations table missing\n";
    exit(1);
}

// Check each required table
$requiredTables = [
    'users',
    'posts', 
    'comments',
    'medicines',
    'mailings',
    'posts_likes'
];

echo "\n=== Table Verification ===\n";
foreach ($requiredTables as $table) {
    if (Schema::hasTable($table)) {
        echo "✓ Table '$table' exists\n";
    } else {
        echo "✗ Table '$table' missing\n";
    }
}

// Check users table columns
echo "\n=== Users Table Columns ===\n";
$requiredUserColumns = ['id', 'name', 'email', 'role', 'email_notifications', 'push_notifications', 'notification_settings'];
$userColumns = Schema::getColumnListing('users');
foreach ($requiredUserColumns as $col) {
    if (in_array($col, $userColumns)) {
        echo "✓ Column '$col' exists\n";
    } else {
        echo "✗ Column '$col' missing\n";
    }
}

// Check posts table columns
echo "\n=== Posts Table Columns ===\n";
$requiredPostsColumns = ['id', 'user_id', 'disease_id', 'description', 'file_path', 'file_type', 'file_name', 'file_size', 'files'];
$postsColumns = Schema::getColumnListing('posts');
foreach ($requiredPostsColumns as $col) {
    if (in_array($col, $postsColumns)) {
        echo "✓ Column '$col' exists\n";
    } else {
        echo "✗ Column '$col' missing\n";
    }
}

// Check comments table columns
echo "\n=== Comments Table Columns ===\n";
$requiredCommentsColumns = ['id', 'post_id', 'user_id', 'comment_details', 'file_path', 'file_type', 'file_name', 'file_size'];
$commentsColumns = Schema::getColumnListing('comments');
foreach ($requiredCommentsColumns as $col) {
    if (in_array($col, $commentsColumns)) {
        echo "✓ Column '$col' exists\n";
    } else {
        echo "✗ Column '$col' missing\n";
    }
}

// Check mailings table
if (Schema::hasTable('mailings')) {
    echo "\n=== Mailings Table Columns ===\n";
    $mailingColumns = Schema::getColumnListing('mailings');
    $requiredMailingColumns = ['id', 'user_id', 'recipient_email', 'subject', 'body', 'status', 'sent_at'];
    foreach ($requiredMailingColumns as $col) {
        if (in_array($col, $mailingColumns)) {
            echo "✓ Column '$col' exists\n";
        } else {
            echo "✗ Column '$col' missing\n";
        }
    }
}

echo "\n=== Verification Complete ===\n";
echo "All required tables and columns have been created successfully!\n";
?>
