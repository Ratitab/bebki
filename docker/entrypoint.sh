#!/bin/sh
set -e

echo "⏳ Waiting for PostgreSQL to be ready..."
until nc -z "${DB_HOST:-postgres}" "${DB_PORT:-5432}" 2>/dev/null; do
  echo "  postgres not ready yet — retrying in 2s..."
  sleep 2
done
echo "✅ PostgreSQL is ready."

# One run for database/migrations and all its subfolders: AppServiceProvider registers
# every subfolder. --force: no confirmation prompt when APP_ENV=production.
echo "⏳ Running migrations..."
php artisan migrate --force

echo "🌱 Seeding reference data..."
php artisan db:seed --force

# Passport keys must stay the same across restarts, or every login becomes invalid.
# Set PASSPORT_PRIVATE_KEY and PASSPORT_PUBLIC_KEY in the environment; only without
# them a key pair is generated, once, into storage/.
if [ -z "$PASSPORT_PRIVATE_KEY" ] && [ ! -f storage/oauth-private.key ]; then
  echo "🔑 Generating Passport keys..."
  php artisan passport:keys
fi

echo "🚀 Starting Octane..."
exec php artisan octane:start --host=0.0.0.0 --port=8585
