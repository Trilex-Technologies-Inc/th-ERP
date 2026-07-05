<?php
	include('include.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	$categoryid = getParam('categoryid');
	$new = true;
	if (isSave()) {
		$categoryid = getParam('categoryid');
		$description = getParam('description');
		$vatcatid = getParam('vatcatid');
		$revenue_accountid = getParam('revenue_accountid');
		$expense_accountid = prepParam('expense_accountid');
		$inventory_accountid = prepNull(getParam('inventory_accountid'));
		$stock = getParam('stock', 0);
		$consignment = getParam('consignment', 0);
		$unittype = prepNull(getParam('unittype'));
		if (isNew()) {
			$sql = "insert into category (description, revenue_accountid, expense_accountid,
                                          vatcatid, stock, inventory_accountid, unittype, consignment)
			        values ('$description', $revenue_accountid, $expense_accountid,
                            $vatcatid, $stock, $inventory_accountid, $unittype, $consignment)";
			sql($sql);
			$categoryid = insert_id();
		} else {
            $updateSQL =
    			"update category set
    				description='$description',
    			    revenue_accountid=$revenue_accountid,
    			    expense_accountid=$expense_accountid,
    			    inventory_accountid=$inventory_accountid,
					vatcatid=$vatcatid,
					stock=$stock,
                    unittype=$unittype,
                    consignment=$consignment
                where categoryid='$categoryid'";
    		sql($updateSQL);
		}
	}
	if (isDelete()) {
		sql("delete from category where categoryid='$categoryid'");
		$categoryid = null;
	}

	$rec = new Dummy();
	if (!isEmpty($categoryid)) {
	    $selectSQL =
  		"select categoryid,
  		       description,
		       revenue_accountid,
			   expense_accountid,
			   inventory_accountid,
			   vatcatid,
			   stock,
               unittype,
               consignment
		from category
		where categoryid='$categoryid'
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
		}
	}

	$vatcategories = rs2array(query("select vatcatid, description from vat_category"));
	$expense_accounts = rs2array(query("select a.accountid, name
	                                    from account a
										join account_group g on g.accountid=a.accountid and groupid=" .
                                        GROUPID_EXPENSES));
	$revenue_accounts = rs2array(query("select a.accountid, name
	                                     from account a
				   						 join account_group g on g.accountid=a.accountid and groupid=" .
                                         GROUPID_REVENUES));
	$assets_accounts = rs2array(query("select a.accountid, name
	                                     from account a
				   						 join account_group g on g.accountid=a.accountid and groupid=" .
                                         GROUPID_ASSETS));
	$unittypes = rs2array(query("select unittype, description from unittype"));

?>
<head>
<title>thERP - <?php etr("Category") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar('configuration.php') ?>
<?php title("<a href='categories.php'>" . tr("Categories") . "</a> > $rec->description") ?>

<form action="category.php" method="POST">
<table>
<tr><td>Id:</td>
<td>
<?php
	if ($new) {
	} else {
		echo $categoryid;
		echo "<input type='hidden' name='categoryid' value='$categoryid'/>";
	}
?>
</td>
<tr><td><?php etr("Description") ?>:</td><td><input type="text" name="description" value="<?php echo $rec->description ?>"/></td>
<tr><td><?php etr("Revenue account") ?>:</td><td><?php comboBox("revenue_accountid", $revenue_accounts, $rec->revenue_accountid, false) ?></td>
<tr><td><?php etr("Expense account") ?>:</td><td><?php comboBox("expense_accountid", $expense_accounts, $rec->expense_accountid, true) ?></td>
<tr><td><?php etr("Inventory account") ?>:</td><td><?php comboBox("inventory_accountid", $assets_accounts, $rec->inventory_accountid, true) ?></td>
<tr><td><?php etr("VAT category") ?>:</td><td><?php comboBox("vatcatid", $vatcategories, $rec->vatcatid, false) ?></td></tr>
<tr><td><?php etr("Stock count") ?>:</td><td><?php checkBox("stock", $rec->stock) ?></td></tr>
<tr><td><?php etr("Consignment") ?>:</td><td><?php checkBox("consignment", $rec->consignment) ?></td></tr>
<tr>
	<td><?php etr("Units of measure") ?>:</td>
	<td><?php combobox('unittype', $unittypes, $rec->unittype, true) ?></td>
</tr>
<tr>
<td colspan=2>
<?php
saveButton();
echo "&nbsp;&nbsp;";
deleteButton();
?>
&nbsp;
</td>
</tr>
</table>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
