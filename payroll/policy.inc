<?php

function buildTabs($policyid, $tab)
{
	echo "<ul class='policy-tabs-list'>";
	if ($tab == 'general')
		echo "<li><span class='active' aria-current='page'>" . tr("General") . "</span></li>";
	else
		echo "<li><a href='policy.php?policyid=" . urlencode($policyid) . "'>" . tr("General") . "</a></li>";

	buildPolicyTab($policyid, $tab, 'payitems', 'policy_payitems.php', tr("Pay items"));
	buildPolicyTab($policyid, $tab, 'attributes', 'policy_attributes.php', tr("Attributes"));
	buildPolicyTab($policyid, $tab, 'selectors', 'policy_groups.php', tr("Selectors"));
	echo "</ul>";
}

function buildPolicyTab($policyid, $currentTab, $tab, $href, $label)
{
	if ($currentTab == $tab) {
		echo "<li><span class='active' aria-current='page'>" . htmlspecialchars($label) . "</span></li>";
	} else if (isEmpty($policyid)) {
		echo "<li><span class='disabled' aria-disabled='true' title='" . htmlspecialchars(tr("Save the policy first")) . "'>" . htmlspecialchars($label) . "</span></li>";
	} else {
		echo "<li><a href='" . htmlspecialchars($href) . "?policyid=" . urlencode($policyid) . "'>" . htmlspecialchars($label) . "</a></li>";
	}
}

function requirePolicyId($policyid)
{
	$value = (string)$policyid;
	if ($value === '' || !ctype_digit($value) || intval($value) < 1) {
		header("Location: policies.php?error=invalid_policy");
		die;
	}

	$policyid = intval($value);
	if (findValue("select count(*) from policy where policyid=$policyid", 0) == 0) {
		header("Location: policies.php?error=invalid_policy");
		die;
	}

	return $policyid;
}

?>
