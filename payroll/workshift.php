<?php
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
<?php include_datebox() ?>
</head>


<body>
<?php
include("menubar.php");
$schedule_link = "<a href='schedule.php?scheduleid=$scheduleid'>";
$schedule_link .= htmlspecialchars($schedule_desc) . "</a>";
title("Configuration > Schedules > $schedule_link > " . htmlspecialchars($starttime));
?>


<form action="workshift.php" method="POST">
<?php newbox() ?>
<input type="hidden" name="scheduleid" value="<?php echo htmlspecialchars($scheduleid) ?>"/>
<input type="hidden" name="shiftid" value="<?php echo htmlspecialchars($shiftid) ?>"/>
<div class="card border-0 shadow-sm">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
<div><h2 class="h5 fw-bold mb-1"><?php echo tr("Workshift") ?></h2><p class="text-secondary small mb-0"><?php echo htmlspecialchars($scheduleid . " - " . $schedule_desc) ?></p></div>
<span class="badge text-bg-light border"><?php echo isEmpty($shiftid) ? tr("New") : '#' . htmlspecialchars($shiftid) ?></span>
</div>
<div class="card-body p-4"><div class="row g-4">
<div class="col-12 col-lg-6"><label class="form-label fw-semibold">Schedule</label><div class="form-control-plaintext"><?php echo htmlspecialchars($scheduleid . " - " . $schedule_desc) ?></div></div>
<div class="col-12 col-lg-6"><label class="form-label fw-semibold">Shift id</label><div class="form-control-plaintext font-monospace"><?php echo isEmpty($shiftid) ? tr("Auto generated") : htmlspecialchars($shiftid) ?></div></div>
<div class="col-12 col-lg-4"><label class="form-label fw-semibold" for="date">Date</label><?php datebox("date", isEmpty($starttime) ? null : strtotime($starttime)) ?></div>
<div class="col-12 col-lg-4"><label class="form-label fw-semibold" for="starttime">Start time</label><?php timebox("starttime", isEmpty($starttime) ? null : date(TIME_PATTERN, strtotime($starttime))) ?></div>
<div class="col-12 col-lg-4"><label class="form-label fw-semibold" for="endtime">End time</label><?php timebox("endtime", isEmpty($endtime) ? null : date(TIME_PATTERN, strtotime($endtime))) ?></div>
</div></div>
<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3"><?php saveButton() ?><?php newButton() ?><a class="btn btn-outline-secondary" href="schedule.php?scheduleid=<?php echo htmlspecialchars($scheduleid) ?>"><?php etr("Back") ?></a></div>
</div>

</form>
<?php bottom() ?>
</body>
