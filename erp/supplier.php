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
$title = $rec->name;
if ($new)
	$title = tr("Create");
title("<a href='suppliers.php'>Suppliers</a> > $title");
?>

<form action="supplier.php" method="POST">
<input type=hidden name=mode value='<?php echo $mode ?>'/>
<table>
<tr><td class=label>Id:</td>
<td>
<?php
	if ($new) {	
	} else {
		echo $supplierid;
		echo "<input type='hidden' name='supplierid' value='$supplierid'/>";
	}
?>
</td>
<tr><td class=label><?php echo tr("Name") ?>:</td><td><input type="text" name="name" value="<?php echo $rec->name ?>"/></td>
<tr><td class=label><?php echo tr("Street address") ?>:</td><td><?php textbox("streetaddress", $rec->streetaddress, 30) ?></td></tr>
<tr><td class=label><?php echo tr("City") ?>:</td><td><?php textbox("city", $rec->city) ?></td></tr>
<tr><td class=label><?php echo tr("Zip code") ?>:</td><td><?php textbox("zipcode", $rec->zipcode) ?></td></tr>
<tr><td class=label><?php echo tr("Country") ?>:</td><td><?php combobox("countrycode", $countries, $rec->countrycode, true) ?></td></tr>
<tr><td class=label><?php echo tr("Contact") ?>:</td><td><?php textbox("contact", $rec->contact, 30) ?></td></tr>
<tr><td class=label><?php echo tr("E-mail") ?>:</td><td><?php textbox("email", $rec->email, 30) ?></td></tr>
<tr>
<td class=label><?php etr("Telephone numbers") ?></td>
</tr>
<?php
while ($row = fetch($phoneNumbers)) {
	echo "<tr>";
	echo "<td>$row->description</td>";
	echo "<td>";
	echo $row->telephoneno;
	echo "&nbsp;";
	deleteIcon("supplier.php?supplierid=$supplierid&del_telephoneno=$row->telephoneno");
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
<tr><td class=label><?php echo tr("VAT number") ?>:</td><td><?php textbox("vatnumber", $rec->vatnumber, 20) ?></td></tr>
<tr>
<td class=label><?php etr("Credit account") ?>:</td>
<td><?php combobox('credit_account', $creditAccounts, $rec->credit_account, true) ?></td>
</tr>
<tr>
<td class=label><?php etr("Credit length") ?>:</td>
<td>
<?php 
numberbox('credit_length', $rec->credit_length);
echo "&nbsp;" . tr("days")
?>
</td>
</tr>
</table>
<br/>
<?php 
saveButton();
echo "&nbsp;";
if (!$new)
	button("Add supplier", "add", "supplier.php");
?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
