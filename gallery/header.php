<?php
// هدر مشترک. متغیر $page نام صفحه‌ی فعلی را می‌گیرد تا منو هایلایت شود.
$page = $page ?? '';
$title = $title ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="robots" content="noindex">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎨</text></svg>">
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700;900&family=JetBrains+Mono&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="top"><div class="wrap">
  <a class="logo" href="gallery.php"><i></i> گالری کارهای کارگاه</a>
  <nav>
    <a href="gallery.php" class="<?= $page === 'gallery' ? 'on' : '' ?>">گالری</a>
    <a href="index.php"   class="<?= $page === 'submit'  ? 'on' : '' ?>">ارسال کار من</a>
    <a href="admin.php"   class="<?= $page === 'admin'   ? 'on' : '' ?>">مدیریت</a>
  </nav>
</div></header>
<main><div class="wrap">
