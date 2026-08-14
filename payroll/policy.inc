<?php

function buildTabs($policyid, $tab)
{
	echo "<ul class='policy-tabs-list'>";
	if ($tab == 'general')
		echo "<li><span class='active'>" . tr("General") . "</span></li>";
	else
		echo "<li><a href='policy.php?policyid=$policyid'>" . tr("General") . "</a></li>";
	if ($tab == 'payitems')
		echo "<li><span class='active'>" . tr("Pay items") . "</span></li>";
	else
		echo "<li><a href='policy_payitems.php?policyid=$policyid'>" . tr("Pay items") . "</a></li>";
	if ($tab == 'attributes')
		echo "<li><span class='active'>" . tr("Attributes") . "</span></li>";
	else
		echo "<li><a href='policy_attributes.php?policyid=$policyid'>" . tr("Attributes") . " </a></li>";
	if ($tab == 'selectors')
		echo "<li><span class='active'>" . tr("Selectors") . "</span></li>";
	else
		echo "<li><a href='policy_groups.php?policyid=$policyid'>" . tr("Selectors") . " </a></li>";
	echo "</ul>";
}

?>
