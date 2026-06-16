#!/bin/sh

set -e

cd /var/www/html

if [ ! -f "artisan" ]; then
    echo "=> Setting up Laravel project..."
    if [ -d "/tmp/laravel-template" ]; then
        cp -rn /tmp/laravel-template/. .
        echo "=> Laravel template copied"
    else
        echo "=> Installing Laravel via composer..."
        composer create-project laravel/laravel /tmp/laravel-temp --prefer-dist 11.* --no-interaction
        cp -rn /tmp/laravel-temp/. .
        rm -rf /tmp/laravel-temp
    fi
fi

if [ ! -f "vendor/autoload.php" ]; then
    echo "=> Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if ! grep -q "laravel/sanctum" composer.json; then
    echo "=> Installing Laravel Sanctum..."
    composer require laravel/sanctum "^4.0" --no-interaction
fi

if [ ! -f ".env" ]; then
    echo "=> Copying .env.example to .env..."
    cp .env.example .env
fi

if [ -z "$APP_KEY" ] || echo "$APP_KEY" | grep -q "PLACEHOLDER"; then
    echo "=> Generating application key..."
    php artisan key:generate
fi

if [ ! -d "storage/framework/cache" ]; then
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
fi

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "=> Waiting for database..."
until php -r "
\$host = getenv('DB_HOST') ?: 'mysql';
\$port = getenv('DB_PORT') ?: 3306;
\$db = getenv('DB_DATABASE') ?: 'recruit';
\$user = getenv('DB_USERNAME') ?: 'recruit';
\$pass = getenv('DB_PASSWORD') ?: 'recruit123';
try {
    \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
    echo 'OK';
} catch (PDOException \$e) {
    exit(1);
}
" > /dev/null 2>&1; do
    echo "   Database not ready yet, waiting..."
    sleep 3
done
echo "   Database is ready!"

echo "=> Running migrations..."
php artisan migrate --force

echo "=> Seeding database..."
php artisan db:seed --force

echo ""
echo "============================================="
echo "   社团招新系统后端启动成功！"
echo "   访问地址: http://localhost:8000"
echo "============================================="
echo ""

exec php artisan serve --host=0.0.0.0 --port=8000
