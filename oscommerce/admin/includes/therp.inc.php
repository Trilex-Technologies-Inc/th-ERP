<?php
define('THERP_HOME', '../../therp');

include(THERP_HOME. '/include/database.inc.php');

function create_salesorder($orders_id)
{
	$customerid = findValue("
	select customerid from customer where name='osCommerce'");
	sql("
	insert into salesorder (orderdate, customerid, createdby, oscommerceid)
	select date_purchased, $customerid, 'oscommerce', $orders_id
	from orders
	where orders_id=$orders_id");
	$orderid = insert_id();
	sql("
	insert into salesorder_item (orderid, no, productid, quantity, unitprice, vat)
	select
		$orderid, 
		orders_products_id,
		p.productid, 
		products_quantity,
		final_price,
		0
	from orders_products op
	join product p on op.products_id=p.oscommerceid
	where op.orders_id=$orders_id
	");
}

?>