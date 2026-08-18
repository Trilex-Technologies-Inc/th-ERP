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
				$productid = (int)findValue(
					"select max(cast(productid as unsigned)) from product
					 where productid regexp '^[0-9]+$'",
					0
				) + 1;
			}
			if (is_numeric($productid) && (int)$productid < 1000)
				$productid = (int)$productid + 1000;
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
				where productid=" . sql_string($productid) . " and supplierid=$supplierid");
			}
			$i++;
		}
		$supplierid = getParam("supplierid_new");
		if (!isEmpty($supplierid)) {
			$productcode_new = getParam("productcode_new");
			sql("
			insert into supplier_price (supplierid, productid, price, supplier_productcode)
			values ($supplierid, " . sql_string($productid) . ", null, '$productcode_new')");
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
	if ($rec == null) {
		$rec = new Dummy();
	}

	$categories = rs2array(query("select categoryid, description from category"));
	$unittypes = rs2array(query("select unittype, description from unittype"));
	$suppliers = rs2array(query("select supplierid, name from supplier"));
	$model = $rec == null ? '' : $rec->model;

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
$title = $model;
if ($new)
	$title = tr("Add product");
title("<a href='products.php'>" . tr("Products") . "</a> > $title");
?>
<form name="postform" action="product.php" method="POST" class="product-editor">
<div class="product-editor-intro">
	<div class="product-editor-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24"><path d="M20 13V7a2 2 0 0 0-1-1.73l-6-3.46a2 2 0 0 0-2 0L5 5.27A2 2 0 0 0 4 7v6a2 2 0 0 0 1 1.73l6 3.46a2 2 0 0 0 2 0l6-3.46A2 2 0 0 0 20 13ZM4.27 6 12 10.5 19.73 6M12 22V10.5"/></svg>
	</div>
	<div>
		<span class="product-editor-eyebrow"><?php etr("Product catalogue") ?></span>
		<h1><?php echo $new ? tr("Create a product") : htmlspecialchars($model) ?></h1>
		<p><?php etr("Maintain product details, classification, and supplier references.") ?></p>
	</div>
	<?php if (!$new) { ?><span class="product-id-badge"><?php etr("Productno") ?> #<?php echo htmlspecialchars($productid) ?></span><?php } ?>
</div>

<section class="card border-0 shadow-sm product-identity-card">
	<div class="card-body">
		<div class="product-section-heading">
			<div><span><?php etr("Identity") ?></span><h2><?php etr("Basic information") ?></h2></div>
		</div>
		<div class="row g-4">
			<div class="col-12 col-md-5">
				<label class="form-label fw-semibold" for="productid"><?php etr("Productno") ?></label>
				<?php if ($new) { ?>
					<div class="product-field"><?php numberbox('productid', '') ?></div>
					<small class="form-text"><?php etr("Leave empty for auto generated") ?></small>
				<?php } else { ?>
					<div class="product-readonly-value"><?php echo htmlspecialchars($productid) ?></div>
					<input type="hidden" name="productid" value="<?php echo htmlspecialchars($productid) ?>"/>
				<?php } ?>
			</div>
			<div class="col-12 col-md-7">
				<label class="form-label fw-semibold" for="model"><?php etr("Model") ?></label>
				<div class="product-field"><?php textbox("model", $rec->model) ?></div>
			</div>
		</div>
	</div>
</section>

<div id="header" class="product-tabs">
	<?php buildTabs($productid, 'general') ?>
</div>
<div id="main" class="product-tab-panel">
	<div id="contents">
		<div class="product-section-heading">
			<div><span><?php etr("General") ?></span><h2><?php etr("Product details") ?></h2></div>
		</div>
		<div class="row g-4">
			<div class="col-12">
				<label class="form-label fw-semibold" for="description"><?php etr("Description") ?></label>
				<textarea rows="5" name="description" id="description"><?php echo htmlspecialchars($rec->description) ?></textarea>
			</div>
			<div class="col-12 col-md-4">
				<label class="form-label fw-semibold" for="categoryid"><?php etr("Category") ?></label>
				<div class="product-field"><?php comboBox("categoryid", $categories, $rec->categoryid, false) ?></div>
			</div>
			<div class="col-12 col-md-4">
				<label class="form-label fw-semibold" for="barcode"><?php etr("Barcode") ?></label>
				<div class="product-field"><?php textbox("barcode", $rec->barcode) ?></div>
			</div>
			<div class="col-12 col-md-4">
				<label class="form-label fw-semibold" for="unittype"><?php etr("Units of measure") ?></label>
				<div class="product-field"><?php combobox('unittype', $unittypes, $rec->unittype, true) ?></div>
			</div>
		</div>

		<?php if (!isEmpty($productid)) { ?>
		<section class="supplier-codes">
			<div class="product-section-heading">
				<div><span><?php etr("Suppliers") ?></span><h2><?php etr("Supplier product codes") ?></h2></div>
			</div>
			<div class="supplier-code-table">
		<?php
			$productid2 = isEmpty($productid) ? 0 : $productid;
			$rs = query("
			select sp.supplierid, name, supplier_productcode
			from supplier_price sp
			join supplier s on s.supplierid=sp.supplierid
			where productid=" . sql_string($productid) . "
			");
			$i = 0;
			while ($row = fetch($rs)) {
				hidden("supplierid_$i", $row->supplierid);
				echo "<div class='supplier-code-row'>";
				echo "<label for='productcode_$i'>" . htmlspecialchars($row->name) . "</label>";
				echo "<div class='product-field'>";
				textbox("productcode_$i", $row->supplier_productcode, 30, true);
				hidden("old_productcode_$i", $row->supplier_productcode);
				echo "</div></div>";
				$i++;
			}
			hidden("supplier_count", $i);
			echo "<div class='supplier-code-row supplier-code-new'>";
			echo "<div class='product-field'>";
			combobox("supplierid_new", $suppliers, null, true);
			echo "</div><div class='product-field'>";
			textbox("productcode_new", null, 30, true);
			echo "</div></div>";
		?>
			</div>
			<small class="form-text"><?php etr("Select a supplier and enter their product reference to add another code.") ?></small>
		</section>
		<?php } ?>
	</div>
</div>

<div class="product-actions-bar">
	<div class="d-flex flex-wrap gap-2">
		<?php button("Save product", "save") ?>
		<?php if (!$new) button("Add product", "add", "product.php") ?>
	</div>
	<div><?php deleteButton() ?></div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>

</body>
