<?php
	include('include.php');

	$new = true;
	if (isSave()) {
		$companyname = getParam('companyname');
		$streetaddress = getParam('streetaddress');
		$city = getParam('city');
		$zipcode = getParam('zipcode');
		$vatnumber = getParam('varnumber');
		$email = getParam('email');
		$registrationno = getParam("registrationno");
		$telephoneno = getParam("telephoneno");
		if (isNew()) {
			$sql = "insert into companyinfo (companyname, streetaddress, city, zipcode, email, vatnumber, registrationno, telephoneno)  
			        values ('$companyname', '$streetaddress', '$city', '$zipcode', '$email', '$vatnumber', '$registrationno', '$telephoneno')";
			sql($sql);
		} else {
			$updateSQL =
				"update companyinfo set
					companyname='$companyname',
					streetaddress='$streetaddress',
					city='$city',
					zipcode='$zipcode',
					email='$email',
					vatnumber='$vatnumber',
					registrationno='$registrationno',
					telephoneno='$telephoneno'
				";
			sql($updateSQL);
		}
		
		$rs = query("select name from company_attribute");
		while ($row = fetch($rs)) {
			$value = getParam($row->name);
			if (isEmpty($value)) {
				sql("delete from company_attribute where name='$row->name'");
			} else {
				sql("
				update company_attribute set value='$value' 
				where name='$row->name'");
			} 
		}
		$new_name = getParam("new_name");
		if (!isEmpty($new_name)) {
			$value = getParam("new_value");
			sql("
			insert into company_attribute (name, value)
			values ('$new_name', '$value')");
		}
	}

	$selectSQL =
	"select companyname,
		   streetaddress,
		   city,
		   zipcode,
		   email,
		   vatnumber,
		   registrationno,
		   telephoneno		   
	from companyinfo
	";
	$rec = find($selectSQL);
	if ($rec != null) {
		$new = false;
	} else
		$rec = new Dummy();
	
	$rs = query("select name, value from company_attribute");

?>
<head>
<title>thERP - <?php echo tr("Company info") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("companyinfo.php") ?>
<?php
$title = tr("Company info");
title($title);
?>
<form action="companyinfo.php" method="POST" class="company-info-page">
	<input type="hidden" name="new" value="<?php echo $new ?>" />
	<header class="company-info-hero">
		<div class="company-info-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 21V7l8-4 8 4v14M8 21v-5h8v5M8 9h2m4 0h2M8 12h2m4 0h2"/></svg></div>
		<div><span><?php etr("Organization profile") ?></span><h1><?php etr("Company information") ?></h1><p><?php etr("Maintain the legal identity, address, and contact details used across thERP documents.") ?></p></div>
	</header>

	<div class="row g-4 company-info-columns">
		<div class="col-12 col-lg-7"><section class="card border-0 shadow-sm company-info-card h-100">
			<div class="card-header bg-white company-info-card-header"><div><span><?php etr("Profile") ?></span><h2><?php etr("Business identity") ?></h2></div></div>
			<div class="card-body"><div class="row g-3">
				<div class="col-12"><label class="form-label fw-semibold" for="companyname"><?php etr("Company name") ?></label><input id="companyname" type="text" name="companyname" value="<?php echo htmlspecialchars($rec->companyname) ?>" required /></div>
				<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="registrationno"><?php etr("Registration no") ?></label><input id="registrationno" type="text" name="registrationno" value="<?php echo htmlspecialchars($rec->registrationno) ?>" /></div>
				<div class="col-12 col-md-6"><label class="form-label fw-semibold" for="varnumber"><?php etr("VAT number") ?></label><input id="varnumber" type="text" name="varnumber" value="<?php echo htmlspecialchars($rec->vatnumber) ?>" /></div>
				<div class="col-12"><label class="form-label fw-semibold" for="streetaddress"><?php etr("Street address") ?></label><input id="streetaddress" type="text" name="streetaddress" value="<?php echo htmlspecialchars($rec->streetaddress) ?>" /></div>
				<div class="col-12 col-md-7"><label class="form-label fw-semibold" for="city"><?php etr("City") ?></label><input id="city" type="text" name="city" value="<?php echo htmlspecialchars($rec->city) ?>" /></div>
				<div class="col-12 col-md-5"><label class="form-label fw-semibold" for="zipcode"><?php etr("Zip code") ?></label><input id="zipcode" type="text" name="zipcode" value="<?php echo htmlspecialchars($rec->zipcode) ?>" /></div>
			</div></div>
		</section></div>

		<div class="col-12 col-lg-5"><section class="card border-0 shadow-sm company-info-card h-100">
			<div class="card-header bg-white company-info-card-header"><div><span><?php etr("Communication") ?></span><h2><?php etr("Contact details") ?></h2></div></div>
			<div class="card-body company-info-fields">
				<div><label class="form-label fw-semibold" for="email"><?php etr("E-mail") ?></label><input id="email" type="email" name="email" value="<?php echo htmlspecialchars($rec->email) ?>" /><small><?php etr("Used as the primary company contact address.") ?></small></div>
				<div><label class="form-label fw-semibold" for="telephoneno"><?php etr("Telephone no") ?></label><input id="telephoneno" type="tel" name="telephoneno" value="<?php echo htmlspecialchars($rec->telephoneno) ?>" /></div>
			</div>
		</section></div>
	</div>

	<section class="card border-0 shadow-sm company-info-card company-info-attributes">
		<div class="card-header bg-white company-info-card-header"><div><span><?php etr("Custom data") ?></span><h2><?php etr("Company attributes") ?></h2></div><small><?php etr("Add details specific to your organization.") ?></small></div>
		<div class="card-body">
			<div class="company-attribute-list">
			<?php $attributeIndex = 0; while ($row = fetch($rs)) { ?>
				<div class="company-attribute-row"><label for="company-attribute-<?php echo $attributeIndex ?>"><?php echo htmlspecialchars($row->name) ?></label><input id="company-attribute-<?php echo $attributeIndex ?>" type="text" name="<?php echo htmlspecialchars($row->name) ?>" value="<?php echo htmlspecialchars($row->value) ?>" /></div>
			<?php $attributeIndex++; } ?>
			</div>
			<div class="company-attribute-new"><div><label for="new_name"><?php etr("New attribute") ?></label><input id="new_name" type="text" name="new_name" placeholder="<?php etr("Name") ?>" /></div><div><label for="new_value"><?php etr("Value") ?></label><input id="new_value" type="text" name="new_value" placeholder="<?php etr("Value") ?>" /></div></div>
		</div>
	</section>

	<div class="company-info-actions"><span><?php etr("Changes apply to company details across the system.") ?></span><input type="submit" name="save" value="<?php etr("Save company information") ?>" /></div>
</form>
<?php bottom() ?>
</body>
