# 🗄️ Database Backup & Restore Guide

## Overview

The Waste Management System includes an automated backup system that protects your database from data loss. Backups are stored in `storage/backups/` with timestamped filenames.

---

## 📋 Table of Contents

1. [Automatic Backups](#automatic-backups)
2. [Manual Backup](#manual-backup)
3. [Restore from Backup](#restore-from-backup)
4. [Docker Backup](#docker-backup)
5. [Change Backup Schedule](#change-backup-schedule)
6. [Cloud Storage (Optional)](#cloud-storage-optional)

---

## Automatic Backups

The system is configured to create automatic daily backups at **2:00 AM** using Laravel's task scheduler.

### Enable the Scheduler

For automatic backups to work, the Laravel scheduler must be running:

```bash
# Option 1: Add to system crontab (Linux/Mac)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# Option 2: Run manually (for testing)
php artisan schedule:run
```

### What Gets Backed Up?

- **SQLite**: The entire `database.sqlite` file is copied
- **MySQL**: All tables and data via `mysqldump` (compressed with gzip)
- **PostgreSQL**: All tables and data via `pg_dump` (compressed with gzip)

### Backup Retention

- Backups are automatically deleted after **7 days**
- You can change this in `app/Console/Commands/BackupDatabase.php`

---

## Manual Backup

### Using Artisan Command

```bash
# Create a backup
php artisan backup:database

# Create a backup AND clean up old ones
php artisan backup:database --cleanup
```

### Expected Output

```
🗄️  Starting database backup...
📦 SQLite backup saved: storage/backups/backup_2024-01-15_02-00-00.sqlite (45.50 KB)
✅ Database backup completed successfully!
```

---

## Restore from Backup

### SQLite Restore

```bash
# 1. Stop the application (optional, prevents data conflicts)
php artisan down

# 2. List available backups
ls -la storage/backups/

# 3. Copy backup file over the current database
cp storage/backups/backup_2024-01-15_02-00-00.sqlite database/database.sqlite

# 4. Bring the application back up
php artisan up

# 5. Clear cache (recommended)
php artisan cache:clear
php artisan config:clear
```

### MySQL Restore

```bash
# 1. Decompress the backup
gunzip storage/backups/backup_2024-01-15_02-00-00.sql.gz

# 2. Restore the database
mysql -u root -p waste_management < storage/backups/backup_2024-01-15_02-00-00.sql

# 3. Clean up
php artisan cache:clear
```

### PostgreSQL Restore

```bash
# 1. Decompress the backup
gunzip storage/backups/backup_2024-01-15_02-00-00.sql.gz

# 2. Restore the database
psql -U postgres -d waste_management < storage/backups/backup_2024-01-15_02-00-00.sql

# 3. Clean up
php artisan cache:clear
```

---

## Docker Backup

### Using the Backup Script

```bash
# Run backup inside the Docker container
docker exec waste-mgmt-app bash /var/www/html/docker/backup/backup.sh

# Schedule with host crontab (runs daily at 2:00 AM)
0 2 * * * docker exec waste-mgmt-app bash /var/www/html/docker/backup/backup.sh
```

### Using Artisan Inside Docker

```bash
docker exec waste-mgmt-app php artisan backup:database --cleanup
```

---

## Change Backup Schedule

Edit `routes/console.php` to change the backup schedule:

```php
// Daily at 2:00 AM (default)
Schedule::command('backup:database --cleanup')->dailyAt('02:00');

// Every 6 hours
Schedule::command('backup:database --cleanup')->everySixHours();

// Twice a day (8 AM and 8 PM)
Schedule::command('backup:database --cleanup')->twiceDaily(8, 20);

// Every hour (high-traffic applications)
Schedule::command('backup:database --cleanup')->hourly();
```

---

## Cloud Storage (Optional)

For production environments, consider storing backups in cloud storage:

### AWS S3

```bash
# 1. Install AWS CLI
pip install awscli

# 2. Configure credentials
aws configure

# 3. Sync backups to S3
aws s3 sync storage/backups/ s3://your-bucket/waste-management-backups/

# 4. Add to crontab (after backup)
0 3 * * * aws s3 sync /path/storage/backups/ s3://your-bucket/backups/
```

### Google Cloud Storage

```bash
# 1. Install gsutil
# 2. Sync backups
gsutil rsync storage/backups/ gs://your-bucket/waste-management-backups/
```

---

## Troubleshooting

| Issue | Solution |
|-------|---------|
| "Permission denied" | Run `chmod 755 storage/backups` |
| "mysqldump not found" | Install: `apt install default-mysql-client` |
| "Backup too large" | Increase PHP memory: `memory_limit=512M` |
| "No space left" | Reduce `$retentionDays` or add cloud storage |
| "Scheduler not running" | Verify crontab entry: `crontab -l` |
