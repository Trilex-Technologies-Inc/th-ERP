<?php
	include('include.php');
	include('employee.inc');
	include('schedule_functions.php');

	$employeeid = getCurrentEmployee();
	$periodid = getCurrentPeriod();
	
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
	if ($type != null) {
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
	if (!isEmpty($del_id)) {
		sql("delete from timeregistration where id=$del_id");
	}

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
	
	
	$now = time();
	$start = roundTime($now, TYPE_DAYS);
	$end = addTime($start, TYPE_DAYS, 1);
	$shifts = getEmployeeWorkshifts($employeeid, $start, $end);
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

<center>
<form name=form1 action="inout.php" method="POST">
<br>
<?php 
echo $givenname . ' ' . $surname . '<br><br>';
echo "<table>";
echo "<tr>";
echo "<td class=label>" . tr("Date") . ":</td>";
echo "<td>";
datebox('date', formatDate($now));
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td class=label>" . tr("Time") . ":</td>";
echo "<td>";
timebox('time', date('H:i', $now));
hidden('org_time', date('H:i', $now));
hidden('seconds', date('s', $now));
echo "</td>";
echo "</tr>";
if ($shift_start != null) {
	echo "<td class=label>" . tr("Schedule") . ":</td>";
	echo "<td>";
	echo date('H:i', $shift_start);
	echo ' - ';
	echo date('H:i', $shift_end);
	echo "</td>";
	echo "</tr>";
}
echo "</table>";
echo "<br>";
if ($lastType == TIME_REGISTRATION_OUT || isEmpty($lastType)) {
	pushButton('In', 'cmd_' . TIME_REGISTRATION_IN);
	echo '&nbsp;';
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
?>
<br><br>

<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Time") ?></th>
<th><?php etr("Type") ?></th>
<?php
$class = 'odd';
while ($row = fetch($history)) {
	echo "<tr class=$class>";
	deleteColumn("inout.php?del_id=$row->id");
	echo "<td>" . formatDate($row->time) . ' ' . date('H:i', $row->time) . "</td>";
	echo "<td>";
	echo tr($types[$row->type]);
	echo "</td>";
	echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
}
?>
</table>

</form>
</center>
<?php bottom() ?>

</body>
