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
	$countrycode = prepStringParam('countrycode');
	$email = getParam('email');
	$vatnumber = getParam('vatnumber');
	$use_vat = getParam('use_vat', 0);
	$pricelistid = getParam('pricelistid', 1);
	$credit_length = prepNull(getParam('credit_length'));
	if (isNew()) {
		$sql = "insert into customer (name, streetaddress, city, zipcode, countrycode, email, pricelistid,
			                              vatnumber, use_vat, credit_length)
		        values ('$name', '$streetaddress', '$city', '$zipcode', $countrycode, '$email', $pricelistid,
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
					countrycode=$countrycode,
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
			   countrycode,
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
$countries = rs2array(query("select countrycode, name from country order by name"));

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
	title(tr("Customer"));
	?>

	<main class="customer-editor-page">
		<header class="customer-editor-intro">
			<a class="customer-editor-back" href="customers.php?mode=<?php echo urlencode($mode) ?>" aria-label="<?php etr("Customers") ?>">&#8592;</a>
			<div class="customer-editor-avatar" aria-hidden="true"><?php echo !$new && !isEmpty($rec->name) ? htmlspecialchars(strtoupper(substr($rec->name, 0, 1))) : '+' ?></div>
			<div class="customer-editor-heading"><span><?php etr("Customer relationship") ?></span><h1><?php echo $new ? tr("Create customer") : htmlspecialchars($rec->name) ?></h1><p><?php etr("Manage contact, billing, tax, and pricing information.") ?></p></div>
			<?php if (!$new) { ?><div class="customer-editor-summary"><span>#<?php echo htmlspecialchars($customerid) ?></span><strong><?php echo formatMoney($balance) ?></strong><small><?php etr("Balance") ?></small></div><?php } ?>
		</header>

		<form action="customer.php" method="POST">
			<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode) ?>" />
			<?php if (!$new) hidden('customerid', $customerid); ?>

			<section class="customer-editor-card card border-0 shadow-sm">
				<div class="card-header bg-white customer-editor-card-header"><div><span><?php etr("Contact") ?></span><h2><?php etr("Customer details") ?></h2></div></div>
				<div class="card-body"><div class="row g-3">
					<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label><?php textbox("name", $rec->name) ?></div>
					<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="email"><?php etr("E-mail") ?></label><?php textbox("email", $rec->email, 30) ?></div>
					<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="streetaddress"><?php etr("Street address") ?></label><?php textbox("streetaddress", $rec->streetaddress, 30) ?></div>
					<div class="col-12 col-md-4"><label class="form-label fw-semibold" for="city"><?php etr("City") ?></label><?php textbox("city", $rec->city) ?></div>
					<div class="col-12 col-md-2"><label class="form-label fw-semibold" for="zipcode"><?php etr("Zip code") ?></label><?php textbox("zipcode", $rec->zipcode) ?></div>
					<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="countrycode"><?php etr("Country") ?></label><?php comboBox("countrycode", $countries, $rec->countrycode, true) ?></div>
				</div></div>
			</section>

			<section class="customer-editor-card card border-0 shadow-sm">
				<div class="card-header bg-white customer-editor-card-header"><div><span><?php etr("Communication") ?></span><h2><?php etr("Telephone numbers") ?></h2></div></div>
				<div class="card-body">
					<div class="customer-phone-list">
					<?php $phoneCount = 0; if ($phoneNumbers != null) while ($row = fetch($phoneNumbers)) {
						$phoneCount++;
						echo "<div class='customer-phone-item'><span>" . htmlspecialchars($row->description) . "</span><strong>" . htmlspecialchars($row->telephoneno) . "</strong><span>";
						deleteIcon("customer.php?customerid=" . urlencode($customerid) . "&del_telephoneno=" . urlencode($row->telephoneno));
						echo "</span></div>";
					} if ($phoneCount == 0) echo "<div class='customer-phone-empty'>" . tr("No telephone numbers added") . "</div>"; ?>
					</div>
					<div class="row g-3 align-items-end customer-phone-new"><div class="col-12 col-md-5"><label class="form-label fw-semibold" for="phonecatid_new"><?php etr("Telephone type") ?></label><?php combobox('phonecatid_new', $phonecats, null, true); ?></div><div class="col-12 col-md-7"><label class="form-label fw-semibold" for="telephoneno_new"><?php etr("Telephone number") ?></label><?php textbox('telephoneno_new', ''); ?></div></div>
				</div>
			</section>

			<section class="customer-editor-card card border-0 shadow-sm">
				<div class="card-header bg-white customer-editor-card-header"><div><span><?php etr("Commercial") ?></span><h2><?php etr("Pricing and tax") ?></h2></div></div>
				<div class="card-body"><div class="row g-3 align-items-end">
					<div class="col-12 col-md-4"><label class="form-label fw-semibold" for="pricelistid"><?php etr("Price list") ?></label><?php combobox("pricelistid", $pricelists, $rec->pricelistid, false) ?></div>
					<div class="col-12 col-md-4"><label class="form-label fw-semibold" for="vatnumber"><?php etr("VAT number") ?></label><?php textbox("vatnumber", $rec->vatnumber, 20) ?></div>
					<div class="col-12 col-md-2"><label class="form-label fw-semibold" for="credit_length"><?php etr("Credit length") ?></label><?php numberbox("credit_length", $rec->credit_length, 5) ?></div>
					<div class="col-12 col-md-2"><div class="customer-vat-toggle"><?php checkbox("use_vat", $rec->use_vat) ?><label for="use_vat"><?php etr("Use VAT") ?></label></div></div>
				</div></div>
			</section>

			<div class="customer-editor-actions"><?php saveButton() ?></div>
			<input type="hidden" name="new" value="<?php echo $new ?>" />
		</form>
	</main>
	<?php bottom() ?>
</body>
