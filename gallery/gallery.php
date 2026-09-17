<?php
/**
 * گالری — همه‌ی کارهای تأییدشده با پیش‌نمایش زنده
 * هر پیش‌نمایش داخل iframe ایزوله (sandbox) اجرا می‌شود، پس کد یک نفر
 * نمی‌تواند به این صفحه یا به کار بقیه دست بزند.
 */
require __DIR__ . '/config.php';

$q    = trim($_GET['q'] ?? '');
$sql  = 'SELECT id, name, university, field, title, descr, live_url, code, is_featured, created_at
         FROM submissions WHERE is_approved = 1';
$args = [];
if ($q !== '') {
    $sql .= ' AND (name LIKE ? OR title LIKE ? OR university LIKE ? OR field LIKE ?)';
    $like = '%' . $q . '%';
    $args = [$like, $like, $like, $like];
}
$sql .= ' ORDER BY is_featured DESC, created_at DESC';

$st = db()->prepare($sql);
$st->execute($args);
$rows = $st->fetchAll();

$total = (int)db()->query('SELECT COUNT(*) AS c FROM submissions WHERE is_approved = 1')
                  ->fetch()['c'];

$page  = 'gallery';
$title = 'گالری — ' . SITE_NAME;
require __DIR__ . '/header.php';
?>

<h1>کارهای شرکت‌کنندگان</h1>
<p class="lead">هر کارت، سایت واقعی یک نفر از شماست که همین‌جا اجرا می‌شود.
روی هر کدام بزنید تا کامل ببینیدش.</p>

<form class="toolbar" method="get">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="جست‌وجو در نام، عنوان، دانشگاه یا رشته">
  <button class="btn sm" type="submit">جست‌وجو</button>
  <?php if ($q !== ''): ?><a class="btn sm" href="gallery.php">پاک کردن</a><?php endif; ?>
  <span class="count"><?= count($rows) ?> از <?= $total ?> کار</span>
  <a class="btn primary sm" href="index.php" style="margin-inline-start:auto">+ ارسال کار من</a>
</form>

<?php if (!$rows): ?>
  <div class="empty">
    <p style="font-size:38px;margin:0">🎨</p>
    <p><?= $q !== '' ? 'برای این جست‌وجو چیزی پیدا نشد.' : 'هنوز کاری ارسال نشده. اولین نفر باشید!' ?></p>
    <p><a class="btn primary" href="index.php">ارسال کار من</a></p>
  </div>
<?php else: ?>
  <div class="grid">
  <?php foreach ($rows as $r): ?>
    <a class="work" href="view.php?id=<?= (int)$r['id'] ?>">
      <div class="shot">
        <?php if (trim((string)$r['code']) !== ''): ?>
          <iframe sandbox="allow-scripts" loading="lazy" tabindex="-1" aria-hidden="true"
                  srcdoc="<?= e($r['code']) ?>"></iframe>
        <?php else: ?>
          <div class="none">🔗 این کار فقط لینک دارد</div>
        <?php endif; ?>
      </div>
      <div class="meta">
        <h3><?= e($r['title']) ?><?= $r['is_featured'] ? '<span class="badge">شاخص</span>' : '' ?></h3>
        <div class="who"><?= e($r['name']) ?>
          <?php if ($r['field'] || $r['university']): ?>
            <span>· <?= e(trim($r['field'] . ' ' . $r['university'])) ?></span>
          <?php endif; ?>
        </div>
        <?php if ($r['descr']): ?><p><?= e($r['descr']) ?></p><?php endif; ?>
      </div>
    </a>
  <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/footer.php'; ?>
