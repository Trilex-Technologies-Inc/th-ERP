<?php
	include('include.php');

    $model = getParam('model');
    $barcode = getParam('barcode');
    $locationid = getParam('locationid');
    $supplierid = getParam('supplierid');

    $del_productid = getParam("del_productid");
    if (!isEmpty($del_productid)) {
		deleteProduct($del_productid);
    }

	$locationSQL = '';
	if (!isEmpty($locationid))
		$locationSQL = " and locationid=$locationid ";
	$selectSQL = "
	select
	    p.productid,
	    model,
		p.barcode,
	    (select sum(diff) from stockmove m where m.productid=p.productid $locationSQL) as quantity,
	    (select sum(soi.quantity)
	     from salesorder_item soi
	     join salesorder so on so.orderid=soi.orderid and so.invoice_transid is null
	     where soi.productid=p.productid $locationSQL) as so_quantity,
	    (select sum(poi.quantity-poi.received_quantity)
	     from purchaseorder_item poi
	     join purchaseorder po on po.orderid=poi.orderid
	     where poi.productid=p.productid $locationSQL) as po_quantity
	from product p ";
	if (!isEmpty($supplierid)) {
		$selectSQL .= " join supplier_price sp ";
		$selectSQL .= " on sp.productid=p.productid and supplierid=$supplierid ";
	}
	$selectSQL .= "
	where model like '$model%'
	and active=1
	";
	if (!isEmpty($barcode)) {
		$selectSQL .= " and barcode like '$barcode%'";
	}
	$mode = getParam('mode');
	$orderid = getParam('orderid');

	$locations = rs2array(query("select locationid, name from location"));
	$suppliers = rs2array(query("select supplierid, name from supplier"));

?>

<?php head("Products") ?>
<script>
function printReport()
{
	document.searchform.action="inventory_report.php";
	document.searchform.submit();
}
</script>
<body>

<?php menubar('products.php') ?>
<?php title(tr("Products")) ?>

<main class="products-page">
<header class="products-intro">
	<div class="products-intro-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24"><path d="M20 13V7a2 2 0 0 0-1-1.73l-6-3.46a2 2 0 0 0-2 0L5 5.27A2 2 0 0 0 4 7v6a2 2 0 0 0 1 1.73l6 3.46a2 2 0 0 0 2 0l6-3.46A2 2 0 0 0 20 13ZM4.27 6 12 10.5 19.73 6M12 22V10.5"/></svg>
	</div>
	<div>
		<span class="products-eyebrow"><?php etr("Inventory") ?></span>
		<h1><?php etr("Products") ?></h1>
		<p><?php etr("Search, review, and open product catalogue records.") ?></p>
	</div>
	<div class="products-create"><?php button("Add product", "add", "product.php") ?></div>
</header>

<form action="products.php" method="GET" name="searchform" class="products-filter">
<div class="card border-0 shadow-sm">
	<div class="card-body">
		<div class="products-section-heading">
			<div><span><?php etr("Search") ?></span><h2><?php etr("Filter products") ?></h2></div>
		</div>
		<div class="row g-3">
			<div class="col-12 col-md-6 col-xl-3">
				<label class="form-label fw-semibold"><?php etr("Model") ?></label>
				<div class="w-100"><?php textbox('model', $model) ?></div>
			</div>
			<div class="col-12 col-md-6 col-xl-3">
				<label class="form-label fw-semibold"><?php etr("Barcode") ?></label>
				<div class="w-100"><?php textbox('barcode', $barcode) ?></div>
			</div>
			<div class="col-12 col-md-6 col-xl-3">
				<label class="form-label fw-semibold"><?php etr("Supplier") ?></label>
				<div class="w-100"><?php combobox('supplierid', $suppliers, $supplierid, true) ?></div>
			</div>
			<div class="col-12 col-md-6 col-xl-3">
				<label class="form-label fw-semibold"><?php etr("Location") ?></label>
				<div class="w-100"><?php combobox('locationid', $locations, $locationid, true) ?></div>
			</div>
		</div>
		<div class="d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
			<?php searchButton() ?>
			<?php button("Print", "print", "javascript:printReport()") ?>
		</div>
	</div>
