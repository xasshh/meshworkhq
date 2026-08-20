#!/usr/bin/env bash
set -e

# Only /data is a persistent volume on Fly. Everything else in the container
# filesystem is discarded on every deploy and machine restart, so uploaded
# avatars, company logos and verification documents must live on the volume or
# they are silently lost the first time the app redeploys. The database itself
# is MySQL and lives off the machine.

VOLUME_STORAGE="/data/storage/app"
APP_STORAGE="/var/www/html/storage/app"

mkdir -p "$VOLUME_STORAGE/public" "$VOLUME_STORAGE/private/verification"

# Carry across anything the image shipped with, then hand the directory over
# to the volume.
if [ -d "$APP_STORAGE" ] && [ ! -L "$APP_STORAGE" ]; then
    cp -rn "$APP_STORAGE/." "$VOLUME_STORAGE/" 2>/dev/null || true
    rm -rf "$APP_STORAGE"
fi

ln -sfn "$VOLUME_STORAGE" "$APP_STORAGE"

# public/storage -> storage/app/public, or every uploaded image 404s.
ln -sfn "$APP_STORAGE/public" /var/www/html/public/storage

chown -R www-data:www-data /data/storage /var/www/html/storage
