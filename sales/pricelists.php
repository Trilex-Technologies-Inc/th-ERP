<?php
include('include.php');
include('policy.inc');

checkPermission(PERMISSIONID_SELL);

$del_listid = getParam('del_listid');
if (!isEmpty($del_listid)) {
	$sql = "
	delete from pricelist
	where listid=$del_listid";
	sql($sql);
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$listid = getParam("listid_$i");
		$description = getParam("description_$i");
		if ($description != getParam("old_description_$i")) {
			sql("update pricelist set description='$description' where listid=$listid");
		}
		$i++;
	}
	$listid_new = getParam('listid_new');
	$description_new = getParam('description_new');
	if (!isEmpty($listid_new)) {
		$sql = "
		insert into pricelist (listid, description)
		values ($listid_new, '$description_new')";
		sql($sql);
	}
}

$sql = "
select
  a.listid,
  description
from pricelist a
";

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Price lists") ?></title>
<?php styleSheet() ?>
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Price lists"))
?>

<form action="pricelists.php" method="POST">
<div class="table-responsive">
<table class="table table-sm table-striped table-hover align-middle w-100">
<thead>
<tr>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Description") ?></th>
</tr>
</thead>
<tbody>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=listid_$i value='$row->listid'/>";
    echo "<tr class='$class'>";
    echo "<td class='text-center'>";
	deleteIcon("pricelists.php?del_listid=$row->listid");
    echo "</td>";
    echo "<td>$row->listid</td>";
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
<td></td>
<td><?php textBox('listid_new', '', 6) ?></td>
<td><?php textBox('description_new', '') ?></td>
</tr>
</tbody>
</table>
</div>
<br/>
<?php saveButton() ?>
</form>
<?php bottom() ?>
</body>
</html>
