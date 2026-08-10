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
	        $shiftid = insert_id();
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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">Schedule:</div>
<div class="col-12 col-md-auto"><?= $scheduleid ?> - <?= $schedule_desc ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">Shift id:</div>
<div class="col-12 col-md-auto"><?= $shiftid ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">Date:</div>
<div class="col-12 col-md-auto"><? datebox("date") ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">Start time:</div>
<div class="col-12 col-md-auto"><? timebox("starttime") ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">End time:</div>
<div class="col-12 col-md-auto"><? timebox("endtime") ?></div>
</div>
</div>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><? saveButton() ?></div>
<div class="col-12 col-md-auto"><? newButton() ?></div>
</div>
</div>

</form>
</body>
