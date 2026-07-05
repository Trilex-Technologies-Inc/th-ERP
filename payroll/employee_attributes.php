<?php
	include('include.php');
	include('employee.inc');

	$employeeid = getParam('employeeid');
	$periodid = getCurrentPeriod();
	$policyid = getPolicy($employeeid, $periodid);
	$tabid = getParam("tabid");

	if (isSave()) {
		$count = getParam('count');
		$starttime = findValue("
		select unix_timestamp(starttime) as starttime
		from period
		where periodid=$periodid");
		$i = 0;
		while ($i < $count) {
			$attributeid = getParam("attributeid_$i");
			$value = prepNull(getParam("value_$i"));
			$old_value = prepNull(getParam("old_value_$i"));
			if ($value != $old_value) {
				sql("insert into emp_attribute 
				     (employeeid, attributeid, fromtime, regtime, value)
					 values ($employeeid, $attributeid, from_unixtime($starttime), now(), $value)");
			}
			$i++;
		}
	}

	if (!isEmpty($employeeid)) {
	    $sql =
  		"select pa.policyid,
  		   pa.attributeid,
  		   ea.value,
  		   description,
  		   row,
  		   col,
		   type
		from policy_attribute pa
		join attribute a on a.attributeid=pa.attributeid
		join policy_attribute_value pav
		on pav.policyid=pa.policyid and pav.attributeid=pa.attributeid
		and pav.regtime = (select max(regtime) from policy_attribute_value pav2
		                        where pav2.policyid=pav.policyid and pav2.attributeid=pav.attributeid
		                        and pav2.fromtime<=now())	
		left outer join emp_attribute ea
		on pa.attributeid=ea.attributeid and ea.employeeid=$employeeid
		and ea.regtime = (select max(regtime) from emp_attribute ea2
		                    where ea2.employeeid=ea.employeeid
		                    and ea2.attributeid=ea.attributeid
		                    and ea2.fromtime<=now())
		where pa.policyid=$policyid
		and pav.value is null
		and tabid=$tabid
		order by row, col
		";
		$attributes = query($sql);

		$surname = findValue("
		select surname from employee where employeeid=$employeeid");
		$givenname = findValue("
		select givenname from employee where employeeid=$employeeid");
	}

?>
<?php head('Employee') ?>

<body>
<?php include("menubar.php") ?>
<?php title("$givenname $surname") ?>

	<div id="header">
	<?php buildTabs($employeeid, "tab_$tabid") ?>
	</div>
	<div id="main">
		<div id="contents">

<form action="employee_attributes.php" method="POST">
<?php
hidden('employeeid', $employeeid);
hidden('tabid', $tabid);
?>
<table>
<?php
$i = 0;
$line = 1;
$col = 1;
echo "<tr>";
while ($row = fetch($attributes)) {
	while ($row->row > $line) {
		echo "</tr><tr>";
		$line++;
		$col = 1;
	}
	while ($row->col > $col) {
		echo "<td/>";
		$col++;
	}
	echo "<input type=hidden name='attributeid_$i' value='$row->attributeid'/>";
	echo "<td class=label>". formatCase($row->description) . ":</td>";
	echo "<td>";
	if ($row->type == ATTRIBUTE_TYPE_BOOLEAN)
		checkbox("value_$i", $row->value);
	else if ($row->type == ATTRIBUTE_TYPE_CHOICE) {
		$choices = rs2array(query("
		select optionid, description
		from attribute_option 
		where attributeid=$row->attributeid
		order by optionid"));
		comboBox("value_$i", $choices, $row->value);
	} else
		numberBox("value_$i", $row->value);
	echo "<input type=hidden name='old_value_$i' value='$row->value'/>";
	$href = "employee_history.php?";
	$href .= "employeeid=$employeeid&attributeid=$row->attributeid";
	echo "&nbsp;<a href='$href'>";
	echo "<img src='../images/history.gif' border=0/>&nbsp;&nbsp;</td>";
	echo "</td>";
	$i++;
	$col++;
}
echo "</tr>";
echo "<input type=hidden name=count value='$i'/>";
?>
</table>
<br/>
<?php saveButton() ?>

</form>
		</div>
	</div>

</body>
