<?php
include('include.php');
include('calculations.php');

header('Content-type: text/plain');

$sql = "
select
	employeeid,
	givenname,
	surname,
	bank_account
from employee";
$employees = query($sql);

$periodid = getCurrentPeriod();
$q = query($sql);
while ($row = fetch($employees)) {
	$paystub = createPayStub($row->employeeid, $periodid);
	echo $row->employeeid . ';';
	echo $row->bank_account . ';';
    printf('%9.2f', $paystub->netPayment);
    echo "\n";
}

?>