</div>
</form>

<form action="products.php" method="POST">
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode) ?>"/>
<input type="hidden" name="orderid" value="<?php echo htmlspecialchars($orderid) ?>"/>
<div class="products-list card border-0 shadow-sm overflow-hidden">
	<div class="card-header bg-white products-list-header">
		<div><span><?php etr("Catalogue") ?></span><h2><?php etr("Product list") ?></h2></div>
	</div>
	<div class="overflow-auto">
		<div class="products-grid">
			<div class="products-grid-header d-flex align-items-center gap-3 px-3 py-3 border-bottom fw-bold small text-uppercase">
				<div class="product-actions text-center"><?php etr("Delete") ?></div>
				<div class="product-number"><?php etr("Productno") ?></div>
				<div class="product-name flex-grow-1"><?php etr("Product") ?></div>
				<?php if (isEmpty($mode)) { ?>
					<div class="product-quantity product-stock text-end"><?php etr("Quantity") ?></div>
					<div class="product-quantity product-sales text-end"><?php etr("Ordered qty, sales") ?></div>
					<div class="product-quantity product-purchase text-end"><?php etr("Ordered qty, purchase") ?></div>
				<?php } ?>
				<div class="product-barcode text-end"><?php etr("Barcode") ?></div>
			</div>
<?php

$rs = query($selectSQL);
$class = "odd";
$productCount = 0;
while ($row = fetch_object($rs)) {
	$productCount++;
	$href = "product.php?productid=$row->productid";
	if ($mode == 'selectproduct')
		$href = "../sales/salesorder.php?orderid=$orderid&productid=$row->productid";
	else if ($mode == 'selectpurchase')
		$href = "purchaseorder.php?orderid=$orderid&productid=$row->productid";
	else if ($mode == 'selectgoodsmove')
		$href = "goodsmove.php?orderid=$orderid&productid=$row->productid";
	else if ($mode == 'selectproduction')
		$href = "../manufacturing/productionorder.php?orderid=$orderid&productid=$row->productid";
	echo "<div class='products-grid-row d-flex align-items-center gap-3 px-3 py-3 border-bottom $class'>";
	echo "<div class='product-actions text-center'>";
	deleteIcon("products.php?del_productid=$row->productid");
	echo "</div>";
	echo "<div class='product-number text-secondary'>" . htmlspecialchars($row->productid) . "</div>";
	echo "<div class='product-name flex-grow-1'><a class='fw-semibold' href='$href'>" . htmlspecialchars($row->model) . "</a></div>";
	if (isEmpty($mode)) {
		$href = "stockmoves.php?productid=$row->productid&locationid=$locationid";
		echo "<div class='product-quantity text-end'><a href='$href' class='sum badge text-bg-light border'>" . htmlspecialchars($row->quantity) . "</a></div>";
		$href = "sales.php?productid=$row->productid&uninvoiced=1";
		echo "<div class='product-quantity product-sales text-end'><a href='$href' class='sum badge text-bg-light border'>" . htmlspecialchars($row->so_quantity) . "</a></div>";
		$href = "purchaseorders.php?productid=$row->productid";
		echo "<div class='product-quantity product-purchase text-end'><a href='$href' class='sum badge text-bg-light border'>" . htmlspecialchars($row->po_quantity) . "</a></div>";
	}
	echo "<div class='product-barcode text-end font-monospace small'>" . htmlspecialchars($row->barcode) . "</div>";
	echo "</div>";
	$class = ($class == "odd" ? "even" : "odd");
}
if ($productCount == 0) {
	echo "<div class='products-empty'><span>⌕</span><strong>" . tr("No products found") . "</strong><small>" . tr("Try changing the search filters.") . "</small></div>";
}
?>
		</div>
	</div>
	<div class="products-list-footer">
		<span><?php echo htmlspecialchars($productCount) ?> <?php etr("Products") ?></span>
	</div>
</div>
</form>
</main>
<?php bottom() ?>
</body>
