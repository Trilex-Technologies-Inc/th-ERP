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

<form action="products.php" method="GET" name=searchform>
<div class="border">
<table>
<tr><td><?php etr("Model") ?>:</td><td><?php textbox('model', $model) ?></td></tr>
<tr><td><?php etr("Barcode") ?>:</td><td><?php textbox('barcode', $barcode) ?></td></tr>
<tr><td><?php etr("Supplier") ?>:</td><td><?php combobox('supplierid', $suppliers, $supplierid, true) ?></td></tr>
<tr><td><?php etr("Location") ?>:</td><td><?php combobox('locationid', $locations, $locationid, true) ?></td></tr>
<tr>
	<td>
	<?php searchButton() ?>
	<?php button("Print", "print", "javascript:printReport()") ?>
	</td>
</tr>
</table>
</div>
</form>
&nbsp;

<form action="products.php" method=POST>
<input type=hidden name=mode value='<?php echo $mode ?>'/>
<input type=hidden name=orderid value='<?php echo $orderid ?>'/>
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Productno") ?></th>
<th><?php etr("Product") ?></th>
<?php
if (isEmpty($mode)) {
	echo "<th>" . tr("Quantity") . "</th>";
	echo "<th>" . tr("Ordered qty, sales") . "</th>";
	echo "<th>" . tr("Ordered qty, purchase") . "</th>";
}
	echo "<th>" . tr("Barcode") . "</th>";

$rs = query($selectSQL);
$class = "odd";
while ($row = fetch_object($rs)) {
	$href = "product.php?productid=$row->productid";
	if ($mode == 'selectproduct')
		$href = "../sales/salesorder.php?orderid=$orderid&productid=$row->productid";
	else if ($mode == 'selectpurchase')
		$href = "purchaseorder.php?orderid=$orderid&productid=$row->productid";
	else if ($mode == 'selectgoodsmove')
		$href = "goodsmove.php?orderid=$orderid&productid=$row->productid";
	else if ($mode == 'selectproduction')
		$href = "../manufacturing/productionorder.php?orderid=$orderid&productid=$row->productid";
	echo "<tr class='$class'>";
	deleteColumn("products.php?del_productid=$row->productid");
	echo "<td>$row->productid</td>";
	echo "<td><a href='$href'>$row->model</a></td>";
	if (isEmpty($mode)) {
		$href = "stockmoves.php?productid=$row->productid&locationid=$locationid";
		echo "<td align=right><a href='$href' class=sum>$row->quantity</a></td>";
		$href = "sales.php?productid=$row->productid&uninvoiced=1";
		echo "<td align=right><a href='$href' class=sum>$row->so_quantity</a></td>";
		$href = "purchaseorders.php?productid=$row->productid";
		echo "<td align=right><a href='$href' class=sum>$row->po_quantity</a></td>";
	}
		echo "<td align=right>$row->barcode</td>";
	echo "</tr>";
	$class = ($class == "odd" ? "even" : "odd");
}
?>
</table>
<br/>
<?php button("Add product", "add", "product.php") ?>
</form>
<?php bottom() ?>
</body>
