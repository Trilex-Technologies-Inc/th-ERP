<?php
include('include.php');
include('calculations.php');

$mess = null;

function lockPeriod($periodid)
{
	$rs = query("
	select employeeid, givenname, surname, glaccountid
	from employee e
	join policy p on p.policyid=e.policyid 
	");
	while ($emp = fetch($rs)) {
		calculateIfNeeded($emp->employeeid, $periodid);
		$narrative = "Salary payment $emp->employeeid - $emp->givenname $emp->surname, period $periodid";
		$transid = findValue("
		select max(transactionid) from transaction", 0);
		$transid++;
		sql("insert into transaction (transactionid, narrative, transtime, createdby, createdtime)
		     values ($transid, '$narrative', now(), '" . getUser() . "', now())");
				
		sql("insert into transaction_part (transactionid, accountid, amount)
		     select $transid,
			   glaccountid,
			   sum(share*amount)
			 from payevent e
			 join payevent_debit pd on pd.payeventid=e.payeventid
			 where periodid=$periodid and glaccountid is not null
			 and employeeid=$emp->employeeid
			 group by glaccountid");
		$amount = findValue("select sum(amount) from transaction_part where transactionid=$transid");
		$amount = (-1) * $amount;
		$accountid = findValue("select default_cash from accountconf");
		sql("insert into transaction_part (transactionid, accountid, amount) values ($transid, $accountid, $amount)");
	}
	sql("update payperiod set locked=1 where periodid=$periodid");
}

if (!isEmpty(getParam('lock'))) {
	$periodid = getCurrentPeriod();
	if (isEmpty($periodid))
		$mess = tr("There is no open payroll period to lock.");
	else
		tx("lockPeriod", array((int)$periodid));
}
if (!isEmpty(getParam('unlock'))) {
	$periodid = findValue("select max(periodid) from payperiod where locked=1");
	if (isEmpty($periodid))
		$mess = tr("There is no locked payroll period to unlock.");
	else {
		$periodid = (int)$periodid;
		sql("update payperiod set locked=0 where periodid=$periodid");
	}
}

$periodid = getCurrentPeriod();
$period = null;
if (!isEmpty($periodid)) {
	$periodid = (int)$periodid;
	$pattern = DATE_PATTERN_MYSQL;
	$sql = <<<SQL
  select p.periodid,
	date_format(starttime, '$pattern') as starttime,
	date_format(endtime, '$pattern') as endtime
  from payperiod p
  where p.periodid=$periodid
SQL;
	$period = find($sql);
}

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php etr("End of period") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("endofperiod.php", "end") ?>
<?php title(tr("End of period")) ?>

<?php if ($mess) { ?><div class="alert alert-warning"><?php echo htmlspecialchars($mess) ?></div><?php } ?>
<?php if ($period) { ?>
<?php etr("Current period is") ?>:
<?php echo htmlspecialchars($period->starttime . ' - ' . $period->endtime); ?><br/>
<?php } else { ?>
<div class="alert alert-info"><?php etr("There is no open payroll period. Unlock the last period or create a new period.") ?></div>
<?php } ?>
<br/>
<form action='endofperiod.php' method=POST>
<input type=submit name='lock' value='<?php etr("Lock current period") ?>' <?php if (!$period) echo 'disabled'; ?>/>
<input type=submit name='unlock' value='<?php etr("Unlock last period") ?>'/>
</form>
<ul>
<li><a href='payment_report.php'><?php etr("Payment report") ?></a></li>
<li><a href='payment_file.php'><?php etr("Payment file") ?></a></li>
<li><a href='statistics.php'><?php etr("Statistics") ?></a></li>
</li>


</body>
