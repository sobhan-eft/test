-- ساخت دیتابیس (روی هاست معمولاً از cPanel ساخته می‌شود، این خط را آنجا اجرا نکنید)
CREATE DATABASE IF NOT EXISTS mysite_db
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_persian_ci;

USE mysite_db;

-- جدول پیام‌های فرم تماس
CREATE TABLE IF NOT EXISTS messages (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL,
  subject    VARCHAR(200) NOT NULL,
  message    TEXT         NOT NULL,
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
