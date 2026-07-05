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
?>
<br>
<form action="companyinfo.php" method="POST">
<table>
<tr><td><?php echo tr("Company name") ?>:</td><td><input type="text" name="companyname" value="<?php echo $rec->companyname ?>"/></td>
<tr><td><?php echo tr("Street address") ?>:</td><td><input type="text" name="streetaddress" value="<?php echo $rec->streetaddress ?>"/></td>
<tr><td><?php echo tr("City") ?>:</td><td><input type="text" name="city" value="<?php echo $rec->city ?>"/></td>
<tr><td><?php echo tr("Zip code") ?>:</td><td><input type="text" name="zipcode" value="<?php echo $rec->zipcode ?>"/></td>
<tr><td><?php echo tr("E-mail") ?>:</td><td><input type="text" name="email" value="<?php echo $rec->email?>"/></td>
<tr><td><?php echo tr("VAT number") ?>:</td><td><input type="text" name="varnumber" value="<?php echo $rec->vatnumber ?>"/></td>
<tr><td><?php echo tr("Registration no") ?>:</td><td><input type="text" name="registrationno" value="<?php echo $rec->registrationno ?>"/></td>
<tr><td><?php echo tr("Telephone no") ?>:</td><td><input type="text" name="telephoneno" value="<?php echo $rec->telephoneno ?>"/></td>
<?php
while ($row = fetch($rs)) {
	echo "<tr>";
	echo "<td>$row->name:</td>";
	echo "<td>";
	textbox($row->name, $row->value);
	echo "</td>";
	echo "</tr>";
}
echo "<tr>";
echo "<td>";
textbox("new_name", $row->value);
echo "</td>";
echo "<td>";
textbox("new_value", $row->value);
echo "</td>";
echo "</tr>";
?>
<tr>
<td colspan=2>
<input type="submit" name="save" value="Save"/>
&nbsp;
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
