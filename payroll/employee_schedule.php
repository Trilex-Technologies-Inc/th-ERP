<?php
	include('include.php');
	include('employee.inc');
	
	checkPermission(PERMISSION_ADMINISTRATE_EMPLOYEES);

	$employeeid = getParam("employeeid");
	$rec = find("select givenname, surname from employee where employeeid=$employeeid");
	$name = $rec->givenname . " " . $rec->surname;

	if (!isEmpty(getParam("delete_last"))) {
		sql("delete from emp_schedule where employeeid=$employeeid order by valid_from desc limit 1");
	}

	if (isSave()) {
		$i = 0;
		$rowcount = getParam("rowcount");
		while ($i < $rowcount) {
		    $from = getParam("from_$i");
			$delete = getParam("del_" . $i);
			if ($delete == "on") {
				sql("delete from emp_schedule where employeeid=$employeeid and unix_timestamp(valid_from)=$from");
			}
			$i++;
		}

		$scheduleid = getParam("scheduleid_new");
		if (!isEmpty($scheduleid)) {
		    $to = getParam("valid_to_new");
		    $to = isEmpty($to) ? "null" : ("from_unixtime(" . parseDate($to) . ")");
		    $from = parseDate(getParam("valid_from_new"));
			$sql = "insert into emp_schedule ";
			$sql .= "(employeeid, valid_from, valid_to, scheduleid) ";
			$sql .= "values ($employeeid, from_unixtime($from), $to, $scheduleid) ";
			sql($sql);
		}
	}
?>

<?php head('Employee') ?>

<body>
<?php menubar("employees.php") ?>
<?php title($name) ?>

	<main class="employee-detail-page">
	<div class="employee-detail-tabs">
		<?php buildTabs($employeeid, 'schedule') ?>
	</div>

<form action="employee_schedule.php" method="POST">
<input type="hidden" name="employeeid" value="<?php echo htmlspecialchars($employeeid) ?>"/>
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<div><h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($name) ?></h2><p class="text-secondary small mb-0"><?php echo tr("Schedule") ?></p></div>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr>
<th class="text-center" style="width: 90px;"><?php echo tr("Delete") ?></th>
<th><?php echo tr("From") ?></th>
<th><?php echo tr("To") ?></th>
<th><?php echo tr("Schedule") ?></th>
</tr></thead>
<tbody>
<?php

$sql = "select ";
$sql .= "unix_timestamp(valid_from) as valid_from, ";
$sql .= "unix_timestamp(valid_to) as valid_to, ";
$sql .= "es.scheduleid, ";
$sql .= "description ";
$sql .= "from emp_schedule es, schedule s ";
$sql .= "where employeeid=$employeeid ";
$sql .= "  and es.scheduleid=s.scheduleid ";
$sql .= "order by valid_from ";
$q = sql($sql);
$i = 0;
$lastdate = 0;
$numrows = num_rows($q);
while ($rec = fetch($q)) {
	$from = $rec->valid_from;
	$lastdate = $rec->valid_to;
	echo "<tr>";
	echo "<td class='text-center'><input type='hidden' name='from_$i' value='" . htmlspecialchars($from) . "'/>";
	if ($i == $numrows-1)
	    echo "<input type='checkbox' name='del_$i'/>";
	echo "</td>";
	echo "<td>" . formatDate($from) . "</td>";
	echo "<td>" . formatDate($rec->valid_to) . "</td>";
	echo "<td>" . htmlspecialchars($rec->description) . "</td>";
	echo "</tr>\n";
	$i++;
}
echo "<input type='hidden' name='rowcount' value='$numrows'/>";
?>
<tr class="table-light">
<td class="text-center text-secondary fw-semibold">+</td>
<td>
<?php
if ($lastdate > 0) {
    $datestr = formatDate($lastdate);
    echo $datestr;
    echo "<input type=hidden name=valid_from_new value='$datestr'/>";
} else {
    datebox("valid_from_new");
}
?>
</td>
<td><?php datebox("valid_to_new") ?></td>
<td>
<select name="scheduleid_new">
<option value='null'>--- <?php etr("Select schedule") ?> ---</option>
<?php
$sql = <<<SQL
select
  scheduleid,
  description
from schedule
SQL;
$rs = query($sql);
while ($row = fetch($rs)) {
    echo "<option value='" . htmlspecialchars($row->scheduleid) . "'>" . htmlspecialchars($row->description) . "</option>";
}
?>
</select>
</td>
</tr>
</tbody>
</table>
</div>
<div class="card-footer bg-white d-flex flex-wrap justify-content-between gap-2 py-3">
	<div>
	<?php if ($numrows > 0) { ?>
		<button type="submit" name="delete_last" value="1" class="btn btn-outline-danger" onclick="return thERPConfirmDeleteSubmit(<?php echo htmlspecialchars(json_encode(tr('Delete the latest schedule assignment?')), ENT_QUOTES) ?>, <?php echo htmlspecialchars(json_encode(tr('Record deleted')), ENT_QUOTES) ?>)"><?php etr("Delete latest") ?></button>
	<?php } ?>
	</div>
	<input type="submit" name="save" value="<?php echo tr("Save") ?>"/>
</div>
</div>
</form>
	</main>
<?php bottom() ?>
</body>
