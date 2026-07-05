<?php
include('include.php');
include('policy.inc');

checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

$del_attributeid = getParam('del_attributeid');
if (!isEmpty($del_attributeid)) {
	$sql = "
	delete from attribute
	where attributeid=$del_attributeid";
	sql($sql);
}

$sql = "
select
  a.attributeid,
  name
from attribute a
where object=" . ATTR_OBJECT_PRODUCT;

$rs = query($sql);
?>

<html>
<head>
<?php metatag() ?>
<title>thERP - <?php echo tr("Product attributes") ?></title>
<?php styleSheet() ?>
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Product attributes"))
?>

<form action="attributes.php" method="POST">
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Name") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=attributeid_$i value='$row->attributeid'/>";
    echo "<tr class='$class'>";
    echo "<td align=center>";
	deleteIcon("attributes.php?del_attributeid=$row->attributeid");
    echo "</td>";
    echo "<td><a href='attribute.php?attributeid=$row->attributeid'>";
    echo $row->name;
    echo "</a></td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
</table>
<br/>
<?php newButton("attribute.php") ?>
</form>
<?php bottom() ?>
</body>
</html>
