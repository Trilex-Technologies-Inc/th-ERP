<?php
include('../include/therp_include.php');

define('METHOD_CASH', 1);
define('METHOD_CARD', 2);

function menubar($currentHref = null, $helpUrl = 'http://therp.sf.net')
{
	top0("Sales");
	echo "<nav class='app-sidebar' aria-label='" . tr("Module navigation") . "'>";
	sidebarHomeLink();
	echo "<div class='app-nav-list'>";
			$percent = 20;
			menu('index.php', 'Sales', $percent, true, $currentHref);
			menu('customers.php', 'Customers', $percent, true, $currentHref);
			menu('period.php', 'Period', $percent, true, $currentHref);
			menu('configuration.php', 'Configuration', $percent, true, $currentHref);
			menu($helpUrl, 'Help', $percent, false, $currentHref);
	echo "</div>";
	echo "</nav>";
	showUpgrade();
}


?>
