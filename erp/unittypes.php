<?php
include('include.php');
include('policy.inc');

$del_unittype = getParam('del_unittype');
if (!isEmpty($del_unittype)) {
	$sql = "
	delete from unittype
	where unittype=$del_unittype";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$unittype = getParam("unittype_$i");
		$description = getParam("description_$i");
		if ($description != getParam("old_description_$i")) {
			sql("update unittype set description='$description' where unittype=$unittype");
		}
		$i++;
	}
	$unittype_new = getParam('unittype_new');
	$description_new = getParam('description_new');
	if (!isEmpty($unittype_new)) {
		$sql = "
		insert into unittype (unittype, description)
		values ($unittype_new, '$description_new')";
		sql($sql);
	}
}

$sql = "
select
  a.unittype,
  description
from unittype a
";

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Unit types") ?></title>
<?php styleSheet() ?>
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Unit types"))
?>

<form action="unittypes.php" method="POST">
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Description") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=unittype_$i value='$row->unittype'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	deleteIcon("unittypes.php?del_unittype=$row->unittype");
    echo "</td>";
    echo "<td>$row->unittype</td>";
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
<td><?php textBox('unittype_new', '', 6) ?></td>
<td><?php textBox('description_new', '') ?></td>
</tr>
</table>
<br/>
<?php saveButton() ?>
</form>
<?php bottom() ?>
</body>
</html>
