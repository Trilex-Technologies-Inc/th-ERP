<?php
	include('include.php');
	include('goodsmove.inc.php');

	checkPermission(PERMISSIONID_PURCHASE);

	$orderid = getParam('orderid');
	$new = true;
	$locationid = getParam('locationid');
	$orderdate = time();

	$sent = 0;
	$received = 0;
	$addable = true;
	$cancelled = false;

	$mess = null;

	if (getParam("action") == "create") {
		$locationid = findValue("select locationid from user where username='" . getUser() . "'", 1);
		$sql = "insert into movesorder (orderdate,  createdby, locationid,toid)
		        values (now(), '" . getUser() . "', $locationid, $locationid)";
		sql($sql);
		$orderid = insert_id();
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
			$addable = (sent==0);
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
<?php title("<a href='goodsmoves.php'>" . tr("Stock move order") . "</a> > $orderid") ?>

<?php
if ($mess != null) {
	echo "<center class=error>$mess</center>";
}
?>

<form name=postform action="goodsmove.php" method="POST">
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
<tr>
	<td class=label><?php etr("From Location") ?>:</td>
	<td>
	<?php
	if ($sent == 0)
		combobox('locationid', $locations, $locationid, false, 'saveForm()');
	else {
		$location = findValue("select name from location where locationid=$locationid");
		echo $location;
	}
	?>
	</td>
</tr>
<tr><td><b><?php etr("To Location") ?>:</b></td><td>
	<?php
	if ($sent == 0)
		combobox('toid', $locations, $toid, false, 'saveForm()');
	else {
		$toid = findValue("select name from location where locationid=$toid");
		echo $toid;
	}
	?>
</td>
<tr><td><b><?php etr("Order date") ?>:</b></td><td><?php echo date(DATE_PATTERN, $orderdate) ?></td></tr>
<tr>
<td class=label><?php etr("State") ?>:</td>
<td>
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
</td>
</tr>
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
<th><?php etr("Amount") ?></th>
<?php
	$class = 'odd';
	$i = 0;
	while ($row = fetch($items)) {
		hidden("no_$i", $row->no);
		echo "<tr class='$class'>";
		$href = "goodsmove.php?orderid=$orderid&del_no=$row->no";
		if ($addable)
			deleteColumn($href);
		$text = $row->productid - $row->model;
		if (!isEmpty($row->supplier_productcode)) 
			$text .= " ($row->supplier_productcode)";
		echo "<td><a href='product.php?productid=$row->productid'>$text</a></td>";
		echo "<td align=right>$row->quantity</td>";
		echo "<td align=right>$row->quantity</td>";
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
	$href = "products.php?mode=selectgoodsmove&orderid=$orderid&supplierid=$supplierid";
	button("Search", "search", $href);
	echo "</td>";
	echo "<td align=right>";
	numberbox('quantity_new', $quantity, 5);
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
<td align=right><b><?php etr("Total") ?>:</b></td>
<td align=right><b><?php echo $sum ?></b></td>
</tr>
</table>
</div>
<br/>
<?php } ?>
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
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
