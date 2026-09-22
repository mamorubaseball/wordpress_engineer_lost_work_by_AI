(function () {
  const toggle = document.querySelector('[data-aje-menu-toggle]');
  const sidebar = document.querySelector('[data-aje-sidebar]');

  if (toggle && sidebar) {
    const mobile = window.matchMedia('(max-width: 780px)');
    const syncSidebar = function () {
      sidebar.inert = mobile.matches && !sidebar.classList.contains('is-open');
    };

    toggle.addEventListener('click', function () {
      const isOpen = sidebar.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      syncSidebar();
    });

    sidebar.addEventListener('click', function (event) {
      if (mobile.matches && event.target.closest('a')) {
        sidebar.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        syncSidebar();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
        sidebar.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        syncSidebar();
        toggle.focus();
      }
    });

    mobile.addEventListener('change', syncSidebar);
    syncSidebar();
  }

  const toc = document.querySelector('[data-aje-toc]');
  const prose = document.querySelector('.aje-prose');

  if (toc && prose) {
    const list = toc.querySelector('ol');
    const headings = prose.querySelectorAll('h2, h3');

    headings.forEach(function (heading, index) {
      if (!heading.id) {
        heading.id = 'section-' + (index + 1);
      }

      const item = document.createElement('li');
      const link = document.createElement('a');
      item.className = heading.tagName === 'H3' ? 'aje-toc-sub' : '';
      link.href = '#' + heading.id;
      link.textContent = heading.textContent;
      item.appendChild(link);
      list.appendChild(item);
    });

    if (headings.length > 1) {
      toc.hidden = false;
    }
  }
})();
