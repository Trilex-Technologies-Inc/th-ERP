<?php
	include('include.php');
		
	$scheduleid = getParam("scheduleid");
	$recur = "off";
	$recur_interval = "";
	if (isSave()) {
	    $description = getParam("description");
	    $recur_type = "null";
	    $recur_interval = "null";
	    if (getParam("recur")) {
	        $recur_type = RECUR_TYPE_DAILY;
	        $recur_interval = getParam("recur_interval");
	    }
	    if (isNew()) {
	        $sql = "insert into schedule ";
	        $sql .= " (description, recur_type, recur_interval) ";
	        $sql .= " values ('$description', $recur_type, $recur_interval) ";
            sql($sql);
            $scheduleid = insert_id();
	    } else {
	        $sql = "update schedule ";
	        $sql .= "set description='$description', ";
	        $sql .= "  recur_type=$recur_type, ";
	        $sql .= "  recur_interval=$recur_interval ";
	        $sql .= "where scheduleid=$scheduleid";
            sql($sql);
	    }
	    $row = 0;
	    $rowcount = getParam("rowcount");
	    while ($row < $rowcount) {
    	    $del = getParam("del_$row");
	        if ($del == "on") {
	            $shiftid = getParam("shiftid_$row");
	            $sql = "delete from schedule_shift ";
	            $sql .= "where scheduleid=$scheduleid and ";
	            $sql .= "  shiftid=$shiftid ";
	            sql($sql);
	            $sql = "delete from workshift ";
	            $sql .= "where shiftid=$shiftid ";
	            sql($sql);
	        }
	        $row++;
        }
        $date = getParam("date_new");
        if ($date != null) {
            $date = parseDate($date);
            $starttime = parseTime(getParam("starttime_new"));
            $endtime = parseTime(getParam("endtime_new"));
            $starttime = mkdatetime($date, $starttime);
            $endtime = mkdatetime($date, $endtime);
            $sql = "insert into workshift ";
            $sql .= "(starttime, endtime) ";
            $sql .= "values (from_unixtime($starttime), from_unixtime($endtime)) ";
            sql($sql);
            $shiftid = insert_id();
            $sql = "insert into schedule_shift (scheduleid, shiftid) ";
            $sql .= "values ($scheduleid, $shiftid)";
            sql($sql);
        }
	}
	if (!isEmpty($scheduleid)) {
        $sql = "select ";
	    $sql .= "  recur_type, ";
	    $sql .= "  recur_interval, ";
	    $sql .= "  description ";
	    $sql .= "from schedule ";
	    $sql .= "where scheduleid=$scheduleid";
        $row = find($sql);
        $description = $row->description;
        if ($row->recur_type != null) {
            $recur = "on";
            $recur_interval = $row->recur_interval;
        } else
            $recur_interval = "";
    } else
        $description = "";

?>

<head>
<?php metatag() ?>
<title>Payroll - Schedule</title>
<?php 
styleSheet();
include_common();
include_datebox();
?>
</head>

<body>

<?php menubar("configuration.php") ?>
<?php
$title = isEmpty($description) ? tr("Create schedule") : $description;
title("<a href='schedules.php'>" . tr("Schedules") . "</a> > " . htmlspecialchars($title))
?>

<form action="schedule.php" method="POST">
<?php newbox() ?>
<input type="hidden" name="scheduleid" value="<?php echo htmlspecialchars($scheduleid) ?>"/>

