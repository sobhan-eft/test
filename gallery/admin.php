<?php
/**
 * پنل مدیریت — فقط برای مدرس
 * ورود با رمزی که در config.php ذخیره شده. تأیید/مخفی‌کردن، شاخص‌کردن،
 * حذف، و خروجی CSV از فهرست شرکت‌کنندگان.
 */
require __DIR__ . '/config.php';
session_start();

$err = '';

// ── خروج
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// ── ورود
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
    if (password_verify($_POST['pass'], ADMIN_HASH)) {
        session_regenerate_id(true);
        $_SESSION['ok']  = true;
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        header('Location: admin.php');
        exit;
    }
    $err = 'رمز درست نیست.';
    usleep(400000); // کمی تأخیر، برای اینکه حدس زدن رمز سخت‌تر شود
}

$in = !empty($_SESSION['ok']);

// ── عملیات‌ها (فقط بعد از ورود و با توکن معتبر)
if ($in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do'])) {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $err = 'درخواست معتبر نبود. صفحه را دوباره باز کنید.';
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $map = [
                'approve'  => 'UPDATE submissions SET is_approved = 1 WHERE id = ?',
                'hide'     => 'UPDATE submissions SET is_approved = 0 WHERE id = ?',
                'feature'  => 'UPDATE submissions SET is_featured = 1 WHERE id = ?',
                'unfeature'=> 'UPDATE submissions SET is_featured = 0 WHERE id = ?',
                'delete'   => 'DELETE FROM submissions WHERE id = ?',
            ];
            if (isset($map[$_POST['do']])) {
                db()->prepare($map[$_POST['do']])->execute([$id]);
                header('Location: admin.php');
                exit;
            }
        }
    }
}

// ── خروجی CSV
if ($in && isset($_GET['csv'])) {
    $rows = db()->query('SELECT id, name, university, field, title, live_url,
                                is_approved, created_at
                         FROM submissions ORDER BY created_at')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="workshop-submissions.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // تا اکسل فارسی را درست باز کند
    fputcsv($out, ['شناسه','نام','دانشگاه','رشته','عنوان سایت','آدرس','تأییدشده','تاریخ']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['name'], $r['university'], $r['field'],
                       $r['title'], $r['live_url'],
                       $r['is_approved'] ? 'بله' : 'خیر', $r['created_at']]);
    }
    fclose($out);
    exit;
}

$page  = 'admin';
$title = 'مدیریت — ' . SITE_NAME;
require __DIR__ . '/header.php';
?>

<?php if (!$in): ?>
  <h1>ورود مدرس</h1>
  <?php if ($err): ?><div class="note d"><?= e($err) ?></div><?php endif; ?>
  <form method="post" class="card" style="max-width:420px">
    <label class="f"><span>رمز عبور</span>
      <input type="password" name="pass" required autofocus></label>
    <p style="margin:16px 0 0"><button class="btn primary" type="submit">ورود</button></p>
  </form>
  <div class="note w" style="max-width:640px"><b>رمز را عوض کنید</b>
    رمز پیش‌فرض <code>admin1405</code> است. برای تغییرش این را در ترمینال بزنید و نتیجه را
    در <code>config.php</code> جایگزین <code>ADMIN_HASH</code> کنید:
    <pre class="code" style="margin-top:8px">php -r "echo password_hash('رمز-جدید', PASSWORD_DEFAULT);"</pre>
  </div>
<?php else:
  $rows = db()->query('SELECT id, name, university, field, title, live_url, code,
                              is_approved, is_featured, created_at
                       FROM submissions ORDER BY created_at DESC')->fetchAll();
  $shown = 0;
  foreach ($rows as $r) { if ($r['is_approved']) { $shown++; } }
  $csrf = e($_SESSION['csrf']);
?>
  <h1>مدیریت کارها</h1>
  <?php if ($err): ?><div class="note d"><?= e($err) ?></div><?php endif; ?>
  <div class="toolbar">
    <span class="count">مجموع <?= count($rows) ?> کار · <?= $shown ?> تا در گالری دیده می‌شود</span>
    <a class="btn sm" href="?csv=1" style="margin-inline-start:auto">⬇ خروجی CSV</a>
    <a class="btn sm" href="gallery.php">دیدن گالری</a>
    <a class="btn sm danger" href="?logout=1">خروج</a>
  </div>

  <?php if (!$rows): ?>
    <div class="empty">هنوز کاری ارسال نشده است.</div>
  <?php else: ?>
  <div class="scrollx"><table class="t">
    <tr><th>#</th><th>فرستنده</th><th>عنوان</th><th>وضعیت</th><th>تاریخ</th><th>کارها</th></tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><b><?= e($r['name']) ?></b><br>
          <span class="count"><?= e(trim($r['field'] . ' ' . $r['university'])) ?></span></td>
        <td><a href="view.php?id=<?= (int)$r['id'] ?>"><?= e($r['title']) ?></a>
          <?php if ($r['live_url']): ?><br>
            <a class="count ltr" href="<?= e($r['live_url']) ?>" target="_blank"
               rel="noopener noreferrer"><?= e($r['live_url']) ?></a><?php endif; ?></td>
        <td>
          <?= $r['is_approved'] ? '<span class="badge ok">در گالری</span>'
                                : '<span class="badge hid">مخفی</span>' ?>
          <?= $r['is_featured'] ? '<span class="badge">شاخص</span>' : '' ?>
          <?= trim((string)$r['code']) === '' ? '<br><span class="count">بدون کد</span>' : '' ?>
        </td>
        <td class="count ltr"><?= e($r['created_at']) ?></td>
        <td>
          <form method="post" style="display:inline">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <?php if ($r['is_approved']): ?>
              <button class="btn sm" name="do" value="hide">مخفی کن</button>
            <?php else: ?>
              <button class="btn sm" name="do" value="approve">تأیید کن</button>
            <?php endif; ?>
            <?php if ($r['is_featured']): ?>
              <button class="btn sm" name="do" value="unfeature">از شاخص دربیار</button>
            <?php else: ?>
              <button class="btn sm" name="do" value="feature">شاخص کن</button>
            <?php endif; ?>
          </form>
          <form method="post" style="display:inline"
                onsubmit="return confirm('این کار برای همیشه حذف شود؟')">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn sm danger" name="do" value="delete">حذف</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table></div>
  <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
