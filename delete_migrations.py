import os
import sys

migrations_dir = r"c:\Users\USER\OneDrive\My Codes\GitHub\MyDoctor\database\migrations"
old_migrations = [
    "2026_03_07_052752_add_notification_preferences_to_users_table.php",
    "2026_03_09_211129_add_file_fields_to_posts_table.php",
    "2026_03_09_211148_add_file_fields_to_comments_table.php",
    "2026_03_10_100123_add_multiple_files_to_posts_table.php",
    "2026_03_13_011943_add_role_to_users_table.php",
]

print("Deleting old migrations...")
for migration in old_migrations:
    path = os.path.join(migrations_dir, migration)
    try:
        if os.path.exists(path):
            os.remove(path)
            print(f"✓ Deleted: {migration}")
        else:
            print(f"○ Not found: {migration}")
    except Exception as e:
        print(f"✗ Error deleting {migration}: {e}")

print("\nRemaining migrations:")
remaining = sorted([f for f in os.listdir(migrations_dir) if f.endswith('.php')])
for f in remaining:
    print(f"  {f}")

print(f"\nTotal migrations: {len(remaining)}")
