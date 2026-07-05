<?php
	include('include.php');
	include('employee.inc');
	
	checkPermission(PERMISSION_ADMINISTRATE_EMPLOYEES);

	$employeeid = getParam("employeeid");
	$rec = find("select givenname, surname from employee where employeeid=$employeeid");
	$name = $rec->givenname . " " . $rec->surname;

	if (isSave()) {
		$i = 0;
		$rowcount = getParam("rowcount");
		while ($i < $rowcount) {
		    $from = getParam("from_$i");
			$delete = getParam("del_" . $i);
			if ($delete == "on") {
				sql("delete from emp_schedule where employeeid=$employeeid and unix_timestamp(valid_from)=$from");
			}
			$i++;
		}

		$scheduleid = getParam("scheduleid_new");
		if (!isEmpty($scheduleid)) {
		    $to = getParam("valid_to_new");
		    $to = isEmpty($to) ? "null" : ("from_unixtime(" . parseDate($to) . ")");
		    $from = parseDate(getParam("valid_from_new"));
			$sql = "insert into emp_schedule ";
			$sql .= "(employeeid, valid_from, valid_to, scheduleid) ";
			$sql .= "values ($employeeid, from_unixtime($from), $to, $scheduleid) ";
			sql($sql);
		}
	}
?>

<?php head('Employee') ?>

<body>
<?php menubar("employees.php") ?>
<?php title($name) ?>

	<div id="header">
	<?php buildTabs($employeeid, 'schedule') ?>
	</div>
	<div id="main">
		<div id="contents">


<form action="employee_schedule.php" method="POST">
<input type=hidden name=employeeid value='<?php echo $employeeid ?>'/>
<table>
<?php

$sql = "select ";
$sql .= "unix_timestamp(valid_from) as valid_from, ";
$sql .= "unix_timestamp(valid_to) as valid_to, ";
$sql .= "es.scheduleid, ";
$sql .= "description ";
$sql .= "from emp_schedule es, schedule s ";
$sql .= "where employeeid=$employeeid ";
$sql .= "  and es.scheduleid=s.scheduleid ";
$sql .= "order by valid_from ";
$q = sql($sql);
echo "<th>" . tr("Delete") . "</th>";
echo "<th>" . tr("From") . "</th>";
echo "<th>" . tr("To") . "</th>\n";
echo "<th>" . tr("Schedule") . "</th>\n";
$class = "odd";
$i = 0;
$lastdate = 0;
$numrows = num_rows($q);
while ($rec = fetch($q)) {
	$from = $rec->valid_from;
	$lastdate = $rec->valid_to;
	echo "<tr class='$class'>";
	echo "<input type='hidden' name='from_$i' value='$from'/>";
	echo "<td align='center'>";
	if ($i == $numrows-1)
	    echo "<input type='checkbox' name='del_$i'/>";
	echo "</td>";
	echo "<td>" . formatDate($from) . "</td>";
	echo "<td>" . formatDate($rec->valid_to) . "</td>";
	echo "<td>$rec->description</td>";
	echo "</tr>\n";
	$class = ($class == "odd" ? "even" : "odd");
	$i++;
}
echo "<input type=hidden name=rowcount value='$numrows'/>";
?>
<tr>
<td></td>
<td>
<?php
if ($lastdate > 0) {
    $datestr = formatDate($lastdate);
    echo $datestr;
    echo "<input type=hidden name=valid_from_new value='$datestr'/>";
} else {
    datebox("valid_from_new");
}
?>
</td>
<td><?php datebox("valid_to_new") ?></td>
<td>
<select name=scheduleid_new>
<option value='null'>--- <?php etr("Select schedule") ?> ---</option>
<?php
$sql = <<<SQL
select
  scheduleid,
  description
from schedule
SQL;
$rs = query($sql);
while ($row = fetch($rs)) {
    echo "<option value='$row->scheduleid'>$row->description</option>";
}
?>
</select>
</td>
<tr>
<td colspan=4>
<input type="submit" name="save" value="Save"/>
&nbsp;
</td>
</tr>

</table>
<input type="hidden" name="employeeid" value="<?php echo $employeeid ?>"/>
</form>
        </div>
    </div>
</body>
