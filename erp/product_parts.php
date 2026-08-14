<?php
	include('include.php');
	include('product.inc.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	$productid = getParam('productid');

	$del_childid = getParam('del_childid');
	if (!isEmpty($del_childid)) {
		sql("delete from bom where parentid=$productid and childid=$del_childid");
	}
	$childid_new = getParam('childid_new');
	if (!isEmpty($childid_new)) {
		$quantity_new = getParam('quantity_new');
		sql("insert into bom (parentid, childid, quantity)
             values ($productid, $childid_new, $quantity_new)");
	}

	$parts = null;
	if (!isEmpty($productid)) {
		$parts = query("select childid, model, bom.quantity
		                from bom
						join product p on p.productid=bom.childid
						where parentid=$productid");
	}

	$model = isEmpty($productid) ? '' : findValue("select model from product where productid=$productid", '');
	$new = isEmpty($model);
	$allProducts = rs2array(query("select productid, model from product"));
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

<form name="postform" action="product_parts.php" method="POST" class="product-editor">
<?php hidden('productid', $productid) ?>
<div class="product-editor-intro">
	<div class="product-editor-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24"><path d="M3 7h7v7H3ZM14 3h7v7h-7ZM14 14h7v7h-7ZM10 10l4-4M10 10l4 8"/></svg>
	</div>
	<div>
		<span class="product-editor-eyebrow"><?php etr("Product catalogue") ?></span>
		<h1><?php echo htmlspecialchars($model) ?></h1>
		<p><?php etr("Maintain product bill of materials and component quantities.") ?></p>
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
	<?php buildTabs($productid, 'parts') ?>
</div>
<div id="main" class="product-tab-panel">
	<div id="contents">
		<div class="product-section-heading">
			<div><span><?php etr("Parts") ?></span><h2><?php etr("Bill of materials") ?></h2></div>
		</div>
		<div class="product-parts-table">
			<div class="product-parts-row product-parts-head">
				<div><?php etr("Delete") ?></div>
				<div><?php etr("Part") ?></div>
				<div class="text-end"><?php etr("Quantity") ?></div>
			</div>
			<?php
			if ($parts != null) {
				while ($row = fetch($parts)) {
					echo "<div class='product-parts-row'>";
					echo "<div class='product-parts-delete'>";
					deleteIcon("product_parts.php?productid=$productid&del_childid=$row->childid");
					echo "</div>";
					echo "<label for='quantity_new'>" . htmlspecialchars($row->model) . "</label>";
					echo "<div class='product-parts-quantity text-end'>" . htmlspecialchars($row->quantity) . "</div>";
					echo "</div>";
				}
			}
			echo "<div class='product-parts-row product-parts-new'>";
			echo "<div></div>";
			echo "<div class='product-field'>";
			comboBox("childid_new", $allProducts, null, true);
			echo "</div>";
			echo "<div class='product-field product-parts-new-quantity'>";
			numberbox("quantity_new", 1);
			echo "</div></div>";
			?>
		</div>
		<small class="form-text"><?php etr("Select a component product and quantity to add it to the bill of materials.") ?></small>
	</div>
</div>

<div class="product-actions-bar">
	<div class="d-flex flex-wrap gap-2">
		<?php button("Save product", "save") ?>
	</div>
</div>
</form>
<?php bottom() ?>

</body>
