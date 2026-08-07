<?php
include('include.php');
include('invoice_pdf.inc.php');
include('salesorder.inc.php');

$orderid = getParam('orderid');

if (array_key_exists('send', $_POST)) {
	$to = getParam('to');
	$cc = getParam('cc');
	$from = getParam('from');
	$subject = getParam('subject');
	$body = getParam('body');
	email_invoice($orderid, $to, $cc, $from, $subject, $body);
	header("Location: salesorder.php?orderid=$orderid");
}

$row = find("
	select email, name
	from customer c
	join salesorder o on o.customerid=c.customerid
	where orderid=$orderid");
$to = $row->email;
$customer = $row->name;
$from = findValue("select email from companyinfo");
$company = findValue("select companyname from companyinfo");
$subject = "Invoice from $company";
$body = "See the attached PDF-file";

?>

<head>
	<title>thERP - <?php etr("Customer") ?></title>
	<?php
	styleSheet();
	?>
</head>

<body>
	<?php menubar('index.php') ?>
	<?php
	$title = "<a href='sales.php'>" . tr("Sales orders") . "</a> > ";
	$title .= "<a href='salesorder.php?orderid=$orderid'>$orderid</a>";
	title($title);
	?>

	<form action="email_invoice.php" method="POST">
		<?php hidden('orderid', $orderid) ?>
		<div class="row g-3 mb-3">
			<div class="col-md-6">
				<label class="form-label"><?php etr("Customer") ?></label>
				<div class="form-control-plaintext"><?php echo htmlspecialchars($customer) ?></div>
			</div>
			<div class="col-md-6">
				<label class="form-label"><?php etr("To") ?></label>
				<?php textbox('to', $to, 60) ?>
			</div>
			<div class="col-md-6">
				<label class="form-label"><?php etr("CC") ?></label>
				<?php textbox('cc', $from, 60) ?>
			</div>
			<div class="col-md-6">
				<label class="form-label"><?php etr("From") ?></label>
				<?php textbox('from', $from, 40) ?>
			</div>
			<div class="col-12">
				<label class="form-label"><?php etr("Subject") ?></label>
				<?php textbox('subject', $subject, 80) ?>
			</div>
			<div class="col-12">
				<label class="form-label"><?php etr("Body") ?></label>
				<textarea name='body' cols=80 rows=10 class='form-control'><?php echo htmlspecialchars($body) ?></textarea>
			</div>
		</div>
		<div class="d-flex gap-2 align-items-center">
			<input type=submit name=send value='Send e-mail' class='btn btn-primary'>
			<?php echo "<a href='invoice_pdf.php?orderid=$orderid' class='btn btn-outline-secondary'>" . tr("Attachment") . "</a>"; ?>
		</div>
	</form>
	<?php bottom() ?>
</body>