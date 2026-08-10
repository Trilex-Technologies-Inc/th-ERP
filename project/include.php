<?php
include_once('../include/therp_include.php');

function menubar()
{
	top0("Project");
	echo "<nav class='app-sidebar' aria-label='" . tr("Module navigation") . "'>";
	echo "<div class='app-nav-list'>";
			$percent = 33;
			menu('projects.php', 'Projects', $percent, true, $currentHref);
			menu('debit.php', 'Debit', $percent, true, $currentHref);
			//menu('configuration.php', 'Configuration', $percent, true, $currentHref);
			menu($helpUrl, 'Help', $percent, false, $currentHref);
	echo "</div>";
	echo "</nav>";
	showUpgrade();
}

?>
