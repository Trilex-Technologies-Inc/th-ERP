<?php
include('../include/therp_include.php');

define('PERMISSION_ADMINISTRATE_USER', 1);

function menubar($currentHref = null)
{
	top0("Common");
	echo "<table width='100%' cellspacing=0 cellpadding=0 >";
	echo "<tr>";
	echo "<td>";
	echo "<table width='100%' class=menubar>";
		echo "<tr>";
			$percent = 20;
			menu('security.php', 'Security', $percent, true, $currentHref);
			menu('languages.php', 'Languages', $percent, true, $currentHref);
			menu('companyinfo.php', 'Company info', $percent, true, $currentHref);
			menu('help.php', 'Help', $percent, false, $currentHref);
		echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
}
?>
