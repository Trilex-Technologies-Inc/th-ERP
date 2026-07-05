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
buildHeader($productid);
?>

<div id="header">
<?php buildTabs($productid, 'parts') ?>
</div>
<div id="main">
	<div id="contents">
<form name=postform action="product_parts.php" method="POST">
<?php 
	hidden('productid', $productid);
	echo "<br/>";
	echo "<table>";
	echo "<th>" . tr("Delete") . "</th>";
	echo "<th>" . tr("Part") . "</th>";
	echo "<th>" . tr("Quantity") . "</th>";
	$class = 'odd';
	while ($row = fetch($parts)) {
		echo "<tr class=$class>";
		echo "<td align=center>";
		deleteIcon("product_parts.php?productid=$productid&del_childid=$row->childid");
		echo "</td>";
		echo "<td>$row->model</td>";
		echo "<td>$row->quantity</td>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
	}
	echo "<tr class=$class/>";
	echo "<td/>";
	echo "<td>";
	comboBox("childid_new", $allProducts, null, true);
	echo "</td>";
	echo "<td>";
	numberbox("quantity_new", 1);
	echo "</td>";
	echo "</tr>";
	echo "</table>";
?>
<br/>
<?php
button("Save product", "save");
?>

</div></div>
</form>
<?php bottom() ?>

</body>
