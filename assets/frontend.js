(function () {
  document.addEventListener('click', function (event) {
    var link = event.target.closest('.fm-btn');
    if (!link) {
      return;
    }

    var href = link.getAttribute('href') || '';
    if (!href || href.indexOf('tel:') === 0 || href.indexOf('mailto:') === 0) {
      return;
    }

    event.preventDefault();
    window.open(href, '_blank', 'noopener,noreferrer');
  });
})();
