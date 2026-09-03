<?php
include('include.php');
include('payevent_include.php');

$employeeid = getParam('employeeid');
$accountid = getParam('accountid');
$no = getParam('no');
$amount = '';
$accountid = null;

$periodid = getParam('periodid');
if (isEmpty($periodid))
	$periodid = getCurrentPeriod();

if (isSave()) {
	$amount = getParam("amount");
	$accountid = getParam('accountid');
	if (!isEmpty($no)) {
		$sql =
		  "update payitem
		   set amount=$amount,
			   accountid=$accountid
		   where employeeid=$employeeid and
				 periodid=$periodid and
				 no=$no
		   ";
		sql($sql);
	} else {
		$no = findValue("select max(no) from payitem where employeeid=$employeeid and periodid=$periodid", 0) + 1;
		$sql = "insert into payitem (employeeid, periodid, no, amount, accountid) ";
		$sql .= "values ($employeeid, $periodid, $no, $amount, $accountid) ";
		sql($sql);
	}
}

if (!isEmpty($accountid)) {
	$rec = find("select
	               amount,
	               accountid
	             from payitem
	             where employeeid=$employeeid and
	                   periodid=$periodid and
	                   no=$no");
	$amount = $rec->amount;
	$accountid = $rec->accountid;
}

$accounts = rs2array(query("select accountid, description from payaccount"));

?>

<head>
<?php metatag() ?>
<title>Payroll - <?php etr("Pay item") ?></title>
<?php styleSheet() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php payEventTitle($employeeid, "Pay item") ?>
<?php title(tr("Pay item")) ?>

<form action="payitem.php" method="POST" name="form1">
<input type="hidden" name="periodid" value="<?php echo htmlspecialchars($periodid) ?>"/>
<input type="hidden" name="no" value="<?php echo htmlspecialchars($no) ?>"/>
<?php hiddenParams() ?>
<div class="card border-0 shadow-sm">
<div class="card-header bg-white py-3">
	<h2 class="h5 fw-bold mb-1"><?php etr("Pay item") ?></h2>
	<p class="text-secondary small mb-0"><?php displayPeriod($periodid) ?></p>
</div>
<div class="card-body p-4">
<div class="row g-4">
<?php eventTypeRow('payitem') ?>
<div class="col-12 col-lg-6">
	<label class="form-label fw-semibold"><?php etr("Type") ?></label>
	<?php comboBox('accountid', $accounts, $accountid, false) ?>
</div>
<div class="col-12 col-lg-6">
	<label class="form-label fw-semibold"><?php etr("Amount") ?></label>
	<input class="form-control" type="text" name="amount" value="<?php echo htmlspecialchars($amount) ?>"/>
</div>
</div>
</div>
<div class="card-footer bg-white d-flex flex-wrap gap-2 py-3">
	<?php button("Submit", "save") ?>
	<?php backButton($employeeid) ?>
</div>
</div>
</form>
<?php bottom() ?>
</body>
