<?php
include('../include/therp_include.php');

function deleteProduct($productid)
{	
	$count = findValue("select count(*) from salesorder_item where productid=$productid");
	$count += findValue("select count(*) from purchaseorder_item where productid=$productid");
	$count += findValue("select count(*) from stockmove where productid=$productid");
	if ($count > 0) {
		sql("update product set active=0 where productid=$productid");
	} else {
		sql("delete from sales_price where productid='$productid'");
		sql("delete from product where productid='$productid'");
		if (oscommerce()) {
    		$oscommerceid = findValue("
    		select oscommerceid from product where productid='$productid'");	
    		if (!isEmpty($oscommerceid))		
				sql("delete from products where products_id=$oscommerceid");
		}
	}
}

function menubar($currentHref = null, $helpUrl = 'http://therp.sf.net')
{
	top0("Stock/Inventory");
	echo "<table width='100%' cellspacing=0 cellpadding=0 >";
	echo "<tr>";
	echo "<td>";
	echo "<table width='100%' class=menubar>";
		echo "<tr>";
			$percent = 20;
			menu('products.php', 'Products', $percent, true, $currentHref);
			menu('purchase.php', 'Purchase', $percent, true, $currentHref);
			menu('goodsmoves.php', 'Stock move', $percent, true, $currentHref);
			menu('configuration.php', 'Configuration', $percent, true, $currentHref);
			menu($helpUrl, 'Help', $percent, false, $currentHref);
		echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	showUpgrade();
}


?>
