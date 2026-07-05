<?php
function add_orderitem($orderid, $productid, $quantity)
{
	$count = findValue("select count(*) from product where productid=$productid", 0);
	if ($count == 0) {
		return tr("Product $productid doesn't exists!");
	}
	$no = findValue("select max(no) from productionorder_item where orderid=$orderid", 0);
	$no++;
	$description = findValue("select description from product where productid=$productid");	
	$sql = "insert into productionorder_item (orderid, no, productid, quantity)
			values ($orderid, $no, $productid, $quantity)";
	sql($sql);
}

function finish_productionorder($orderid)
{
	sql("insert into transaction (transtime, createdby, createdtime)
	     values (now(), '" . getUser() . "', now())");
	$transactionid = insert_id();
	sql("update productionorder set transactionid=$transactionid where orderid=$orderid");
	$rs = query("select childid, 
	               bom.quantity as bomq,
                   poi.quantity as orderq,
				   p.purchase_price
                from productionorder po
                join productionorder_item poi on poi.orderid=po.orderid
                join bom on bom.parentid=poi.productid
                join product p on p.productid=bom.childid
				where po.orderid=$orderid");
	$narrative = "Production order $orderid";
	$price = 0;
    while ($row = fetch($rs)) {
		$diff = (-1) * $row->bomq * $row->orderq;
		sql("insert into stockmove (productid, diff, narrative, transactionid, productionorderid, createdby)
		     values ($row->childid, $diff, '$narrative', $transactionid, $orderid, '" . getUser() . "')");
		$price += $row->purchase_price * $row->bomq * $row->orderq;
	}
	$rs = query("select poi.productid, 
                   poi.quantity as orderq
                from productionorder po
                join productionorder_item poi on poi.orderid=po.orderid
                join product p on p.productid=poi.productid
				where po.orderid=$orderid");
    while ($row = fetch($rs)) {
		sql("insert into stockmove (productid, diff, narrative, transactionid, productionorderid, createdby)
		     values ($row->productid, $row->orderq, '$narrative', $transactionid, $orderid, '" . getUser() . "')");
	}
	$rawmaterial = findValue("select raw_material from accountconf");
	$finishedgoods= findValue("select finished_goods from accountconf");
	$price = (-1) * $price;
	sql("insert into transaction_part (transactionid, accountid, amount)
	     values ($transactionid, $rawmaterial, $price)");
	$price = (-1) * $price;
	sql("insert into transaction_part (transactionid, accountid, amount)
	     values ($transactionid, $finishedgoods, $price)");
}

function cancel_order($orderid)
{
	$narrative = tr("Cancel production order ") . $orderid;
	$transid = findValue("select transactionid from productionorder where orderid=$orderid");
	if ($transid != null)
		cancel_transaction($transid, $narrative);
	query("update stockmove set diff=0 where productionorderid=$orderid");
	sql("update productionorder set cancelled=1 where orderid=$orderid");
}

?>