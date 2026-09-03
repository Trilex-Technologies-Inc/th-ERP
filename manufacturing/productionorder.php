<?php
	include('include.php');
	include('productionorder.inc.php');

	$orderid = getParam('orderid');
	$new = true;
	$createdtime = time();
	$transid = null;
	$addable = true;
	$cancelled = false;
	$createdby = null;
	$mess = null;
	if (getParam("action") == "create") {
		$sql = "insert into productionorder (createdtime, createdby)
		        values (now(), '" . getUser() . "')";
		sql($sql);
		$orderid = insert_id();
	}
	$productid_new = getParam('productid_new');
	if (array_key_exists('add', $_POST) || !isEmpty($productid_new)) {
		if (!isEmpty($productid_new)) {
			$quantity = getParam('quantity_new');
			$mess = add_orderitem($orderid, $productid_new, $quantity);
		}
	}
	if (array_key_exists('cancel', $_POST)) {
		tx("cancel_order", array($orderid));
	}

	$del_no = getParam("del_no");
	if (!isEmpty($del_no)) {
		sql("delete from productionorder_item where orderid=$orderid and no=$del_no");
		$productid = null;
	}
	if (array_key_exists('finish', $_POST)) {
		tx("finish_productionorder", array($orderid));
	}

	$items = null;
	$rec = new Dummy();
	$addable = true;
	if (!isEmpty($orderid)) {
	    $sql =
  		"select orderid,
  		       unix_timestamp(createdtime) as createdtime,
		       transactionid,
			   cancelled,
			   so.createdby
		from productionorder so
		where orderid=$orderid
		";
		$rec = find($sql);
		$cancelled = $rec->cancelled;
		if ($rec->transactionid != null)
			$addable = false;
		if ($rec->cancelled)
			$addable = false;
		$new = false;

		$sql = "
		select
		  si.productid,
		  model,
		  si.quantity,
		  no
		from productionorder_item si
		join product p on p.productid=si.productid
		where orderid=$orderid";
		$items = query($sql);
	}

	$productid = getParam('productid');
	$statusText = tr("Registered");
	$statusClass = "is-draft";
	if ($cancelled) {
		$statusText = tr("Cancelled");
		$statusClass = "is-cancelled";
	} else if (!isEmpty($rec->transactionid)) {
		$statusText = tr("Finished");
		$statusClass = "is-received";
	}

?>

<head>
<title>thERP - <?php etr("Production order") ?></title>
<?php
styleSheet();
include_common();
?>
<script>
function onLoad()
{
	if (document.postform.productid_new)
		document.postform.productid_new.focus();
}
</script>
</head>

<body onLoad="onLoad()">
<?php
menubar("productionorders.php");
$title = $new ? tr("Register") : $orderid;
title(tr("Production order")) ;
?>

<main class="production-order-page">
	<header class="production-order-intro">
		<a class="production-order-back" href="productionorders.php" aria-label="<?php etr("Production orders") ?>">&#8592;</a>
		<div class="production-order-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 20V9l5 3V9l5 3V5h4l2 15H4Zm3 0v-3m4 3v-3m4 3v-3"/></svg></div>
		<div class="production-order-heading">
			<span><?php etr("Manufacturing") ?></span>
			<h1><?php etr("Production order") ?> <em>#<?php echo htmlspecialchars($orderid) ?></em></h1>
			<p><?php etr("Add finished products and complete the order to update inventory.") ?></p>
		</div>
		<span class="stock-move-status <?php echo $statusClass ?>"><?php echo $statusText ?></span>
	</header>

<?php
if ($mess != null) {
	echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($mess) . "</div>";
}
?>

<form name="postform" action="productionorder.php" method="POST">
<section class="production-order-overview card border-0 shadow-sm">
	<div class="card-header bg-white production-order-card-header"><div><span><?php etr("Order details") ?></span><h2><?php etr("Production information") ?></h2></div></div>
	<div class="card-body">
