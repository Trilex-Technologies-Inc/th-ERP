<?php
	include('include.php');
	include('product.inc.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	$productid = getParam('productid');
	$new = true;
	if (isSave()) {
		$productid = getParam('productid');
		$description = getParam('description');
		$model = getParam('model');
		$categoryid = getParam("categoryid");
		$unittype = prepNull(getParam('unittype'));
		$barcode=getParam("barcode");
		if (isNew()) {
			if (isEmpty($productid)) {
				$productid = findValue("select max(lpad(productid, 32, ' ')) from product", 0);
				$productid++;
			}
			if ($productid < 1000)
				$productid += 1000;
			if (isEmpty($unittype)) {
				$unittype = findValue("select unittype from category
                                       where categoryid=$categoryid");
				$unittype = prepNull($unittype);
			}
			$sql = "insert into product (productid, model, description,
                                         categoryid, unittype, barcode)
                    values ('$productid', '$model', '$description',
                            $categoryid, $unittype, '$barcode')";
			sql($sql);
			if (oscommerce()) {
				$now = time();
				$sql = "
				insert into products (
					products_quantity,
					products_model,
					products_price,
					products_date_added,
					products_weight,
					products_status,
					products_tax_class_id
				) values (
					0,
					'$model',
					0,
					from_unixtime($now),
					0,
					1,
					1
				)";
				sql($sql);
				$oscommerceid = insert_id();
				$languages_id = findValue("
				select min(languages_id) from languages");
				sql("
				insert into products_description 
				(products_id, language_id, products_name, products_description)
				values 
				($oscommerceid, $languages_id, '$model', '$description')");
				$categories_id = findValue("
				select min(categories_id) from categories");
				sql("
				insert into products_to_categories (products_id, categories_id)
				values ($oscommerceid, $categories_id)");
				sql("
				update product set oscommerceid=$oscommerceid 
				where productid='$productid'");
			}
		} else {
            $updateSQL =
    			"update product set
    				model='$model',
    			    description='$description',
					categoryid=$categoryid,
                    unittype=$unittype,
                    barcode='$barcode'
                where productid='$productid'";
    		sql($updateSQL);
    		$oscommerceid = findValue("
    		select oscommerceid from product where productid='$productid'");
    		if (!isEmpty($oscommerceid)) {
    			sql("
    			update products set
    				products_model='$model'
    			where products_id=$oscommerceid");
				$languages_id = findValue("
				select min(languages_id) from languages");
				sql("
				update products_description set 
					products_name='$model',
					products_description='$description'
				where products_id=$oscommerceid and language_id=$languages_id");
    		}
		}
		$count = getParam("supplier_count");
		$i = 0;
		while ($i < $count) {
			$supplierid = getParam("supplierid_$i");
			$old_productcode = getParam("old_productcode_$i");
			$productcode = getParam("productcode_$i");
			if ($productcode != $old_productcode) {
				$productcode = prepNull($productcode);
				sql("
				update supplier_price set supplier_productcode='$productcode'
				where productid=$productid and supplierid=$supplierid");
			}
			$i++;
		}
		$supplierid = getParam("supplierid_new");
		if (!isEmpty($supplierid)) {
			$productcode_new = getParam("productcode_new");
			sql("
			insert into supplier_price (supplierid, productid, price, supplier_productcode)
			values ($supplierid, $productid, null, '$productcode_new')");
		}
		
	}

	if (isDelete()) {
		deleteProduct($productid);
		$productid = null;
	}

	$rec = new Dummy();
	$parts = null;
	if (!isEmpty($productid)) {
	    $selectSQL =
  		"select p.productid,
  		       model,
		       p.description,
			   p.barcode,
		       purchase_price,
			   c.categoryid,
			   stock,
               p.unittype,
               percent
		from product p
		left outer join category c on c.categoryid=p.categoryid
		left outer join vat_category vc on vc.vatcatid=c.vatcatid
		where p.productid='$productid'
		group by p.productid
		";
		$rec = find($selectSQL);
		if ($rec != null) {
			$new = false;
		}
	}

	$categories = rs2array(query("select categoryid, description from category"));
	$unittypes = rs2array(query("select unittype, description from unittype"));
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
if ($new)
	$title = tr("Add product");
title("<a href='products.php'>" . tr("Products") . "</a> > $title");
?>
<form name=postform action="product.php" method="POST">
<table>
<tr><td><?php etr("Productno") ?>:</td>
<td>
<?php
	if ($new) {
		numberbox('productid', '');
		echo "&nbsp;(" . tr("Leave empty for auto generated") . ")";
	} else {
		echo $productid;
		echo "<input type='hidden' name='productid' value='$productid'/>";
	}
?>
</td>
<tr><td><?php etr("Model") ?>:</td><td><?php textbox("model", $rec->model) ?></td>

</table>

<div id="header">
<?php buildTabs($productid, 'general') ?>
</div>
<div id="main">
	<div id="contents">

<table>
<tr><td class=label><?php etr("Description") ?>:</td><td><textarea rows=10 cols=60 name='description'><?php echo $rec->description ?></textarea></td>
<tr><td class=label><?php etr("Category") ?>:</td><td><?php comboBox("categoryid", $categories, $rec->categoryid, false) ?></td></tr>
<tr><td class=label><?php etr("Barcode") ?>:</td><td><?php textbox("barcode", $rec->barcode) ?></td></tr>
<tr>
	<td class=label><?php etr("Units of measure") ?>:</td>
	<td><?php combobox('unittype', $unittypes, $rec->unittype, true) ?></td>
</tr>

<tr>
	<td class=label><?php etr("Supplier product code") ?>:</td>
	<td>
		<table>
		<?php
		if (!isEmpty($productid)) {
			$productid2 = isEmpty($productid) ? 0 : $productid;
			$rs = query("
			select sp.supplierid, name, supplier_productcode
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
				textbox("productcode_$i", $row->supplier_productcode, 30, true);
				hidden("old_productcode_$i", $row->supplier_productcode);
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
				textbox("productcode_new", $row->supplier_productcode, 30, true);
				echo "</td>";
			echo "</tr>";
		}
		?>
		</table>

  	</td>
</tr>


</table>
<br/>
<?php
button("Save product", "save");
echo "&nbsp;";
deleteButton();
echo "&nbsp;";
if (!$new)
	button("Add product", "add", "product.php");
?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>

</div></div>
</form>
<?php bottom() ?>

</body>
