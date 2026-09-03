<?php
	include('include.php');
	include('product.inc.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	function changeQuantity($productid, $locationid, $diff, $createtrans)
	{
		$productidSql = sql_string($productid);
		$narrative = tr("Stock adjustment");
		$transid = "null";
		if ($createtrans) {
			$finished_goods = findValue("select finished_goods from accountconf");
			$inventory_adjustment = findValue("select inventory_adjustment from accountconf");
			$standardCost = findValue("select purchase_price from product where productid=$productidSql");
			sql("insert into transaction (narrative, transtime, createdtime)
				 values ('$narrative', now(), now())");
			$transid = insert_id();
			$amount = $diff * $standardCost;
			sql("insert into transaction_part (transactionid, accountid, amount)
				 values ($transid, $finished_goods, $amount)");
			$amount = (-1) * $amount;
			sql("insert into transaction_part (transactionid, accountid, amount)
				 values ($transid, $inventory_adjustment, $amount)");
		}
		sql("insert into stockmove (productid, diff, narrative, transactionid, locationid)
			 values ($productidSql, $diff, '$narrative', $transid, $locationid)");
		$parts = query("select childid, quantity from bom where parentid=$productidSql");
		while ($row = fetch($parts)) {
			$childdiff = $diff * $row->quantity;
			sql("insert into stockmove (productid, diff, narrative, transactionid, locationid)
				 values (" . sql_string($row->childid) . ", $childdiff, '$narrative', $transid, $locationid)");
		}
	}

	function move($productid, $fromid, $toid, $diff)
	{
		changeQuantity($productid, $fromid, (-1) * $diff, false);
		changeQuantity($productid, $toid, $diff, false);
	}

	$productid = getParam('productid');
	$productidSql = sql_string($productid);
	if (isSave()) {
		$reorder_level = prepParam('reorder_level');
		$reorder_qty = prepParam('reorder_qty');
		sql("
		update product set 
			reorder_level=$reorder_level,
			reorder_qty=$reorder_qty
		where productid=$productidSql");
		
		$rs = query("select locationid from location");
		while ($row = fetch($rs)) {
			$locationid = $row->locationid;
			$diff = getParam("diff_$locationid");
			if (!isEmpty($diff) && $diff != 0) {
				tx("changeQuantity", array($productid, $locationid, $diff, getParam('createtrans')));
			}
		}
	}

	if (array_key_exists('move', $_POST)) {
		$fromid = getParam("from_locationid");
		$toid = getParam("to_locationid");
		$diff = getParam("diff");
		tx("move", array($productid, $fromid, $toid, $diff));
	}

	$rec = new Dummy();
	if (!isEmpty($productid)) {
	    $selectSQL =
  		"select p.productid,
  		       model,
		       p.description,
		       stock,
		       reorder_level,
		       reorder_qty
		from product p
		join category c on c.categoryid=p.categoryid
		where p.productid='$productid'
		group by p.productid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
		}
	}
	if ($rec == null) {
		$rec = new Dummy();
	}

	$productid2Sql = isEmpty($productid) ? "''" : $productidSql;
	$rs = query("
	select l.locationid, sum(diff) as quantity, l.name as location
	from location l
	left outer join stockmove m on l.locationid=m.locationid and productid=$productid2Sql
	group by l.locationid");

	$locations = rs2array(query("select locationid, name from location"));
	$model = $rec->model;
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

<form name="postform" action="product_stock.php" method="POST" class="product-editor">
<?php hidden('productid', $productid) ?>
<div class="product-editor-intro">
	<div class="product-editor-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16ZM3.3 7 12 12l8.7-5M12 22V12"/></svg>
	</div>
	<div>
		<span class="product-editor-eyebrow"><?php etr("Product catalogue") ?></span>
		<h1><?php echo htmlspecialchars($model) ?></h1>
		<p><?php etr("Maintain stock balances, movements, and reorder targets.") ?></p>
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
	<?php buildTabs($productid, 'stock') ?>
</div>
<div id="main" class="product-tab-panel">
	<div id="contents">
		<div class="product-section-heading">
			<div><span><?php etr("Stock") ?></span><h2><?php etr("Location balances") ?></h2></div>
			<a class="btn btn-sm btn-light border fw-semibold" href="stockmoves.php?productid=<?php echo htmlspecialchars($productid) ?>"><?php etr("Show stock moves") ?></a>
		</div>
		<div class="product-stock-table">
			<div class="product-stock-row product-stock-head">
				<div><?php etr("Location") ?></div>
				<div class="text-end"><?php etr("Quantity") ?></div>
				<div class="text-end"><?php etr("Diff") ?></div>
			</div>
			<?php
			while ($row = fetch($rs)) {
				$quantity = isEmpty($row->quantity) ? 0 : $row->quantity;
				echo "<div class='product-stock-row'>";
				echo "<div class='fw-semibold'>" . htmlspecialchars($row->location) . "</div>";
				echo "<div class='product-stock-quantity text-end'>" . htmlspecialchars($quantity) . "</div>";
				echo "<div class='product-field product-stock-diff'>";
				numberbox("diff_$row->locationid", '', 7, 0, true);
				echo "</div></div>";
			}
			?>
		</div>
		<label class="product-stock-ledger">
			<?php checkbox('createtrans', 1) ?>
			<span><?php etr("Create general ledger transaction for stock movement") ?></span>
		</label>

		<section class="supplier-codes">
			<div class="product-section-heading">
				<div><span><?php etr("Move") ?></span><h2><?php etr("Transfer stock") ?></h2></div>
			</div>
			<div class="product-stock-move">
				<div class="product-field"><?php numberbox('diff', '', 5) ?></div>
				<span><?php etr("pieces from") ?></span>
				<div class="product-field"><?php combobox('from_locationid', $locations, null, true) ?></div>
				<span><?php etr("to") ?></span>
				<div class="product-field"><?php combobox('to_locationid', $locations, null, true) ?></div>
				<?php button("Move", "move") ?>
			</div>
		</section>

		<section class="supplier-codes">
			<div class="product-section-heading">
				<div><span><?php etr("Planning") ?></span><h2><?php etr("Re-order settings") ?></h2></div>
			</div>
			<div class="row g-4">
				<div class="col-12 col-md-6">
					<label class="form-label fw-semibold" for="reorder_level"><?php etr("Re-order level") ?></label>
					<div class="product-field"><?php numberbox('reorder_level', $rec->reorder_level) ?></div>
				</div>
				<div class="col-12 col-md-6">
					<label class="form-label fw-semibold" for="reorder_qty"><?php etr("Re-order quantity") ?></label>
					<div class="product-field"><?php numberbox('reorder_qty', $rec->reorder_qty) ?></div>
				</div>
			</div>
		</section>
	</div>
</div>

<div class="product-actions-bar">
	<div class="d-flex flex-wrap gap-2">
		<?php button("Update quantities", "save") ?>
	</div>
</div>
</form>

<?php bottom() ?>

</body>
