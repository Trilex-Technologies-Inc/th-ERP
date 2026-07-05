<?php
include('../include/therp_include.php');

define('ACCOUNTID_CASH', 1010);
define('ACCOUNTID_SALES', 4100);
define('ACCOUNTID_COST_OF_SALES', 5000);
define('ACCOUNTID_RECEIVABLES', 1100);
define('ACCOUNTID_PAYABLES', 2100);
define('ACCOUNTID_INVENTORY', 1460);
define('ACCOUNTID_VAT_PAYABLE', 2300);
define('ACCOUNTID_VAT_RECEIVALE', 2310);

function menubar($currentHref = null)
{
	top0("Accounting");
	echo "<table width='100%' cellspacing=0 cellpadding=0 >";
	echo "<tr>";
	echo "<td>";
	echo "<table width='100%' class=menubar>";
		echo "<tr>";
			$percent = 20;
			menu('index.php', 'Register', $percent, true, $currentHref);
			menu('balance.php', 'Balance', $percent, true, $currentHref);
			menu('transactions.php', 'History', $percent, true, $currentHref);
			menu('configuration.php', 'Configuration', $percent, true, $currentHref);
			menu('help.php', 'Help', $percent, false, $currentHref);
		echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
}

?>
