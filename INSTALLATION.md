# Installation Guide - Apprenticeship Portal

This guide will help you deploy the Apprenticeship Portal website to your 34SP hosting environment.

## Prerequisites

- 34SP hosting account with:
  - PHP 8.0 or higher
  - MySQL 8.0 or higher
  - Apache web server
  - Access to phpMyAdmin or MySQL command line
- FileZilla or another FTP client
- Your 34SP FTP credentials
- Your MySQL database credentials

## Step 1: Database Setup

### 1.1 Create Database

1. Log into your 34SP control panel
2. Navigate to MySQL Databases
3. Create a new database (e.g., `apprenticeship_portal`)
4. Create a database user with a strong password
5. Grant ALL PRIVILEGES to the user on the database

### 1.2 Import Schema

1. Open phpMyAdmin from your 34SP control panel
2. Select your newly created database
3. Click on the "Import" tab
4. Choose the file `/database/schema.sql` from your local project
5. Click "Go" to import the database structure

The schema includes two test accounts:
- **Employer**: email: `employer@techcorp.com`, password: `password`
- **Applicant**: email: `john@example.com`, password: `password`

**IMPORTANT**: Change or delete these accounts in production!

## Step 2: Configuration

### 2.1 Update Database Configuration

Edit `config/database.php` and update the following values:

```php
define('DB_HOST', 'your_db_host');      // Usually 'localhost'
define('DB_NAME', 'your_db_name');      // Your database name
define('DB_USER', 'your_db_user');      // Your database username
define('DB_PASS', 'your_db_password');  // Your database password
```

### 2.2 Update Site Configuration

Edit `config/config.php` and update:

```php
define('SITE_URL', 'https://yourdomain.com');
define('SITE_EMAIL', 'your@email.com');
```

### 2.3 Set Error Reporting

For production, change in `config/config.php`:

```php
error_reporting(0);
ini_set('display_errors', 0);
```

## Step 3: File Upload via FileZilla

### 3.1 Connect to Server

1. Open FileZilla
2. Enter your 34SP FTP credentials:
   - Host: Your FTP server address
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: 21 (or as provided by 34SP)
3. Click "Quickconnect"

### 3.2 Upload Files

1. On the remote site (right panel), navigate to your web root directory
   - Usually `/public_html/` or `/htdocs/`
2. On the local site (left panel), navigate to your project directory
3. Select all project files and folders
4. Right-click and select "Upload"
5. Wait for all files to upload

### 3.3 Set File Permissions

Right-click on these directories and set permissions to **755**:
- `/uploads/`
- `/uploads/cv/`
- `/uploads/documents/`

For extra security, create empty `index.html` files in upload directories:
```html
<!-- Prevent directory listing -->
```

## Step 4: Apache Configuration

The `.htaccess` file is already included and will:
- Enable clean URLs
- Redirect HTTP to HTTPS (if SSL is configured)
- Prevent direct access to sensitive files
- Set security headers

If you encounter issues, ensure mod_rewrite is enabled on your server.

## Step 5: Testing

### 5.1 Test Database Connection

Visit: `https://yourdomain.com/`

If you see the home page without errors, the database connection is working.

### 5.2 Test User Authentication

1. Try logging in with the test accounts
2. Test registration with a new account
3. Verify email validation works

### 5.3 Test File Uploads

1. Log in as an applicant
2. Go to Profile
3. Try uploading a CV (PDF, DOC, or DOCX)
4. Verify the file appears in `/uploads/cv/`

### 5.4 Test Core Functionality

**For Applicants:**
- Browse apprenticeships
- Apply to an apprenticeship
- View applications

**For Employers:**
- Create an apprenticeship listing
- Add custom form fields
- View and manage applications
- Update application statuses

## Step 6: Security Hardening

### 6.1 Change Default Passwords

Delete or change the sample user accounts in the database:

```sql
DELETE FROM users WHERE email IN ('employer@techcorp.com', 'john@example.com');
```

### 6.2 Update Session Configuration

In `config/config.php`, consider adding:

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // Only if using HTTPS
ini_set('session.use_strict_mode', 1);
```

### 6.3 Enable HTTPS

1. Obtain an SSL certificate from your hosting provider (many offer free Let's Encrypt certificates)
2. Enable HTTPS in your 34SP control panel
3. The `.htaccess` file will automatically redirect HTTP to HTTPS

### 6.4 Protect Configuration Files

Ensure the `.htaccess` file includes:

```apache
<FilesMatch "^(config|database)\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Step 7: Backup Strategy

### 7.1 Database Backups

Set up regular MySQL database backups:
- Use phpMyAdmin Export feature
- Or configure automated backups in 34SP control panel
- Recommended frequency: Daily

### 7.2 File Backups

Backup the following regularly:
- `/uploads/` directory (contains user CVs)
- Configuration files
- Database

## Troubleshooting

### Issue: "Database connection failed"

**Solution:**
- Verify database credentials in `config/database.php`
- Ensure database exists
- Check that database user has proper permissions

### Issue: "500 Internal Server Error"

**Solution:**
- Check Apache error logs in your hosting control panel
- Verify `.htaccess` syntax
- Ensure file permissions are correct (755 for directories, 644 for files)
- Check PHP version compatibility

### Issue: File uploads failing

**Solution:**
- Verify upload directory permissions (755)
- Check PHP upload_max_filesize setting
- Ensure uploads directory exists and is writable

### Issue: CSS/JavaScript not loading

**Solution:**
- Clear browser cache
- Verify file paths in templates/shared/header.php
- Check that CDN resources are accessible

### Issue: Email functions not working

**Solution:**
- Configure PHP mail settings on your server
- Consider using a third-party email service (SendGrid, Mailgun)
- Update email configuration in `config/config.php`

## Support

For technical issues related to:
- **Hosting**: Contact 34SP support
- **Application**: Check the production document or create an issue in the repository

## Next Steps

After successful deployment:

1. **Customize branding**: Update logos, colors, and site name
2. **Add content**: Create initial apprenticeship listings
3. **Test thoroughly**: Have test users go through the full application flow
4. **Monitor**: Check error logs regularly
5. **Update**: Keep PHP and dependencies up to date

## Additional Resources

- [34SP Knowledge Base](https://www.34sp.com/support)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

---

**Deployment Checklist:**

- [ ] Database created and schema imported
- [ ] Configuration files updated with production credentials
- [ ] All files uploaded via FTP
- [ ] File permissions set correctly
- [ ] Test accounts removed or passwords changed
- [ ] HTTPS enabled and working
- [ ] File upload functionality tested
- [ ] Email notifications configured (if implemented)
- [ ] Backup strategy in place
- [ ] Error logging configured
- [ ] Site tested on multiple browsers and devices

---

**Congratulations!** Your Apprenticeship Portal is now live! 🎉