<div class="card border-0 shadow-sm mb-4">
	<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
		<div>
			<h2 class="h5 fw-bold mb-1"><?php echo htmlspecialchars($title) ?></h2>
			<p class="text-secondary small mb-0"><?php etr("Schedule") ?></p>
		</div>
		<span class="badge text-bg-light border"><?php echo isEmpty($scheduleid) ? tr("New") : '#' . htmlspecialchars($scheduleid) ?></span>
	</div>
	<div class="card-body p-4">
		<div class="row g-4">
			<div class="col-12 col-lg-3">
				<label class="form-label fw-semibold"><?php etr("Id") ?></label>
				<div class="form-control-plaintext font-monospace"><?php echo isEmpty($scheduleid) ? tr("Auto generated") : htmlspecialchars($scheduleid) ?></div>
			</div>
			<div class="col-12 col-lg-9">
				<label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label>
				<input class="form-control" id="description" type="text" name="description" value="<?php echo htmlspecialchars($description) ?>" required />
			</div>
			<div class="col-12">
				<div class="d-flex flex-wrap align-items-center gap-3">
					<div class="form-check mb-0">
						<?php checkbox('recur', $recur) ?>
						<label class="form-check-label fw-semibold" for="recur"><?php etr("Recur") ?></label>
					</div>
					<div class="d-flex flex-wrap align-items-center gap-2">
						<input class="form-control" type="text" name="recur_interval" value="<?php echo htmlspecialchars($recur_interval) ?>" size="4" style="max-width: 90px;"/>
						<span class="text-secondary small"><?php etr("number of days") ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
	<div class="card-header bg-white py-3">
		<h2 class="h5 fw-bold mb-1"><?php etr("Work shifts") ?></h2>
		<p class="text-secondary small mb-0"><?php etr("Schedule shift times") ?></p>
	</div>
	<div class="table-responsive">
		<table class="table table-hover align-middle mb-0">
			<thead>
				<tr>
					<th class="text-center" style="width: 90px;"><?php etr("Delete") ?></th>
					<th class="text-end" style="width: 120px;"><?php etr("No") ?></th>
					<th><?php etr("Date") ?></th>
					<th style="width: 180px;"><?php etr("Start") ?></th>
					<th style="width: 180px;"><?php etr("End") ?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			    $i = 0;
			    if (!isEmpty($scheduleid)) {
			        $sql = <<<SQL
        select
          w.shiftid,
          unix_timestamp(starttime) as starttime,
          unix_timestamp(endtime) as endtime,
          recur_type,
          recur_interval,
          recur_count
        from workshift w , schedule_shift ss
        where w.shiftid=ss.shiftid and
          scheduleid=$scheduleid
        order by starttime
SQL;
			        $rs = query($sql);
			        while ($row = fetch_object($rs)) {
			            $shiftid = htmlspecialchars($row->shiftid);
			            $date = htmlspecialchars(date(DATE_PATTERN, $row->starttime));
			            $starttime = htmlspecialchars(date(TIME_PATTERN, $row->starttime));
			            $endtime = htmlspecialchars(date(TIME_PATTERN, $row->endtime));
			            echo "<tr>";
			            echo "<td class='text-center'>";
			            echo "<input type='checkbox' name='del_$i'/>";
			            echo "<input type='hidden' name='shiftid_$i' value='$shiftid'/>";
			            echo "</td>";
			            echo "<td class='text-end font-monospace'>$shiftid</td>";
			            echo "<td>$date</td>";
			            echo "<td>$starttime</td>";
			            echo "<td>$endtime</td>";
			            echo "</tr>\n";
			            $i++;
			        }
			    }
			    echo "<input type='hidden' name='rowcount' value='$i'/>";
			?>
				<tr class="table-light">
					<td class="text-center text-secondary fw-semibold">+</td>
					<td></td>
					<td><?php datebox("date_new") ?></td>
					<td><?php timebox("starttime_new") ?></td>
					<td><?php timebox("endtime_new") ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3">
		<?php saveButton() ?>
		<?php if (!isEmpty($scheduleid)) { ?>
			<?php button("View calendar", "View", "schedule_calendar.php?scheduleid=$scheduleid") ?>
		<?php } ?>
		<a class="btn btn-outline-secondary" href="schedules.php"><?php etr("Back") ?></a>
	</div>
</div>
</form>
<?php bottom() ?>
</body>
