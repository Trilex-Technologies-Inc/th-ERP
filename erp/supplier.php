<?php
	include('include.php');

	$supplierid = getParam('supplierid');
	$mode = getParam('mode');
	$new = true;
	if (isSave()) {
		$name = getParam('name');
		$supplierid = prepNull($supplierid);
		$streetaddress = getParam('streetaddress');
		$city = getParam('city');
		$zipcode = getParam('zipcode');
		$email = getParam('email');
		$contact = getParam('contact');
		$vatnumber = getParam('vatnumber');
		$credit_account = prepNull(getParam('credit_account'));
		$credit_length = prepNull(getParam('credit_length'));
		$countrycode = prepNull(getParam('countrycode'));
		if (isNew()) {
			$sql = "
			insert into supplier (
				supplierid, 
				name, 
				streetaddress, 
				city, 
				zipcode, 
				email, 
				vatnumber, 
				credit_account, 
				credit_length, 
				countrycode,
				contact) 
  			values (
				$supplierid, 
				'$name', 
				'$streetaddress', 
				'$city', 
				'$zipcode', 
				'$email', 
				'$vatnumber', 
				$credit_account, 
				$credit_length, 
				'$countrycode',
				'$contact')";
			sql($sql);
			$supplierid = insert_id();
		} else {
            $updateSQL =
    			"update supplier set
    			    name='$name',
					streetaddress='$streetaddress',
					city='$city',
					zipcode='$zipcode',
					email='$email',
					vatnumber='$vatnumber',
                    credit_account=$credit_account,					
                    credit_length=$credit_length,
                    countrycode='$countrycode',
                    contact='$contact'				
                where supplierid=$supplierid";
    		sql($updateSQL);
		}
		if ($mode == 'createpayable') {
			header("Location: suppliers.php?mode=$mode");
			die;
		}		
		$phonecatid_new = getParam('phonecatid_new');
		if (!isEmpty($phonecatid_new)) {
			$telephoneno_new = getParam('telephoneno_new');
			sql("insert into supplier_phone (supplierid, telephoneno, phonecatid)
			     values ($supplierid, '$telephoneno_new', $phonecatid_new)");
		}
	}

	$del_telephoneno = getParam('del_telephoneno');
	if (!isEmpty($del_telephoneno)) {
		sql("delete from supplier_phone where supplierid=$supplierid and telephoneno='$del_telephoneno'");
	}
	
	$rec = new Dummy();
	$phoneNumbers = null;
	if (!isEmpty($supplierid)) {
	    $selectSQL =
  		"select supplierid,
		       name,
			   streetaddress,
			   city,
			   zipcode,
			   email,
			   vatnumber,
			   credit_account,
			   credit_length,
			   countrycode,
			   contact
		from supplier
		where supplierid=$supplierid
		";
		$rec = find($selectSQL);
		$new = false;
		$phoneNumbers = query("
		select telephoneno, cp.phonecatid, description
		from supplier_phone cp
		join phone_category c on c.phonecatid=cp.phonecatid
		where supplierid=$supplierid
		");		
	}
	
	$creditAccounts = rs2array(query("select a.accountid, concat(a.accountid, ' - ', name)
	                                  from account a
									  join account_group ag on ag.accountid=a.accountid 
									  and groupid=" . GROUPID_LIABILITIES));
	$phonecats = rs2array(query("
	select phonecatid, description from phone_category"));
	$phonecats = array_merge(
					array(array('', "-- " . tr("Telephone type") . " --")),
                    $phonecats);
    $countries = rs2array(query("
    select countrycode, name from country"));
	
?>
<head>
<title>thERP - Supplier</title>
<?php styleSheet() ?>
</head>

<body>
<?php 
menubar('purchase.php');
$title = htmlspecialchars($rec->name);
if ($new)
	$title = tr("Create");
title("<a href='suppliers.php?mode=" . htmlspecialchars($mode) . "'>" . tr("Suppliers") . "</a> > $title");
?>

<form action="supplier.php" method="POST" class="supplier-editor">
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode) ?>"/>

<header class="supplier-editor-intro">
	<div class="supplier-editor-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M16 11h6"/></svg>
	</div>
	<div>
		<span class="supplier-editor-eyebrow"><?php echo $mode == 'createorder' ? tr("Purchase order setup") : tr("Supplier directory") ?></span>
		<h1><?php echo $new ? tr("Create supplier") : htmlspecialchars($rec->name) ?></h1>
		<p><?php echo $mode == 'createorder' ? tr("Add the supplier details needed for your new purchase order.") : tr("Maintain supplier contact and purchasing information.") ?></p>
	</div>
	<?php if ($mode == 'createorder') { ?>
		<div class="supplier-editor-step"><span>1</span><div><small><?php etr("Purchase order") ?></small><strong><?php etr("New supplier") ?></strong></div></div>
	<?php } else if (!$new) { ?>
		<span class="supplier-editor-id"><?php etr("Supplier") ?> #<?php echo htmlspecialchars($supplierid) ?></span>
	<?php } ?>
</header>

<section class="card border-0 shadow-sm supplier-editor-card">
	<div class="card-body">
		<div class="supplier-editor-section-heading">
			<div><span><?php etr("Company") ?></span><h2><?php etr("Supplier information") ?></h2></div>
			<?php if (!$new) { ?><span class="supplier-inline-id">#<?php echo htmlspecialchars($supplierid) ?></span><?php } ?>
		</div>
		<?php if (!$new) { ?><input type="hidden" name="supplierid" value="<?php echo htmlspecialchars($supplierid) ?>"/><?php } ?>
		<div class="row g-4">
			<div class="col-12">
				<label class="form-label fw-semibold" for="supplier-name"><?php etr("Name") ?></label>
				<input id="supplier-name" type="text" name="name" value="<?php echo htmlspecialchars($rec->name) ?>"/>
			</div>
		</div>
	</div>
</section>

<div class="row g-4 supplier-editor-columns">
	<div class="col-12 col-lg-6">
		<section class="card border-0 shadow-sm h-100 supplier-editor-card">
			<div class="card-body">
				<div class="supplier-editor-section-heading"><div><span><?php etr("Address") ?></span><h2><?php etr("Location details") ?></h2></div></div>
				<div class="supplier-editor-fields">
					<div><label class="form-label fw-semibold"><?php etr("Street address") ?></label><div class="supplier-editor-field"><?php textbox("streetaddress", $rec->streetaddress, 30) ?></div></div>
					<div class="row g-3">
						<div class="col-7"><label class="form-label fw-semibold"><?php etr("City") ?></label><div class="supplier-editor-field"><?php textbox("city", $rec->city) ?></div></div>
						<div class="col-5"><label class="form-label fw-semibold"><?php etr("Zip code") ?></label><div class="supplier-editor-field"><?php textbox("zipcode", $rec->zipcode) ?></div></div>
					</div>
					<div><label class="form-label fw-semibold"><?php etr("Country") ?></label><div class="supplier-editor-field"><?php combobox("countrycode", $countries, $rec->countrycode, true) ?></div></div>
				</div>
			</div>
		</section>
	</div>
	<div class="col-12 col-lg-6">
		<section class="card border-0 shadow-sm h-100 supplier-editor-card">
			<div class="card-body">
				<div class="supplier-editor-section-heading"><div><span><?php etr("Contact") ?></span><h2><?php etr("Primary contact") ?></h2></div></div>
				<div class="supplier-editor-fields">
					<div><label class="form-label fw-semibold"><?php etr("Contact") ?></label><div class="supplier-editor-field"><?php textbox("contact", $rec->contact, 30) ?></div></div>
					<div><label class="form-label fw-semibold"><?php etr("E-mail") ?></label><div class="supplier-editor-field"><?php textbox("email", $rec->email, 30) ?></div></div>
					<div><label class="form-label fw-semibold"><?php etr("Telephone numbers") ?></label>
						<?php if ($phoneNumbers != null) { while ($row = fetch($phoneNumbers)) { ?>
							<div class="supplier-phone-item"><span><small><?php echo htmlspecialchars($row->description) ?></small><?php echo htmlspecialchars($row->telephoneno) ?></span><?php deleteIcon("supplier.php?supplierid=$supplierid&mode=" . urlencode($mode) . "&del_telephoneno=" . urlencode($row->telephoneno)) ?></div>
						<?php } } ?>
						<div class="supplier-phone-new"><div class="supplier-editor-field"><?php combobox('phonecatid_new', $phonecats, null, true) ?></div><div class="supplier-editor-field"><?php textbox('telephoneno_new', '') ?></div></div>
						<small class="form-text"><?php etr("Select a telephone type to add this number when saving.") ?></small>
					</div>
				</div>
			</div>
		</section>
	</div>
</div>

<section class="card border-0 shadow-sm supplier-editor-card supplier-purchasing-card">
	<div class="card-body">
		<div class="supplier-editor-section-heading"><div><span><?php etr("Purchasing") ?></span><h2><?php etr("Tax and credit settings") ?></h2></div></div>
		<div class="row g-4">
			<div class="col-12 col-md-4"><label class="form-label fw-semibold"><?php etr("VAT number") ?></label><div class="supplier-editor-field"><?php textbox("vatnumber", $rec->vatnumber, 20) ?></div></div>
			<div class="col-12 col-md-5"><label class="form-label fw-semibold"><?php etr("Credit account") ?></label><div class="supplier-editor-field"><?php combobox('credit_account', $creditAccounts, $rec->credit_account, true) ?></div></div>
			<div class="col-12 col-md-3"><label class="form-label fw-semibold"><?php etr("Credit length") ?></label><div class="supplier-credit-length"><div class="supplier-editor-field"><?php numberbox('credit_length', $rec->credit_length) ?></div><span><?php etr("days") ?></span></div></div>
		</div>
	</div>
</section>

<div class="supplier-editor-actions">
	<div class="d-flex flex-wrap gap-2">
		<?php saveButton() ?>
		<?php if (!$new) button("Add supplier", "add", "supplier.php?mode=$mode") ?>
	</div>
	<a class="supplier-editor-back" href="suppliers.php?mode=<?php echo urlencode($mode) ?>">&#8592; <?php etr("Back to suppliers") ?></a>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
