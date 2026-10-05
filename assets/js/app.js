/**
 * Mykid — main demo site script (index + Package A/B placeholders).
 * Kept intentionally small: only logs navigation for demo debugging.
 */
(function () {
  'use strict';

  var DEBUG = ['localhost', '127.0.0.1', ''].indexOf(window.location.hostname) !== -1;

  function log(fn, msg, data) {
    if (DEBUG) console.log('[MykidSite][' + fn + '] ' + msg, data || '');
  }

  function init() {
    log('init', 'START', { page: window.location.pathname });

    document.querySelectorAll('.pkg a.btn').forEach(function (link) {
      link.addEventListener('click', function () {
        log('packageClick', 'navigate', { href: link.getAttribute('href') });
      });
    });

    log('init', 'END');
  }

  document.addEventListener('DOMContentLoaded', init);
})();
