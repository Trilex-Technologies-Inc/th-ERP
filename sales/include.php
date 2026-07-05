<?php
include('../include/therp_include.php');

define('METHOD_CASH', 1);
define('METHOD_CARD', 2);

function menubar($currentHref = null, $helpUrl = 'http://therp.sf.net')
{
	top0("Sales");
	echo "<table width='100%' cellspacing=0 cellpadding=0 >";
	echo "<tr>";
	echo "<td>";
	echo "<table width='100%' class=menubar>";
		echo "<tr>";
			$percent = 20;
			menu('index.php', 'Sales', $percent, true, $currentHref);
			menu('customers.php', 'Customers', $percent, true, $currentHref);
			menu('period.php', 'Period', $percent, true, $currentHref);
			menu('configuration.php', 'Configuration', $percent, true, $currentHref);
			menu($helpUrl, 'Help', $percent, false, $currentHref);
		echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	showUpgrade();
}


?>