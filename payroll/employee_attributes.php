<?php
	include('include.php');
	include('employee.inc');

	$employeeid = getParam('employeeid');
	$periodid = getCurrentPeriod();
	$policyid = getPolicy($employeeid, $periodid);
	$tabid = getParam("tabid");
	$tabname = findValue("select name from emp_tab where tabid=$tabid");

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

	<main class="employee-detail-page">
	<div class="employee-detail-tabs">
		<?php buildTabs($employeeid, "tab_$tabid") ?>
	</div>

<form action="employee_attributes.php" method="POST">
<?php
hidden('employeeid', $employeeid);
hidden('tabid', $tabid);
?>
<section class="employee-detail-card card border-0 shadow-sm">
	<div class="card-header bg-white employee-detail-card-header">
		<div><span><?php etr("Employee details") ?></span><h2><?php echo htmlspecialchars($tabname) ?></h2></div>
	</div>
<div class="card-body">
<div class="row g-3">
<?php
$i = 0;
while ($row = fetch($attributes)) {
	echo "<div class='col-12 col-md-6'><div class='employee-attribute-field'>";
	echo "<input type='hidden' name='attributeid_$i' value='" . htmlspecialchars($row->attributeid) . "'/>";
	echo "<label for='value_$i'>" . htmlspecialchars(formatCase($row->description)) . "</label>";
	echo "<div>";
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
	echo "<input type='hidden' name='old_value_$i' value='" . htmlspecialchars($row->value) . "'/>";
	$href = "employee_history.php?";
	$href .= "employeeid=$employeeid&attributeid=$row->attributeid";
	echo "<a href='" . htmlspecialchars($href) . "' title='" . htmlspecialchars(tr("History")) . "'>";
	image('history.gif');
	echo "</a></div></div></div>";
	$i++;
}
if ($i == 0)
	echo "<div class='col-12'><div class='alert alert-light border mb-0 text-secondary'>" . tr("No fields are configured for this tab") . ".</div></div>";
echo "<input type='hidden' name='count' value='$i'/>";
?>
</div>
</div>
<div class="card-footer bg-white d-flex justify-content-end py-3"><?php saveButton() ?></div>
</section>

</form>
	</main>

<?php bottom() ?>
</body>
