<?php
include('../include/therp_include.php');

function deleteProduct($productid)
{	
	$productid = addslashes($productid);
	$count = findValue("select count(*) from salesorder_item where productid='$productid'", 0);
	$count += findValue("select count(*) from purchaseorder_item where productid='$productid'", 0);
	$count += findValue("select count(*) from stockmove where productid='$productid'", 0);
	$count += findValue("select count(*) from bom where parentid='$productid' or childid='$productid'", 0);
	if ($count > 0) {
		// Products referenced by orders, stock movements, or BOMs must remain
		// available to preserve historical and manufacturing relationships.
		sql("update product set active=0 where productid='$productid'");
	} else {
		$oscommerceid = findValue("select oscommerceid from product where productid='$productid'", null);
		sql("delete from sales_price where productid='$productid'");
		sql("delete from product where productid='$productid'");
		if (oscommerce()) {
			if (!isEmpty($oscommerceid))
				sql("delete from products where products_id=$oscommerceid");
		}
	}
}

function menubar($currentHref = null, $helpUrl = 'http://therp.sf.net')
{
	top0("Stock/Inventory");
	echo "<nav class='app-sidebar' aria-label='" . tr("Module navigation") . "'>";
	sidebarHomeLink();
	echo "<div class='app-nav-list'>";
			$percent = 20;
			menu('products.php', 'Products', $percent, true, $currentHref);
			menu('purchase.php', 'Purchase', $percent, true, $currentHref);
			menu('goodsmoves.php', 'Stock move', $percent, true, $currentHref);
			menu('configuration.php', 'Configuration', $percent, true, $currentHref);
			menu($helpUrl, 'Help', $percent, false, $currentHref);
	echo "</div>";
	echo "</nav>";
	showUpgrade();
}


?>
