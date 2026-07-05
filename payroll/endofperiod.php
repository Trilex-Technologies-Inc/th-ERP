<?php
include('include.php');
include('calculations.php');

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
	tx("lockPeriod", array($periodid));
}
if (!isEmpty(getParam('unlock'))) {
	$periodid = findValue("select max(periodid) from payperiod where locked=1");
	sql("update payperiod set locked=0 where periodid=$periodid");
}

$periodid = getCurrentPeriod();

$pattern = DATE_PATTERN_MYSQL;
$sql = <<<SQL
  select p.periodid,
	date_format(starttime, '$pattern') as starttime,
	date_format(endtime, '$pattern') as endtime
  from payperiod p
  where p.periodid=$periodid
SQL;
$period = find($sql);

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php etr("End of period") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php menubar("endofperiod.php", "end") ?>

<br/>
<?php etr("Current period is") ?>:
<?php echo $period->starttime . ' - ' . $period->endtime; ?><br/>
<br/>
<form action='endofperiod.php' method=POST>
<input type=submit name='lock' value='<?php etr("Lock current period") ?>'/>
<input type=submit name='unlock' value='<?php etr("Unlock last period") ?>'/>
</form>
<ul>
<li><a href='payment_report.php'><?php etr("Payment report") ?></a></li>
<li><a href='payment_file.php'><?php etr("Payment file") ?></a></li>
<li><a href='statistics.php'><?php etr("Statistics") ?></a></li>
</li>


</body>
