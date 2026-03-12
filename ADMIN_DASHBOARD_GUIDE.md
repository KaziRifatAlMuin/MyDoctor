# Admin Dashboard Guide

## Overview
The Admin Dashboard is a comprehensive management system accessible to admin users through the profile dropdown menu. It provides complete control over user management and medical information across the platform.

## Accessing the Admin Dashboard

1. **Click the Profile Icon** in the top-right corner of the navigation bar
2. **Select "Admin Dashboard"** from the dropdown menu (only visible to admin users)
3. You'll be redirected to the spectacular admin dashboard with full system management capabilities

## Features

### 1. **Dashboard Overview (Statistics)**
- **Total Users**: Count of all registered users in the system
- **Administrators**: Number of admin accounts
- **Members**: Total member accounts
- **New This Week**: Users registered in the past 7 days
- **Total Medicines**: All medicines recorded by all users
- **Health Metrics**: Total health metrics entries across the platform

### 2. **Users Management Tab**
Complete user management interface with search and filtering capabilities.

#### Features:
- **User List Table** showing:
  - User avatar and name
  - Email address
  - User role (Admin/Member) with color-coded badges
  - Registration date
  
#### Actions:
- **Edit User** - Modify user information (Name, Email, Phone, Occupation, Blood Group, DOB)
  - Opens modal dialog for editing
  - Changes are saved with PATCH request
  - Changes apply immediately
  
- **View Medical Info** - Quick view of user's medical data
  - Shows medicines assigned to user
  - Shows diseases recorded for user
  - Provides quick medical profile overview

#### Filters:
- **Search**: Filter users by name or email
- **Role Filter**: Show only Admins or Members
- **Search Button**: Apply filters to table

### 3. **Medical Information Tab**
Comprehensive medical data management for all users.

#### Sub-sections:

##### A. **User Diseases**
- View all diseases recorded across the platform
- Shows disease name and diagnosed date
- Display user associated with each disease record

Actions per disease:
- **Edit** - Modify disease information
- **Delete** - Remove disease record permanently

##### B. **User Medicines**
- View all medicines recorded by users
- Shows:
  - Medicine name
  - Associated user
  - Dosage and frequency
  - Start date

Actions per medicine:
- **Edit** - Modify medicine details
- **Delete** - Remove medicine record

##### C. **Health Metrics**
- Display all health metrics recorded by users
- Shows:
  - Metric type (e.g., Blood Pressure, Temperature)
  - User who recorded it
  - Date recorded
  - Value and unit
  - Notes

Actions per metric:
- **Edit** - Update metric information
- **Delete** - Remove metric from system

#### Filters:
- **Search**: Find by user name
- **Type Filter**: Filter by Disease, Medicine, or Health Metrics
- **Search Button**: Apply combined filters

### 4. **Activity Log Tab**
Real-time tracking of system activities.

#### Displays:
- **User Registrations**: Shows new users joining the platform
- **Community Posts**: Track new posts created
- **Medicine Reminders**: Monitor medicine schedule updates
- **Timestamps**: Relative time display (e.g., "2 hours ago")
- **Activity Icons**: Visual indicators for activity type

## User Interface Features

### Design Elements
- **Gradient Header**: Eye-catching admin header with system title
- **Statistics Cards**: Color-coded stat cards for quick overview
  - Blue: User count statistics
  - Orange: Admin metrics
  - Green: Active users and health metrics
  - Red: Critical metrics

### Interactive Elements
- **Tabbed Navigation**: Easy switching between Users, Medical Info, and Activity
- **Responsive Tables**: Mobile-friendly data presentation
- **Modal Dialogs**: Smooth editing experience without page reloads
- **Search & Filter**: Real-time filtering of results
- **Pagination**: Handles large datasets efficiently (50 users per page)

### Visual Feedback
- **Hover Effects**: Table rows highlight on hover
- **Loading States**: Spinner animation while loading data
- **Empty States**: Clear messaging when no data available
- **Success/Error Messages**: Toast notifications for actions

## Permissions & Security

- **Role Requirement**: Only users with `role = 'admin'` can access
- **Middleware Protection**: Admin middleware verifies access on every request
- **CSRF Protection**: All form submissions protected with CSRF tokens
- **Data Validation**: Server-side validation on all updates

## Editing User Information

### How to Edit a User:
1. Find the user in the Users Management tab
2. Click the **Edit** button (pencil icon)
3. Modal dialog opens with user information
4. Update desired fields:
   - Full Name
   - Email
   - Phone Number
   - Occupation
   - Blood Group
   - Date of Birth
5. Click **Save Changes**
6. Changes are immediately applied

### Fields Available for Editing:
- ✅ Name
- ✅ Email
- ✅ Phone
- ✅ Occupation
- ✅ Blood Group (O+, O-, A+, A-, B+, B-, AB+, AB-)
- ✅ Date of Birth

## Managing Medical Information

### Editing Medical Data:
Medical information can be edited or deleted from the Medical Information tab.

**Disease Records**:
- Edit: Modify disease details for a user
- Delete: Remove disease record (cannot be undone)

**Medicine Records**:
- Edit: Update dosage, frequency, or dates
- Delete: Remove medicine from user's list

**Health Metrics**:
- Edit: Update recorded values or notes
- Delete: Remove metric entry

### Important Notes:
- Deleted records cannot be recovered
- All changes are logged in the activity log
- Changes reflect immediately in user's health section
- Admin can view/modify any user's data

## Cannot Do (Restrictions):
❌ Add new users (prevent fake account creation)
❌ Delete user accounts (data integrity)
❌ Modify user roles (security)
❌ Change user passwords directly

Can only EDIT existing data, not create new users.

## API Endpoints Used

The admin dashboard uses the following API endpoints:

```
GET  /api/users/{id}              - Get user basic information
GET  /api/users/{id}/medical      - Get user's medical data
POST /health/disease/{id}         - Create/update disease (handled by HealthController)
DELETE /health/disease/{id}       - Delete disease record
PUT  /health/metric/{id}          - Update health metric
DELETE /health/metric/{id}        - Delete health metric
PATCH /profile/update             - Update user information
```

## Performance Considerations

- **Pagination**: User list shows 50 items per page for performance
- **Lazy Loading**: Medical data loads on-demand via modals
- **Caching**: Recent activities cached to reduce DB queries
- **Indexes**: Database indexes on frequently searched fields

## Browser Compatibility

- Chrome/Edge: Full support
- Firefox: Full support
- Safari: Full support
- Mobile browsers: Responsive design optimized

## Troubleshooting

### Admin Dashboard Link Not Showing:
- Verify user has `role = 'admin'` in database
- Check admin middleware is registered in HTTP kernel

### Edit Modal Not Opening:
- Ensure JavaScript is enabled
- Check browser console for errors
- Verify CSRF token is present

### Changes Not Saving:
- Confirm you have admin access
- Check network tab for API errors
- Verify form validation passes

### Medical Data Not Displaying:
- Check user has associated medical records
- Verify foreign keys in database
- Check relationships in User model

## Future Enhancements

Planned features for next versions:
- User activity analytics and charts
- Bulk user operations
- Medical data import/export
- Advanced search and filtering
- User account status management
- System logs and audit trail
- Admin role management
- Backup and restore functionality

---

**Last Updated**: March 2026
**Version**: 1.0
**Admin Dashboard Status**: ✅ FULLY FUNCTIONAL
