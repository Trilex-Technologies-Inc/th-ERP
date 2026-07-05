<?php
include('include.php');
include('employee.inc');

$employeeid = getParam('employeeid');
$periodid = getCurrentPeriod();

$del_no = getParam('del_no');
if (!isEmpty($del_no)) {
	$fromperiodid = getParam('fromperiodid');
	$sql = "
	update emp_payitem
	set toperiodid=$periodid
	where employeeid=$employeeid and no=$del_no";
	sql($sql);
	sql("delete from emp_payitem where fromperiodid=toperiodid");
}

if (isSave()) {
	$count = getParam('count');
	$i = 0;
	while ($i < $count) {
		$no = getParam("no_$i");
		$accountid = getParam("accountid_$i");
		$value = prepNull(getParam("value_$i"));
		sql("update emp_payitem set accountid=$accountid, value=$value where employeeid=$employeeid and no=$no");
		$i++;
	}
	$accountid_new = getParam('accountid_new');
	if (!isEmpty($accountid_new)) {
		$value_new = prepNull(getParam('value_new'));
		$no = findValue("select max(no) from emp_payitem where employeeid=$employeeid", 0) + 1;
		$sql = "
		insert into emp_payitem (employeeid, no, fromperiodid, toperiodid, accountid, value)
		values ($employeeid, $no, $periodid, null, $accountid_new, $value_new)";
		sql($sql);
	}
}

$sql = "
select
  pp.no,
  value,
  accountid
from emp_payitem pp
where employeeid=$employeeid and pp.fromperiodid<=$periodid and (pp.toperiodid>$periodid or pp.toperiodid is null)
";

$rs = query($sql);

$accounts = rs2array(query("select a.accountid, description
							from payaccount a"));
?>

<html>
<?php head('Employee') ?>
<body>

<?php
menubar("employees.php");
title(getEmployeeStr($employeeid));
?>

	<div id="header">
	<?php buildTabs($employeeid, 'payitems') ?>
	</div>
	<div id="main">
		<div id="contents">
<form action="employee_payitems.php" method="POST">
<input type=hidden name=employeeid value='<?php echo $employeeid ?>'/>
<input type=hidden name=accounttype value='<?php echo $accounttype ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("No") ?></th>
<th><?php echo tr("Account") ?></th>
<th><?php echo tr("Value") ?></th>
<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name=no_$i value='$row->no'/>";
    echo "<tr class='$class'>";
	deleteColumn("employee_payitems.php?del_no=$row->no&employeeid=$employeeid");
    echo "<td>$row->no</td>";
    echo "<td>";
    comboBox("accountid_$i", $accounts, $row->accountid, false);
    echo "</td>";
    echo "<td>";
	numberbox("value_$i", $row->value);
	echo "</td>";
    echo "</tr>";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
hidden('count', $i);
?>
<tr>
<td/>
<td/>
<td><?php comboBox('accountid_new', $accounts, null, true) ?></td>
<td><input type=text name=value_new /></td>
</tr>
</table>
<br/>
<?php saveButton() ?>
</form>
		</div>
	</div>
<?php bottom() ?>
</body>
</html>