<div class="container-fluid px-0 erp-form-layout">
<?php
	if (!$new) {
		echo "<div class='row g-3 align-items-center mb-2'><div class='col-12 col-md-auto'><b>" . tr("Order id") . ":</b></div>";
		echo "<div class='col-12 col-md-auto'>";
		echo $orderid;
		echo "<input type='hidden' name='orderid' value='$orderid'/>";
		echo "</div>";
	}
?>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-3"><b><?php etr("Created date") ?>:</b></div><div class="col-12 col-md-9"><?php echo date(DATE_PATTERN, $rec->createdtime) ?></div></div>
<?php
echo "<div class='row g-3 align-items-center mb-2'>";
echo "<div class='col-12 col-md-auto'>" . tr("Status") . ":</div>";
echo "<div class='col-12 col-md-auto'>";
if (!isEmpty($rec->transactionid)) {
	echo tr("Finished") . "&nbsp;&nbsp;<a href='../accounting/transaction.php?transactionid=$rec->transactionid'>Show transaction</a>";
} else
	echo tr("Registered");
echo "</div>";
echo "</div>";
if ($cancelled) {
	echo "<div class='row g-3 align-items-center mb-2'>";
	echo "<div class='col-12 col-md-auto'>";
	echo tr("This order is cancelled");
	echo "</div>";
	echo "</div>";
}
?>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php etr("Created by") ?>:</div>
<div class="col-12 col-md-auto"><?php echo $rec->createdby ?></div>
</div>
</div></div></section>
<?php if ($items != null) { ?>
<section class="production-order-items card border-0 shadow-sm overflow-hidden">
	<div class="card-header bg-white production-order-card-header"><div><span><?php etr("Order lines") ?></span><h2><?php etr("Products to manufacture") ?></h2></div></div>
	<div class="erp-table-responsive"><table class="erp-data-table production-order-table"><thead><tr>
<?php
if ($addable)
	echo "<th>" . tr("Delete") . "</th>";
?>
<th><?php etr("Product") ?></th>
<th><?php etr("Quantity") ?></th>
<?php if ($addable) { ?><th><?php etr("Action") ?></th><?php } ?>
</tr></thead><tbody>
<?php
	$class = 'odd';
	$i = 0;
	while ($row = fetch($items)) {
		echo "<tr class='$class'>";
		$href = "productionorder.php?orderid=$orderid&del_no=$row->no";
		if ($addable)
			deleteColumn($href);
		$text = htmlspecialchars($row->productid . ' - ' . $row->model);
		echo "<td><input type='hidden' name='productid_$i' value='$row->productid'/><a href='../erp/product.php?productid=$row->productid'>$text</a></td>";
		echo "<td align=right>$row->quantity</td>";
		if ($addable)
			echo "<td></td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
	}
?>
<?php
if ($addable) {
	echo "<tr class='$class production-order-add-row'>";
	echo "<td class='production-order-add-marker'><span aria-hidden='true'>+</span></td>";
	echo "<td><div class='production-product-picker'>";
	numberbox('productid_new', $productid);
	button("Search", "search", "../erp/products.php?mode=selectproduction&orderid=$orderid");
	echo "</div><small>" . tr("Enter a product ID or search the catalog") . "</small></td>";
	echo "<td><div class='production-quantity-field'><input type='text' name='quantity_new' value='1' size='5' aria-label='" . tr("Quantity") . "'/><small>" . tr("Units to produce") . "</small></div></td>";
	echo "<td class='production-add-action'><input type='submit' name='add' value='" . tr("Add product") . "'/></td>";
	echo "</tr>";
}
?>
</tbody></table></div>
<input type="hidden" name="count" value="<?php echo $i ?>" />
</section>
<?php } ?>

<div class="production-order-actions">
<?php
if (!$new) {
	button("Finish", "finish", null, 'F');
	echo "&nbsp;&nbsp;";
	if (!$cancelled)
		echo "<input type=submit name='cancel' value='" . tr("Cancel order") . "'/>&nbsp;";
	button("Show stock moves", "moves", "../erp/stockmoves.php?productionorderid=$orderid");
}
?>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>
</body>
