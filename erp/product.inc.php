<?php
function buildTabs($productid, $tab)
{
	echo "<ul id=primary>";
	if ($tab == 'general')
		echo "<li><span>" . tr("General") . "</span></li>";
	else
		buildLink("product.php?productid=$productid", tr("General"), $productid);
	if ($tab == 'prices')
		echo "<li><span>" . tr("Prices") . "</span></li>";
	else
		buildLink("product_prices.php?productid=$productid", tr("Prices"), $productid);
	if ($tab == 'stock')
		echo "<li><span>" . tr("Stock") . "</span></li>";
	else
		buildLink("product_stock.php?productid=$productid", tr("Stock"), $productid);
	if ($tab == 'attributes')
		echo "<li><span>" . tr("Attributes") . "</span></li>";
	else
		buildLink("product_attributes.php?productid=$productid", tr("Attributes"), $productid);
	if ($tab == 'parts')
		echo "<li><span>" . tr("Parts") . "</span></li>";
	else
		buildLink("product_parts.php?productid=$productid", tr("Parts"), $productid);
	echo "</ul>";
}

function buildLink($href, $text, $productid)
{
	echo "<li>";
	if (isEmpty($productid))
		echo  "<a>$text</a>";
	else
		echo "<a href='$href'>$text</a>";
	echo "</li>";
}

function buildHeader($productid)
{
	$model = findValue("select model from product where productid=$productid");
	title("<a href='products.php'>" . tr("Products") . "</a> > $model");
	echo "<div class='container-fluid px-0 erp-form-layout'>";
	echo "<div class='row g-3 align-items-center mb-2'><div class='col-12 col-md-auto'>" . tr("Productno") . ":</div>";
	echo "<div class='col-12 col-md-auto'>";
	echo $productid;
	echo "</div>";
	echo "</div><div class='row g-3 align-items-center mb-2'><div class='col-12 col-md-auto'>" . tr("Model") . ":</div><div class='col-12 col-md-auto'>$model</div></div>";
	echo "</div>";
	hidden('productid', $productid);
}


?>
