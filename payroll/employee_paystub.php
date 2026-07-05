<?php
include('include.php');
include('calculations.php');
include('employee.inc');

$employeeid0 = getParam("employeeid");
$selfservice = false;
if ($employeeid0 == 'current') {
	checkPermission(PERMISSION_SELF_SERVICE);
	$employeeid = getCurrentEmployee();
	$selfservice = true;
	$_REQUEST['selfservice'] = true;
} else {
	checkPermission(PERMISSION_ADMINISTRATE_EMPLOYEES);
	$employeeid = $employeeid0;
}

$employee = find("select givenname, surname from employee where employeeid=$employeeid");
$periodid = getParam('periodid');
if (isEmpty($periodid))
	$periodid = getCurrentPeriod();
if (!isEmpty(getParam("prev")))
	$periodid--;
if (!isEmpty(getParam("next")))
	$periodid++;
$readonly = ($periodid != getCurrentPeriod());

$del_payeventid = getParam('del_payeventid');
if (!isEmpty($del_payeventid)) {
	sql("delete from payevent where payeventid=$del_payeventid");
	sql("update employee set calctime=null where employeeid=$employeeid");
}

$mess = null;
if (!isEmpty(getParam("calc"))) {
	calculate($employeeid, $periodid);
} else {
	$mess = calculateIfNeeded($employeeid, $periodid);
}

$rs = query("
select
	payeventid,
	a.accountid,
	quantity,
	unit_price,
	amount,
	narrative,
	inputtype,
	derived,
	unix_timestamp(starttime) as starttime,
	unix_timestamp(endtime) as endtime,
	pg.groupid as payable
from payevent pe
join payaccount a on a.accountid=pe.accountid
left outer join payaccount_group pg 
on pg.accountid=pe.accountid and groupid=".GROUPID_PAYABLE."
where employeeid=$employeeid and periodid=$periodid
order by calcseq
");

?>

<?php head('Pay stub') ?>

<body>
<?php 
top("employees.php", "Pay stub", $employee->givenname . ' ' . $employee->surname);
if ($mess != null)
	echo "<center><p>$mess</p></center>";
?>


<?php if (!$selfservice) { ?>
	<div id="header">
	<?php buildTabs($employeeid, 'paystub') ?>
	</div>
	<div id="main">
		<div id="contents">
<?php } ?>

<center>
<form action="employee_paystub.php" method="GET">
	<table>
		<tr>
		<td><input type="submit" name="prev" value=" < "/></td>
		<td><?php displayPeriod($periodid) ?></td>
		<td><input type="submit" name="next" value=" > "/></td>
		</tr>
	</table>
	<input type="hidden" name="employeeid" value="<?php echo $employeeid0 ?>"/>
	<input type="hidden" name="periodid" value="<?php echo $periodid ?>"/>
</form>

<form action='employee_paystub.php' method=POST>
<input type=hidden name=employeeid value='<?php echo $employeeid0 ?>'/>
<table>
<th><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Account") ?></th>
<th><?php echo tr("Date") ?></th>
<th><?php echo tr("Quantity") ?></th>
<th><?php echo tr("Unit price") ?></th>
<th><?php echo tr("Amount") ?></th>
<th><?php echo tr("To pay") ?></th>

<?php
$class = "odd";
$i = 0;
while ($row = fetch($rs)) {
	echo "<input type=hidden name='accountid_$i' value='$row->accountid'/>";
	echo "<input type=hidden name='payeventid_$i' value='$row->payeventid'/>";
    $href = null;
    $deletable = false;
    if ($row->derived != 1) {
    	//$href = "payevent.php?payeventid=$row->payeventid&back=paystub";
    	$deletable = !$readonly;
    }
    $href = "payevent.php?payeventid=$row->payeventid&back=paystub";
    echo "<tr class='$class'>";
    echo "<td align=center>";
    if ($deletable)
	    deleteIcon("employee_paystub.php?employeeid=$employeeid0&del_payeventid=$row->payeventid");
    echo "</td>";
    echo "<td align=right><a href='$href'>$row->payeventid</a></td>";
	echo "<td>";
	if ($href != null)
		echo "<a href='$href'>";
	echo $row->accountid .' - '.$row->narrative;
	if ($href != null)
		echo "</a>";
	echo "</td>";
	echo "<td>";
	echo formatDateInterval($row->starttime, $row->endtime);
	echo "</td>";
	echo "<td align=right>" . formatQuantity($row->quantity, $row->inputtype) . "</td>";
	echo "<td align=right>";
	if ($row->unit_price != null)
		echo formatMoney($row->unit_price);
	echo "</td>";
	echo "<td align=right>";
	echo formatMoney($row->amount);
	echo "</td>";
	echo "<td align=right>";
	if ($row->payable != null)
		echo formatMoney($row->amount);	
	echo "</td>";
	echo "</tr>\n";
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}
?>
<input type=hidden name=count value='<?php echo $i ?>'/>
<tr class='<?php echo $class ?>'>
<td/>
<td><b>Total</b></td>
<td/>
<td/>
<td/>
<td/>
<?php
$payable = findValue("select sum(amount)
                      from payevent pe
                      join payaccount_group g on g.accountid=pe.accountid
                      and g.groupid=" . GROUPID_PAYABLE . "
                      where employeeid=$employeeid and periodid=$periodid") ?>
<td align=right><b><?php echo formatMoney($payable) ?></b></td>
<td/>
</tr>

</table>
<br/>
<?php
if (!$readonly) {
	button("Calculate", "calc");
	echo "&nbsp;";
	button("New", "add", "payevent.php?employeeid=$employeeid0&back=paystub");
	echo "&nbsp;";
	button("Print", "print", "payslip.php?employeeid=$employeeid0&periodid=$periodid");
}
?>
</form>
</center>

<?php if (!$selfservice) { ?>
		</div>
	</div>
<?php } ?>
<?php bottom() ?>
</body>
