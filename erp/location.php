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

<form action="location.php" method="POST" class="location-editor">
<input type="hidden" name="locationid" value="<?php echo htmlspecialchars($locationid) ?>"/>
<header class="location-editor-intro">
	<div class="location-editor-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24"><path d="M12 21s7-5.2 7-12A7 7 0 0 0 5 9c0 6.8 7 12 7 12Z"/><circle cx="12" cy="9" r="2.5"/></svg>
	</div>
	<div>
		<span class="location-editor-eyebrow"><?php etr("Inventory configuration") ?></span>
		<h1><?php echo $new ? tr("Create location") : htmlspecialchars($name) ?></h1>
		<p><?php etr("Maintain warehouse, office, and stock movement addresses.") ?></p>
	</div>
	<?php if (!$new) { ?><span class="location-editor-id"><?php etr("Id") ?> #<?php echo htmlspecialchars($locationid) ?></span><?php } ?>
</header>

<section class="card border-0 shadow-sm location-editor-card">
	<div class="card-body">
		<div class="location-section-heading">
			<div><span><?php etr("Location") ?></span><h2><?php etr("Address details") ?></h2></div>
		</div>
		<div class="row g-4">
			<div class="col-12 col-md-6">
				<label class="form-label fw-semibold" for="name"><?php etr("Name") ?></label>
				<input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name) ?>"/>
			</div>
			<div class="col-12 col-md-6">
				<label class="form-label fw-semibold" for="streetaddress"><?php etr("Street address") ?></label>
				<input type="text" name="streetaddress" id="streetaddress" value="<?php echo htmlspecialchars($streetaddress) ?>"/>
			</div>
			<div class="col-12 col-md-6">
				<label class="form-label fw-semibold" for="city"><?php etr("City") ?></label>
				<input type="text" name="city" id="city" value="<?php echo htmlspecialchars($city) ?>"/>
			</div>
			<div class="col-12 col-md-6">
				<label class="form-label fw-semibold" for="zipcode"><?php etr("Zip code") ?></label>
				<input type="text" name="zipcode" id="zipcode" value="<?php echo htmlspecialchars($zipcode) ?>"/>
			</div>
		</div>
	</div>
</section>

<div class="location-editor-actions">
	<?php button($new ? "Create location" : "Save location", "save") ?>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>

</body>
