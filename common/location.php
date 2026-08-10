<?php
	include('include.php');

	$locationid = getParam('locationid');
	$new = true;
	if (isSave()) {
		$name = getParam('name');
		$streetaddress = getParam('streetaddress');
		$city = getParam('city');
		$zipcode = getParam('zipcode');
		$email = getParam('email');
		if (isNew()) {
			$sql = "insert into location (name, streetaddress, city, zipcode, email)  
			        values ('$name', '$streetaddress', '$city', '$zipcode', '$email')";
			sql($sql);
			$locationid = insert_id();
		} else {
			$updateSQL =
				"update location set
					name='$name',
					streetaddress='$streetaddress',
					city='$city',
					zipcode='$zipcode',
					email='$email'
				where locationid=$locationid";
			sql($updateSQL);
		}
	}

	$rec = new Dummy();
	if (!isEmpty($locationid)) {
	    $selectSQL =
  		"select locationid,
		       name,
			   streetaddress,
			   city,
			   zipcode,
			   email
		from location
		where locationid=$locationid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
		}
	}

?>
<head>
<title>thERP - <?php echo tr("Location") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php
$title = tr("Configuration") . " > " . tr("Company address");
title($title);
?>

<form action="location.php" method="POST">
<input type=hidden name=locationid value='<?php echo $locationid ?>'/>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Name") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="name" value="<?php echo $rec->name ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Street address") ?>:</div><div class="col-12 col-md-auto"><?php textbox("streetaddress", $rec->streetaddress, 30) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("City") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="city" value="<?php echo $rec->city ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Zip code") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="zipcode" value="<?php echo $rec->zipcode ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("E-mail") ?>:</div><div class="col-12 col-md-auto"><?php textbox('email', $rec->email, 30) ?></div>

</div><div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
<input type="submit" name="save" value="Save"/>
&nbsp;
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
