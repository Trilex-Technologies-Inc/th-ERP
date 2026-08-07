(function () {
  function hasFormControls(table) {
    return !!table.querySelector('input, select, textarea, button');
  }
  function hasHeaders(table) {
    return !!table.querySelector('th');
  }
  function isNavigation(table) {
    return !!table.querySelector('.app-nav-item') || table.classList.contains('menubar');
  }
  function isTinyLayout(table) {
    var rows = table.rows ? table.rows.length : 0;
    return rows <= 1 && !hasHeaders(table) && !hasFormControls(table);
  }

  document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.sidebar-toggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        var open = document.body.classList.toggle('sidebar-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }

    document.querySelectorAll('table').forEach(function (table) {
      if (isNavigation(table) || isTinyLayout(table)) return;

      if (hasFormControls(table) && !hasHeaders(table)) {
        table.classList.add('erp-form-table');
        return;
      }

      if (hasHeaders(table) || (!hasFormControls(table) && table.rows && table.rows.length > 1)) {
        table.classList.add('erp-data-table', 'table', 'table-hover', 'align-middle');
        if (!table.parentElement.classList.contains('erp-table-responsive')) {
          var wrapper = document.createElement('div');
          wrapper.className = 'erp-table-responsive table-responsive';
          table.parentNode.insertBefore(wrapper, table);
          wrapper.appendChild(table);
        }
      }
    });

    document.querySelectorAll('.app-nav-item a').forEach(function (link) {
      link.addEventListener('click', function () {
        document.body.classList.remove('sidebar-open');
      });
    });
  });
})();
