<?php
include_once('../include/therp_include.php');

function menubar()
{
	top0("Project");
	echo "<table width='100%' cellspacing=0 cellpadding=0 >";
	echo "<tr>";
	echo "<td>";
	echo "<table width='100%' class=menubar>";
		echo "<tr>";
			$percent = 33;
			menu('projects.php', 'Projects', $percent, true, $currentHref);
			menu('debit.php', 'Debit', $percent, true, $currentHref);
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