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
                    $phonecats);
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
<input type=hidden name=mode value='<?php echo $mode ?>'/>
<table>
<tr><td><?php etr("Customer id") ?>:</td>
<td>
<?php
	if (!$new) {
		echo $customerid;
		hidden('customerid', $customerid);
	}
?>
</td>
</tr>
<tr><td><?php echo tr("Name") ?>:</td><td><?php textbox("name", $rec->name) ?></td></tr>
<tr><td><?php echo tr("Street address") ?>:</td><td><?php textbox("streetaddress", $rec->streetaddress, 30) ?></td></tr>
<tr><td><?php echo tr("City") ?>:</td><td><?php textbox("city", $rec->city) ?></td></tr>
<tr><td><?php echo tr("Zip code") ?>:</td><td><?php textbox("zipcode", $rec->zipcode) ?></td></tr>
<tr><td><?php echo tr("E-mail") ?>:</td><td><?php textbox("email", $rec->email, 30) ?></td></tr>
<tr>
<td><?php etr("Telephone numbers") ?></td>
</tr>
<?php
while ($row = fetch($phoneNumbers)) {
	echo "<tr>";
	echo "<td>$row->description</td>";
	echo "<td>";
	echo $row->telephoneno;
	echo "&nbsp;";
	deleteIcon("customer.php?customerid=$customerid&del_telephoneno=$row->telephoneno");
	echo "</td>";
	echo "</tr>";
}
echo "<tr>";
echo "<td>";
combobox('phonecatid_new', $phonecats, null, true);
echo "</td>";
echo "<td>";
textbox('telephoneno_new', '');
echo "</td>";
echo "</tr>";
?>
<tr>
	<td><?php echo tr("Price list") ?>:</td>
	<td><?php combobox("pricelistid", $pricelists, $rec->pricelistid, false) ?></td>
</tr>
<tr><td><?php echo tr("VAT number") ?>:</td><td><?php textbox("vatnumber", $rec->vatnumber, 20) ?></td></tr>
<tr><td><?php echo tr("Credit length") ?>:</td><td><?php numberbox("credit_length", $rec->credit_length, 5) ?></td></tr>
<tr><td><?php echo tr("Use VAT") ?>:</td><td><?php checkbox("use_vat", $rec->use_vat) ?></td></tr>
<tr><td><?php echo tr("Balance") ?>:</td><td><?php echo formatMoney($balance) ?></td></tr>
</table>
<br/>
<?php saveButton() ?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
