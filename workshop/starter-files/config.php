<?php
/**
 * تنظیمات اتصال به دیتابیس
 * -------------------------------------------------
 * روی لوکال‌هاست (XAMPP) مقادیر پیش‌فرض زیر کار می‌کند.
 * بعد از انتقال به هاست، این چهار مقدار را با اطلاعاتی که
 * در cPanel → MySQL Databases ساختید جایگزین کنید.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'mysite_db');   // نام دیتابیس
define('DB_USER', 'root');        // روی هاست: نام کاربر دیتابیس
define('DB_PASS', '');            // روی هاست: رمز کاربر دیتابیس

function db() {
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
