<?php
include('include.php');
include('policy.inc');

$policyid = getParam('policyid');
$periodid = getCurrentPeriod();

$del_groupid = getParam('del_groupid');
if (!isEmpty($del_groupid)) {
	$sql = "
	delete from policy_accountgroup
	where policyid=$policyid and groupid=$del_groupid";
	sql($sql);
}

if (isSave()) {
	$groupid_new = getParam('groupid_new');
	if (!isEmpty($groupid_new)) {
		$sql = "
		insert into policy_accountgroup (policyid, groupid)
		values ($policyid, $groupid_new)";
		sql($sql);
	}
}

$sql = "
select
  pa.groupid,
  description
from policy_accountgroup pa
left outer join payaccountgroup_description d on d.groupid=pa.groupid and language='" . getLanguage() . "'
where policyid=$policyid
";

$rs = query($sql);

$attrs = rs2array(query("select a.groupid, description
                         from payaccountgroup a
                         "));

$description = findValue("select description from policy_description where policyid=$policyid and language='" . getLanguage() . "'");
?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Policy") ?></title>
<?php styleSheet() ?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
<script type="text/javascript" src="common.js"></script>
</head>
<body>

<?php
menubar("configuration.php", "policy");
title("<a href='policies.php'>Policies</a> > $description")
?>

	<div id="header">
	<?php buildTabs($policyid, 'selectors') ?>
	</div>
	<div id="main">
		<div id="contents">
<form action="policy_groups.php" method="POST">
<input type=hidden name=policyid value='<?php echo $policyid ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Account group") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=groupid_$i value='$row->groupid'/>";
    echo "<tr class='$class'>";
	deleteColumn("policy_groups.php?del_groupid=$row->groupid&policyid=$policyid");
    echo "<td>";
    echo formatCase($row->description);
    echo "</td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
<tr class='<?php echo $class ?>'>
<td/>
<td><?php comboBox('groupid_new', $attrs, null, true) ?></td>
</tr>
</table>
<br/>
<?php saveButton() ?>
</form>
		</div>
	</div>
<?php bottom() ?>
</body>
</html>
