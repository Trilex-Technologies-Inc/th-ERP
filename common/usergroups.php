<?php
include('include.php');

checkPermission(PERMISSION_ADMINISTRATE_USER);

$del_groupid = getParam('del_groupid');
if (!isEmpty($del_groupid)) {
	sql("delete from usergroup_permission where groupid=$del_groupid");
	sql("delete from user_group where groupid=$del_groupid");
	$sql = "
	delete from usergroup
	where groupid=$del_groupid";
	sql($sql);
}

$sql = "
select
  a.groupid,
  description
from usergroup a
";

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("User groups") ?></title>
<?php styleSheet() ?>
</head>
<body>

<?php
menubar("security.php");
title(tr("User groups"));
?>
<center>
<form action="usergroups.php" method="POST">
<input type=hidden name=policyid value='<?php echo $policyid ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Description") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=groupid_$i value='$row->groupid'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	deleteIcon("usergroups.php?del_groupid=$row->groupid");
    echo "</td>";
    echo "<td>$row->groupid</td>";
    echo "<td>";
    echo "<a href='usergroup.php?groupid=$row->groupid'>$row->description</a>";
    echo "</td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
</table>
<br/>
<?php newButton("usergroup.php") ?>
</form>
</center>
<?php bottom() ?>
</body>
</html>
