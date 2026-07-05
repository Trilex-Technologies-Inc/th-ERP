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
	document.postform.productid_new.focus();
}
</script>
</head>

<body onLoad="onLoad()">
<?php
menubar("productionorders.php");
$title = $new ? tr("Register") : $orderid;
title("<a href='productionorders.php'>" . tr("Production orders") . "</a> > $title") ;
?>

<?php
if ($mess != null) {
	echo "<center class=error>$mess</center>";
}
?>

<form name=postform action="productionorder.php" method="POST">
<input type=hidden name=customerid value='<?php echo $customerid ?>'/>
<table>
<?php
	if (!$new) {
		echo "<tr><td><b>" . tr("Order id") . ":</b></td>";
		echo "<td>";
		echo $orderid;
		echo "<input type='hidden' name='orderid' value='$orderid'/>";
		echo "</td>";
	}
?>
<tr><td><b><?php etr("Created date") ?>:</b></td><td><?php echo date(DATE_PATTERN, $rec->createdtime) ?></td></tr>
<?php
echo "<tr>";
echo "<td class=label>" . tr("Status") . ":</td>";
echo "<td>";
if (!isEmpty($rec->transactionid)) {
	echo tr("Finished") . "&nbsp;&nbsp;<a href='../accounting/transaction.php?transactionid=$rec->transactionid'>Show transaction</a>";
} else
	echo tr("Registered");
echo "</td>";
echo "</tr>";
if ($cancelled) {
	echo "<tr>";
	echo "<td colspan=2>";
	echo tr("This order is cancelled");
	echo "</td>";
	echo "</tr>";
}
?>
<tr>
<td class=label><?php etr("Created by") ?>:</td>
<td><?php echo $rec->createdby ?></td>
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
		echo "<input type=hidden name=productid_$i value='$row->productid'/>";
		echo "<tr class='$class'>";
		$href = "productionorder.php?orderid=$orderid&del_no=$row->no";
		if ($addable)
			deleteColumn($href);
		echo "<td><a href='../erp/product.php?productid=$row->productid'>$row->productid - $row->model</a></td>";
		echo "<td align=right>$row->quantity</td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
	}
?>
<input type=hidden name=count value='<?php echo $i ?>'/>
<?php
if ($addable) {
	echo "<tr class='<?php echo $class ?>'>";
	echo "<td/>";
	echo "<td>";
	//echo "<input id='product_new' type=text name='productid_new' value='$productid' >";
	numberbox('productid_new', $productid);
	button("Search", "search", "../erp/products.php?mode=selectproduction&orderid=$orderid");
	echo "</td>";
	echo "<td align=right><input type=text name='quantity_new' value='1' size=5/></td>";
	echo "<td><input type=submit name=add value='Add'/></td>";
	echo "</tr>";
}
?>
</table>
</div>
<br/>
<?php } ?>

<?php
if (!$new) {
	button("Finish", "finish", null, 'F');
	echo "&nbsp;&nbsp;";
	if (!$cancelled)
		echo "<input type=submit name='cancel' value='" . tr("Cancel order") . "'/>&nbsp;";
	button("Show stock moves", "moves", "../erp/stockmoves.php?productionorderid=$orderid");
}
?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>