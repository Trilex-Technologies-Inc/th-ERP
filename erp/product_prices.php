<?php
	include('include.php');
	include('product.inc.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	$productid = getParam('productid');
	$new = true;
	if (isSave()) {
		$purchase_price = prepNull(getParam('purchase_price'));
		$updateSQL =
			"update product set
				purchase_price=$purchase_price
			where productid='$productid'";
		sql($updateSQL);
    	$oscommerceid = findValue("
    	select oscommerceid from product where productid='$productid'");
    	$osclistid = null;
		if (!isEmpty($oscommerceid)) {
			$osclistid = findValue("
			select listid from pricelist
			where vat_included=1
			order by listid");
		}
		$rs = sql("select listid from pricelist");
		while ($row = fetch($rs)) {
			$old_price = getParam("old_salesprice_$row->listid");
			$price = getParam("salesprice_$row->listid");
			if ($price != $old_price) {
				$price = prepNull($price);
				sql("
				update sales_price set price=$price
				where productid=$productid and listid=$row->listid");
				if (affected_rows() == 0) {
					sql("
					insert into sales_price (productid, listid, price)
					values ($productid, $row->listid, $price)");
				}
				if ($row->listid == $osclistid) {
					sql("
					update products set products_price=$price
					where products_id=$oscommerceid");
				}
			}
		}
		$count = getParam("supplier_count");
		$i = 0;
		while ($i < $count) {
			$supplierid = getParam("supplierid_$i");
			$old_price = getParam("old_purchaseprice_$i");
			$price = getParam("purchaseprice_$i");
			if ($price != $old_price) {
				$price = prepNull($price);
				sql("
				update supplier_price set price=$price
				where productid=$productid and supplierid=$supplierid");
			}
			$i++;
		}
		$supplierid = getParam("supplierid_new");
		if (!isEmpty($supplierid)) {
			$price = getParam("purchaseprice_new");
			sql("
			insert into supplier_price (supplierid, productid, price)
			values ($supplierid, $productid, $price)");
		}
	}

	$rec = new Dummy();
	if (!isEmpty($productid)) {
	    $selectSQL =
		"select p.productid,
		       model,
		       p.description,
		       purchase_price
		from product p
		where p.productid='$productid'
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
		}
	}
	if ($rec == null) {
		$rec = new Dummy();
	}
	$model = $rec->model;

	$suppliers = rs2array(query("select supplierid, name from supplier"));

?>
<head>
<title>thERP - <?php etr("Product") ?></title>
<?php
styleSheet();
styleSheet('tabs');
include_common();
?>
</head>

<body>
<?php
menubar('products.php');
$title = $model;
title("<a href='products.php'>" . tr("Products") . "</a> > $title");
?>

<form name="postform" action="product_prices.php" method="POST" class="product-editor">
<?php hidden('productid', $productid) ?>
<div class="product-editor-intro">
	<div class="product-editor-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
	</div>
	<div>
		<span class="product-editor-eyebrow"><?php etr("Product catalogue") ?></span>
		<h1><?php echo htmlspecialchars($model) ?></h1>
		<p><?php etr("Maintain product purchase and sales prices.") ?></p>
	</div>
	<?php if (!$new) { ?><span class="product-id-badge"><?php etr("Productno") ?> #<?php echo htmlspecialchars($productid) ?></span><?php } ?>
</div>

<section class="card border-0 shadow-sm product-identity-card">
	<div class="card-body">
		<div class="product-section-heading">
			<div><span><?php etr("Identity") ?></span><h2><?php etr("Basic information") ?></h2></div>
		</div>
		<div class="row g-4">
			<div class="col-12 col-md-5">
				<label class="form-label fw-semibold"><?php etr("Productno") ?></label>
				<div class="product-readonly-value"><?php echo htmlspecialchars($productid) ?></div>
			</div>
			<div class="col-12 col-md-7">
				<label class="form-label fw-semibold"><?php etr("Model") ?></label>
				<div class="product-readonly-value"><?php echo htmlspecialchars($model) ?></div>
			</div>
		</div>
	</div>
</section>

<div id="header" class="product-tabs">
	<?php buildTabs($productid, 'prices') ?>
</div>
<div id="main" class="product-tab-panel">
	<div id="contents">
		<div class="product-section-heading">
			<div><span><?php etr("Prices") ?></span><h2><?php etr("Sales price") ?></h2></div>
		</div>
		<div class="supplier-code-table">
		<?php
		$productid2 = isEmpty($productid) ? 0 : $productid;
		$rs = query("
		select pl.listid, pl.description, price
		from pricelist pl
		left outer join sales_price sp on sp.listid=pl.listid and sp.productid=$productid2
		");
		while ($row = fetch($rs)) {
			echo "<div class='supplier-code-row'>";
			echo "<label for='salesprice_$row->listid'>" . htmlspecialchars($row->description) . "</label>";
			echo "<div class='product-field'>";
			moneybox("salesprice_$row->listid", $row->price);
			hidden("old_salesprice_$row->listid", $row->price);
			echo "</div></div>";
		}
		?>
		</div>

		<section class="supplier-codes">
			<div class="product-section-heading">
				<div><span><?php etr("Suppliers") ?></span><h2><?php etr("Purchase price") ?></h2></div>
			</div>
			<div class="supplier-code-table">
		<?php
		$productid2 = isEmpty($productid) ? 0 : $productid;
		$rs = query("
		select sp.supplierid, name, price
		from supplier_price sp
		join supplier s on s.supplierid=sp.supplierid
		where productid=$productid
		");
		$i = 0;
		while ($row = fetch($rs)) {
			hidden("supplierid_$i", $row->supplierid);
			echo "<div class='supplier-code-row'>";
			echo "<label for='purchaseprice_$i'>" . htmlspecialchars($row->name) . "</label>";
			echo "<div class='product-field'>";
			moneybox("purchaseprice_$i", $row->price);
			hidden("old_purchaseprice_$i", $row->price);
			echo "</div></div>";
			$i++;
		}
		hidden("supplier_count", $i);
		echo "<div class='supplier-code-row supplier-code-new'>";
		echo "<div class='product-field'>";
		combobox("supplierid_new", $suppliers, null, true);
		echo "</div><div class='product-field'>";
		moneybox("purchaseprice_new", null);
		echo "</div></div>";
		?>
			</div>
			<small class="form-text"><?php etr("Select a supplier and enter a purchase price to add another supplier price.") ?></small>
		</section>
	</div>
</div>

<div class="product-actions-bar">
	<div class="d-flex flex-wrap gap-2">
		<?php button("Save product", "save") ?>
	</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

<?php bottom() ?>

</body>
