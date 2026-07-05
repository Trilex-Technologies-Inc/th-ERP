<?php
	include('include.php');
	include('product.inc.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	$productid = getParam('productid');
	$new = true;
	if (isSave()) {
		$purchase_price = prepNull(getParam('purchase_price'));
		$updateSQL =
			"update product set
				purchase_price=$purchase_price
			where productid='$productid'";
		sql($updateSQL);
    	$oscommerceid = findValue("
    	select oscommerceid from product where productid='$productid'");
    	$osclistid = null;
		if (!isEmpty($oscommerceid)) {
			$osclistid = findValue("
			select listid from pricelist 
			where vat_included=1
			order by listid");		
		}
		$rs = sql("select listid from pricelist");
		while ($row = fetch($rs)) {
			$old_price = getParam("old_salesprice_$row->listid");
			$price = getParam("salesprice_$row->listid");
			if ($price != $old_price) {
				$price = prepNull($price);
				sql("
				update sales_price set price=$price
				where productid=$productid and listid=$row->listid");
				if (affected_rows() == 0) {
					sql("
					insert into sales_price (productid, listid, price)
					values ($productid, $row->listid, $price)");
				}
				if ($row->listid == $osclistid) {
					sql("
					update products set products_price=$price
					where products_id=$oscommerceid");
				}
			}
		}
		$count = getParam("supplier_count");
		$i = 0;
		while ($i < $count) {
			$supplierid = getParam("supplierid_$i");
			$old_price = getParam("old_purchaseprice_$i");
			$price = getParam("purchaseprice_$i");
			if ($price != $old_price) {
				$price = prepNull($price);
				sql("
				update supplier_price set price=$price
				where productid=$productid and supplierid=$supplierid");
			}
			$i++;
		}
		$supplierid = getParam("supplierid_new");
		if (!isEmpty($supplierid)) {
			$price = getParam("purchaseprice_new");
			sql("
			insert into supplier_price (supplierid, productid, price)
			values ($supplierid, $productid, $price)");
		}
	}

	$rec = new Dummy();
	if (!isEmpty($productid)) {
	    $selectSQL =
  		"select p.productid,
  		       model,
		       p.description,
		       purchase_price
		from product p
		where p.productid='$productid'
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
		}
	}

	$suppliers = rs2array(query("select supplierid, name from supplier"));

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
<?php buildTabs($productid, 'prices') ?>
</div>
<div id="main">
	<div id="contents">

<form name=postform action="product_prices.php" method="POST">
<?php hidden('productid', $productid) ?>
<table>
<tr>
	<td><?php etr("Sales price") ?>:</td>
	<td>
		<table>
		<?php
		$productid2 = isEmpty($productid) ? 0 : $productid;
		$rs = query("
		select pl.listid, pl.description, price
		from pricelist pl
		left outer join sales_price sp on sp.listid=pl.listid and sp.productid=$productid2
		");
		while ($row = fetch($rs)) {
			echo "<tr>";
			echo "<td>$row->description:</td>";
			echo "<td>";
			moneybox("salesprice_$row->listid", $row->price);
			hidden("old_salesprice_$row->listid", $row->price);
			echo "</td>";
			echo "</tr>";
		}
		?>
		</table>
	</td>
</tr>
<tr>
	<td><?php etr("Purchase price") ?>:</td>
	<td>
		<table>
		<?php
		$productid2 = isEmpty($productid) ? 0 : $productid;
		$rs = query("
		select sp.supplierid, name, price
		from supplier_price sp
		join supplier s on s.supplierid=sp.supplierid
		where productid=$productid
		");
		$i = 0;
		while ($row = fetch($rs)) {
			hidden("supplierid_$i", $row->supplierid);
			echo "<tr>";
			echo "<td>$row->name:</td>";
			echo "<td>";
			moneybox("purchaseprice_$i", $row->price);
			hidden("old_pruchaseprice_$i", $row->price);
			echo "</td>";
			echo "</tr>";
			$i++;
		}
		hidden("supplier_count", $i);
		echo "<tr>";
		echo "<td>";
		combobox("supplierid_new", $suppliers, null, true);
		echo "</td>";
		echo "<td>";
		moneybox("purchaseprice_new", $row->price);
		echo "</td>";
		echo "</tr>";
		?>
		</table>

  	</td>
</tr>

</table>
<br/>
<?php
button("Save product", "save");
echo "&nbsp;";
?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</div></div>
<?php bottom() ?>

</body>
