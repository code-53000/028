#!/bin/sh

cd /var/www/html

echo "============================================"
echo "   正在启动社团招新系统后端..."
echo "============================================"

if [ ! -f "artisan" ]; then
    echo "=> 检测到缺少 Laravel 核心文件，从模板复制..."
    if [ -d "/tmp/laravel-template" ]; then
        echo "   复制 Laravel 核心文件（不覆盖自定义代码）..."
        
        cp -f /tmp/laravel-template/artisan . 2>/dev/null || true
        
        if [ ! -d "public" ]; then
            cp -rn /tmp/laravel-template/public . 2>/dev/null || true
        fi
        
        if [ ! -d "bootstrap" ]; then
            cp -rn /tmp/laravel-template/bootstrap . 2>/dev/null || true
        fi
        
        if [ ! -d "vendor" ]; then
            cp -rn /tmp/laravel-template/vendor . 2>/dev/null || true
        fi
        
        if [ ! -d "resources" ]; then
            cp -rn /tmp/laravel-template/resources . 2>/dev/null || true
        fi
        
        if [ ! -f "composer.json" ]; then
            cp -f /tmp/laravel-template/composer.json . 2>/dev/null || true
        fi
        
        if [ ! -f "composer.lock" ]; then
            cp -f /tmp/laravel-template/composer.lock . 2>/dev/null || true
        fi
        
        if [ ! -f "package.json" ]; then
            cp -f /tmp/laravel-template/package.json . 2>/dev/null || true
        fi
        
        if [ ! -f "vite.config.js" ]; then
            cp -f /tmp/laravel-template/vite.config.js . 2>/dev/null || true
        fi
        
        if [ ! -f "phpunit.xml" ]; then
            cp -f /tmp/laravel-template/phpunit.xml . 2>/dev/null || true
        fi
        
        if [ ! -f ".env.example" ]; then
            cp -f /tmp/laravel-template/.env.example . 2>/dev/null || true
        fi
        
        if [ ! -d "tests" ]; then
            cp -rn /tmp/laravel-template/tests . 2>/dev/null || true
        fi
        
        if [ ! -d "database/migrations" ]; then
            mkdir -p database
            cp -rn /tmp/laravel-template/database/migrations database/ 2>/dev/null || true
            cp -rn /tmp/laravel-template/database/factories database/ 2>/dev/null || true
            cp -f /tmp/laravel-template/database/seeders/DatabaseSeeder.php database/seeders/ 2>/dev/null || true
        fi
        
        if [ ! -d "config" ]; then
            cp -rn /tmp/laravel-template/config . 2>/dev/null || true
        fi
        
        if [ ! -f "routes/web.php" ]; then
            mkdir -p routes
            cp -f /tmp/laravel-template/routes/web.php routes/ 2>/dev/null || true
            cp -f /tmp/laravel-template/routes/console.php routes/ 2>/dev/null || true
        fi
        
        if [ ! -d "app" ]; then
            cp -rn /tmp/laravel-template/app . 2>/dev/null || true
        fi
        
        if [ -f "artisan" ]; then
            echo "   Laravel 核心文件复制完成"
        else
            echo "   警告: artisan 复制失败，尝试完整复制..."
            for f in /tmp/laravel-template/* /tmp/laravel-template/.[!.]* /tmp/laravel-template/..?*; do
                if [ -e "$f" ] && [ ! -e "$(basename $f)" ]; then
                    cp -rf "$f" . 2>/dev/null || true
                fi
            done
        fi
    else
        echo "   模板目录不存在，通过 composer 安装 Laravel..."
        composer create-project laravel/laravel /tmp/laravel-temp --prefer-dist 11.* --no-interaction || true
        if [ -d "/tmp/laravel-temp" ]; then
            cp -rn /tmp/laravel-temp/artisan . 2>/dev/null || true
            cp -rn /tmp/laravel-temp/public . 2>/dev/null || true
            cp -rn /tmp/laravel-temp/bootstrap . 2>/dev/null || true
            cp -rn /tmp/laravel-temp/vendor . 2>/dev/null || true
            cp -rn /tmp/laravel-temp/resources . 2>/dev/null || true
            rm -rf /tmp/laravel-temp
        fi
    fi
fi

if [ ! -f "artisan" ]; then
    echo "ERROR: artisan 文件仍然不存在！目录内容如下："
    ls -la
    echo "============================================"
    echo "   启动失败：缺少 Laravel 核心文件"
    echo "============================================"
    sleep 3
    exit 1
fi

echo "=> 检查 vendor 目录..."
if [ ! -f "vendor/autoload.php" ]; then
    echo "   安装 composer 依赖..."
    composer install --no-interaction --prefer-dist --optimize-autoloader || true
fi

if ! grep -q "laravel/sanctum" composer.json 2>/dev/null; then
    echo "=> 安装 Laravel Sanctum..."
    composer require laravel/sanctum "^4.0" --no-interaction || true
fi

if [ ! -f ".env" ]; then
    echo "=> 复制 .env 文件..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
    else
        cp /tmp/laravel-template/.env.example .env 2>/dev/null || true
    fi
fi

if [ -z "$APP_KEY" ] || grep -q "APP_KEY=$" .env 2>/dev/null || grep -q "base64:PLAC" .env 2>/dev/null; then
    echo "=> 生成应用密钥..."
    php artisan key:generate --force || true
fi

echo "=> 确保目录存在并设置权限..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

chmod -R 777 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "=> 清除旧缓存..."
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

echo "=> 等待数据库连接..."
DB_READY=0
for i in $(seq 1 30); do
    if php -r "
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
" > /dev/null 2>&1; then
        DB_READY=1
        break
    fi
    echo "   数据库未就绪 ($i/30)，等待中..."
    sleep 2
done

if [ $DB_READY -ne 1 ]; then
    echo "ERROR: 数据库连接超时！"
    sleep 2
fi

echo "=> 数据库就绪，执行数据库迁移..."
php artisan migrate --force || echo "警告: 迁移执行可能有问题，继续启动..."

echo "=> 灌入测试数据..."
php artisan db:seed --force || echo "警告: 数据填充可能有问题，继续启动..."

echo ""
echo "============================================"
echo "   社团招新系统后端启动成功！"
echo "   访问地址: http://localhost:8000"
echo "   API 前缀:  http://localhost:8000/api"
echo "============================================"
echo ""

exec php artisan serve --host=0.0.0.0 --port=8000
