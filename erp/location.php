<?php
	include('include.php');

	$locationid = getParam('locationid');
	$name = '';
	$streetaddress = '';
	$city = '';
	$zipcode = '';
	$new = true;
	if (isSave()) {
		$name = getParam('name');
		$streetaddress = getParam('streetaddress');
		$city = getParam('city');
		$zipcode = getParam('zipcode');
		if (isNew()) {
			$sql = "insert into location (name, streetaddress, city, zipcode)  
			        values ('$name', '$streetaddress', '$city', '$zipcode')";
			sql($sql);
			$locationid = insert_id();
		} else {
			$updateSQL =
				"update location set
					name='$name',
					streetaddress='$streetaddress',
					city='$city',
					zipcode='$zipcode'
				where locationid=$locationid";
			sql($updateSQL);
		}
	}

	if (!isEmpty($locationid)) {
	    $selectSQL =
  		"select locationid,
		       name,
			   streetaddress,
			   city,
			   zipcode
		from location
		where locationid=$locationid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$locationid = $rec->locationid;
			$name = $rec->name;
			$streetaddress = $rec->streetaddress;
			$city = $rec->city;
			$zipcode = $rec->zipcode;
			$new = false;
		}
	}

?>
<head>
<title>thERP - <?php echo tr("Location") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("configuration.php") ?>
<?php
$title = "<a href='locations.php'>" . tr("Locations") . "</a> > $name";
title($title);
?>

<form action="location.php" method="POST">
<input type=hidden name=locationid value='<?php echo $locationid ?>'/>
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Name") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="name" value="<?php echo $name ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Street address") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="streetaddress" value="<?php echo $streetaddress ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("City") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="city" value="<?php echo $city ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php echo tr("Zip code") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="zipcode" value="<?php echo $zipcode ?>"/></div>

</div><div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
<input type="submit" name="save" value="Save"/>
&nbsp;
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>

</body>
