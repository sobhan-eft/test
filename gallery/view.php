<?php
/** نمایش کامل یک کار: پیش‌نمایش بزرگ + کد */
require __DIR__ . '/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$row = null;
if ($id) {
    $st = db()->prepare('SELECT * FROM submissions WHERE id = ? AND is_approved = 1 LIMIT 1');
    $st->execute([$id]);
    $row = $st->fetch() ?: null;
}

$page  = 'gallery';
$title = $row ? ($row['title'] . ' — ' . $row['name']) : 'پیدا نشد';
require __DIR__ . '/header.php';
?>

<?php if (!$row): ?>
  <div class="empty">
    <h1>این کار پیدا نشد</h1>
    <p>ممکن است حذف شده باشد یا هنوز تأیید نشده باشد.</p>
    <p><a class="btn primary" href="gallery.php">بازگشت به گالری</a></p>
  </div>
<?php else: ?>
  <p><a href="gallery.php">← بازگشت به گالری</a></p>
  <h1><?= e($row['title']) ?></h1>
  <p class="lead">
    <b><?= e($row['name']) ?></b>
    <?php if ($row['field'] || $row['university']): ?>
      · <?= e(trim($row['field'] . ' ' . $row['university'])) ?>
    <?php endif; ?>
    <?php if ($row['descr']): ?><br><?= e($row['descr']) ?><?php endif; ?>
  </p>

  <?php if ($row['live_url']): ?>
    <p><a class="btn primary" href="<?= e($row['live_url']) ?>" target="_blank" rel="noopener noreferrer">
      باز کردن سایت زنده ↗</a></p>
  <?php endif; ?>

  <?php if (trim((string)$row['code']) !== ''): ?>
    <div class="browser" style="margin-top:16px">
      <div class="chrome"><span class="d"></span><span class="d"></span><span class="d"></span>
        <span class="u"><?= e($row['live_url'] ?: 'پیش‌نمایش') ?></span></div>
      <iframe sandbox="allow-scripts" style="min-height:70vh"
              title="پیش‌نمایش کار" srcdoc="<?= e($row['code']) ?>"></iframe>
    </div>

    <h2 style="margin-top:26px">کد این سایت</h2>
    <p class="count">می‌توانید ببینید دیگران چطور کارشان را ساخته‌اند.</p>
    <p><button class="btn sm" id="copy">کپی کد</button></p>
    <pre class="code" id="src"><?= e($row['code']) ?></pre>
    <script>
      document.getElementById('copy').addEventListener('click', function () {
        var b = this, t = document.getElementById('src').textContent;
        var ok = function () { b.textContent = '✓ کپی شد';
                               setTimeout(function () { b.textContent = 'کپی کد'; }, 1500); };
        if (navigator.clipboard) { navigator.clipboard.writeText(t).then(ok, ok); } else { ok(); }
      });
    </script>
  <?php else: ?>
    <div class="note"><b>این کار فقط لینک دارد</b> فرستنده کد را نگذاشته و فقط آدرس سایتش را داده است.</div>
  <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
