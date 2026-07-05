 <?php
function add_orderitem($orderid, $productid, $quantity)
{
	$count = findValue("select count(*) from product where productid=$productid", 0);
	if ($count == 0) {
		return tr("Product $productid doesn't exists!");
	}

	$no = findValue("select max(no) from movesorder_item where orderid=$orderid", 0);
	$no++;
	$toid = findValue("select toid from movesorder where orderid=$orderid");
	$description = findValue("select description from product where productid=$productid");
	$sql = "insert into movesorder_item (orderid, no, productid, quantity, comment)
			values ($orderid, $no, $productid, $quantity, '$description')";
	sql($sql);
	return null;
}

function changeQuantity($orderid,$productid, $locationid, $diff, $createtrans)
{
	$narrative = tr("Stock Move Order")."#".$orderid;
	$transid = "null";
	if ($createtrans) {
	}
	sql("insert into stockmove (movesorderid,productid, diff, narrative, transactionid, locationid)
		 values ($orderid,$productid, $diff, '$narrative', $transid, $locationid)");
	$parts = query("select childid, quantity from bom where parentid=$productid");
	while ($row = fetch($parts)) {
		$childdiff = $diff * $row->quantity;
		sql("insert into stockmove (movesorderid,productid, diff, narrative, transactionid, locationid)
			 values ($orderid,$row->childid, $childdiff, '$narrative', $transid, $locationid)");
	}
}


function send_goods($orderid, $no = null, $received_quantity = null)
{
	$fromid = findValue("select locationid from movesorder where orderid=$orderid", null);
	$sql = "select productid,quantity from movesorder_item where orderid=$orderid";
	$items = query($sql);
	while ($row = fetch($items)) {
		changeQuantity($orderid,$row->productid, $fromid, (-1) * $row->quantity, false);
	}
	sql("update movesorder set sent=1 where orderid=$orderid");
}
function receive_goods($orderid, $no = null, $received_quantity = null)
{
	$toid = findValue("select toid from movesorder where orderid=$orderid", null);
	$sql = "select productid,quantity from movesorder_item where orderid=$orderid";
	$items = query($sql);
	while ($row = fetch($items)) {
		changeQuantity($orderid,$row->productid, $toid, (1) * $row->quantity, false);
	}
	sql("update movesorder set received=1 where orderid=$orderid");
}

function cancel_order($orderid)
{
	$narrative = tr("Cancel order ") . $orderid;
	$transid = findValue("select invoice_transid from movesorder where orderid=$orderid");
	if ($transid != null)
		cancel_transaction($transid, $narrative);
	$moves = query("update stockmove set diff=0 where movesorderid=$orderid");
	sql("update movesorder set cancelled=1 where orderid=$orderid");
}

?>
