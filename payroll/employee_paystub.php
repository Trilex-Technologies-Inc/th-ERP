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
?>

<main class="employee-detail-page">
<?php if (!$selfservice) { ?>
	<div class="employee-detail-tabs"><?php buildTabs($employeeid, 'paystub') ?></div>
<?php } ?>
<?php if ($mess != null) { ?><div class="alert alert-info" role="alert"><?php echo htmlspecialchars($mess) ?></div><?php } ?>

<form action="employee_paystub.php" method="GET" class="d-flex justify-content-center align-items-center gap-3 mb-3">
	<button class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px" type="submit" name="prev" aria-label="<?php etr("Previous period") ?>">&#8249;</button>
	<div class="text-center">
		<div class="text-uppercase text-secondary fw-bold" style="font-size:.7rem;letter-spacing:.08em"><?php etr("Pay period") ?> #<?php echo htmlspecialchars($periodid) ?></div>
		<div class="fw-bold mt-1"><?php displayPeriod($periodid) ?></div>
	</div>
	<button class="btn btn-outline-secondary rounded-circle" style="width:42px;height:42px" type="submit" name="next" aria-label="<?php etr("Next period") ?>">&#8250;</button>
	<input type="hidden" name="employeeid" value="<?php echo $employeeid0 ?>"/>
	<input type="hidden" name="periodid" value="<?php echo $periodid ?>"/>
</form>

<form action="employee_paystub.php" method="POST">
<input type="hidden" name="employeeid" value="<?php echo htmlspecialchars($employeeid0) ?>"/>
<div class="card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
	<div><h2 class="h5 fw-bold mb-1"><?php etr("Pay stub") ?></h2><p class="text-secondary small mb-0"><?php echo htmlspecialchars($employee->givenname . ' ' . $employee->surname) ?></p></div>
	<span class="badge <?php echo $readonly ? 'text-bg-light border' : 'text-bg-success' ?>"><?php echo $readonly ? tr("Closed period") : tr("Current period") ?></span>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead><tr><th class="text-center" style="width:75px"><?php echo tr("Delete") ?></th>
<th><?php echo tr("Id") ?></th>
<th><?php echo tr("Account") ?></th>
<th><?php echo tr("Date") ?></th>
<th><?php echo tr("Quantity") ?></th>
<th><?php echo tr("Unit price") ?></th>
<th><?php echo tr("Amount") ?></th>
<th class="text-end"><?php echo tr("To pay") ?></th></tr></thead><tbody>

<?php
$i = 0;
while ($row = fetch($rs)) {
    $href = null;
    $deletable = false;
    if ($row->derived != 1) {
    	//$href = "payevent.php?payeventid=$row->payeventid&back=paystub";
    	$deletable = !$readonly;
    }
    $href = "payevent.php?payeventid=$row->payeventid&back=paystub";
    echo "<tr>";
    echo "<td class='text-center'>";
	echo "<input type='hidden' name='accountid_$i' value='" . htmlspecialchars($row->accountid) . "'/><input type='hidden' name='payeventid_$i' value='" . htmlspecialchars($row->payeventid) . "'/>";
    if ($deletable)
	    deleteIcon("employee_paystub.php?employeeid=$employeeid0&del_payeventid=$row->payeventid");
    echo "</td>";
    echo "<td class='font-monospace'><a href='$href'>" . htmlspecialchars($row->payeventid) . "</a></td>";
	echo "<td>";
	if ($href != null)
		echo "<a href='$href'>";
	echo htmlspecialchars($row->accountid . ' - ' . $row->narrative);
	if ($href != null)
		echo "</a>";
	echo "</td>";
	echo "<td>";
	echo formatDateInterval($row->starttime, $row->endtime);
	echo "</td>";
	echo "<td class='text-end'>" . formatQuantity($row->quantity, $row->inputtype) . "</td>";
	echo "<td class='text-end'>";
	if ($row->unit_price != null)
		echo formatMoney($row->unit_price);
	echo "</td>";
	echo "<td class='text-end fw-semibold'>";
	echo formatMoney($row->amount);
	echo "</td>";
	echo "<td class='text-end fw-bold'>";
	if ($row->payable != null)
		echo formatMoney($row->amount);	
	echo "</td>";
	echo "</tr>\n";
    $i++;
}
?>
<tr class="table-light">
<td><input type="hidden" name="count" value="<?php echo $i ?>"/></td>
<td colspan="5"><strong><?php etr("Total payable") ?></strong></td>
<?php
$payable = findValue("select sum(amount)
                      from payevent pe
                      join payaccount_group g on g.accountid=pe.accountid
                      and g.groupid=" . GROUPID_PAYABLE . "
                      where employeeid=$employeeid and periodid=$periodid") ?>
<td></td>
<td class="text-end"><strong><?php echo formatMoney($payable) ?></strong></td>
</tr>

</tbody></table></div>
<div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
	<span class="text-secondary small"><?php echo $i ?> <?php etr("entries") ?></span>
	<div class="d-flex flex-wrap gap-2">
<?php
if (!$readonly) {
	button("Calculate", "calc");
	button("New", "add", "payevent.php?employeeid=$employeeid0&back=paystub");
	button("Print", "print", "payslip.php?employeeid=$employeeid0&periodid=$periodid");
}
?>
	</div>
</div>
</div>
</form>
</main>
<?php bottom() ?>
</body>
