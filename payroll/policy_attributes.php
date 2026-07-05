<?php
include('include.php');
include('policy.inc');

$policyid = getParam('policyid');
$periodid = getCurrentPeriod();

$del_attributeid = getParam('del_attributeid');
if (!isEmpty($del_attributeid)) {
	sql("delete from policy_attribute where attributeid=$del_attributeid");
}

if (isSave()) {
	$periodstart = findValue("
	select unix_timestamp(starttime) 
	from payperiod where periodid=$periodid");
	$i = 0;
	$count = getParam("count");
	while ($i < $count) {
		$value = prepNull(getParam("value_$i"));
		$old_value = prepNull(getParam("old_value_$i"));
		$attributeid = getParam("attributeid_$i");
		if ($value != $old_value) {
			sql("insert into policy_attribute_value (policyid, attributeid, fromtime, regtime, value)
				 values ($policyid, $attributeid, from_unixtime($periodstart), now(), $value)");
		}
		$tabid = prepNull(getParam("tabid_$i"));
		$row = prepNull(getParam("row_$i"));
		$col = prepNull(getParam("col_$i"));
		$sql = "
		update policy_attribute
		set tabid=$tabid, row=$row, col=$col
		where policyid=$policyid and attributeid=$attributeid";
		sql($sql);
		$i++;
	}
	$attributeid_new = getParam('attributeid_new');
	if (!isEmpty($attributeid_new)) {
		$value_new = prepNull(getParam('value_new'));
		$count = findValue("
		select count(*) from policy_attribute 
		where policyid=$policyid and attributeid=$attributeid_new");
		if ($count == 0) { 
			$sql = "
			insert into policy_attribute (policyid, attributeid)
			values ($policyid, $attributeid_new)";
			sql($sql);
		}
		$sql = "
		insert into policy_attribute_value (policyid, attributeid, value, fromtime, regtime)
		values ($policyid, $attributeid_new, $value_new,from_unixtime($periodstart), now())";
		sql($sql);
	}
}

$sql = "
select
  a.attributeid,
  description,
  value,
  tabid,
  row,
  col
from policy_attribute_value pa
join attribute a on a.attributeid=pa.attributeid
join policy_attribute p on p.policyid=pa.policyid and p.attributeid=pa.attributeid
where pa.policyid=$policyid
and regtime = (select max(regtime) from policy_attribute_value pa2
                        where pa2.policyid=pa.policyid and pa2.attributeid=pa.attributeid
                        and pa2.fromtime<=now()
                        )
";

$rs = query($sql);

$attrs = rs2array(query("select a.attributeid, description
                         from attribute a"));
$tabs = rs2array(query("select tabid, name from emp_tab"));                         

$description = findValue("select description from policy_description where policyid=$policyid and language='" . getLanguage() . "'");
?>

<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Policy") ?></title>
<?php
styleSheet();
include_common();
?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
</head>
<body>

<?php
menubar("configuration.php", "policy");
title("<a href='policies.php'>Policies</a> > $description")
?>

	<div id="header">
	<?php buildTabs($policyid, 'attributes') ?>
	</div>
	<div id="main">
		<div id="contents">
<form action="policy_attributes.php" method="POST">
<input type=hidden name=policyid value='<?php echo $policyid ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Attribute") ?></th>
<th><?php echo tr("Default value") ?></th>
<th><?php echo tr("Tab") ?></th>
<th><?php echo tr("Row") ?></th>
<th><?php echo tr("Col") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=attributeid_$i value='$row->attributeid'/>";
    echo "<tr class='$class'>";
	deleteColumn("policy_attributes.php?del_attributeid=$row->attributeid&policyid=$policyid");
    echo "<td>";
    echo formatCase($row->description);
    echo "</td>";
    echo "<td>";
    echo numberBox("value_$i", $row->value);
    echo "</td>";
	hidden("old_value_$i", $row->value);
	echo "<td>";
	combobox("tabid_$i", $tabs, $row->tabid, true);
	echo "</td>";
	echo "<td>";
	numberbox("row_$i", $row->row);
	echo "</td>";
	echo "<td>";
	numberbox("col_$i", $row->col);
	echo "</td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
<tr class='<?php echo $class ?>'>
<td/>
<td><?php comboBox('attributeid_new', $attrs, null, true) ?></td>
<td><?php numberBox('value_new', '') ?></td>
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
