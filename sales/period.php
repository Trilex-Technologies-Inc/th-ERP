<?php
include('include.php');
include('salesorder.inc.php');
include('period.inc.php');

$cycleid = 1;
$periodid = getParam('periodid');
$period = getReceivablesPeriod($cycleid);
if (isEmpty($periodid) && $period != null)
	$periodid = $period->periodid;

if (!isEmpty(getParam('create'))) {
	tx("createReceivables", array($cycleid, $periodid));
}
if (!isEmpty(getParam('send'))) {
	tx("sendReceivables", array($cycleid, $periodid));
}

if (!isEmpty(getParam('timedebit'))) {
	tx("createTimeDebitInvoices", array());
}

$period = getReceivablesPeriod($cycleid);
?>

<?php head('Period') ?>

<body>

<?php top("period.php", "Period") ?>

<br/>
<?php if ($period == null) { ?>
<div class="alert alert-info" role="status"><?php etr("There are no receivables periods waiting to be processed.") ?></div>
<?php } else { ?>
<?php etr("Current period is") ?>:
<?php echo formatDate($period->starttime) . ' - ' . formatDate($period->endtime); ?><br/><br/>
<form action='period.php' method=POST>
<?php
hidden('periodid', $period->periodid);

if ($period->state_receivables != STATE_RECEIVABLES_CREATED)
	button("Create recurring receivables", 'create');
if ($period->state_receivables == STATE_RECEIVABLES_CREATED)
	button("Send receivables", 'send');
	
echo "<br><br><br>";
button("Create time debit invoices", 'timedebit');
?>
</form>
<?php } ?>
<?php bottom() ?>
</body>
