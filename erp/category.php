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
<?php title($new ? tr("Create category") : htmlspecialchars($rec->description)) ?>

<form action="category.php" method="POST" class="category-editor">
	<?php if (!$new) { ?><input type="hidden" name="categoryid" value="<?php echo htmlspecialchars($categoryid) ?>" /><?php } ?>
	<input type="hidden" name="new" value="<?php echo $new ?>" />

	<header class="category-editor-intro">
		<a class="category-editor-back" href="categories.php" aria-label="<?php etr("Categories") ?>">&#8592;</a>
		<div class="categories-intro-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 6.5 12 3l8 3.5-8 3.5-8-3.5Zm0 5L12 15l8-3.5M4 16.5 12 20l8-3.5"/></svg></div>
		<div class="category-editor-heading"><span><?php etr("Product configuration") ?></span><h1><?php echo $new ? tr("Create category") : htmlspecialchars($rec->description) ?></h1><p><?php etr("Set the product, tax, accounting, and inventory defaults for this category.") ?></p></div>
		<?php if (!$new) { ?><span class="category-editor-id">#<?php echo htmlspecialchars($categoryid) ?></span><?php } ?>
	</header>

	<section class="card border-0 shadow-sm category-editor-card">
		<div class="card-header bg-white category-editor-card-header"><div><span><?php etr("Category details") ?></span><h2><?php etr("General settings") ?></h2></div></div>
		<div class="card-body"><div class="row g-3">
			<div class="col-12 col-lg-8"><label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label><input id="description" type="text" name="description" value="<?php echo htmlspecialchars($rec->description) ?>" required /></div>
			<div class="col-12 col-lg-4"><label class="form-label fw-semibold" for="vatcatid"><?php etr("VAT category") ?></label><div class="category-editor-field"><?php comboBox("vatcatid", $vatcategories, $rec->vatcatid, false) ?></div></div>
			<div class="col-12 col-lg-6"><label class="form-label fw-semibold" for="unittype"><?php etr("Units of measure") ?></label><div class="category-editor-field"><?php comboBox('unittype', $unittypes, $rec->unittype, true) ?></div></div>
		</div></div>
	</section>

	<div class="row g-4 category-editor-columns">
		<div class="col-12 col-lg-7"><section class="card border-0 shadow-sm category-editor-card h-100">
			<div class="card-header bg-white category-editor-card-header"><div><span><?php etr("General ledger") ?></span><h2><?php etr("Accounting defaults") ?></h2></div></div>
			<div class="card-body category-editor-fields">
				<div><label class="form-label fw-semibold" for="revenue_accountid"><?php etr("Revenue account") ?></label><div class="category-editor-field"><?php comboBox("revenue_accountid", $revenue_accounts, $rec->revenue_accountid, false) ?></div><small><?php etr("Used when products in this category are sold.") ?></small></div>
				<div><label class="form-label fw-semibold" for="expense_accountid"><?php etr("Expense account") ?></label><div class="category-editor-field"><?php comboBox("expense_accountid", $expense_accounts, $rec->expense_accountid, true) ?></div></div>
				<div><label class="form-label fw-semibold" for="inventory_accountid"><?php etr("Inventory account") ?></label><div class="category-editor-field"><?php comboBox("inventory_accountid", $assets_accounts, $rec->inventory_accountid, true) ?></div></div>
			</div>
		</section></div>
		<div class="col-12 col-lg-5"><section class="card border-0 shadow-sm category-editor-card h-100">
			<div class="card-header bg-white category-editor-card-header"><div><span><?php etr("Inventory") ?></span><h2><?php etr("Stock behavior") ?></h2></div></div>
			<div class="card-body category-option-list">
				<label class="category-option" for="stock"><span><strong><?php etr("Stock count") ?></strong><small><?php etr("Track quantities for products in this category.") ?></small></span><?php checkBox("stock", $rec->stock) ?></label>
				<label class="category-option" for="consignment"><span><strong><?php etr("Consignment") ?></strong><small><?php etr("Treat products as consignment inventory.") ?></small></span><?php checkBox("consignment", $rec->consignment) ?></label>
			</div>
		</section></div>
	</div>

	<div class="category-editor-actions"><a href="categories.php">&#8592; <?php etr("Back to categories") ?></a><div><?php saveButton(); if (!$new) { deleteButton(); } ?></div></div>
</form>
<?php bottom() ?>
</body>
