#!/usr/bin/env bash
set -e

cd /var/www/html

# MySQL lives off the machine, so it can still be accepting connections a few
# seconds after the container is up. Migrating against a database that is not
# ready yet fails the boot for no good reason.
for attempt in $(seq 1 15); do
    if /usr/bin/php artisan db:monitor --databases=mysql --max=1 >/dev/null 2>&1; then
        break
    fi
    echo "Waiting for the database (attempt ${attempt}/15)..."
    sleep 2
done

/usr/bin/php artisan migrate --force --no-ansi -q

# Reference data the product cannot function without: the brief wizard offers
# no skills without the taxonomy, and matching is done on skill tags. Both
# seeders are idempotent, so this is safe on every boot.
/usr/bin/php artisan db:seed --class=SkillSeeder --force --no-ansi -q
/usr/bin/php artisan db:seed --class=CreditBundleSeeder --force --no-ansi -q
