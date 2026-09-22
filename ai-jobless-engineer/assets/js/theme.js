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

  // Submenu accordion / toggle logic
  const navGroups = document.querySelectorAll('.aje-nav-group');
  navGroups.forEach(function (group) {
    const parentLink = group.querySelector(':scope > a');
    const subnav = group.querySelector('.aje-subnav');

    if (parentLink && subnav) {
      // Add toggle arrow indicator
      const arrow = document.createElement('span');
      arrow.className = 'aje-nav-arrow dashicons dashicons-arrow-down-alt2';
      arrow.setAttribute('aria-hidden', 'true');
      parentLink.appendChild(arrow);

      // Check if active
      const hasCurrent = subnav.querySelector('.is-current') || parentLink.classList.contains('is-current');
      if (hasCurrent) {
        group.classList.add('is-expanded');
      }

      parentLink.addEventListener('click', function (e) {
        e.preventDefault();
        const isExpanded = group.classList.toggle('is-expanded');
        parentLink.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
      });
    }
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
})();
