<?php
include('include.php');
include('salesorder.inc.php');
include('period.inc.php');

$cycleid = 1;
$periodid = getParam('periodid');

if (!isEmpty(getParam('create'))) {
	tx("createReceivables", array($cycleid, $periodid));
}
if (!isEmpty(getParam('send'))) {
	tx("sendReceivables", array($cycleid, $periodid));
}

if (!isEmpty(getParam('timedebit'))) {
	tx("createTimeDebitInvoices", array());
}

$period = find("
select unix_timestamp(starttime) as starttime,
	unix_timestamp(endtime) as endtime,
	state_receivables,
	periodid
from period p
where periodid=(
	select min(periodid) 
	from period p2
	where state_receivables != " . STATE_RECEIVABLES_SENT . " 
	or state_receivables is null
	and p2.cycleid=p.cycleid
	and cycleid)
and cycleid=$cycleid");
?>

<?php head('Period') ?>

<body>

<?php top("period.php", "Period") ?>

<br/>
<?php etr("Current period is") ?>:
<?php echo formatDate($period->starttime) . ' - ' . formatDate($period->endtime); ?><br/>
<br/>
<form action='period.php' method=POST>
<?php
hidden('periodid', $period->periodid);

if ($period->state_receivables != STATE_RECEIVABLES_CREATED)
	button("Create recurring receivables", 'create');
if ($period->state_receivables != null || $period->state_receivables == STATE_RECEIVABLES_CREATED)
	button("Send receivables", 'send');
	
echo "<br><br><br>";
button("Create time debit invoices", 'timedebit');
?>
</form>
<?php bottom() ?>
</body>
