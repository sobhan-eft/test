<?php
/**
 * دریافت و ذخیره‌ی پیام فرم تماس
 * -------------------------------------------------
 * نکات امنیتی که در این فایل رعایت شده:
 *  ۱) استفاده از PDO + Prepared Statement  → جلوگیری از SQL Injection
 *  ۲) اعتبارسنجی ورودی‌ها با filter_var
 *  ۳) پاک‌سازی خروجی با htmlspecialchars   → جلوگیری از XSS
 *  ۴) نگه‌داشتن اطلاعات اتصال در فایل جداگانه config.php
 */

require __DIR__ . '/config.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || mb_strlen($name) < 3) {
        $errors[] = 'لطفاً نام خود را کامل وارد کنید.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'آدرس ایمیل معتبر نیست.';
    }
    if ($subject === '') {
        $errors[] = 'موضوع پیام را وارد کنید.';
    }
    if (mb_strlen($message) < 10) {
        $errors[] = 'متن پیام باید حداقل ۱۰ کاراکتر باشد.';
    }

    if (!$errors) {
        try {
            $sql  = 'INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)';
            $stmt = db()->prepare($sql);          // ← Prepared Statement
            $stmt->execute([$name, $email, $subject, $message]);
            $success = true;
        } catch (PDOException $e) {
            // در محیط واقعی خطا را فقط لاگ کنید، نه نمایش به کاربر
            error_log('DB error: ' . $e->getMessage());
            $errors[] = 'خطایی در ثبت پیام رخ داد. لطفاً بعداً دوباره تلاش کنید.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>نتیجه ارسال پیام</title>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body{font-family:'Vazirmatn',Tahoma,sans-serif;background:#f6f9fc;color:#12232e;
         display:grid;place-items:center;height:100vh;margin:0;line-height:2}
    .box{background:#fff;border:1px solid #dfe8ef;border-radius:16px;padding:34px 40px;
         max-width:520px;text-align:center;box-shadow:0 12px 30px rgba(14,79,110,.08)}
    .ok{color:#0f9d58;font-size:52px}
    .err{color:#d93025;font-size:52px}
    ul{text-align:right;color:#d93025;padding-inline-start:20px}
    a{display:inline-block;margin-top:18px;background:#f0b429;color:#2a1c00;
      padding:10px 26px;border-radius:30px;text-decoration:none;font-weight:700}
  </style>
</head>
<body>
  <div class="box">
  <?php if ($success): ?>
    <div class="ok">✓</div>
    <h2>پیام شما با موفقیت ثبت شد</h2>
    <p>ممنون از تماس شما، در اولین فرصت پاسخ می‌دهم.</p>
  <?php elseif ($errors): ?>
    <div class="err">!</div>
    <h2>پیام ارسال نشد</h2>
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <h2>این صفحه مستقیماً قابل مشاهده نیست</h2>
    <p>لطفاً از طریق فرم تماس سایت اقدام کنید.</p>
  <?php endif; ?>
    <a href="index.html#contact">بازگشت به سایت</a>
  </div>
</body>
</html>
