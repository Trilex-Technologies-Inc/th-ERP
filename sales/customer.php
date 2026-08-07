<?php
include('include.php');
include('salesorder.inc.php');

$customerid = getParam('customerid');
$mode = getParam('mode');
$new = true;
if (isSave()) {
	$name = getParam('name');
	$streetaddress = getParam('streetaddress');
	$city = getParam('city');
	$zipcode = getParam('zipcode');
	$email = getParam('email');
	$vatnumber = getParam('vatnumber');
	$use_vat = getParam('use_vat', 0);
	$pricelistid = getParam('pricelistid', 1);
	$credit_length = prepNull(getParam('credit_length'));
	if (isNew()) {
		$sql = "insert into customer (name, streetaddress, city, zipcode, email, pricelistid,
			                              vatnumber, use_vat, credit_length)
			        values ('$name', '$streetaddress', '$city', '$zipcode', '$email', $pricelistid,
			                '$vatnumber', $use_vat, $credit_length)";
		sql($sql);
		$customerid = insert_id();
	} else {
		$updateSQL =
			"update customer set
					name='$name',
					streetaddress='$streetaddress',
					city='$city',
					zipcode='$zipcode',
					email='$email',
					vatnumber='$vatnumber',
					use_vat=$use_vat,
					pricelistid=$pricelistid,
					credit_length=$credit_length
				where customerid=$customerid";
		sql($updateSQL);
	}
	if ($mode == 'createorder') {
		header("Location: customers.php?mode=$mode");
		die;
	}
	$phonecatid_new = getParam('phonecatid_new');
	if (!isEmpty($phonecatid_new)) {
		$telephoneno_new = getParam('telephoneno_new');
		sql("insert into customer_phone (customerid, telephoneno, phonecatid)
			     values ($customerid, '$telephoneno_new', $phonecatid_new)");
	}
}
$del_telephoneno = getParam('del_telephoneno');
if (!isEmpty($del_telephoneno)) {
	sql("delete from customer_phone where customerid=$customerid and telephoneno='$del_telephoneno'");
}

$rec = new Dummy();
$rec->use_vat = 1;
$balance = 0;
$phoneNumbers = null;
if (!isEmpty($customerid)) {
	$selectSQL =
		"select customerid,
		       name,
			   streetaddress,
			   city,
			   zipcode,
			   email,
			   vatnumber,
			   use_vat,
			   credit_length,
			   pricelistid
		from customer
		where customerid=$customerid
		";
	$rec = find($selectSQL);
	$phoneNumbers = query("
		select telephoneno, cp.phonecatid, description
		from customer_phone cp
		join phone_category c on c.phonecatid=cp.phonecatid
		where customerid=$customerid
		");
	$balance = getCustomerBalance($customerid);
	$new = false;
}

$phonecats = rs2array(query("
	select phonecatid, description from phone_category"));
$phonecats = array_merge(
	array(array('', "-- " . tr("Telephone type") . " --")),
	$phonecats
);
$pricelists = rs2array(query("select listid, description from pricelist"));

?>

<head>
	<title>thERP - <?php etr("Customer") ?></title>
	<?php
	styleSheet();
	?>
</head>

<body>
	<?php menubar('customers.php') ?>
	<?php
	$title = "<a href='customers.php'>" . tr("Customers") . "</a> > ";
	if ($mode == 'createorder')
		$title = tr("Create order") . " > ";
	if ($new)
		$title .= tr("Create customer");
	else
		$title .= "$rec->name";
	title($title);
	?>

	<form action="customer.php" method="POST">
		<input type=hidden name=mode value='<?php echo $mode ?>' />
		<div class="border p-3 mb-4">
			<div class="row g-3">
				<div class="col-md-3">
					<label class="form-label"><?php etr("Customer id") ?></label>
					<div class="form-control-plaintext"><?php if (!$new) {
															echo $customerid;
															hidden('customerid', $customerid);
														} ?></div>
				</div>
				<div class="col-md-6">
					<label class="form-label"><?php echo tr("Name") ?></label>
					<?php textbox("name", $rec->name) ?>
				</div>
				<div class="col-md-6">
					<label class="form-label"><?php echo tr("Street address") ?></label>
					<?php textbox("streetaddress", $rec->streetaddress, 30) ?>
				</div>
				<div class="col-md-3">
					<label class="form-label"><?php echo tr("City") ?></label>
					<?php textbox("city", $rec->city) ?>
				</div>
				<div class="col-md-3">
					<label class="form-label"><?php echo tr("Zip code") ?></label>
					<?php textbox("zipcode", $rec->zipcode) ?>
				</div>
				<div class="col-md-6">
					<label class="form-label"><?php echo tr("E-mail") ?></label>
					<?php textbox("email", $rec->email, 30) ?>
				</div>
			</div>
			<div class="row g-3 mt-4">
				<div class="col-12">
					<strong><?php etr("Telephone numbers") ?></strong>
				</div>
				<?php
				while ($row = fetch($phoneNumbers)) {
					echo "<div class='row g-2 align-items-center'>";
					echo "<div class='col-auto'>$row->description</div>";
					echo "<div class='col'>";
					echo $row->telephoneno;
					echo "&nbsp;";
					deleteIcon("customer.php?customerid=$customerid&del_telephoneno=$row->telephoneno");
					echo "</div>";
					echo "</div>";
				}
				?>
			</div>
			<div class="row g-3 align-items-end mt-3">
				<div class="col-md-4"><?php combobox('phonecatid_new', $phonecats, null, true); ?></div>
				<div class="col-md-4"><?php textbox('telephoneno_new', ''); ?></div>
			</div>
			<div class="row g-3 mt-4">
				<div class="col-md-4">
					<label class="form-label"><?php echo tr("Price list") ?></label>
					<?php combobox("pricelistid", $pricelists, $rec->pricelistid, false) ?>
				</div>
				<div class="col-md-4">
					<label class="form-label"><?php echo tr("VAT number") ?></label>
					<?php textbox("vatnumber", $rec->vatnumber, 20) ?>
				</div>
				<div class="col-md-2">
					<label class="form-label"><?php echo tr("Credit length") ?></label>
					<?php numberbox("credit_length", $rec->credit_length, 5) ?>
				</div>
				<div class="col-md-2">
					<label class="form-label"><?php echo tr("Use VAT") ?></label>
					<?php checkbox("use_vat", $rec->use_vat) ?>
				</div>
			</div>
			<div class="row g-3 mt-3">
				<div class="col-md-4">
					<label class="form-label"><?php echo tr("Balance") ?></label>
					<div class="form-control-plaintext"><?php echo formatMoney($balance) ?></div>
				</div>
			</div>
		</div>
		<br />
		<?php saveButton() ?>
		<input type="hidden" name="new" value="<?php echo $new ?>" />
	</form>
	<?php bottom() ?>
</body>