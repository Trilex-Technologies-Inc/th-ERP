<?php
include('include.php');
include('salesorder.inc.php');

set_error_handler("serverErrorHandler");

$action = getParam("action");
if ($action == "getlocations") {
	response("select locationid, name from location");
	die;
}
if ($action == "getsalesorder") {
	$orderid = getParam("orderid");
	$sql =
	"select so.orderid,
		   unix_timestamp(orderdate) as orderdate,
		   customerid,
		   invoice_transid,
		   cancelled,
		   so.createdby,
		   rso.orderid as recur_orderid,
		   active,
		   credit_orgid,
		   comment,
		   locationid,
		   c.name as customername
	from salesorder so
	join customer c on c.customerid=so.customerid
	left outer join transaction t on t.transactionid=so.invoice_transid
	left outer join recur_salesorder rso on rso.orderid=so.orderid
	where so.orderid=$orderid
	";
	$row = find($sql);
	echo $row->customerid.';';
	echo $row->customername.';';
	echo $row->locationid.';';

	$sql = "
	select
	  si.productid,
	  model,
	  si.quantity,
	  unitprice,
	  vat,
	  no,
	  percent,
	  u.description as unittype,
	  purchase_price
	from salesorder_item si
	join product p on p.productid=si.productid
	join category c on c.categoryid=p.categoryid
	join vat_category vc on vc.vatcatid=c.vatcatid
	left outer join unittype u on u.unittype=p.unittype
	where orderid=$orderid
	and si.productid != " . PRODUCTID_ROUNDING;
	$rs = query($sql);
	while ($row = fetch($rs)) {
		echo $row->no.';';
		echo $row->productid.';';
		echo $row->model.';';
		echo $row->quantity.';';
		echo $row->unitprice.';';
	}
	die;
}
if ($action == "savesalesorder") {
	$orderid = getParam("orderid");
	$locationid = getParam("locationid");
	if ($orderid == -1) {
		$customerid = CUSTOMERID_CASH;
		$sql = "insert into salesorder (orderdate, customerid, createdby, locationid)
				values (now(), $customerid, '" . getUser() . "', $locationid)";
		sql($sql);
		$orderid = insert_id();
	}
	echo $orderid.';';
	$count = getParam("count");
	for ($i = 0; $i < $count; $i++) {
		$no = getParam("no_$i");
		$productid = getParam("productid_$i");
		$quantity = getParam("quantity_$i");
		if ($no == -1) {
			$ret = add_orderitem($orderid, $productid, $quantity);
		} else {
			sql("
			update salesorder_item
			set quantity=$quantity
			where orderid=$orderid and no=$no");
		}
	}
	die;
}

if ($action == "searchproducts") {
	$model = getParam("model");
	$sql = "
	select
		producid,
		model
	from product
	where model like '$model%'";
	response($sql);
}

function response($sql)
{
	$rs = query($sql);
	$colcount = num_fields($rs);
	while ($row = fetch_array($rs)) {
		for ($i=0; $i < $colcount; $i++)
			echo $row[$i] . ';';
		echo "\n";
	}
}

?>
