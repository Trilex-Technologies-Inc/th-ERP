<?php
include('../include/therp_include.php');

function menubar($currentHref = null, $helpUrl = 'http://therp.sf.net')
{
	top0("Stock/Inventory");
	echo "<table width='100%' cellspacing=0 cellpadding=0 >";
	echo "<tr>";
	echo "<td>";
	echo "<table width='100%' class=menubar>";
		echo "<tr>";
			$percent = 50;
			menu('productionorders.php', 'Production orders', $percent, true, $currentHref);
			//menu('configuration.php', 'Configuration', $percent, true, $currentHref);
			menu($helpUrl, 'Help', $percent, false, $currentHref);
		echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	showUpgrade();
}


?>
