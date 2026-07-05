<?php
function getSelectedDate()
{
	$date = getParam('date');
	if (isEmpty($date)) {
		$date = time();
		$year = date('Y', $date);
		$month = date('m', $date);
		$day = date('d', $date);
		$date = mktime(0, 0, 0, $month, $day, $year);
	}
	if (!isEmpty(getParam('prev'))) {
		$date = strtotime('-1 day', $date);
	}
	if (!isEmpty(getParam('next'))) {
		$date = strtotime('next day', $date);
	}
	return $date;
}

function dateSelector($script, $date)
{
	echo "<form action='$script' method=GET>";
	echo "<input type=hidden name=date value='$date'/>";
	echo "<center>";
	echo "<input type=submit name='prev' value=' < '/>";
	echo "&nbsp;";
	echo date(DATE_PATTERN, $date);
	echo "&nbsp;";
	echo "<input type=submit name='next' value=' > '/>";
	echo "</center>";
	echo "</form>";
}

function saveLateArrival($employeeid, $i)
{
	$late = getParam("late_$i");
	if ($late != getParam("old_late_$i")) {
		sql("delete from absence where employeeid=$employeeid and periodid=$periodid and starttime=from_unixtime($starttime)");
		if ($late == 1) {
			sql("insert into absence (employeeid, periodid, absencetype, quantity, starttime, endtime)
				 values ($employeeid, $periodid, " . ABSENCE_TYPE_FIX . ", 1, from_unixtime($starttime), from_unixtime($endtime))");
		} else {
		}
	}
}

function dailyreport_tabheader()
{
	echo "<div id=header>";
	echo "<ul id=primary>";
	echo "<li><span>" . tr("Hourly paid") . "</span></li>";
	echo "<li><a href='dailyreport_daily.php'>" . tr("Daily paid") . "</a></li>";
	echo "<li><a href='dailyreport_salaried.php'>" . tr("Salaried") . "</a></li>";
	echo "</ul>";
	echo "</div>";
	echo "<div id=main>";
	echo "<div id=contents>";
}

function dailyreport_tabfooter()
{
	echo "</div>";
	echo "</div>";
}

?>