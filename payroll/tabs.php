<?php
include('include.php');

checkPermission(PERMISSION_CONFIGURATE_PAYROLL);

$del_tabid = getParam('del_tabid');
if (!isEmpty($del_tabid)) {
	$sql = "
	delete from emp_tab
	where tabid=$del_tabid";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$tabid = getParam("tabid_$i");
		$name = getParam("name_$i");
		$no_of_cols = getParam("no_of_cols_$i");
		sql("
		update emp_tab set name='$name', no_of_cols=$no_of_cols
		where tabid=$tabid");
		$i++;
	}
	$tabid_new = getParam('tabid_new');
	$name_new = getParam('name_new');
	$no_of_cols_new = getParam("no_of_cols_new");
	if (!isEmpty($tabid_new)) {
		$sql = "
		insert into emp_tab (tabid, name, no_of_cols)
		values ($tabid_new, '$name_new', $no_of_cols)";
		sql($sql);
	}
}

$sql = "
select
  a.tabid,
  name,
  no_of_cols
from emp_tab a
";

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Tabs") ?></title>
<?php 
styleSheet();
include_common();
?>
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Tabs"))
?>

<form action="tabs.php" method="POST">
<input type=hidden name=policyid value='<?php echo $policyid ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Name") ?></th>
<th><?php echo tr("No of columns") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=tabid_$i value='$row->tabid'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	deleteIcon('emp_tabs.php?del_tabid=$row->tabid');
    echo "</td>";
    echo "<td>$row->tabid</td>";
    echo "<td>";
    textBox("name_$i", $row->name);
    echo "</td>";
    echo "<td>";
    numberbox("no_of_cols_$i", $row->no_of_cols);
    echo "</td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
<tr>
<td/>
<td><?php textBox('tabid_new', '', 6) ?></td>
<td><?php textBox('name_new', '') ?></td>
<td><?php numberbox('no_of_cols_new', '') ?></td>
</tr>
</table>
<br/>
<?php saveButton() ?>
</form>
<?php bottom() ?>
</body>
</html>
