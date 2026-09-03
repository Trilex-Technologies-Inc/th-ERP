<?php
include('include.php');
include('policy.inc');

checkPermission(PERMISSION_CONFIGURATE_PAYROLL);
$periodid = getCurrentPeriod();

$del_attributeid = getParam('del_attributeid');
if (!isEmpty($del_attributeid)) {
	sql("delete from attribute_value where attributeid=$del_attributeid");
	$sql = "
	delete from attribute
	where attributeid=$del_attributeid";
	sql($sql);
}

if (isSave()) {
	$periodstart = findValue("select unix_timestamp(starttime) 
	                          from payperiod where periodid=$periodid");
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$attributeid = getParam("attributeid_$i");
		$name = getParam("name_$i");
		$description = getParam("description_$i");
		$type = prepNull(getParam("type_$i"));
		sql("
		update attribute set 
			name='$name',
			description='$description', 
			type=$type 
		where attributeid=$attributeid");
		$value = getParam("value_$i");
		if ($value != getParam("old_value_$i")) {
			sql("insert into attribute_value (attributeid, fromtime, regtime, value)
				 values ($attributeid, from_unixtime($periodstart), now(), $value)");
		}		
		$i++;
	}
	$name_new = getParam('name_new');
	$description_new = getParam('description_new');
	if (!isEmpty($name_new)) {
		$type_new = prepNull(getParam("type_new"));
		$sql = "
		insert into attribute (name, description, type)
		values ('$name_new', '$description_new', $type_new)";
		sql($sql);
		$attributeid = insert_id();
		$value = prepNull(getParam('value_new'));
		sql("insert into attribute_value (attributeid, fromtime, regtime, value)
			 values ($attributeid, from_unixtime($periodstart), now(), $value)");
	}
}

$sql = "
select
  a.attributeid,
  name,
  description,
  description,
  value,
  type
from attribute a
left outer join attribute_value v on v.attributeid=a.attributeid and regtime=
(select max(regtime) from attribute_value v2 where v2.attributeid=v.attributeid)
where a.object=" . ATTR_OBJECT_EMPLOYEE;

$rs = query($sql);

$types = array();
$types[] = array(ATTRIBUTE_TYPE_NUMERIC, tr("Numeric"));
$types[] = array(ATTRIBUTE_TYPE_BOOLEAN, tr("Boolean"));
$types[] = array(ATTRIBUTE_TYPE_CHOICE, tr("Choice"));

?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Attributes") ?></title>
<?php
styleSheet();
include_common();
?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
</head>
<body>

<?php
menubar("configuration.php");
title(tr("Attributes"));
?>

<form action="attributes.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white py-3">
<h2 class="h5 fw-bold mb-1"><?php echo tr("Attributes") ?></h2>
<p class="text-secondary small mb-0"><?php echo tr("Employee payroll fields") ?></p>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr>
<th class="text-center" style="width: 90px;"><?php echo tr("Delete") ?></th>
<th><?php echo tr("Name") ?></th>
<th><?php echo tr("Description") ?></th>
<th style="width: 170px;"><?php echo tr("Default value") ?></th>
<th style="width: 190px;"><?php echo tr("Type") ?></th>
<th style="width: 120px;"><?php echo tr("Choice") ?></th>
</tr></thead>
<tbody>
<?php
$i = 0;
while ($row = fetch($rs)) {
	$attributeid = htmlspecialchars($row->attributeid);
	$name = htmlspecialchars($row->name);
    echo "<tr>";
    echo "<td class='text-center'>";
	deleteIcon("attributes.php?del_attributeid=$attributeid");
    echo "</td>";
    echo "<td>";
    echo "<input type='hidden' name='attributeid_$i' value='$attributeid'/>";
    echo "<input class='form-control' type='text' name='name_$i' value='$name'/>";
    echo "</td>";
    echo "<td>";
    textBox("description_$i", $row->description, 40);
    echo "</td>";
    echo "<td>";
    numberbox("value_$i", $row->value);
    hidden("old_value_$i", $row->value);
    echo "</td>";
    hidden("old_description_$i", $row->description);
	echo "<td>";
	combobox("type_$i", $types, $row->type, true);
	echo "</td>";
	echo "<td>";
	if ($row->type == ATTRIBUTE_TYPE_CHOICE) {
		echo "<a class='btn btn-outline-primary btn-sm' href='attribute.php?attributeid=$attributeid'>";
		echo tr("Choices") . "</a>";
	}
	echo "</td>";
    echo "</tr>";
    $i++;
}
hidden('count', $i);
?>
<tr class="table-light">
<td class="text-center text-secondary fw-semibold">+</td>
<td><input class="form-control" type="text" name="name_new" /></td>
<td><?php textBox('description_new', '', 40) ?></td>
<td><?php numberbox('value_new', '') ?></td>
<td><?php combobox('type_new', $types, null, true) ?></td>
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
