<?php
include('include.php');
include('dailyreport.inc');

$date = getSelectedDate();

$periodid = getCurrentPeriod();

if (isSave()) {
	$starttime = $date;
	$endtime = strtotime('+1 day', $starttime);
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$employeeid = getParam("employeeid_$i");
		saveLateArrival($employeeid, $i);
		$absent = getParam("absent_$i");
		if ($absent != getParam("old_absent_$i")) {
			sql("delete from absence where employeeid=$employeeid and periodid=$periodid and starttime=from_unixtime($starttime)");
			if ($absent == 1) {
				sql("insert into absence (employeeid, periodid, absencetype, quantity, starttime, endtime)
				     values ($employeeid, $periodid, " . ABSENCE_TYPE_DAYS . ", 1, from_unixtime($starttime), from_unixtime($endtime))");
			} else {
			}
		}
		$attending = getParam("attending_$i");
		if ($attending != getParam("old_attending_$i")) {
			sql("delete from attendence where employeeid=$employeeid and periodid=$periodid and starttime=from_unixtime($starttime)");
			if ($attending == 1) {
				$attendingtype = ATTENDENCE_TYPE_DAYS;
				$quantity = 1;
				$emptype = getEmployeeType($employeeid, $periodid);
				if ($emptype == EMPLOYEE_TYPE_HOURLY) {
					$attendingtype = ATTENDENCE_TYPE_MINUTES;
					$quantity = 8 * 60;
				}
				sql("insert into attendence (employeeid, periodid, attendencetype, quantity, starttime, endtime)
				     values ($employeeid, $periodid, $attendingtype, $quantity, from_unixtime($starttime), from_unixtime($endtime))");
			}
		}
		$attendence = getParam("attendence_$i");
		$old_attendence = getParam("old_attendence_$i");
		if ($attendence != $old_attendence) {
			$quantity = hours2minutes($attendence);
			sql("update attendence set attendencetype=" . ATTENDENCE_TYPE_MINUTES . ", quantity=$quantity
			     where employeeid=$employeeid and periodid=$periodid and starttime=from_unixtime($starttime)");
			if (affected_rows() == 0) {
				sql("delete from attendence where employeeid=$employeeid and periodid=$periodid and starttime=from_unixtime($starttime)");
				sql("insert into attendence (employeeid, periodid, attendencetype, quantity, starttime, endtime)
				     values ($employeeid, 
					         $periodid, 
							 " . ATTENDENCE_TYPE_MINUTES . ", 
							 $quantity, 
							 from_unixtime($starttime), 
							 from_unixtime($endtime))");
			}
		}
		$i++;
	}
}

$sql = "select
          e.employeeid,
          givenname,
          surname,
          a.quantity as attendence_quantity,
          ab.quantity as absence_quantity,
          typeid,
          absencetype,
          attendencetype
        from employee e
        join employee_type et on e.employeeid=et.employeeid
        and et.fromperiodid=(select min(fromperiodid) from employee_type et2 where et2.employeeid=et.employeeid and et2.fromperiodid<=$periodid)
        left outer join attendence a on a.employeeid=e.employeeid
        and a.periodid=$periodid and a.starttime=from_unixtime($date)
        left outer join absence ab on ab.employeeid=e.employeeid
        and ab.periodid=$periodid and ab.starttime=from_unixtime($date)
        order by typeid, employeeid
        ";
$employees = query($sql);

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Attendence") ?></title>
<?php styleSheet() ?>
<LINK REL=StyleSheet HREF="tabs.css" TYPE="text/css">
</head>

<body>
<?php include("menubar.php") ?>
<?php title(tr("Daily report")) ?>

<?php dateSelector('dailyreport_hourly.php', $date) ?>

<?php dailyreport_tabheader() ?>
<center>
<form action='dailyreport_hourly.php' method=POST>
<input type=hidden name=date value='<?php echo $date ?>'/>
<table>
<th><?php echo tr("Name") ?></th>
<th><?php echo tr("Late arrival") ?></th>
<th><?php echo tr("Working full day") ?></th>
<th><?php echo tr("Working hours") ?></th>

<?php
$i = 0;
$class = "odd";
while ($row = fetch($employees)) {
	echo "<input type=hidden name=employeeid_$i value='$row->employeeid'/>\n";
    echo "<tr class='$class'>\n";
	echo "<td><a href='employee_detail.php?employeeid=$row->employeeid'>$row->givenname $row->surname</a></td>\n";
	$late = ($row->absencetype == ABSENCE_TYPE_FIX && $row->absence_quantity == 1);
	$checked = $late ? 'checked' : '';
	echo "<td align=center><input type=checkbox name='late_$i' value='1' $checked/></td>";
	echo "<input type=hidden name='old_late_$i' value='$late'/>";
	$attending = ($row->typeid == EMPLOYEE_TYPE_DAILY && $row->attendence_quantity == 1);
	$checked = $attending ? 'checked' : '';
	echo "<td align=center><input type=checkbox name='attending_$i' value='1' $checked/></td>";
	echo "<input type=hidden name='old_attending_$i' value='$attending'/>";
	$quantity = null;
	if ($row->attendencetype == ATTENDENCE_TYPE_MINUTES) {
		$quantity = minutes2hours($row->attendence_quantity);
	}
	echo "<td align=center><input type=text name='attendence_$i' value='$quantity' columns=5/></td>\n";
	echo "<input type=hidden name='old_attendence_$i' value='$quantity' />";
	echo "</tr>\n";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
?>
<input type=hidden name=count value='<?php echo $i ?>'/>
<tr>
<td><input type=submit name=save value='Save'/>
</tr>
</table>
</form>
</center>
<?php dailyreport_tabfooter() ?>
</body>