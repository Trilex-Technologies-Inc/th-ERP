<?php
include('../include/therp_include.php');

define('PERMISSION_ADMINISTRATE_USER', 1);

function menubar($currentHref = null)
{
	top0("Common");
	echo "<nav class='app-sidebar' aria-label='" . tr("Module navigation") . "'>";
	echo "<div class='app-nav-list'>";
			$percent = 20;
			menu('security.php', 'Security', $percent, true, $currentHref);
			menu('languages.php', 'Languages', $percent, true, $currentHref);
			menu('companyinfo.php', 'Company info', $percent, true, $currentHref);
			menu('help.php', 'Help', $percent, false, $currentHref);
	echo "</div>";
	echo "</nav>";
}
?>
