<?
	include('include.php');

	$shiftid = getParam('shiftid');
	$scheduleid = getParam('scheduleid');
	$schedule_desc = "";
	if (!isEmpty($scheduleid))
	    $schedule_desc = select_value("select description from schedule where scheduleid=$scheduleid");

	if (isSave()) {
	    $date = getParam("date");
	    $starttime = $date . " " . getParam("starttime");
	    $endtime = $date . " " . getParam("endtime");
	    if (isNew()) {
	        $sql = "insert into workshift ";
	        $sql .= "(starttime, endtime) ";
	        $sql .= "values ('$starttime', '$endtime') ";
	        sql($sql);
	        $shiftid = mysql_insert_id();
	        $sql = "insert into schedule_shift ";
	        $sql .= "(scheduleid, shiftid) ";
	        $sql .= "values ($scheduleid, $shiftid) ";
	        sql($sql);
	    } else {
	        $sql = "update workshift ";
	        $sql .= "set starttime='$starttime', ";
	        $sql .= "  endtime='$endtime' ";
	        $sql .= "where shiftid=$shiftid ";
	        sql($sql);
	    }
	} else if (!isEmpty($shiftid)) {
	    $sql = "select ";
	    $sql .= "  starttime, ";
	    $sql .= "  endtime ";
	    $sql .= "from workshift ";
	    $sql .= "where shiftid=$shiftid ";
	    $row = find($sql);
	    $starttime = $row->starttime;
	    $endtime = $row->endtime;
	} else
        $starttime = "";


?>

<head>
<?php metatag() ?>
<title>Payroll - Workshift</title>
<?php styleSheet() ?>
<? include_datebox() ?>
</head>


<body>
<?
include("menubar.php");
$schedule_link = "<a href='schedule.php?scheduleid=$scheduleid'>";
$schedule_link .= "$schedule_desc</a>";
title("Configuration > Schedules > $schedule_link > $starttime");
?>


<form action="workshift.php" method="POST">
<? newbox() ?>
<input type=hidden name=scheduleid value='<?= $scheduleid ?>'/>
<table>
<tr>
<td>Schedule:</td>
<td><?= $scheduleid ?> - <?= $schedule_desc ?></td>
</tr>
<tr>
<td>Shift id:</td>
<td><?= $shiftid ?></td>
</tr>
<tr>
<td>Date:</td>
<td><? datebox("date") ?></td>
</tr>
<tr>
<td>Start time:</td>
<td><? timebox("starttime") ?></td>
</tr>
<tr>
<td>End time:</td>
<td><? timebox("endtime") ?></td>
</tr>
</table>
<table>
<tr>
<td><? saveButton() ?></td>
<td><? newButton() ?></td>
</tr>
</table>

</form>
</body>
