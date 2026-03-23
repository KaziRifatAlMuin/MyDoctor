@echo off
cd "c:\Users\USER\OneDrive\My Codes\GitHub\MyDoctor\database\migrations"

echo Deleting old alteration migrations...

if exist "2026_03_07_052752_add_notification_preferences_to_users_table.php" (
    del "2026_03_07_052752_add_notification_preferences_to_users_table.php"
    echo Deleted: 2026_03_07_052752_add_notification_preferences_to_users_table.php
)

if exist "2026_03_09_211129_add_file_fields_to_posts_table.php" (
    del "2026_03_09_211129_add_file_fields_to_posts_table.php"
    echo Deleted: 2026_03_09_211129_add_file_fields_to_posts_table.php
)

if exist "2026_03_09_211148_add_file_fields_to_comments_table.php" (
    del "2026_03_09_211148_add_file_fields_to_comments_table.php"
    echo Deleted: 2026_03_09_211148_add_file_fields_to_comments_table.php
)

if exist "2026_03_10_100123_add_multiple_files_to_posts_table.php" (
    del "2026_03_10_100123_add_multiple_files_to_posts_table.php"
    echo Deleted: 2026_03_10_100123_add_multiple_files_to_posts_table.php
)

if exist "2026_03_13_011943_add_role_to_users_table.php" (
    del "2026_03_13_011943_add_role_to_users_table.php"
    echo Deleted: 2026_03_13_011943_add_role_to_users_table.php
)

echo.
echo Cleanup complete!
pause
