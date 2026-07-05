<?php
	include('include.php');
	include('purchaseorder.inc.php');

	checkPermission(PERMISSIONID_PURCHASE);

	$orderid = getParam('orderid');
	$new = true;
	$supplierid = getParam('supplierid');
	$orderdate = time();

	$payableid = null;
	$addable = true;
	$cancelled = false;

	$mess = null;

	if (getParam("action") == "create") {
		$locationid = findValue("select locationid from user where username='" . getUser() . "'", 1);
		$sql = "insert into purchaseorder (orderdate, supplierid, createdby, locationid)
		        values (now(), $supplierid, '" . getUser() . "', $locationid)";
		sql($sql);
		$orderid = insert_id();
	}

	if (isSave()) {
		$locationid = getParam("locationid");
		sql("update purchaseorder set locationid=$locationid where orderid=$orderid");
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
			$unitprice = getParam('unitprice_new');
			$mess = add_orderitem($orderid, $productid, $quantity, $unitprice);
		}
	}
	if (array_key_exists('receive', $_POST)) {
		tx("receive_goods", array($orderid));
	}
	if (array_key_exists('cancel', $_POST)) {
		cancel_order($orderid);
	}
	if (array_key_exists('payable', $_POST)) {
		tx("create_payable_from_purchaseorder" , array($orderid));
	}

	if (array_key_exists('pay', $_POST)) {
		tx("pay_purchaseorder", array($orderid));
	}

	$del_no = getParam("del_no");
	if (!isEmpty($del_no)) {
		sql("delete from purchaseorder_item where orderid=$orderid and no=$del_no");
		$productid = null;
	}

	$items = null;
	$payed = 0;
	$sum = 0;
	$vatSum = 0;
	$createdby = null;
	$paymentCount = 0;
	$payment_transid = null;
	$locationid = null;
	if (!isEmpty($orderid)) {
	    $sql =
  		"select po.orderid,
  		       unix_timestamp(orderdate) as orderdate,
		       po.supplierid,
			   cancelled,
			   p.payableid,
			   po.createdby,
			   locationid
		from purchaseorder po
		left outer join payable p on p.payableid=po.payableid
		where po.orderid=$orderid
		";
		$rec = find($sql);
		if ($rec != null) {
			$orderid = $rec->orderid;
			$supplierid = $rec->supplierid;
			$orderdate = $rec->orderdate;
			$cancelled = $rec->cancelled;
			$payableid = $rec->payableid;
			$addable = isEmpty($payableid);
			$createdby = $rec->createdby;
			$locationid = $rec->locationid;
			$new = false;

			$sql = "
			select
			  si.productid,
			  model,
			  si.quantity,
			  si.received_quantity,
			  unitprice,
			  vat,
			  no,
			  stock,
			  supplier_productcode
			from purchaseorder_item si
			join purchaseorder po on po.orderid=si.orderid
			join product p on p.productid=si.productid
			join category c on c.categoryid=p.categoryid
			left outer join supplier_price sp on sp.supplierid=po.supplierid and sp.productid=si.productid
			where si.orderid=$orderid";
			$items = query($sql);
			$payed = 0;
			if (!isEmpty($payableid))
				$payed = findValue("select sum(amount) from payment_allocation where payableid=$payableid");
			$sum = findValue("select sum(unitprice*quantity) from purchaseorder_item where orderid=$orderid");
			$vatSum = findValue("select sum(vat*quantity) from purchaseorder_item where orderid=$orderid");
			if (!isEmpty($payableid)) {
				$paymentCount = findValue("select count(*) from payment_allocation where payableid=$payableid");
			}
			if ($paymentCount == 1) {
				$payment_transid = findValue("select transactionid
				                              from payment_allocation ra
											  join payment r on r.paymentid=ra.paymentid
											  where payableid=$payableid");
			}
		}
		$unreceived = findValue("
		select sum(quantity - received_quantity)
		from purchaseorder_item
		where orderid=$orderid");
		$received = findValue("
		select sum(received_quantity)
		from purchaseorder_item
		where orderid=$orderid");
	}
	$toPay = $sum + $vatSum;

	$productid = getParam('productid');
	$unitprice = '';
	$quantity = 1;
	if (!isEmpty($productid)) {
		$unitprice = findValue("
		select price
		from supplier_price
		where productid=$productid
		and supplierid=$supplierid");
		$quantity = findValue("
		select reorder_qty from product 
		where productid=$productid");
	}

	$supplier = null;
	if (!isEmpty($supplierid)) {
		$supplier = find("select name from supplier where supplierid=$supplierid");
	}

	$locations = rs2array(query("select locationid, name from location"));

?>

<head>
<title>thERP - <?php etr("Purchase order") ?></title>
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
<?php title("<a href='purchaseorders.php'>" . tr("Purchase orders") . "</a> > $orderid") ?>

<?php
if ($mess != null) {
	echo "<center class=error>$mess</center>";
}
?>

<form name=postform action="purchaseorder.php" method="POST">
<input type=hidden name=supplierid value='<?php echo $supplierid ?>'/>
<table>
<?php
	if (!$new) {
		echo "<tr><td><b>" . tr("Order id") . ":</b></td>";
		echo "<td>";
		echo $orderid;
		hidden('orderid', $orderid);
		echo "</td>";
	}
?>
<tr><td><b><?php etr("Supplier") ?>:</b></td><td><?php echo $supplier->name ?></td>
<tr>
	<td class=label><?php etr("Location") ?>:</td>
	<td>
	<?php
	if ($received == 0 || $unreceived > 0)
		combobox('locationid', $locations, $locationid, false, 'saveForm()');
	else {
		$location = findValue("select name from location where locationid=$locationid");
		echo $location;
	}
	?>
	</td>
</tr>
<tr><td><b><?php etr("Order date") ?>:</b></td><td><?php echo date(DATE_PATTERN, $orderdate) ?></td></tr>
<?php
if (!isEmpty($payableid)) {
	echo "<tr>";
	echo "<td class=label>" . tr("Payable") . ":</td>";
	echo "<td>";
	echo "<a href='payable.php?payableid=$payableid'>" . tr("Show payable") . "</a>";
	echo "</td>";
	echo "</tr>";
}
?>
<tr>
<td class=label><?php etr("Payment") ?>:</td>
<td>
<?php
	if ($payed >= $toPay && $payed > 0)
		etr("Fully paid");
	else
		echo formatMoney($payed) . " / " . formatMoney($toPay);
	echo "&nbsp;&nbsp;";
	if ($payed > 0) {
		if ($paymentCount > 1)
			echo "<a href='payable_payments.php?payableid=$payableid'>" . tr("Show payments") . "</a>";
		else
			echo "<a href='../accounting/transaction.php?transactionid=$payment_transid'>" . tr("Show transaction") . "</a>";
	}
?>
</td>
</tr>
<?php
if ($cancelled) {
	echo "<tr>";
	echo "<td colspan=2>";
	echo tr("This order is cancelled");
	if ($cancel_transid != null)
		echo " <a href='transaction.php?transactionid=$cancel_transid'>" . tr("Show transaction") . "</a>";
	echo "</td>";
	echo "</tr>";
}

?>
<tr>
<td class=label><?php etr("Created by") ?>:</td>
<td><?php echo $createdby ?></td>
</tr>
</table>
<br/>
<?php if ($items != null) { ?>
<div class='border'>
<table>
<?php
if ($addable)
	echo "<th>" . tr("Delete") . "</th>";
?>
<th><?php etr("Product") ?></th>
<th><?php etr("Quantity") ?></th>
<th><?php etr("Received qty") ?></th>
<th><?php etr("Unit price") ?></th>
<th><?php etr("Amount") ?></th>
<th><?php etr("VAT") ?></th>
<?php
	$class = 'odd';
	$i = 0;
	while ($row = fetch($items)) {
		hidden("no_$i", $row->no);
		echo "<tr class='$class'>";
		$href = "purchaseorder.php?orderid=$orderid&del_no=$row->no";
		if ($addable)
			deleteColumn($href);
		$text = $row->productid - $row->model;
		if (!isEmpty($row->supplier_productcode)) 
			$text .= " ($row->supplier_productcode)";
		echo "<td><a href='product.php?productid=$row->productid'>$text</a></td>";
		echo "<td align=right>$row->quantity</td>";
		echo "<td align=right>";
		if ($row->stock) {
			echo $row->received_quantity;
		}
		echo "</td>";
		echo "<td align=right>" . formatMoney($row->unitprice) . "</td>";
		$amount = $row->quantity * $row->unitprice;
		echo "<td align=right>" . formatMoney($amount) . "</td>";
		echo "<td align=right>" . formatMoney($row->vat) . "</td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
	}
?>
<input type=hidden name=count value='<?php echo $i ?>'/>
<?php
if ($addable) {
	echo "<tr class='$class'>";
	echo "<td/>";
	echo "<td>";
	numberbox('productid_new', $productid);
	$href = "products.php?mode=selectpurchase&orderid=$orderid&supplierid=$supplierid";
	button("Search", "search", $href);
	echo "</td>";
	echo "<td align=right>";
	numberbox('quantity_new', $quantity, 5);
	echo "</td>";
	echo "<td/>";
	echo "<td align=right>";
	moneyBox('unitprice_new', $unitprice);
	echo "</td>";
	echo "<td>",
	button("Add", "add");
	echo "</td>";
	echo "<td/>";
	echo "</tr>";
}
?>
<tr>
<?php
if ($addable) echo "<td/>";
?>
<td/>
<td/>
<td/>
<td/>
<td align=right><?php echo formatMoney($sum) ?></td>
<td align=right><?php echo formatMoney($vatSum) ?></td>
</tr>
<tr>
<?php $colspan = $addable ? 4 : 3 ?>
<td colspan='<?php echo $colspan ?>'/>
<td align=right class=label><?php etr("To pay") ?>:</td>
<td align=right><?php echo formatMoney($toPay) ?></td>
</tr>
</table>
</div>
<br/>
<?php } ?>
<?php
if (!$new) {
	if (isEmpty($payableid)) {
		if ($unreceived > 0) {
			button("Receive goods", "receive");
			echo "&nbsp;";
		}
		button("Register payable", "payable");
		echo "&nbsp;";
	}
}
if (!isEmpty($payableid) && $toPay > $payed) {
	button("Pay", "pay");
	echo "&nbsp;";
}
if (!$new)
	button("Show stock moves", "moves", "stockmoves.php?purchaseorderid=$orderid");
?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
