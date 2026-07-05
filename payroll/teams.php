<?php
include('include.php');
include('policy.inc');

checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

$del_teamid = getParam('del_teamid');
if (!isEmpty($del_teamid)) {
	$sql = "
	delete from team
	where teamid=$del_teamid";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$teamid = getParam("teamid_$i");
		$description = getParam("description_$i");
		if ($description != getParam("old_description_$i")) {
			sql("update team set description='$description' where teamid=$teamid");
		}
		$i++;
	}
	$teamid_new = getParam('teamid_new');
	$description_new = getParam('description_new');
	if (!isEmpty($teamid_new)) {
		$sql = "
		insert into team (teamid, description)
		values ($teamid_new, '$description_new')";
		sql($sql);
	}
}

$sql = "
select
  a.teamid,
  description
from team a
";

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Teams") ?></title>
<?php styleSheet() ?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Teams"))
?>

<form action="teams.php" method="POST">
<input type=hidden name=policyid value='<?php echo $policyid ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Description") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=teamid_$i value='$row->teamid'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	deleteIcon('teams.php?del_teamid=$row->teamid');
    echo "</td>";
    echo "<td>$row->teamid</td>";
    echo "<td>";
    textBox("description_$i", $row->description);
    echo "</td>";
    hidden("old_description_$i", $row->description);
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
<tr>
<td/>
<td><?php textBox('teamid_new', '', 6) ?></td>
<td><?php textBox('description_new', '') ?></td>
</tr>
</table>
<br/>
<?php saveButton() ?>
</form>
<?php bottom() ?>
</body>
</html>
