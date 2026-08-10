<?php
include('../include/therp_include.php');

function menubar($currentHref = null, $helpUrl = 'http://therp.sf.net')
{
	top0("Stock/Inventory");
	echo "<nav class='app-sidebar' aria-label='" . tr("Module navigation") . "'>";
	echo "<div class='app-nav-list'>";
			$percent = 50;
			menu('productionorders.php', 'Production orders', $percent, true, $currentHref);
			//menu('configuration.php', 'Configuration', $percent, true, $currentHref);
			menu($helpUrl, 'Help', $percent, false, $currentHref);
	echo "</div>";
	echo "</nav>";
	showUpgrade();
}


?>
