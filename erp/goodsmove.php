<?php
	include('include.php');
	include('goodsmove.inc.php');

	checkPermission(PERMISSIONID_PURCHASE);

	$orderid = getParam('orderid');
	$new = true;
	$locationid = getParam('locationid');
	$toid = getParam('toid');
	$orderdate = time();

	$sent = 0;
	$received = 0;
	$addable = true;
	$cancelled = false;

	$mess = null;

	if (getParam("action") == "create") {
		$locationid = findValue("select locationid from user where username='" . getUser() . "'");
		if (isEmpty($locationid))
			$locationid = findValue("select locationid from location order by locationid limit 1");
		if (isEmpty($locationid) || !is_numeric($locationid)) {
			echo tr("No location configured");
			die;
		}

		$locationid = (int) $locationid;
		$requestedToid = getParam('toid');
		$toid = !isEmpty($requestedToid) && is_numeric($requestedToid)
			? (int) $requestedToid
			: $locationid;
		$sql = "insert into movesorder (orderdate,  createdby, locationid,toid)
		        values (now(), '" . getUser() . "', $locationid, $toid)";
		sql($sql);
		$orderid = insert_id();
		header("Location: goodsmove.php?orderid=$orderid");
		die;
	}

	if (isSave()) {
		$locationid = getParam("locationid");
		$toid = getParam("toid");
		sql("update movesorder set locationid=$locationid where orderid=$orderid");
		sql("update movesorder set toid=$toid where orderid=$orderid");
		sql("update user set locationid=$locationid where username='" . getUser() . "'");
		/* $count = getParam('count');
		$i = 0;
		while ($i < $count) {
			$received_quantity = getParam("received_quantity_$i");
			if ($received_quantity != getParam("old_received_quantity_$i")) {
				$no = getParam("no_$i");
				tx("receive_goods", array($orderid, $no, $received_quantity));
			}
			$i++;
		}*/
	}
	if (array_key_exists('add', $_POST)) {
		$productid = getParam('productid_new');
		if (!isEmpty($productid)) {
			$quantity = getParam('quantity_new');
			$mess = add_orderitem($orderid, $productid, $quantity);
		}
	}
	if (array_key_exists('send', $_POST)) {
		tx("send_goods", array($orderid));
	}
	if (array_key_exists('receive', $_POST)) {
		tx("receive_goods", array($orderid));
	}
	if (array_key_exists('cancel', $_POST)) {
		cancel_order($orderid);
	}


	$del_no = getParam("del_no");
	if (!isEmpty($del_no)) {
		sql("delete from movesorder_item where orderid=$orderid and no=$del_no");
		$productid = null;
	}

	$items = null;
	$payed = 0;
	$sum = 0;
	$vatSum = 0;
	$createdby = null;
	$paymentCount = 0;
	$payment_transid = null;
	$cancel_transid = null;
	$locationid = null;
	if (!isEmpty($orderid)) {
	    $sql =
  		"select po.orderid,
  		       unix_timestamp(orderdate) as orderdate,
		       po.toid,
			   po.cancelled,
		       po.sent,
		       po.received,
			   po.createdby,
			   locationid
		from movesorder po
		where po.orderid=$orderid
		";
		$rec = find($sql);
		if ($rec != null) {
			$orderid = $rec->orderid;
			$toid = $rec->toid;
			$orderdate = $rec->orderdate;
			$cancelled = $rec->cancelled;
			$sent = $rec->sent;
			$received = $rec->received;
			$addable = ($sent == 0);
			$createdby = $rec->createdby;
			$locationid = $rec->locationid;
			$new = false;

			$sql = "
			select
			  si.productid,
			  model,
			  si.quantity,
			  si.no,
			  stock
			from movesorder_item si
			join movesorder mo on mo.orderid=si.orderid
			join product p on p.productid=si.productid
			join category c on c.categoryid=p.categoryid
			where si.orderid=$orderid";
			$items = query($sql);
			$payed = 0;
			$sum = findValue("select sum(quantity) from movesorder_item where orderid=$orderid");
		}
	}

	$productid = getParam('productid');
	$unitprice = '';
	$quantity = 1;
	if (!isEmpty($productid)) {
		$quantity = findValue("
		select reorder_qty from product 
		where productid=$productid");
	}

	$to = null;
	if (!isEmpty($toid)) {
		$to = find("select name from location where locationid=$toid");
	}

	$locations = rs2array(query("select locationid, name from location"));
	$statusText = tr("Draft");
	$statusClass = "is-draft";
	if ($cancelled) {
		$statusText = tr("Cancelled");
		$statusClass = "is-cancelled";
	} else if ($received == '1') {
		$statusText = tr("Received");
		$statusClass = "is-received";
	} else if ($sent == '1') {
		$statusText = tr("Sent");
		$statusClass = "is-sent";
	}

?>

<head>
<title>thERP - <?php etr("Stock move order") ?></title>
<?php
styleSheet();
include_common();
?>
<script>
function saveForm()
{
	var saveElement = document.createElement('input');
	saveElement.setAttribute('type', 'hidden');
	saveElement.setAttribute('name', 'save');
	saveElement.setAttribute('value', 'Save');
	document.postform.appendChild(saveElement);
	document.postform.submit();
}
</script>
</head>

<body>
<?php menubar('purchase.php') ?>
<?php title(tr("Stock move order")) ?>

<main class="stock-move-detail-page">
	<header class="stock-move-detail-intro">
		<a class="stock-move-back" href="goodsmoves.php" aria-label="<?php etr("Stock move orders") ?>">&#8592;</a>
		<div class="stock-moves-intro-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 7h11l-3-3M17 17H6l3 3M18 7l-3 3M6 17l3-3"/></svg></div>
		<div class="stock-move-detail-heading">
			<span class="stock-moves-eyebrow"><?php etr("Inventory transfer") ?></span>
			<h1><?php etr("Stock move order") ?> <span>#<?php echo htmlspecialchars($orderid) ?></span></h1>
			<p><?php etr("Review the transfer route, products, and fulfillment status.") ?></p>
		</div>
		<span class="stock-move-status <?php echo $statusClass ?>"><?php echo $statusText ?></span>
	</header>

<?php
if ($mess != null) {
	echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($mess) . "</div>";
}
?>

<form name="postform" action="goodsmove.php" method="POST">
<section class="stock-move-overview card border-0 shadow-sm">
<div class="card-header bg-white stock-move-card-header"><div><span><?php etr("Transfer details") ?></span><h2><?php etr("Route and order information") ?></h2></div></div>
<div class="card-body">
<div class="container-fluid px-0 erp-form-layout">
<?php
	if (!$new) {
		echo "<div class='row g-3 align-items-center mb-2'><div class='col-12 col-md-auto'><b>" . tr("Order id") . ":</b></div>";
		echo "<div class='col-12 col-md-auto'>";
		echo $orderid;
		hidden('orderid', $orderid);
		echo "</div>";
	}
?>
<div class="row g-3 align-items-center mb-3">
	<div class="col-12 col-md-3"><b><?php etr("From Location") ?>:</b></div>
	<div class="col-12 col-md-9 stock-move-origin-field">
	<?php
	if ($sent == 0)
		combobox('locationid', $locations, $locationid, false, 'saveForm()');
	else {
		$location = findValue("select name from location where locationid=$locationid");
		echo $location;
	}
	?>
	</div>
</div>
<div class="row g-3 align-items-center mb-3"><div class="col-12 col-md-3"><b><?php etr("To Location") ?>:</b></div><div class="col-12 col-md-9 stock-move-location-field is-destination">
	<?php
	if ($sent == 0)
		combobox('toid', $locations, $toid, false, 'saveForm()');
	else {
		$toid = findValue("select name from location where locationid=$toid");
		echo $toid;
	}
	?>
</div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><b><?php etr("Order date") ?>:</b></div><div class="col-12 col-md-auto"><?php echo date(DATE_PATTERN, $orderdate) ?></div></div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php etr("State") ?>:</div>
<div class="col-12 col-md-auto">
<?php
	if ($cancelled) {
		echo tr("This order is cancelled");
		if ($cancel_transid != null)
			echo " <a href='transaction.php?transactionid=$cancel_transid'>" . tr("Show transaction") . "</a>";
	}else {
		if($received == '1') echo tr("Received");
		else if($sent == '1') echo tr("Sent");
		else echo tr("Not Register");
	}
?>
</div>
</div>
<div class="row g-3 align-items-center mb-2">
<div class="col-12 col-md-auto"><?php etr("Created by") ?>:</div>
<div class="col-12 col-md-auto"><?php echo $createdby ?></div>
</div>
</div></div></section>
<?php if ($items != null) { ?>
<section class="stock-move-items card border-0 shadow-sm overflow-hidden">
<div class="card-header bg-white stock-move-card-header"><div><span><?php etr("Order lines") ?></span><h2><?php etr("Products to transfer") ?></h2></div></div>
<div class="erp-table-responsive">
<table class="erp-data-table stock-move-items-table">
<thead><tr>
<?php
if ($addable)
	echo "<th>" . tr("Delete") . "</th>";
?>
<th><?php etr("Product") ?></th>
<th><?php etr("Quantity") ?></th>
<th><?php etr("Amount") ?></th>
</tr></thead><tbody>
<?php
	$class = 'odd';
	$i = 0;
	while ($row = fetch($items)) {
		hidden("no_$i", $row->no);
		echo "<tr class='$class'>";
		$href = "goodsmove.php?orderid=$orderid&del_no=$row->no";
		if ($addable)
			deleteColumn($href);
		$text = $row->productid . ' - ' . $row->model;
		echo "<td><a href='product.php?productid=$row->productid'>$text</a></td>";
		echo "<td align=right>$row->quantity</td>";
		echo "<td align=right>$row->quantity</td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
	}
?>
<?php
if ($addable) {
	echo "<tr class='stock-move-add-line-row'><td colspan='" . ($addable ? 4 : 3) . "'>";
	echo "<div class='stock-move-add-line'>";
	echo "<div class='stock-move-add-field stock-move-product-picker'>";
	echo "<label for='productid_new'>" . tr("Product") . "</label>";
	echo "<div>";
	numberbox('productid_new', $productid);
	$href = "products.php?mode=selectgoodsmove&orderid=$orderid";
	button("Search", "search", $href);
	echo "</div></div>";
	echo "<div class='stock-move-add-field'>";
	echo "<label for='quantity_new'>" . tr("Quantity") . "</label>";
	numberbox('quantity_new', $quantity, 5);
	echo "</div>";
	echo "<div class='stock-move-add-submit'>";
	button("Add", "add");
	echo "</div>";
	echo "</div>";
	echo "</td></tr>";
}
?>
<tr>
<?php
if ($addable) echo "<td/>";
?>
<td/>
<td align=right><b><?php etr("Total") ?>:</b></td>
<td align=right><b><?php echo $sum ?></b></td>
</tr>
</tbody></table></div>
<input type="hidden" name="count" value="<?php echo $i ?>" />
</section>
<?php } ?>
<div class="stock-move-actions">
<?php
	if ($sent==0) {
			button("Send goods", "send");
			echo "&nbsp;";
	}
	else{
		if ($received==0) {
			button("Receive goods", "receive");
			echo "&nbsp;";
		}
	}

	if (!$cancelled) {
		button("Cancel order", 'cancel');
		echo "&nbsp;";
	}
	if (!$new)
		button("Show stock moves", "moves", "stockmoves.php?movesorderid=$orderid");
?>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>
</body>
