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

<main class="employee-detail-page">
	<div class="employee-detail-tabs">
		<?php buildTabs($employeeid, 'payitems') ?>
	</div>

<form action="employee_payitems.php" method="POST">
<input type="hidden" name="employeeid" value="<?php echo htmlspecialchars($employeeid) ?>"/>
<div class="card border-0 shadow-sm overflow-hidden">
	<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
		<div>
			<h2 class="h5 fw-bold mb-1"><?php etr("Pay items") ?></h2>
			<p class="text-secondary small mb-0"><?php echo htmlspecialchars(getEmployeeStr($employeeid)) ?> &middot; <?php etr("Recurring payroll values") ?></p>
		</div>
		<span class="badge text-bg-light border"><?php etr("Current period") ?></span>
	</div>
	<div class="table-responsive">
	<table class="table table-hover align-middle mb-0">
	<thead><tr>
		<th class="text-center" style="width: 80px;"><?php echo tr("Delete") ?></th>
		<th style="width: 90px;"><?php echo tr("No") ?></th>
		<th><?php echo tr("Account") ?></th>
		<th style="width: 220px;"><?php echo tr("Value") ?></th>
	</tr></thead>
	<tbody>
<?php
$i = 0;
while ($row = fetch($rs)) {
    echo "<tr>";
	echo "<td class='text-center'>";
	echo "<input type='hidden' name='no_$i' value='" . htmlspecialchars($row->no) . "'/>";
	deleteIcon("employee_payitems.php?del_no=$row->no&employeeid=$employeeid");
	echo "</td>";
    echo "<td class='font-monospace text-secondary'>" . htmlspecialchars($row->no) . "</td>";
    echo "<td>";
    comboBox("accountid_$i", $accounts, $row->accountid, false);
    echo "</td>";
    echo "<td>";
	numberbox("value_$i", $row->value);
	echo "</td>";
    echo "</tr>";
    $i++;
}
hidden('count', $i);
?>
<tr class="table-light">
<td class="text-center text-secondary fw-bold">+</td>
<td><span class="badge text-bg-primary"><?php etr("New") ?></span></td>
<td><?php comboBox('accountid_new', $accounts, null, true) ?></td>
<td><input class="form-control" type="number" step="any" name="value_new" aria-label="<?php echo tr("New value") ?>"/></td>
</tr>
	</tbody>
	</table>
	</div>
	<div class="card-footer bg-white d-flex justify-content-between align-items-center gap-2 py-3">
		<span class="text-secondary small"><?php echo $i ?> <?php etr("pay items") ?></span>
		<?php saveButton() ?>
	</div>
</div>
</form>
</main>
<?php bottom() ?>
</body>
</html>
