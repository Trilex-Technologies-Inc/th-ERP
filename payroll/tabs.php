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
		values ($tabid_new, '$name_new', $no_of_cols_new)";
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
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white py-3">
	<h2 class="h5 fw-bold mb-1"><?php echo tr("Tabs") ?></h2>
	<p class="text-secondary small mb-0"><?php echo tr("Employee detail sections") ?></p>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr>
<th class="text-center" style="width: 90px;"><?php echo tr("Delete") ?></th>
<th class="text-end" style="width: 120px;"><?php echo tr("Id") ?></th>
<th><?php echo tr("Name") ?></th>
<th style="width: 180px;"><?php echo tr("No of columns") ?></th>
</tr></thead>
<tbody>
<?php
$i = 0;
while ($row = fetch($rs)) {
	$tabid = htmlspecialchars($row->tabid);
    echo "<tr>";
    echo "<td class='text-center'>";
	deleteIcon("tabs.php?del_tabid=$tabid");
    echo "</td>";
    echo "<td class='text-end font-monospace'>$tabid<input type='hidden' name='tabid_$i' value='$tabid'/></td>";
    echo "<td>";
    textBox("name_$i", $row->name);
    echo "</td>";
    echo "<td>";
    numberbox("no_of_cols_$i", $row->no_of_cols);
    echo "</td>";
    echo "</tr>";
    $i++;
}
hidden('count', $i);
?>
<tr class="table-light">
<td class="text-center text-secondary fw-semibold">+</td>
<td><?php textBox('tabid_new', '', 6) ?></td>
<td><?php textBox('name_new', '') ?></td>
<td><?php numberbox('no_of_cols_new', '') ?></td>
</tr>
</tbody>
</table>
</div>
<div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
	<?php saveButton() ?>
	<span class="text-secondary small"><?php echo $i ?> <?php echo tr("records") ?></span>
</div>
</div>
</form>
<?php bottom() ?>
</body>
</html>
