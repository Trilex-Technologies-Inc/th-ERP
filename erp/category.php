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
<div class="container-fluid px-0 erp-form-layout">
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto">Id:</div>
<div class="col-12 col-md-auto">
<?php
	if ($new) {
	} else {
		echo $categoryid;
		echo "<input type='hidden' name='categoryid' value='$categoryid'/>";
	}
?>
</div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Description") ?>:</div><div class="col-12 col-md-auto"><input type="text" name="description" value="<?php echo $rec->description ?>"/></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Revenue account") ?>:</div><div class="col-12 col-md-auto"><?php comboBox("revenue_accountid", $revenue_accounts, $rec->revenue_accountid, false) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Expense account") ?>:</div><div class="col-12 col-md-auto"><?php comboBox("expense_accountid", $expense_accounts, $rec->expense_accountid, true) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Inventory account") ?>:</div><div class="col-12 col-md-auto"><?php comboBox("inventory_accountid", $assets_accounts, $rec->inventory_accountid, true) ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("VAT category") ?>:</div><div class="col-12 col-md-auto"><?php comboBox("vatcatid", $vatcategories, $rec->vatcatid, false) ?></div></div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Stock count") ?>:</div><div class="col-12 col-md-auto"><?php checkBox("stock", $rec->stock) ?></div></div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><?php etr("Consignment") ?>:</div><div class="col-12 col-md-auto"><?php checkBox("consignment", $rec->consignment) ?></div></div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Units of measure") ?>:</div>
	<div class="col-12 col-md-auto"><?php combobox('unittype', $unittypes, $rec->unittype, true) ?></div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto">
<?php
saveButton();
echo "&nbsp;&nbsp;";
deleteButton();
?>
&nbsp;
</div>
</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
