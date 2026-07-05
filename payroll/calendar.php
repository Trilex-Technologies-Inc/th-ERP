<?php
include("include.php");
include("schedule_functions.php");

$employeeid0 = getParam("employeeid");
$selfservice = false;
if ($employeeid0 == 'current') {
	checkPermission(PERMISSION_SELF_SERVICE);
	$employeeid = getCurrentEmployee();
	$selfservice = true;
	$_REQUEST['selfservice'] = 1;
} else {
	checkPermission(PERMISSION_ADMINISTRATE_EMPLOYEES);
	$employeeid = $employeeid0;
}

$employee = find("select givenname, surname from employee where employeeid=$employeeid");

$year = getParam("year");
if (isEmpty($year))
	$year = date("Y");
$month = getParam("month");
if (isEmpty($month))
	$month = date("m");
if (!isEmpty(getParam("prev")))
	$month--;
if (!isEmpty(getParam("next")))
	$month++;
$date = mktime(0,0,0, $month, 1, $year);
/*if (!isEmpty(getParam("prev")))
	$date = strtotime("last month", $date);
if (!isEmpty(getParam("next")))
	$date = strtotime("next month", $date);	*/
$year = date("y", $date);
$month = date("m", $date);
$yymm = date("ym", $date);

function addEvent($eventMap0, $date, $event)
{
	$key = date("yMd", $date);
	if (array_key_exists($key, $eventMap0)) {
		$dayList = $eventMap0[$key];
		$dayList[] = $event;
		$eventMap0[$key] = $dayList;
	} else {
		$dayList = array();
		$dayList[] = $event;
		$eventMap0[$key] = $dayList;
	}
	return $eventMap0;
}

?>
<html>
<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Employee calendar") ?></title>
<?php styleSheet() ?>
<LINK REL=StyleSheet HREF="calendar.css" TYPE="text/css">
</head>

<body>

<?php
$href = $selfservice ? "selfservice_settings.php" : "employee_detail.php?employeeid=$employeeid";
$emplink = "<a href='$href'>";
$emplink .= "$employee->givenname $employee->surname</a>";
top("employees.php", "calendar", $emplink);
?>

<center>

<form action="calendar.php" method="GET">
	<table>
		<tr>
		<td><input type="submit" name="prev" value=" < "/></td>
		<td><?php echo date("Y", $date) . ' ' . tr(date("M", $date)) ?></td>
		<td><input type="submit" name="next" value=" > "/></td>
		</tr>
	</table>
	<input type="hidden" name="employeeid" value="<?php echo $employeeid0 ?>"/>
	<input type="hidden" name="year" value="<?php echo $year ?>"/>
	<input type="hidden" name="month" value="<?php echo $month ?>"/>
</form>

<table class="calendar" width="100%">
<th><?php echo tr("Sunday") ?></th>
<th><?php echo tr("Monday") ?></th>
<th><?php echo tr("Tuesday") ?></th>
<th><?php echo tr("Wednesday") ?></th>
<th><?php echo tr("Thursday") ?></th>
<th><?php echo tr("Friday") ?></th>
<th><?php echo tr("Saturday") ?></th>
<?php
$lastdate = strtotime("next month", $date);
$eventMap = array();

$shifts = getEmployeeWorkshifts($employeeid, $date, $lastdate);
foreach ($shifts as $shift) {
    $shiftid = $shift[0];
    $shiftstart = $shift[1];
    $shiftend = $shift[2];
    $html = "<span>";
    $html .= date(TIME_PATTERN, $shiftstart) . " - ";
    $html .= date(TIME_PATTERN, $shiftend) . "</span>";
    $eventMap = addEvent($eventMap, $shiftstart, $html);
}


$sql = "
select
	'payevent' as type,
	payeventid,
	description,
	value,
	inputtype,
    unix_timestamp(starttime) as starttime,
    unix_timestamp(endtime) as endtime
from payevent pe
join payaccount pa on pa.accountid=pe.accountid
where employeeid=$employeeid and derived is null
union
select
	'trip',
	tripid,
	purpuse,
	distance,
	0,		
    unix_timestamp(starttime) as starttime,
    unix_timestamp(endtime) as endtime
from trip 
where employeeid=$employeeid
";
$q = query($sql);
while ($rec = fetch_object($q)) {
	$day = $rec->starttime;
	if ($rec->type == 'payevent')
		$href = "payevent.php?payeventid=$rec->payeventid";
	else if ($rec->type == 'trip')
		$href = "trip.php?tripid=$rec->payeventid";
	$html = "<a class='event' href='$href'>";
	$value = null;
	if ($rec->inputtype == INPUT_TYPE_MINUTES)
		$value = minutes2hours($rec->value) . 'h';
	if (!isEmpty($value))
		$value = '(' . $value . ')';
	$html .= "<font color='black'>" . $rec->description . " $value</font></a>";
	while ($day < $rec->endtime) {
		$key = date("yMd", $day);
		$eventMap = addEvent($eventMap, $day, $html);
		$day = strtotime("next day", $day);
	}
}

$date = strtotime("last sunday", $date);
echo "<tr height='70'>";
while (true) {
	echo "<td class='calendar' valign='top' width='14%'>";
	echo "<table width='100%'>";
	echo "<tr>";
	if (date("m", $date) == $month) {
		echo "<td>";
		echo "<b>";
		echo date("d", $date);
		echo "</b>";
		echo "</td>";
		echo "<td align='right'>";		
		$href = "payevent.php?employeeid=$employeeid0&starttime=$date";
		echo "<a class='new' href='$href'>" . tr("New") . "</a>\n";
		echo "</td>";
	}
	echo "</tr>";
	$day = date("yMd", $date);
	if (array_key_exists($day, $eventMap)) {
		$list = $eventMap[$day];
		foreach ($list as $event) {
			echo "<tr>";
			echo "<td colspan='2'>";
			echo "$event<br/>";
			echo "</td>";
			echo "</tr>";
		}
	}
	echo "</table>";
	echo "</td>";
	if (date("w", $date) == 6) {
		echo "</tr>";
		if (date("ym", $date) > $yymm)
			break;
		echo "<tr height='70'>";
	}
	$date =addDay($date);
}
echo "</tr>";

?>
</table>

</center>
<?php bottom() ?>
</body>
</html>
