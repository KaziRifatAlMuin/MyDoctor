# System Maintenance Page - Usage Guide

This document explains how to use the maintenance page system in MyDoctor.

## Overview

A professional maintenance page has been created to inform users when system repairs or maintenance is happening. The page includes:

- Animated header with pulsing icon
- Status information
- Estimated time to completion
- Description of work being performed
- Contact information
- Auto-refresh functionality (every 30 seconds)
- Professional design with Tailwind CSS

## Files Created

1. **`resources/views/maintenance.blade.php`** - The main maintenance page view
2. **`app/Http/Controllers/MaintenanceController.php`** - Controller for managing the maintenance page
3. **`app/Http/Middleware/MaintenanceMode.php`** - Middleware to enable/disable maintenance mode globally

## How to Use

### Option 1: Direct Route Access

Access the maintenance page directly at:
```
/maintenance
```

You can also pass custom parameters via query string:
```
/maintenance?status=Database%20Upgrade&time=2%20hours&description=Optimizing%20database%20performance
```

### Option 2: Using the Controller

Update the route in `routes/web.php` to use the controller:

```php
Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance');
```

Or to use the `show` method with query parameters:

```php
Route::get('/maintenance', [MaintenanceController::class, 'show'])->name('maintenance');
```

### Option 3: Enable Global Maintenance Mode

To enable maintenance mode across the entire application:

1. Add to your `.env` file:
```
APP_MAINTENANCE_MODE=true
APP_MAINTENANCE_STATUS="Database Migration"
APP_MAINTENANCE_TIME="Within 2 hours"
APP_MAINTENANCE_DESCRIPTION="We are migrating our database for better performance"
```

2. Register the middleware in `app/Http/Kernel.php`:
```php
protected $middleware = [
    // ... other middleware
    \App\Http\Middleware\MaintenanceMode::class,
];
```

3. Add config values to `config/app.php`:
```php
'maintenance_mode' => env('APP_MAINTENANCE_MODE', false),
'maintenance_status' => env('APP_MAINTENANCE_STATUS', 'Maintenance in progress'),
'maintenance_time' => env('APP_MAINTENANCE_TIME', 'Expected to be complete soon'),
'maintenance_description' => env('APP_MAINTENANCE_DESCRIPTION', 'System repairs and improvements'),
```

**Note**: Admins can still access the application during maintenance mode.

## Customizing the Page

### Change Contact Information

Edit `resources/views/maintenance.blade.php` and update the contact section:

```html
<p class="text-gray-700">
    <strong>Email:</strong>
    <a href="mailto:your-email@example.com" class="text-blue-600 hover:text-blue-700">
        your-email@example.com
    </a>
</p>
```

### Modify Colors and Styling

The page uses Tailwind CSS classes. You can modify:
- **Header gradient**: `from-blue-600 to-blue-700`
- **Accent color**: Change `blue-` prefix to other colors (green, red, purple, etc.)
- **Background**: `from-blue-50 via-white to-blue-50`

### Disable Auto-Refresh

Remove or comment out this section in the script:

```javascript
// Auto-refresh every 30 seconds
setTimeout(function() {
    location.reload();
}, 30000);
```

## Usage Examples

### Example 1: Simple Maintenance Mode

```php
Route::get('/maintenance', function () {
    return view('maintenance', [
        'status' => 'System Upgrade',
        'estimated_time' => '3 hours',
        'work_description' => 'We are upgrading our servers for better performance'
    ]);
})->name('maintenance');
```

### Example 2: Database Backup

```php
return view('maintenance', [
    'status' => 'Database Backup in Progress',
    'estimated_time' => '30 minutes',
    'work_description' => 'We are creating a backup of all your data to ensure its safety'
]);
```

### Example 3: Security Updates

```php
return view('maintenance', [
    'status' => 'Security Update',
    'estimated_time' => '1 hour',
    'work_description' => 'Applying critical security patches to protect your data'
]);
```

## Features

✅ **Professional Design** - Modern, responsive layout that works on all devices
✅ **Auto-Refresh** - Page automatically refreshes every 30 seconds
✅ **Animations** - Smooth animations and transitions
✅ **Real-time Updates** - Shows last update time
✅ **Contact Information** - Easy way for users to reach support
✅ **Data Safety Message** - Assures users their data is safe
✅ **Admin Bypass** - Admins can still access the app
✅ **Customizable** - Easy to customize text and styling

## Notes

- The page uses Tailwind CSS via CDN for styling
- Auto-refresh is set to 30 seconds (adjustable in the script)
- The page displays UTC time
- All data safety information is reassuring to users

---

**Last Updated**: 2026-04-25
