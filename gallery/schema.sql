-- ============================================================
--  گالری کارهای کارگاه
--  در phpMyAdmin: تب Import ← این فایل ← Go
--  (روی هاست، دیتابیس را اول از cPanel بسازید و دو خط اول را حذف کنید)
-- ============================================================

CREATE DATABASE IF NOT EXISTS workshop_gallery
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci;
USE workshop_gallery;

CREATE TABLE IF NOT EXISTS submissions (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,              -- نام و نام خانوادگی
  university  VARCHAR(150) DEFAULT NULL,          -- دانشگاه
  field       VARCHAR(100) DEFAULT NULL,          -- رشته
  title       VARCHAR(150) NOT NULL,              -- عنوان سایت
  descr       VARCHAR(400) DEFAULT NULL,          -- توضیح کوتاه
  live_url    VARCHAR(300) DEFAULT NULL,          -- آدرس سایت منتشرشده (اختیاری)
  code        MEDIUMTEXT   DEFAULT NULL,          -- کد HTML (اختیاری)
  edit_token  CHAR(32)     NOT NULL,              -- برای ویرایش بعدی توسط خود فرستنده
  is_approved TINYINT(1)   NOT NULL DEFAULT 1,
  is_featured TINYINT(1)   NOT NULL DEFAULT 0,    -- کار شاخص، اول گالری
  ip_hash     CHAR(64)     DEFAULT NULL,
  created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_listing (is_approved, is_featured, created_at),
  INDEX idx_token (edit_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;
