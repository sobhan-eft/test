// منوی همبرگری در موبایل
document.querySelector('.burger').addEventListener('click', function () {
  document.querySelector('.menu').classList.toggle('open');
});

// بستن منو بعد از کلیک روی لینک
document.querySelectorAll('.menu a').forEach(function (a) {
  a.addEventListener('click', function () {
    document.querySelector('.menu').classList.remove('open');
  });
});

// اسکرول نرم
document.querySelectorAll('a[href^="#"]').forEach(function (a) {
  a.addEventListener('click', function (e) {
    var el = document.querySelector(a.getAttribute('href'));
    if (!el) return;
    e.preventDefault();
    el.scrollIntoView({ behavior: 'smooth' });
  });
});

// انیمیشن نمایان‌شدن هنگام اسکرول
document.querySelectorAll('.sec').forEach(function (s) { s.classList.add('reveal'); });
var io = new IntersectionObserver(function (entries) {
  entries.forEach(function (en) { if (en.isIntersecting) en.target.classList.add('show'); });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
