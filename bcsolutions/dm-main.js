(function ($) {
  'use strict';

  var WidgetDigitalMarketingHandler = function ($scope, $) {
      var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      
      // 1. Fade-up stagger
      var pending = [];
      var flushT = null;
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (!en.isIntersecting) return;
          io.unobserve(en.target);
          pending.push(en.target);
          clearTimeout(flushT);
          flushT = setTimeout(function () {
            pending.forEach(function (el, i) {
              setTimeout(function () { el.classList.add('in'); }, i * 90);
            });
            pending = [];
          }, 40);
        });
      }, { threshold: 0.12 });
      
      $scope[0].querySelectorAll('.au').forEach(function (el) { 
          io.observe(el); 
      });

      // 2. Counter berjalan
      var cio = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (!en.isIntersecting) return;
          cio.unobserve(en.target);
          var el = en.target;
          var end = +el.getAttribute('data-to');
          if (reduced) { el.textContent = end; return; }
          var t0 = null;
          function tick(ts) {
            if (!t0) t0 = ts;
            var k = Math.min(1, (ts - t0) / 1600);
            el.textContent = Math.round(end * (1 - Math.pow(1 - k, 3)));
            if (k < 1) requestAnimationFrame(tick);
          }
          requestAnimationFrame(tick);
        });
      }, { threshold: 0.6 });
      
      $scope[0].querySelectorAll('.dm-count').forEach(function (el) { 
          cio.observe(el); 
      });

      // 3. Marquee teks stroke + clients carousel (duplikasi track)
      $scope[0].querySelectorAll('.dm-track, .dm-clients-track').forEach(function (mq) {
        mq.innerHTML += mq.innerHTML;
      });

      // 4. Filter case studies
      var filters = $scope[0].querySelector('.dm-filters');
      if (filters) {
          var items = $scope[0].querySelectorAll('.dm-work-item');
          filters.addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            
            // Remove active from all buttons
            filters.querySelectorAll('button').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            
            var f = btn.dataset.f;
            items.forEach(function (it) {
              var show = f === 'all' || it.dataset.cat === f;
              if (show) {
                it.classList.remove('dm-hide');
                it.style.opacity = '0';
                it.style.transform = 'translateY(14px)';
                requestAnimationFrame(function () {
                  it.style.opacity = '1';
                  it.style.transform = 'none';
                });
              } else {
                it.classList.add('dm-hide');
              }
            });
          });
      }
  };

  // Init Elementor Hook
  $(window).on('elementor/frontend/init', function () {
      elementorFrontend.hooks.addAction('frontend/element_ready/bcs_digital_marketing.default', WidgetDigitalMarketingHandler);
  });
})(jQuery);
