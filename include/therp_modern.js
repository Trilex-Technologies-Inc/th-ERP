(function () {
  var deleteNoticeKey = 'therp-delete-notice';

  window.thERPConfirmDelete = function (url, confirmMessage, successMessage) {
    if (!window.confirm(confirmMessage || 'Are you sure you want to delete this record?')) {
      return false;
    }

    var target = new URL(url, window.location.href);
    var form = document.createElement('form');
    form.method = 'post';
    form.action = target.pathname;
    target.searchParams.forEach(function (value, name) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      form.appendChild(input);
    });
    document.body.appendChild(form);

    try {
      window.sessionStorage.setItem(deleteNoticeKey, successMessage || 'Record deleted');
    } catch (error) {
      // Deletion still works when browser storage is unavailable.
    }
    form.submit();
    return false;
  };

  window.thERPConfirmDeleteSubmit = function (confirmMessage, successMessage) {
    if (!window.confirm(confirmMessage || 'Are you sure you want to delete this record?')) {
      return false;
    }
    try {
      window.sessionStorage.setItem(deleteNoticeKey, successMessage || 'Record deleted');
    } catch (error) {
      // Form submission still works when browser storage is unavailable.
    }
    return true;
  };

  function showDeleteNotice() {
    var message = '';
    try {
      message = window.sessionStorage.getItem(deleteNoticeKey) || '';
      window.sessionStorage.removeItem(deleteNoticeKey);
    } catch (error) {
      return;
    }
    if (!message) return;

    var notice = document.createElement('div');
    notice.className = 'alert alert-success shadow position-fixed top-0 end-0 m-3';
    notice.setAttribute('role', 'status');
    notice.style.zIndex = '1080';
    notice.textContent = message;
    document.body.appendChild(notice);
    window.setTimeout(function () {
      notice.remove();
    }, 4000);
  }

  window.thERPPrintDocument = function (url) {
    var printWindow = window.open(url, '_blank');
    if (!printWindow) {
      window.location.href = url;
      return false;
    }

    var printed = false;
    function openPrintDialog() {
      if (printed || printWindow.closed) return;
      printed = true;
      try {
        printWindow.focus();
        printWindow.print();
      } catch (error) {
        // Keep the PDF open so it can still be printed from the browser viewer.
      }
    }

    printWindow.addEventListener('load', function () {
      window.setTimeout(openPrintDialog, 300);
    }, { once: true });
    window.setTimeout(openPrintDialog, 1500);
    return false;
  };

  function hasHeaders(table) {
    return !!table.querySelector('th');
  }
  function isNavigation(table) {
    return !!table.querySelector('.app-nav-item') || table.classList.contains('menubar');
  }
  document.addEventListener('DOMContentLoaded', function () {
	showDeleteNotice();

	document.querySelectorAll('form').forEach(function (form) {
	  form.addEventListener('submit', function (event) {
		var selectedDeletes = form.querySelectorAll('input[type="checkbox"][name^="del_"]:checked');
		if (!selectedDeletes.length) return;
		if (!window.confirm('Are you sure you want to delete the selected records?')) {
		  event.preventDefault();
		  return;
		}
		try {
		  window.sessionStorage.setItem(deleteNoticeKey, 'Selected records deleted');
		} catch (error) {
		  // Form submission still works when browser storage is unavailable.
		}
	  });
	});
	document.querySelectorAll('input[type="text"], input[type="password"], input[type="email"], input[type="number"], input[type="date"], input[type="time"], textarea').forEach(function (control) {
		control.classList.add('form-control');
	});
	document.querySelectorAll('select').forEach(function (control) {
		control.classList.add('form-select');
	});
	document.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (control) {
		control.classList.add('form-check-input');
	});
	document.querySelectorAll('input[type="submit"], input[type="button"]').forEach(function (control) {
		control.classList.add('btn', 'btn-primary');
	});

    var toggle = document.querySelector('.sidebar-toggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        var open = document.body.classList.toggle('sidebar-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }

    document.querySelectorAll('table').forEach(function (table) {
      if (isNavigation(table) || table.classList.contains('calendar')) return;

      if (hasHeaders(table) || table.rows) {
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
