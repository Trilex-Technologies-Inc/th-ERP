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
<?php include_datebox() ?>
</head>

<body>

<?php include("menubar.php") ?>
<?php payEventTitle($employeeid, "Pay item") ?>
<?php title(tr("Pay item")) ?>

<form action="payitem.php" method=POST name='form1' class="border">
<input type=hidden name=periodid value="<?php echo $periodid ?>"/>
<input type=hidden name=no value="<?php echo $no ?>"/>
<?php hiddenParams() ?>
<div class="container-fluid px-0 erp-form-layout">
<?php eventTypeRow('payitem') ?>
<div class="row g-3 align-items-center mb-2">
  <div class="col-12 col-md-auto">Period:</div>
  <div class="col-12 col-md-auto"><?php displayPeriod($periodid) ?></div>
</div><div class="row g-3 align-items-center mb-2">
  <div class="col-12 col-md-auto">Type:</div>
  <div class="col-12 col-md-auto"><?php comboBox('accountid', $accounts, $accountid, false) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
  <div class="col-12 col-md-auto">Amount:</div>
  <div class="col-12 col-md-auto"><input type=text name='amount' value='<?php echo $amount ?>'/></div>
</div>
</div>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php button("Submit", "save") ?></div>
</div>
</div>
&nbsp;
<?php backButton($employeeid) ?>
</form>
</body>
