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
<table>
<tr>
	<td><?php etr("Customer") ?>:</td>
	<td><?php echo $customer ?></td>
</tr>
<tr>
	<td><?php etr("To") ?>:</td>
	<td><?php textbox('to', $to, 60) ?></td>
</tr>
<tr>
	<td><?php etr("CC") ?>:</td>
	<td><?php textbox('cc', $from, 60) ?></td>
</tr>
<tr>
	<td><?php etr("From") ?>:</td>
	<td><?php textbox('from', $from, 40) ?></td>
</tr>
<tr>
	<td><?php etr("Subject") ?>:</td>
	<td><?php textbox('subject', $subject, 80) ?></td>
</tr>
<tr>
	<td colspan=2>
		<textarea name='body' cols=80 rows=10><?php echo $body ?></textarea>
	</td>
</tr>
</table>
<table>
<tr>
<td><input type=submit name=send value='Send e-mail'></td>
<td>&nbsp;</td>
<td><?php echo "<a href='invoice_pdf.php?orderid=$orderid'>" . tr("Attachment") . "</a>"; ?></td>
</tr>
</table>
</form>
<?php bottom() ?>
</body>
