#!/bin/bash
# =============================================================================
# Database Backup Script (Docker / Cron)
# =============================================================================
#
# This script can be used as a standalone backup solution, either:
# 1. Run manually inside the Docker container
# 2. Added to crontab for scheduled backups
#
# USAGE:
#   # Inside Docker container:
#   docker exec waste-mgmt-app bash /var/www/html/docker/backup/backup.sh
#
#   # Or add to crontab (runs daily at 2:00 AM):
#   0 2 * * * docker exec waste-mgmt-app bash /var/www/html/docker/backup/backup.sh
#
# =============================================================================

set -e  # Exit on any error

# ------------------------------------------
# Configuration
# ------------------------------------------
BACKUP_DIR="/var/www/html/storage/backups"
RETENTION_DAYS=7
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
DB_CONNECTION="${DB_CONNECTION:-sqlite}"

# Create backup directory
mkdir -p "$BACKUP_DIR"

echo "🗄️  Starting database backup..."
echo "   Database: $DB_CONNECTION"
echo "   Time:     $TIMESTAMP"

# ------------------------------------------
# Perform Backup
# ------------------------------------------
if [ "$DB_CONNECTION" = "sqlite" ]; then
    # SQLite: Simple file copy
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    BACKUP_FILE="$BACKUP_DIR/backup_${TIMESTAMP}.sqlite"

    if [ -f "$DB_FILE" ]; then
        cp "$DB_FILE" "$BACKUP_FILE"
        echo "✅ SQLite backup saved: $BACKUP_FILE"
    else
        echo "⚠️  SQLite database file not found: $DB_FILE"
        exit 1
    fi

elif [ "$DB_CONNECTION" = "mysql" ]; then
    # MySQL: Use mysqldump with gzip compression
    BACKUP_FILE="$BACKUP_DIR/backup_${TIMESTAMP}.sql.gz"

    mysqldump \
        --host="${DB_HOST:-mysql}" \
        --port="${DB_PORT:-3306}" \
        --user="${DB_USERNAME:-root}" \
        --password="${DB_PASSWORD:-secret}" \
        "${DB_DATABASE:-waste_management}" \
        | gzip > "$BACKUP_FILE"

    echo "✅ MySQL backup saved: $BACKUP_FILE"

elif [ "$DB_CONNECTION" = "pgsql" ]; then
    # PostgreSQL: Use pg_dump with gzip compression
    BACKUP_FILE="$BACKUP_DIR/backup_${TIMESTAMP}.sql.gz"

    PGPASSWORD="${DB_PASSWORD}" pg_dump \
        --host="${DB_HOST:-postgres}" \
        --port="${DB_PORT:-5432}" \
        --username="${DB_USERNAME:-postgres}" \
        "${DB_DATABASE:-waste_management}" \
        | gzip > "$BACKUP_FILE"

    echo "✅ PostgreSQL backup saved: $BACKUP_FILE"

else
    echo "❌ Unsupported database: $DB_CONNECTION"
    exit 1
fi

# ------------------------------------------
# Show backup size
# ------------------------------------------
BACKUP_SIZE=$(du -sh "$BACKUP_FILE" | cut -f1)
echo "📦 Backup size: $BACKUP_SIZE"

# ------------------------------------------
# Cleanup old backups (older than RETENTION_DAYS)
# ------------------------------------------
echo "🧹 Cleaning up backups older than $RETENTION_DAYS days..."
DELETED=$(find "$BACKUP_DIR" -name "backup_*" -type f -mtime +$RETENTION_DAYS -delete -print | wc -l)
echo "   Removed $DELETED old backup(s)"

echo ""
echo "========================================"
echo "✅ Backup completed successfully!"
echo "   File: $BACKUP_FILE"
echo "   Size: $BACKUP_SIZE"
echo "========================================"
