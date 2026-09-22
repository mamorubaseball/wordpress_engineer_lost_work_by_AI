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

  document.querySelectorAll('.aje-nav-group').forEach(function (group, index) {
    let button = group.querySelector('.aje-nav-toggle');

    if (!button) {
      const link = group.querySelector(':scope > a');
      if (!link) return;

      const heading = document.createElement('div');
      heading.className = 'aje-nav-group-head';
      link.parentNode.insertBefore(heading, link);
      heading.appendChild(link);

      button = document.createElement('button');
      button.type = 'button';
      button.className = 'aje-nav-toggle';
      button.setAttribute('aria-expanded', 'true');
      button.setAttribute('aria-label', link.textContent.trim() + 'のメニューを開閉');
      button.innerHTML = '<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>';
      heading.appendChild(button);

      const subnav = group.querySelector(':scope > .aje-subnav');
      if (subnav) {
        if (!subnav.id) subnav.id = 'aje-subnav-generated-' + (index + 1);
        button.setAttribute('aria-controls', subnav.id);
      }
      group.classList.add('is-expanded');
    }

    button.addEventListener('click', function () {
      const group = button.closest('.aje-nav-group');
      if (!group) return;

      const expanded = group.classList.toggle('is-expanded');
      button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    });
  });

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

  const copyButton = document.querySelector('[data-aje-copy-url]');

  if (copyButton && navigator.clipboard) {
    copyButton.addEventListener('click', function () {
      navigator.clipboard.writeText(window.location.href).then(function () {
        const status = document.querySelector('.aje-copy-status');
        if (status) {
          status.textContent = 'URLをコピーしました';
        }
      });
    });
  }
})();
