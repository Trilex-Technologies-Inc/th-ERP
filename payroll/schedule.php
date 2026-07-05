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
            $scheduleid = mysql_insert_id();
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
            $shiftid = mysql_insert_id();
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
<?php title("<a href='schedules.php'>" .tr ("Schedules") . "</a> > $description") ?>

<form action="schedule.php" method=POST>
<?php newbox() ?>
<input type=hidden name=scheduleid value='<?php echo $scheduleid ?>'/>
<div class="border">
<table>
<tr><td><?php etr("Id") ?>:</td><td><?php echo $scheduleid ?></td></tr>
<tr>
<td><?php etr("Description") ?>:</td><td><?php textbox("description", $description) ?></td>
</tr>
<tr>
<td><?php etr("Recur") ?>:</td>
<td>
<?php checkbox('recur', $recur) ?>
<input type=text name=recur_interval value='<?php echo $recur_interval ?>' size=4/>
(<?php etr("number of days") ?>)
</td>
</tr>
</table>
</div>
&nbsp;

<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("No") ?></th>
<th><?php etr("Date") ?></th>
<th><?php etr("Start") ?></th>
<th><?php etr("End") ?></th>
<?php
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
        $i = 0;
        $class = "odd";
        while ($row = fetch_object($rs)) {
            echo "<input type=hidden name=shiftid_$i value='$row->shiftid'/>";
            echo "<tr class='$class'>";
            echo "<td align=center><input type=checkbox name='del_$i'/></td>";
            echo "<td align=right>$row->shiftid</td>";
            $date = date(DATE_PATTERN, $row->starttime);
            $starttime = date(TIME_PATTERN, $row->starttime);
            $endtime = date(TIME_PATTERN, $row->endtime);
            echo "<td align=center>$date</td>";
            echo "<td align=center>$starttime</td>";
            echo "<td align=center>$endtime</td>";
            echo "</tr>\n";
            $class = ($class == "odd" ? "even" : "odd");
            $i++;
        }
        echo "<input type=hidden name=rowcount value=$i/>";
    }
?>
<tr>
<td/>
<td/>
<td><?php datebox("date_new") ?></td>
<td><?php timebox("starttime_new") ?></td>
<td><?php timebox("endtime_new") ?></td>
</tr>
</table>
<table>
<tr>
<td><?php saveButton() ?></td>
<td><?php button("View calendar", "View", "schedule_calendar.php?scheduleid=$scheduleid") ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
