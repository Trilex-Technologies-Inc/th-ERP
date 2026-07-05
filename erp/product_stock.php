<?php
	include('include.php');
	include('product.inc.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	function changeQuantity($productid, $locationid, $diff, $createtrans)
	{
		$narrative = tr("Stock adjustment");
		$transid = "null";
		if ($createtrans) {
			$finished_goods = findValue("select finished_goods from accountconf");
			$inventory_adjustment = findValue("select inventory_adjustment from accountconf");
			$standardCost = findValue("select purchase_price from product where productid=$productid");
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
			 values ($productid, $diff, '$narrative', $transid, $locationid)");
		$parts = query("select childid, quantity from bom where parentid=$productid");
		while ($row = fetch($parts)) {
			$childdiff = $diff * $row->quantity;
			sql("insert into stockmove (productid, diff, narrative, transactionid, locationid)
				 values ($row->childid, $childdiff, '$narrative', $transid, $locationid)");
		}
	}

	function move($productid, $fromid, $toid, $diff)
	{
		changeQuantity($productid, $fromid, (-1) * $diff, false);
		changeQuantity($productid, $toid, $diff, false);
	}

	$productid = getParam('productid');
	if (isSave()) {
		$reorder_level = prepParam('reorder_level');
		$reorder_qty = prepParam('reorder_qty');
		sql("
		update product set 
			reorder_level=$reorder_level,
			reorder_qty=$reorder_qty
		where productid=$productid");		
		
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

		$rs = query("
		select l.locationid, sum(diff) as quantity, l.name as location
		from location l 
		left outer join stockmove m on l.locationid=m.locationid and productid=$productid
		group by l.locationid");
	}


	$locations = rs2array(query("select locationid, name from location"));
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
$title = $rec->model;
buildHeader($productid);
?>

<div id="header">
<?php buildTabs($productid, 'stock') ?>
</div>
<div id="main">
	<div id="contents">

<form name=postform action="product_stock.php" method="POST">
<?php hidden('productid', $productid) ?>
<table>
<?php
echo "<th>" . tr("Location") . "</th>";
echo "<th>" . tr("Quantity") . "</th>";
echo "<th>" . tr("Diff") . "</th>";
$class = 'odd';
while ($row = fetch($rs)) {
	echo "<tr class=$class>";
	echo "<td>";
	echo $row->location;
	echo "</td>";
	echo "<td align=right>";
	if (isEmpty($row->quantity))
		echo "0";
	else
		echo $row->quantity;
	echo "</td>";
	echo "<td align=right>";
	echo numberbox("diff_$row->locationid", '', 7, 0, true);
	echo "</td>";
	echo "</tr>";
	$class = ($class == "odd" ? "even" : "odd");
}
?>
</table>
<br/>

<?php
button("Update quantities", "save");
echo "&nbsp;";
checkbox('createtrans', 1);
echo "&nbsp;";
etr("Create general ledger transaction for stock movement");
echo "<br><br><br>";

echo tr("Move") . "&nbsp;";
numberbox('diff', '', 5);
echo "&nbsp;" . tr("pieces from") . "&nbsp;";
combobox('from_locationid', $locations, null, true);
echo "&nbsp;" . tr("to") . "&nbsp;";
combobox('to_locationid', $locations, null, true);
button("Move", "move");

echo "<br><br>";
echo "<a href='stockmoves.php?productid=$productid'>" . tr("Show stock moves") . "</a>";
?>
<br><br>
<table>
<tr>
	<td class=label><?php etr("Re-order level") ?>:</td>
	<td><?php numberbox('reorder_level', $rec->reorder_level) ?>
</tr>
<tr>
	<td class=label><?php etr("Re-order quantity") ?>:</td>
	<td><?php numberbox('reorder_qty', $rec->reorder_qty) ?>
</tr>
</table>
<br><br>

<?php saveButton() ?>
</form>

</div></div>
<?php bottom() ?>

</body>
