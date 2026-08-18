<?php
	include('include.php');
	include('product.inc.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	$productid = getParam('productid');
	$productidSql = sql_string($productid);
	$new = true;
	if (isSave()) {
		$count = getParam("count");
		$i = 0;
		while ($i < $count) {
			$attributeid = getParam("attributeid_$i");
			$old_optionid = getParam("old_optionid_$i");
			$optionid = getParam("optionid_$i");
			if ($optionid != $old_optionid) {
				sql("
				update product_attribute_option_value set optionid=$optionid
				where productid=$productidSql and attributeid=$attributeid");
			}
			$i++;
		}
		$attributeid = getParam("attributeid_new");
		if (!isEmpty($attributeid)) {
			$optionid = getParam("optionid_new");
			sql("
			insert into product_attribute_option_value (attributeid, productid, optionid)
			values ($attributeid, $productidSql, $optionid)");
		}
	}
	
	$del_attributeid = getParam("del_attributeid");
	if (!isEmpty($del_attributeid)) {
		sql("
		delete from product_attribute_option_value
		where productid=$productidSql and attributeid=$del_attributeid");
	}

	$attributes = rs2array(query("
	select attributeid, name 
	from attribute
	where object=" . ATTR_OBJECT_PRODUCT));
	$attributeid = getParam("attributeid_new");
	$options = array();
	if (!isEmpty($attributeid)) {
		$options = rs2array(query("
		select optionid, description from attribute_option
		where attributeid=$attributeid"));
	}
	$model = isEmpty($productid) ? '' : findValue("select model from product where productid=$productidSql", '');
	$new = isEmpty($model);

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
title("<a href='products.php'>" . tr("Products") . "</a> > $title");
?>

<form name="postform" action="product_attributes.php" method="POST" class="product-editor">
<?php hidden('productid', $productid) ?>
<div class="product-editor-intro">
	<div class="product-editor-icon" aria-hidden="true">
		<svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
	</div>
	<div>
		<span class="product-editor-eyebrow"><?php etr("Product catalogue") ?></span>
		<h1><?php echo htmlspecialchars($model) ?></h1>
		<p><?php etr("Maintain product attributes and option values.") ?></p>
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
				<label class="form-label fw-semibold"><?php etr("Productno") ?></label>
				<div class="product-readonly-value"><?php echo htmlspecialchars($productid) ?></div>
			</div>
			<div class="col-12 col-md-7">
				<label class="form-label fw-semibold"><?php etr("Model") ?></label>
				<div class="product-readonly-value"><?php echo htmlspecialchars($model) ?></div>
			</div>
		</div>
	</div>
</section>

<div id="header" class="product-tabs">
	<?php buildTabs($productid, 'attributes') ?>
</div>
<div id="main" class="product-tab-panel">
	<div id="contents">
		<div class="product-section-heading">
			<div><span><?php etr("Attributes") ?></span><h2><?php etr("Product options") ?></h2></div>
		</div>
		<div class="product-attribute-table">
			<div class="product-attribute-row product-attribute-head">
				<div><?php etr("Delete") ?></div>
				<div><?php etr("Attribute") ?></div>
				<div><?php etr("Option") ?></div>
			</div>
			<?php
			$productid2 = isEmpty($productid) ? 0 : $productid;
			$rs = query("
			select v.attributeid, o.description, v.optionid, a.name
			from product_attribute_option_value v
			join attribute_option o on o.attributeid=v.attributeid and o.optionid=v.optionid
			join attribute a on a.attributeid=v.attributeid
			where productid=" . sql_string($productid2) . " and a.object=" . ATTR_OBJECT_PRODUCT);
			$i = 0;
			while ($row = fetch($rs)) {
				hidden("attributeid_$i", $row->attributeid);
				echo "<div class='product-attribute-row'>";
				echo "<div class='product-attribute-delete'>";
				deleteIcon("product_attributes.php?productid=$productid&del_attributeid=$row->attributeid");
				echo "</div>";
				echo "<label for='optionid_$i'>" . htmlspecialchars($row->name) . "</label>";
				$options0 = rs2array(query("
				select optionid, description from attribute_option
				where attributeid=$row->attributeid"));
				echo "<div class='product-field'>";
				echo combobox("optionid_$i", $options0, $row->optionid, false);
				echo "</div></div>";
				$i++;
			}
			hidden("count", $i);
			echo "<div class='product-attribute-row product-attribute-new'>";
			echo "<div></div>";
			echo "<div class='product-field'>";
			combobox("attributeid_new", $attributes, $attributeid, true, 'document.postform.submit()');
			echo "</div>";
			echo "<div class='product-field'>";
			if (count($options) > 0)
				combobox("optionid_new", $options, null, false);
			echo "</div></div>";
			?>
		</div>
		<small class="form-text"><?php etr("Select an attribute first to load its available options.") ?></small>
	</div>
</div>

<div class="product-actions-bar">
	<div class="d-flex flex-wrap gap-2">
		<?php button("Save product", "save") ?>
	</div>
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

<?php bottom() ?>

</body>
