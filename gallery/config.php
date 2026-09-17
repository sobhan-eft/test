<?php
/**
 * تنظیمات گالری کارهای کارگاه
 * ─────────────────────────────────────────────
 * فقط همین فایل را ویرایش کنید. بقیه‌ی فایل‌ها نیازی به تغییر ندارند.
 */

// ── دیتابیس (روی هاست از cPanel → MySQL Databases بگیرید)
define('DB_HOST', 'localhost');
define('DB_NAME', 'workshop_gallery');
define('DB_USER', 'root');
define('DB_PASS', '');

// ── کدی که سر کارگاه به بچه‌ها می‌گویید. بدون آن کسی نمی‌تواند چیزی بفرستد.
define('WORKSHOP_CODE', 'yazd1405');

// ── رمز پنل مدیریت.
//    این مقدار پیش‌فرض برای رمز «admin1405» است. حتماً عوضش کنید:
//    php -r "echo password_hash('رمز-دلخواه-شما', PASSWORD_DEFAULT);"
define('ADMIN_HASH', '$2y$12$C8EaiJsvNylRz/U2.fpgIu2l2oZhb6nhfEskBv8QDQhFAK93YEesO');

// ── نام کارگاه (در هدر صفحه‌ها نشان داده می‌شود)
define('SITE_NAME', 'گالری کارهای کارگاه طراحی سایت با هوش مصنوعی');

// ── تنظیمات
define('AUTO_APPROVE', true);   // true: کار بلافاصله در گالری دیده شود
define('MAX_CODE_BYTES', 300000); // حداکثر حجم کد ارسالی (۳۰۰ کیلوبایت)
define('MAX_PER_HOUR', 6);      // حداکثر ارسال از یک نفر در ساعت
define('IP_SALT', 'change-this-to-any-random-text'); // برای هش کردن IP

// ─────────────────────────────────────────────

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/** پاک‌سازی خروجی — هر متنی که از کاربر آمده باید از این رد شود */
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** شناسه‌ی بی‌نام فرستنده، برای محدودیت تعداد ارسال. خود IP ذخیره نمی‌شود. */
function ip_hash(): string {
    return hash('sha256', IP_SALT . ($_SERVER['REMOTE_ADDR'] ?? ''));
}

function token(): string {
    return bin2hex(random_bytes(16));
}
