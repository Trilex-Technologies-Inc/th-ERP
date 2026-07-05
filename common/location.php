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
<table>
<tr><td><?php echo tr("Name") ?>:</td><td><input type="text" name="name" value="<?php echo $rec->name ?>"/></td>
<tr><td><?php echo tr("Street address") ?>:</td><td><?php textbox("streetaddress", $rec->streetaddress, 30) ?></td>
<tr><td><?php echo tr("City") ?>:</td><td><input type="text" name="city" value="<?php echo $rec->city ?>"/></td>
<tr><td><?php echo tr("Zip code") ?>:</td><td><input type="text" name="zipcode" value="<?php echo $rec->zipcode ?>"/></td>
<tr><td><?php echo tr("E-mail") ?>:</td><td><?php textbox('email', $rec->email, 30) ?></td>

<tr>
<td colspan=2>
<input type="submit" name="save" value="Save"/>
&nbsp;
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
