<html>
<head>
<?php metatag() ?>
<title>Payroll - Schedule calendar</title>
<?php styleSheet() ?>
<LINK REL=StyleSheet HREF="calendar.css" TYPE="text/css">
</head>

<?
include("include.php");
include("schedule_functions.php");

$scheduleid = getParam("scheduleid");

$description = "";
$recur_type = null;
$recur_interval = null;
if (!isEmpty($scheduleid)) {
    $sql = "select ";
    $sql .= "  recur_type, ";
    $sql .= "  recur_interval, ";
    $sql .= "  description ";
    $sql .= "from schedule ";
    $sql .= "where scheduleid=$scheduleid";
    $row = find($sql);
    $description = $row->description;
    $recur_type = $row->recur_type;
    $recur_interval = $row->recur_interval;
}

$year = getParam("year");
if (isEmpty($year))
	$year = date("Y");
$month = getParam("month");
if (isEmpty($month))
	$month = date("m");
$date = mktime(0,0,0, $month, 1, $year);
if (!isEmpty(getParam("prev")))
	$date = strtotime("last month", $date);
if (!isEmpty(getParam("next")))
	$date = strtotime("next month", $date);
$year = date("y", $date);
$month = date("m", $date);
$yymm = date("ym", $date);
?>

<body>

<? include("menubar.php") ?>
<? title("Configuration > <a href='schedules.php'>Schedules</a> > <a href='schedule.php?scheduleid=$scheduleid'>$description</a> > Calendar"); ?>
<center>

<form action="schedule_calendar.php" method="GET">
	<table>
		<tr>
		<td><input type="submit" name="prev" value=" < "/></td>
		<td><?= date("Y M", $date) ?></td>
		<td><input type="submit" name="next" value=" > "/></td>
		</tr>
	</table>
	<input type="hidden" name="scheduleid" value="<?= $scheduleid ?>"/>
	<input type="hidden" name="year" value="<?= $year ?>"/>
	<input type="hidden" name="month" value="<?= $month ?>"/>
</form>

<table class="calendar" width="100%">
<th>Sunday</th>
<th>Monday</th>
<th>Tuesday</th>
<th>Wednesday</th>
<th>Thursday</th>
<th>Friday</th>
<th>Saturday</th>
<?
$lastdate = strtotime("next month", $date);

$list = getWorkshifts($scheduleid, $date, $lastdate);

$shiftMap = array();
foreach ($list as $shift) {
    $shiftid = $shift[0];
    $start = $shift[1];
    $end = $shift[2];
    $label = date(TIME_PATTERN, $start) . " - " . date(TIME_PATTERN, $end);
    $shiftMap[date("yMd", $start)] = $label;
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
	}
	echo "</tr>";
	$day = date("yMd", $date);
	if (array_key_exists($day, $shiftMap)) {
		$label = $shiftMap[$day];
		echo "<tr>";
		echo "<td>";
		echo $label;
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "</td>";
	if (date("w", $date) == 6) {
		echo "</tr>";
		if (date("ym", $date) > $yymm)
			break;
		echo "<tr height='70'>";
	}
	$date = addDay($date);
}
echo "</tr>";

?>
</table>

</center>
</body>
</html>