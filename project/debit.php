<?php
	include('include.php');

	$employeeid = findValue("
	select employeeid from user where username='" . getUser() . "'");
	$periodid = 
	$periodid = findValue("
	select periodid
	from payperiod where isnull(locked) or locked=0
	order by payperiod.starttime limit 1");
		
	$projectid = getParam('projectid');

	$start = getParam('start');
	if (isEmpty($start)) {
		$start = roundTime(time(), TYPE_WEEKS);
	}
	if (!isEmpty(getParam('prev'))) {
		$start = strtotime('-1 week', $start);
	}
	if (!isEmpty(getParam('next'))) {
		$start = strtotime('next week', $start);
	}
	$end = addTime($start, TYPE_WEEKS);

	function convertValue($minutes, $payaccountid)
	{
		$value = $minutes;
		$inputtype = findValue("select inputtype from payaccount where accountid=$payaccountid");
		if ($inputtype == INPUT_TYPE_DAYS)
			$value = $minutes / 8 / 60;
		return $value;
	}

	function insertRecord($employeeid, $projectid, $taskid, $starttime, $endtime, $minutes)
	{
		$payaccountid = findValue("select payaccountid from task where projectid=$projectid and taskid=$taskid");
		if (isEmpty($payaccountid)) {
			sql("insert into timedebit (employeeid, starttime, endtime, projectid, taskid, minutes)
				 values ($employeeid, from_unixtime($starttime), from_unixtime($endtime), $projectid, $taskid, $minutes)");
		} else {
			$value = convertValue($minutes, $payaccountid);
			$inputtype = findValue("select inputtype from payaccount where accountid=$payaccountid");
			if ($inputtype == INPUT_TYPE_DAYS)
				$value = $minutes / 8 / 60;
			$periodid = getCurrentPeriod();
			sql("insert into payevent (employeeid, starttime, endtime, accountid, value, periodid, regtime)
				 values ($employeeid, from_unixtime($starttime), from_unixtime($endtime), $payaccountid, $value, $periodid, now())");
		}

	}

	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$projectid0 = getParam("projectid_$i");
		$taskid0 = getParam("taskid_$i");
		$date = $start;
		while ($date < $end) {
			$key = $projectid0 . '_' . $taskid0 . '_' . $date;
			$minutes = parseTime(getParam($key));
			if ($minutes != getParam("old_$key")) {
				$payaccountid = findValue("select payaccountid from task where projectid=$projectid0 and taskid=$taskid0");
				if (isEmpty($payaccountid)) {
					sql("update timedebit set minutes=$minutes
						 where employeeid=$employeeid
						 and projectid=$projectid0 and taskid=$taskid0
						 and starttime=from_unixtime($date)");
				} else {
					$value = convertValue($minutes, $payaccountid);
					sql("update payevent set value=$value
					     where employeeid=$employeeid
						 and accountid=$payaccountid
						 and starttime=from_unixtime($date)");
				}
				if (affected_rows() == 0) {
					$end2 = addDay($date);
					insertRecord($employeeid, $projectid0, $taskid0, $date, $end2, $minutes);
				}
			}
			$date = addDay($date);
		}
		$i++;
	}
	$taskid_new = getParam('taskid_new');
	if (!isEmpty($taskid_new)) {
		$date = $start;
		while ($date < $end) {
			$key = $projectid . '_new_' . $date;
			$minutes = parseTime(getParam($key));
			if ($minutes > 0) {
				$end2 = addDay($date);
				insertRecord($employeeid, $projectid, $taskid_new, $date, $end2, $minutes);
			}
			$date = addDay($date);
		}
	}

	$givenname = findValue("select givenname from employee where employeeid=$employeeid");
	$surname = findValue("select surname from employee where employeeid=$employeeid");

	$rows = query("select unix_timestamp(starttime) as starttime,
	                 p.projectid,
					 t.taskid,
					 p.description as pdesc,
					 t.description as tdesc,
					 minutes,
					 " . INPUT_TYPE_MINUTES . " as inputtype
				   from timedebit d
				   join project p on d.projectid=p.projectid
				   join task t on t.projectid=d.projectid and t.taskid=d.taskid
				   where employeeid=$employeeid
				   and starttime >= from_unixtime($start) and endtime <= from_unixtime($end)
				   union
                   select unix_timestamp(starttime) as starttime,
	                 t.projectid,
					 t.taskid,
					 p.description as pdesc,
					 t.description as tdesc,
					 value,
					 inputtype
				   from payevent pe
				   join payaccount a on a.accountid=pe.accountid
				   join task t on t.payaccountid=pe.accountid
				   join project p on p.projectid=t.projectid
				   where employeeid=$employeeid
				   and starttime >= from_unixtime($start) and endtime <= from_unixtime($end)
				   order by projectid, taskid, starttime
				   ");
	$tasks = array();
	$cells = array();
	while ($row = fetch($rows)) {
		$key = $row->projectid . '_' . $row->taskid;
		$tasks[$key] = array($row->projectid, $row->pdesc, $row->taskid, $row->tdesc);
		$key = $row->projectid . '_' . $row->taskid . '_' . $row->starttime;
		$minutes = $row->minutes;
		if ($row->inputtype == INPUT_TYPE_DAYS)
			$minutes = 8 * 60;
		$cells[$key] = $minutes;
	}

	$projects = rs2array(query("select projectid, description from project"));
	$allTasks = array();
	if (!isEmpty($projectid)) {
		$allTasks = rs2array(query("select taskid, description from task where projectid=$projectid"));
	}
?>

<?php head("Debit") ?>

<script>
function onProjectChange()
{
	<?php
	echo "document.location.href=\"debit.php?employeeid=$employeeid";
	echo "&start=$start";
	echo "&projectid=\" + form1.projectid.value;\n";
	?>
}

</script>
</head>

<body>
<?php top("debit", "Debit"); ?>

<center>
<br/>
<form action='debit.php' method=GET>
<?php hidden('start', $start) ?>
<input type=submit name='prev' value=' < '/>
&nbsp;
<?php
echo formatDate($start);
echo "&nbsp;-&nbsp;";
echo formatDate($end);
?>
&nbsp;
<input type=submit name='next' value=' > '/>
</form>

<form name=form1 action="debit.php" method="POST">
<?php hidden('start', $start) ?>
<br>
<?php
echo $givenname . ' ' . $surname . '<br><br>';
?>

<table>
<?php
	echo "<th>" . tr("Project") . "</th>";
	echo "<th>" . tr("Task") . "</th>";
	$date = $start;
	echo "<th>" . tr("Sunday") . "<br>" . formatDate($date) . "</th>";
	$date = addDay($date);
	echo "<th>" . tr("Monday") . "<br>" . formatDate($date) . "</th>";
	$date = addDay($date);
	echo "<th>" . tr("Tuesday") . "<br>" . formatDate($date) . "</th>";
	$date = addDay($date);
	echo "<th>" . tr("Wednesday") . "<br>" . formatDate($date) . "</th>";
	$date = addDay($date);
	echo "<th>" . tr("Thursday") . "<br>" . formatDate($date) . "</th>";
	$date = addDay($date);
	echo "<th>" . tr("Friday") . "<br>" . formatDate($date) . "</th>";
	$date = addDay($date);
	echo "<th>" . tr("Saturday") . "<br>" . formatDate($date) . "</th>";
	$lastProjectid = null;
	$lastTaskid = null;
	$class = 'odd';
	$i = 0;
	$allocated = 0;
	foreach ($tasks as $task) {
		echo "<tr class=$class>";
		$projectid0 = $task[0];
		$pdesc = $task[1];
		$taskid = $task[2];
		$tdesc = $task[3];
		hidden("projectid_$i" , $projectid0);
		hidden("taskid_$i" , $taskid);
		echo "<td>$pdesc</td>";
		echo "<td>$tdesc</td>";
		$date = $start;
		while ($date < $end) {
			echo "<td>";
			$key = $projectid0 . '_' . $taskid . '_' . $date;
			$minutes = 0;
			if (array_key_exists($key, $cells)) {
				$minutes = $cells[$key];
			}
			$allocated += $minutes;
			timebox($key, minutes2hours($minutes));
			hidden("old_$key", $minutes);
			echo "</td>\n";
			$date = addDay($date);
		}
		$class = ($class == "odd" ? "even" : "odd");
		echo "</tr>\n";
		$i++;
	}
	hidden('count', $i);
	echo "<tr class=$class>";
	echo "<td>";
	combobox('projectid', $projects, $projectid, true, "onProjectChange()");
	echo "</td>";
	echo "<td>";
	combobox('taskid_new', $allTasks, null, true);
	echo "</td>";
	if (!isEmpty($projectid)) {
		$date = $start;
		while ($date < $end) {
			echo "<td>";
			$key = $projectid . '_new_' . $date;
			$minutes = 0;
			echo numberbox($key, minutes2hours($minutes), 6);
			echo "</td>\n";
			$date = addDay($date);
		}
	}
	echo "</tr>\n";


?>
</table>
<p><?php echo tr("Allocated time") . ": " . minutes2hours($allocated) ?></p>
<br/>
<?php saveButton() ?>

</form>
<?php bottom() ?>
</center>

</body>
