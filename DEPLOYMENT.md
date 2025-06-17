# Deployment Checklist for InfinityFree

## Pre-deployment Steps
1. Database Backup
   - Export your local database
   - Keep a backup of all SQL files

2. File Structure Check
   - Ensure all files are in their correct directories
   - Verify file permissions (755 for directories, 644 for files)
   - Check for any hardcoded local paths

3. Configuration Updates
   - Update database credentials in `includes/db_connect.php`
   - Update any absolute paths to relative paths
   - Check all file permissions

## Directory Structure
```
/
├── admin/              # Admin panel files
├── customer/           # Customer-facing files
├── includes/           # Shared PHP files
├── assets/            # Static assets (CSS, JS, images)
├── uploads/           # Uploaded files
├── qr_codes/          # Generated QR codes
├── .htaccess          # Apache configuration
└── DEPLOYMENT.md      # This file
```

## Deployment Steps
1. Create InfinityFree Account
   - Sign up at infinityfree.net
   - Choose a domain name
   - Note down your FTP credentials

2. Database Setup
   - Create a new database in InfinityFree control panel
   - Import your local database
   - Update database credentials in `includes/db_connect.php`

3. File Upload
   - Upload all files maintaining the directory structure
   - Use FTP client (FileZilla recommended)
   - Upload files to the `htdocs` directory

4. Post-deployment Checks
   - Test admin login
   - Test customer registration/login
   - Verify file uploads work
   - Check QR code generation
   - Test payment processing
   - Verify email functionality

## Important URLs
- Admin Panel: `https://your-domain.com/admin/`
- Customer Site: `https://your-domain.com/customer/`

## Security Considerations
1. Change default admin credentials
2. Ensure all sensitive files are protected
3. Keep backup of database and files
4. Monitor error logs regularly

## Troubleshooting
1. 500 Internal Server Error
   - Check file permissions
   - Verify .htaccess configuration
   - Check PHP version compatibility

2. Database Connection Issues
   - Verify database credentials
   - Check database host name
   - Ensure database exists

3. File Upload Issues
   - Check upload directory permissions
   - Verify PHP upload limits
   - Check file size restrictions

## Maintenance
1. Regular backups
2. Monitor disk space
3. Check error logs
4. Update security patches
5. Monitor performance

## Support
- InfinityFree Support: https://app.infinityfree.net/support
- PHP Version: 7.4 or higher recommended
- MySQL Version: 5.7 or higher recommended 