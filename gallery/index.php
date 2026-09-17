<?php
/**
 * صفحه‌ی ارسال کار
 * ─────────────────────────────────────────────
 * شرکت‌کننده مشخصاتش را وارد می‌کند، کد HTML را پیست می‌کند (یا فایلش را انتخاب
 * می‌کند)، پیش‌نمایش را همان‌جا می‌بیند و می‌فرستد.
 * بعد از ارسال یک «لینک ویرایش» می‌گیرد تا بتواند بعداً کارش را به‌روز کند.
 */
require __DIR__ . '/config.php';

$errors  = [];
$saved   = null;          // بعد از ثبت موفق پر می‌شود
$editing = null;          // اگر با لینک ویرایش آمده باشد

// ── حالت ویرایش: ?edit=<token>
$tok = $_GET['edit'] ?? $_POST['edit_token'] ?? '';
if ($tok !== '' && preg_match('/^[a-f0-9]{32}$/', $tok)) {
    $st = db()->prepare('SELECT * FROM submissions WHERE edit_token = ? LIMIT 1');
    $st->execute([$tok]);
    $editing = $st->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code_in    = $_POST['code']       ?? '';
    $name       = trim($_POST['name']       ?? '');
    $university = trim($_POST['university'] ?? '');
    $field      = trim($_POST['field']      ?? '');
    $title_in   = trim($_POST['title']      ?? '');
    $descr      = trim($_POST['descr']      ?? '');
    $live_url   = trim($_POST['live_url']   ?? '');
    $wcode      = trim($_POST['wcode']      ?? '');

    // ── اعتبارسنجی
    if (!hash_equals(WORKSHOP_CODE, $wcode)) {
        $errors[] = 'کد کارگاه درست نیست. کد را از مدرس بپرسید.';
    }
    if (mb_strlen($name) < 3) {
        $errors[] = 'نام و نام خانوادگی را کامل وارد کنید.';
    }
    if (mb_strlen($title_in) < 3) {
        $errors[] = 'عنوان سایت را وارد کنید.';
    }
    if ($live_url !== '' && !filter_var($live_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'آدرس سایت معتبر نیست. باید با http:// یا https:// شروع شود.';
    }
    if (trim($code_in) === '' && $live_url === '') {
        $errors[] = 'یا کد سایت را پیست کنید، یا آدرس سایت منتشرشده را بدهید.';
    }
    if (strlen($code_in) > MAX_CODE_BYTES) {
        $errors[] = 'حجم کد بیشتر از ' . round(MAX_CODE_BYTES / 1000) . ' کیلوبایت است.';
    }
    if (mb_strlen($descr) > 400) {
        $errors[] = 'توضیح کوتاه نباید بیشتر از ۴۰۰ کاراکتر باشد.';
    }

    // ── محدودیت تعداد ارسال (فقط برای ارسال جدید)
    if (!$errors && !$editing) {
        $st = db()->prepare(
            'SELECT COUNT(*) AS c FROM submissions
             WHERE ip_hash = ? AND created_at > (NOW() - INTERVAL 1 HOUR)');
        $st->execute([ip_hash()]);
        if ((int)$st->fetch()['c'] >= MAX_PER_HOUR) {
            $errors[] = 'تعداد ارسال‌های شما در این ساعت زیاد بوده. کمی بعد دوباره تلاش کنید.';
        }
    }

    if (!$errors) {
        if ($editing) {
            $st = db()->prepare(
                'UPDATE submissions
                    SET name = ?, university = ?, field = ?, title = ?,
                        descr = ?, live_url = ?, code = ?
                  WHERE edit_token = ?');
            $st->execute([$name, $university, $field, $title_in,
                          $descr, $live_url, $code_in, $editing['edit_token']]);
            $st = db()->prepare('SELECT * FROM submissions WHERE edit_token = ?');
            $st->execute([$editing['edit_token']]);
            $saved = $st->fetch();
        } else {
            $t  = token();
            $st = db()->prepare(
                'INSERT INTO submissions
                   (name, university, field, title, descr, live_url, code,
                    edit_token, is_approved, ip_hash)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $st->execute([$name, $university, $field, $title_in, $descr,
                          $live_url, $code_in, $t, AUTO_APPROVE ? 1 : 0, ip_hash()]);
            $st = db()->prepare('SELECT * FROM submissions WHERE edit_token = ?');
            $st->execute([$t]);
            $saved = $st->fetch();
        }
        $editing = $saved;
    }
}

// مقادیری که در فرم نشان داده می‌شوند
$v = static function (string $k, $default = '') use ($editing) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($GLOBALS['errors'])) {
        return $_POST[$k] ?? $default;
    }
    return $editing[$k] ?? $default;
};

$page  = 'submit';
$title = 'ارسال کار من — ' . SITE_NAME;
require __DIR__ . '/header.php';
?>

<?php if ($saved): ?>
  <div class="note g">
    <b>✓ کار شما ثبت شد</b>
    حالا در <a href="gallery.php">گالری</a> دیده می‌شود
    (<a href="view.php?id=<?= (int)$saved['id'] ?>">صفحه‌ی کار شما</a>).
  </div>
  <div class="note w">
    <b>این لینک را برای خودتان نگه دارید</b>
    اگر بعداً خواستید کارتان را به‌روز کنید، از همین لینک وارد شوید:
    <p style="margin:8px 0 0">
      <code class="ltr" style="white-space:normal;word-break:break-all"><?=
        e((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
          . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
          . strtok($_SERVER['REQUEST_URI'] ?? '/index.php', '?')
          . '?edit=' . $saved['edit_token']) ?></code>
    </p>
  </div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="note d"><b>ارسال نشد</b>
    <ul style="margin:6px 0 0;padding-inline-start:20px">
      <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<h1><?= $editing && $saved === null ? 'ویرایش کار من' : 'ارسال کار من' ?></h1>
<p class="lead">کد سایتی که ساختید را اینجا بگذارید تا در گالری کارگاه دیده شود.
پیش‌نمایش را همین‌جا، قبل از ارسال، می‌بینید.</p>

<form method="post" id="form">
  <input type="hidden" name="edit_token" value="<?= e($editing['edit_token'] ?? '') ?>">

  <div class="row">
    <label class="f"><span>نام و نام خانوادگی <em>*</em></span>
      <input type="text" name="name" required maxlength="100" value="<?= e($v('name')) ?>"></label>
    <label class="f"><span>عنوان سایت <em>*</em></span>
      <input type="text" name="title" required maxlength="150"
             placeholder="مثلاً: صفحه شخصی من" value="<?= e($v('title')) ?>"></label>
  </div>
  <div class="row">
    <label class="f"><span>دانشگاه</span>
      <input type="text" name="university" maxlength="150" value="<?= e($v('university')) ?>"></label>
    <label class="f"><span>رشته</span>
      <input type="text" name="field" maxlength="100" value="<?= e($v('field')) ?>"></label>
  </div>
  <label class="f"><span>توضیح کوتاه (اختیاری)</span>
    <input type="text" name="descr" maxlength="400"
           placeholder="در یک جمله بگویید چه ساختید" value="<?= e($v('descr')) ?>"></label>
  <label class="f"><span>آدرس سایت منتشرشده (اختیاری)</span>
    <input type="url" name="live_url" maxlength="300" dir="ltr"
           placeholder="https://your-site.ir" value="<?= e($v('live_url')) ?>"></label>

  <label class="f"><span>کد HTML سایت شما</span></label>
  <div class="toolbar">
    <input type="file" id="pick" accept=".html,.htm,.txt" style="flex:0 0 auto">
    <span class="count" id="size">۰ کاراکتر</span>
    <button type="button" class="btn sm" id="refresh">↻ به‌روزرسانی پیش‌نمایش</button>
  </div>

  <div class="split">
    <textarea class="code" name="code" id="code" spellcheck="false"
      placeholder="کل کدی که هوش مصنوعی به شما داد را اینجا پیست کنید..."><?= e($v('code')) ?></textarea>
    <div class="browser">
      <div class="chrome"><span class="d"></span><span class="d"></span><span class="d"></span>
        <span class="u">پیش‌نمایش</span></div>
      <iframe id="prev" sandbox="allow-scripts" title="پیش‌نمایش کار شما"></iframe>
    </div>
  </div>

  <label class="f"><span>کد کارگاه <em>*</em></span>
    <input type="text" name="wcode" required maxlength="40" dir="ltr"
           placeholder="کدی که مدرس گفت"></label>

  <p style="margin-top:20px">
    <button class="btn primary" type="submit">
      <?= $editing && $saved === null ? 'ذخیره‌ی تغییرات' : 'ارسال کار من' ?></button>
    <a class="btn" href="gallery.php">دیدن گالری</a>
  </p>
</form>

<div class="note"><b>نکته</b>
اگر سایتتان چند فایل دارد (<code>style.css</code> و <code>script.js</code> جدا)،
از هوش مصنوعی بخواهید همه را در یک فایل <code>index.html</code> ادغام کند:
«تمام CSS و JavaScript را داخل همین فایل HTML بگذار و یک فایل واحد بده».</div>

<script>
(function () {
  var ta = document.getElementById('code'),
      fr = document.getElementById('prev'),
      sz = document.getElementById('size'),
      t;
  function fa(n){ return String(n).replace(/\d/g, function(d){ return '۰۱۲۳۴۵۶۷۸۹'[d]; }); }
  function render() {
    fr.srcdoc = ta.value || '<!doctype html><html dir="rtl"><body style="font-family:Tahoma;' +
      'display:grid;place-items:center;height:100vh;margin:0;color:#8aa2b3">' +
      'کد را که پیست کنید، اینجا می‌بینیدش</body></html>';
    sz.textContent = fa(ta.value.length) + ' کاراکتر';
  }
  ta.addEventListener('input', function () { clearTimeout(t); t = setTimeout(render, 450); });
  document.getElementById('refresh').addEventListener('click', render);
  document.getElementById('pick').addEventListener('change', function (ev) {
    var f = ev.target.files[0];
    if (!f) return;
    var r = new FileReader();
    r.onload = function () { ta.value = r.result; render(); };
    r.readAsText(f, 'UTF-8');
  });
  render();
})();
</script>

<?php require __DIR__ . '/footer.php'; ?>
