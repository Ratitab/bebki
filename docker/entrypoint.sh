#!/bin/sh
set -e

echo "⏳ Waiting for PostgreSQL to be ready..."
until nc -z "${DB_HOST:-postgres}" "${DB_PORT:-5432}" 2>/dev/null; do
  echo "  postgres not ready yet — retrying in 2s..."
  sleep 2
done
echo "✅ PostgreSQL is ready."

echo "⏳ Running migrations..."
php artisan migrate --path=database/migrations
php artisan migrate --path=database/migrations/companies
php artisan migrate --path=database/migrations/users
php artisan migrate --path=database/migrations/categories
php artisan migrate --path=database/migrations/countries
php artisan migrate --path=database/migrations/payments

echo "🌱 Seeding reference data..."
php artisan db:seed

echo "🔑 Installing Passport keys (skip if already exist)..."
php artisan passport:keys --force 2>/dev/null || true

echo "🚀 Starting Octane..."
exec php artisan octane:start --host=0.0.0.0 --port=8585
