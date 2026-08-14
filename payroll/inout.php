<?php
	include('include.php');
	include('employee.inc');
	include('schedule_functions.php');

	$employeeid = getCurrentEmployee();
	$periodid = getCurrentPeriod();
	$employeeMissing = isEmpty($employeeid);
	
	function pushButton($label, $cmd)
	{
		$width = 75;
		if (strlen($label) > 3)
			$width = 150;
		echo "<input type=submit name=$cmd value='$label' style='width: $width" . "px; height: 75px; font-size: 20pt'/>";
	}
	
	$types = array();
	$types[TIME_REGISTRATION_IN] = 'In';
	$types[TIME_REGISTRATION_START_BREAK] = 'Start break';
	$types[TIME_REGISTRATION_END_BREAK] = 'End break';
	$types[TIME_REGISTRATION_OUT] = 'Out';
	$type = null;
	foreach (array_keys($types) as $type2) {
		if (array_key_exists("cmd_$type2", $_POST)) {
			$type = $type2;
			break;
		}
	}
	if ($type != null && !$employeeMissing) {
		$date = parseDate(getParam('date'));
		$timeStr = getParam('time');
		$seconds = 0;
		if ($timeStr == getParam('org_time'))
			$seconds = getParam('seconds');
		$time = parseTime($timeStr);
		$time = mkdatetime($date, $time, $seconds);
		sql("insert into timeregistration (time, type, employeeid) 
		     values (from_unixtime($time), $type, $employeeid)");
	}
	
	$del_id = getParam('del_id');
	if (!isEmpty($del_id) && !$employeeMissing) {
		sql("delete from timeregistration where id=$del_id");
	}

	if (!$employeeMissing) {
		$givenname = findValue("select givenname from employee where employeeid=$employeeid");
		$surname = findValue("select surname from employee where employeeid=$employeeid");

		$lastType = findValue("select type
		                       from timeregistration r
							   where time=(select max(time)
							               from timeregistration r2
										   where r2.employeeid=r.employeeid)
					           and employeeid=$employeeid");
		$history = query("select id, unix_timestamp(time) as time, type
		                  from timeregistration
						  where employeeid=$employeeid
						  order by time desc
						  limit 5");
	}
	
	
	$now = time();
	$start = roundTime($now, TYPE_DAYS);
	$end = addTime($start, TYPE_DAYS, 1);
	$shifts = $employeeMissing ? array() : getEmployeeWorkshifts($employeeid, $start, $end);
	$shift_start = null;
	if (count($shifts) > 0) {
		$shift = $shifts[0];
		$shift_start = $shift[1];
		$shift_end = $shift[2];
	}
	
	
?>

<?php head('In / Out') ?>

<body>
<?php top("reporting.php", "In / Out") ?>

<?php if ($employeeMissing) { ?>
<div class="alert alert-warning" role="alert">
	<?php etr("Your user account is not connected to an employee record.") ?>
	<?php etr("Please ask an administrator to open your user profile and select an employee.") ?>
</div>
<?php } else { ?>
<form name="form1" action="inout.php" method="POST">
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white py-3">
<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($givenname . ' ' . $surname) ?></h2>
<p class="text-secondary small mb-0"><?php etr("In / Out") ?></p>
</div>
<div class="card-body p-4">
<?php 
echo "<div class='row g-4 align-items-end'>";
echo "<div class='col-12 col-lg-4'><label class='form-label fw-semibold'>" . tr("Date") . "</label>";
datebox('date', formatDate($now));
echo "</div>";
echo "<div class='col-12 col-lg-4'><label class='form-label fw-semibold'>" . tr("Time") . "</label>";
timebox('time', date('H:i', $now));
hidden('org_time', date('H:i', $now));
hidden('seconds', date('s', $now));
echo "</div>";
if ($shift_start != null) {
	echo "<div class='col-12 col-lg-4'><label class='form-label fw-semibold'>" . tr("Schedule") . "</label><div class='form-control-plaintext'>";
	echo date('H:i', $shift_start);
	echo ' - ';
	echo date('H:i', $shift_end);
	echo "</div>";
	echo "</div>";
}
echo "</div>";
echo "<div class='d-flex flex-wrap gap-2 mt-4'>";
if ($lastType == TIME_REGISTRATION_OUT || isEmpty($lastType)) {
	pushButton('In', 'cmd_' . TIME_REGISTRATION_IN);
}
if ($lastType == TIME_REGISTRATION_IN || 
	$lastType == TIME_REGISTRATION_END_BREAK) {
	pushButton('Start break', 'cmd_' . TIME_REGISTRATION_START_BREAK);
}
if ($lastType == TIME_REGISTRATION_START_BREAK) {
	pushButton('End break', 'cmd_' . TIME_REGISTRATION_END_BREAK);
}
if ($lastType == TIME_REGISTRATION_IN || 
    $lastType == TIME_REGISTRATION_START_BREAK ||
	$lastType == TIME_REGISTRATION_END_BREAK) {
	pushButton('Out', 'cmd_' . TIME_REGISTRATION_OUT);
}
echo "</div>";
?>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr><th class="text-center" style="width: 90px;"><?php etr("Delete") ?></th><th><?php etr("Time") ?></th><th><?php etr("Type") ?></th></tr></thead>
<tbody>
<?php
$count = 0;
while ($row = fetch($history)) {
	$count++;
	echo "<tr>";
	echo "<td class='text-center'>";
	deleteIcon("inout.php?del_id=$row->id");
	echo "</td>";
	echo "<td>" . formatDate($row->time) . ' ' . date('H:i', $row->time) . "</td>";
	echo "<td>";
	echo tr($types[$row->type]);
	echo "</td>";
	echo "</tr>";
}
if ($count == 0)
	echo "<tr><td colspan='3' class='text-center text-secondary py-5'>" . tr("No records found") . "</td></tr>";
?>
</tbody>
</table>
</div>
</div>
</form>
<?php } ?>
<?php bottom() ?>

</body>
