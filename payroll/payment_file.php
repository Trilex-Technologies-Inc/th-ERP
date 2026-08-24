<?php
include('include.php');
include('calculations.php');

header('Content-type: text/plain');

$periodid = getCurrentPeriod();
if (isEmpty($periodid)) {
	echo tr("There is no open payroll period. Unlock the last period or create a new period.") . "\n";
	exit;
}
$periodid = (int)$periodid;

$sql = "
select
	employeeid,
	givenname,
	surname,
	bank_account
from employee";
$employees = query($sql);

while ($row = fetch($employees)) {
	$paystub = createPayStub($row->employeeid, $periodid);
	echo $row->employeeid . ';';
	echo $row->bank_account . ';';
    printf('%9.2f', $paystub->netPayment);
    echo "\n";
}

?>
