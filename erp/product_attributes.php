<?php
	include('include.php');
	include('product.inc.php');

	checkPermission(PERMISSIONID_MANAGE_PRODUCTS);

	$productid = getParam('productid');
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
				where productid=$productid and attributeid=$attributeid");
			}
			$i++;
		}
		$attributeid = getParam("attributeid_new");
		if (!isEmpty($attributeid)) {
			$optionid = getParam("optionid_new");
			sql("
			insert into product_attribute_option_value (attributeid, productid, optionid)
			values ($attributeid, $productid, $optionid)");
		}
	}
	
	$del_attributeid = getParam("del_attributeid");
	if (!isEmpty($del_attributeid)) {
		sql("
		delete from product_attribute_option_value
		where productid=$productid and attributeid=$del_attributeid");
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
<?php buildTabs($productid, 'attributes') ?>
</div>
<div id="main">
	<div id="contents">

<form name=postform action="product_attributes.php" method="POST">
<table>
<th><?php etr("Delete") ?></th>
<th><?php etr("Attribute") ?></th>
<th><?php etr("Option") ?></th>
<?php
hidden('productid', $productid);
$productid2 = isEmpty($productid) ? 0 : $productid;
$rs = query("
select v.attributeid, o.description, v.optionid, a.name
from product_attribute_option_value v
join attribute_option o on o.attributeid=v.attributeid and o.optionid=v.optionid
join attribute a on a.attributeid=v.attributeid
where productid=$productid and a.object=" . ATTR_OBJECT_PRODUCT);
$i = 0;
$class = 'odd';
while ($row = fetch($rs)) {
	hidden("attributeid_$i", $row->attributeid);
	echo "<tr class=$class>";
	deleteColumn("product_attributes.php?productid=$productid&del_attributeid=$row->attributeid");
	echo "<td>$row->name</td>";
	$options0 = rs2array(query("
	select optionid, description from attribute_option
	where attributeid=$row->attributeid"));
	echo "<td>";
	echo combobox("optionid_$i", $options0, $row->optionid, false);
	echo "</td>";
	echo "</tr>";
	$i++;
	$class = $class == 'odd' ? 'even' : 'odd';
}
hidden("count", $i);
echo "<tr>";
echo "<td/>";
echo "<td>";
combobox("attributeid_new", $attributes, $attributeid, true, 'document.postform.submit()');
echo "</td>";
echo "<td>";
if (count($options) > 0)
	combobox("optionid_new", $options, null, false);
echo "</td>";
echo "</tr>";
?>

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
