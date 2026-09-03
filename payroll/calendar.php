<?php
include("include.php");
include("schedule_functions.php");
include("employee.inc");

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

<main class="employee-calendar">
<?php if (!$selfservice) { ?>
	<nav class="employee-detail-tabs" aria-label="<?php echo tr("Employee sections") ?>">
		<?php buildTabs($employeeid, 'calendar') ?>
	</nav>
<?php } ?>
<form action="calendar.php" method="GET" class="calendar-toolbar">
	<input class="calendar-nav" type="submit" name="prev" value="&#8249;" aria-label="<?php echo tr("Previous month") ?>"/>
	<div class="calendar-heading">
		<span class="calendar-eyebrow"><?php echo tr("Employee calendar") ?></span>
		<h2><?php echo tr(date("M", $date)) . ' ' . date("Y", $date) ?></h2>
	</div>
	<input class="calendar-nav" type="submit" name="next" value="&#8250;" aria-label="<?php echo tr("Next month") ?>"/>
	<input type="hidden" name="employeeid" value="<?php echo $employeeid0 ?>"/>
	<input type="hidden" name="year" value="<?php echo $year ?>"/>
	<input type="hidden" name="month" value="<?php echo $month ?>"/>
</form>

<div class="calendar-legend" aria-label="<?php echo tr("Calendar legend") ?>">
	<span><i class="legend-dot shift-dot"></i><?php echo tr("Work shift") ?></span>
	<span><i class="legend-dot event-dot"></i><?php echo tr("Pay event or trip") ?></span>
</div>
<div class="calendar-frame">
<table class="calendar">
<thead><tr><th scope="col"><?php echo tr("Sunday") ?></th>
<th><?php echo tr("Monday") ?></th>
<th><?php echo tr("Tuesday") ?></th>
<th><?php echo tr("Wednesday") ?></th>
<th><?php echo tr("Thursday") ?></th>
<th><?php echo tr("Friday") ?></th>
<th><?php echo tr("Saturday") ?></th></tr></thead><tbody>
<?php
$lastdate = strtotime("next month", $date);
$eventMap = array();

$shifts = getEmployeeWorkshifts($employeeid, $date, $lastdate);
foreach ($shifts as $shift) {
    $shiftid = $shift[0];
    $shiftstart = $shift[1];
    $shiftend = $shift[2];
    $html = "<span class='calendar-shift'>";
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
	$html .= "<span>" . $rec->description . " $value</span></a>";
	while ($day < $rec->endtime) {
		$key = date("yMd", $day);
		$eventMap = addEvent($eventMap, $day, $html);
		$day = strtotime("next day", $day);
	}
}

$date = strtotime("last sunday", $date);
$today = date("Ymd");
echo "<tr>";
while (true) {
	$isCurrentMonth = date("m", $date) == $month;
	$classes = array("calendar-day");
	if (!$isCurrentMonth) $classes[] = "outside-month";
	if (date("Ymd", $date) == $today) $classes[] = "today";
	if (date("w", $date) == 0 || date("w", $date) == 6) $classes[] = "weekend";
	echo "<td class='" . implode(" ", $classes) . "'>";
	echo "<div class='day-header'>";
	echo "<span class='day-number'>" . date("j", $date) . "</span>";
	if ($isCurrentMonth) {
		$href = "payevent.php?employeeid=$employeeid0&starttime=$date";
		echo "<a class='new' href='$href' title='" . tr("New pay event") . "'><span>+</span> " . tr("New") . "</a>\n";
	}
	echo "</div><div class='day-events'>";
	$day = date("yMd", $date);
	if (array_key_exists($day, $eventMap)) {
		$list = $eventMap[$day];
		foreach ($list as $event) {
			echo $event;
		}
	}
	echo "</div>";
	echo "</td>";
	if (date("w", $date) == 6) {
		echo "</tr>";
		if (date("ym", $date) > $yymm)
			break;
		echo "<tr>";
	}
	$date =addDay($date);
}
echo "</tr>";

?>
</tbody></table>
</div>
</main>
<?php bottom() ?>
</body>
</html>
