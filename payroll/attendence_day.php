<?php
include('include.php');

$formid = getParam("formid");

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

$periodid = getCurrentPeriod();
$accounts = rs2array(query("select a.accountid, inputtype, d.description
                           from payaccount a
						   join payaccount_group ag on ag.accountid=a.accountid
						   join daily_form f on f.groupid=ag.groupid
						   join payaccount_description d on d.accountid=a.accountid and language='" . getLanguage() . "'
						   where f.formid=$formid"));

if (isSave()) {
	$starttime = $date;
	$endtime = strtotime('+1 day', $starttime);
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$employeeid = getParam("employeeid_$i");
		$policyid = getPolicy($employeeid, $periodid);
		foreach ($accounts as $account) {
			$accountid = $account[0];
			$inputtype = $account[1];
			$value = getParam("value_" . $employeeid . "_" . $accountid);
			$old_value = getParam("old_value_" . $employeeid . "_" . $accountid);
			if ($value != $old_value) {
				if ($inputtype == INPUT_TYPE_DAYS)
					$value = 1;
				else if ($inputtype == INPUT_TYPE_MINUTES)
					$value = hours2minutes($value);
				sql("update payevent set value=$value
				     where employeeid=$employeeid and periodid=$periodid and accountid=$accountid and starttime=from_unixtime($starttime)");
				if (affected_rows() != 1) {
					sql("delete from payevent
					     where employeeid=$employeeid and periodid=$periodid
						 and accountid=$accountid and starttime=from_unixtime($starttime)");
					sql("insert into payevent (employeeid, periodid, value, starttime, endtime, accountid, regtime)
					     values ($employeeid, $periodid, $value, from_unixtime($starttime), from_unixtime($endtime), $accountid, now())");
				}
			}
		}
		$i++;
	}
}

$sql = "select
          e.employeeid,
          givenname,
          surname ";
foreach ($accounts as $account) {
	$accountid = $account[0];
	$sql .= ",pe$accountid.value as value_$accountid ";
}
$sql .= "from employee e
         join emp_team et on et.employeeid=e.employeeid
         join daily_form f on f.teamid=et.teamid and formid=$formid ";
foreach ($accounts as $account) {
	$accountid = $account[0];
	$sql .= "
        left outer join payevent pe$accountid on pe$accountid.employeeid=e.employeeid
        and pe$accountid.periodid=$periodid and pe$accountid.starttime=from_unixtime($date) and pe$accountid.accountid=$accountid ";
}
sql("set SQL_BIG_SELECTS=1");
$employees = query($sql);

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php echo tr("Daily report") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php title(tr("Daily report")) ?>

<form action='attendence_day.php' method=GET>
<?php hidden('formid', $formid) ?>
<input type=hidden name=date value='<?php echo $date ?>'/>
<center>
<input type=submit name='prev' value=' < '/>
&nbsp;
<?php echo date(DATE_PATTERN, $date) ?>
&nbsp;
<input type=submit name='next' value=' > '/>
</center>
</form>

<center>
<form action='attendence_day.php' method=POST>
<?php hidden('formid', $formid) ?>
<input type=hidden name=date value='<?php echo $date ?>'/>
<table>
<?php
	echo "<th>" . tr("Name") . "</th>";
	foreach ($accounts as $account) {
		$description = $account[2];
		echo "<th>";
		echo $description;
		echo "</th>";
	}
?>

<?php
$i = 0;
$class = "odd";
while ($row = fetch($employees)) {
	$employeeid = $row->employeeid;
	echo "<input type=hidden name=employeeid_$i value='$row->employeeid'/>\n";
    echo "<tr class='$class'>\n";
	echo "<td><a href='employee_detail.php?employeeid=$row->employeeid'>$row->givenname $row->surname</a></td>\n";
	foreach ($accounts as $account) {
		echo "<td align=center>";
		$accountid = $account[0];
		$inputtype = $account[1];
		eval('$value = $row->value_' . $accountid . ';');
		if ($inputtype == INPUT_TYPE_MINUTES)
			$value = minutes2hours($value);
		$name = "value_" . $employeeid . "_" . $accountid;
		if ($inputtype == INPUT_TYPE_DAYS || $inputtype == INPUT_TYPE_UNITS)
			checkbox($name, $value);
		else
			textbox($name, $value, 8);
		hidden("old_$name", $value);
		echo "</td>";
	}
	echo "</tr>\n";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
?>
<input type=hidden name=count value='<?php echo $i ?>'/>
</table>
<br/>
<?php saveButton() ?>
</form>
</center>


</body>
