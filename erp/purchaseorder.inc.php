<?php
function add_orderitem($orderid, $productid, $quantity, $unitprice)
{
	$count = findValue("select count(*) from product where productid=$productid", 0);
	if ($count == 0) {
		return tr("Product $productid doesn't exists!");
	}

	$no = findValue("select max(no) from purchaseorder_item where orderid=$orderid", 0);
	$no++;
	$supplierid = findValue("select supplierid from purchaseorder where orderid=$orderid");
	if (isEmpty($unitprice))
		$unitprice = findValue("
		select price
		from supplier_price
		where productid=$productid and supplierid=$supplierid");
	if (isEmpty($unitprice))
		return tr("No unit price supplied"). "!";
	$vat = findValue("select percent
	                  from vat_category v
					  join category c on c.vatcatid=v.vatcatid
					  join product p on p.categoryid=c.categoryid
					  where productid=$productid");
	$vat = $quantity * $unitprice * $vat/100;
	$description = findValue("select description from product where productid=$productid");
	$sql = "insert into purchaseorder_item (orderid, no, productid, quantity, unitprice, vat, comment)
			values ($orderid, $no, $productid, $quantity, $unitprice, $vat, '$description')";
	sql($sql);
	return null;
}

function receive_goods($orderid, $no = null, $received_quantity = null)
{
	$narrative = "Receive goods order $orderid";
	sql("insert into transaction (transtime, narrative, createdby, createdtime)
	     values (now(), '$narrative', '" . getUser() . "', now())");
	$transid = insert_id();
	$locationid = findValue("select locationid from purchaseorder where orderid=$orderid");
	$supplierid = findValue("select supplierid from purchaseorder where orderid=$orderid");
	if ($no == null) {
		sql("update purchaseorder_item set received_quantity=quantity where orderid=$orderid");
		$amount = findValue("select sum(pi.unitprice*pi.quantity)
		                     from purchaseorder_item pi
							 join product p on p.productid=pi.productid
							 where orderid=$orderid");
		$rs = query("select no, quantity, productid from purchaseorder_item where orderid=$orderid");
		while ($row = fetch($rs)) {
			sql("insert into stockmove (productid, diff, narrative, transactionid,
			                            purchaseorderid, no, createdby, locationid)
			     values ($row->productid, $row->quantity, '$narrative', $transid,
			             $orderid, $row->no, '" . getUser() . "', $locationid)");
		}
		$rs = query("select inventory_accountid, sum(pi.unitprice*pi.quantity) as amount
		             from purchaseorder_item pi
		             join product p on p.productid=pi.productid
		             join category c on c.categoryid=p.categoryid
		             where pi.orderid=$orderid
		             group by inventory_accountid");
		while ($row = fetch($rs)) {
			$amount2 = $row->amount;
			if (isEmpty($amount2))
				$amount2 = 0;
			if ($row->inventory_accountid != null) {
				sql("insert into transaction_part (transactionid, accountid, amount)
					 values ($transid, $row->inventory_accountid, $amount2)");
			}
		}

	} else {
		$old_quantity = findValue("
		select received_quantity from purchaseorder_item
		where orderid=$orderid and no=$no");
		$productid = findValue("select productid from purchaseorder_item where orderid=$orderid and no=$no");
		$diff = $received_quantity - $old_quantity;
		sql("
		update purchaseorder_item set received_quantity=$received_quantity
		where orderid=$orderid and no=$no");
		$standardCost = findValue("
		select price
		from product p
	    join supplier_price sp on sp.productid=p.productid and sp.supplierid=$supplierid
		where productid=$productid");
		$amount = $diff * $standardCost;
		sql("insert into stockmove (productid, diff, narrative, transactionid,
		                            purchaseorderid, no, createdby, locationid)
		     values ($productid, $diff, '$narrative', $transid,
		             $orderid, $no, '" . getUser() . "', $locationid)");
		$inventory_accountid = findValue("select inventory_accountid
		                                  from category c
		                                  join product p on p.categoryid=c.categoryid
		                                  where productid=$productid");
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $inventory_accountid, $amount)");
	}
	$goods_received_suspense_accountid = findValue("select goods_received_suspense from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $goods_received_suspense_accountid, (-1) * $amount)");
}

function getPurchaseOrderTotalEx($orderid)
{
	return findValue("select sum(unitprice*si.quantity)
	                  from purchaseorder_item si
                      where orderid=$orderid");
}

function getPurchaseOrderTotalVat($orderid)
{
	return findValue("select sum(vat*quantity)
	                  from purchaseorder_item si
                      where orderid=$orderid");
}

function getPurchaseOrderTotalIncVat($orderid)
{
	return findValue("select sum(si.quantity*(unitprice+vat))
	                  from purchaseorder_item si
                      where orderid=$orderid");
}

function getCreditAccount($supplierid)
{
	$accountid = findValue("select credit_account from supplier where supplierid=$supplierid");
	if (isEmpty($accountid))
		$accountid = findValue("select account_payable from accountconf");
	return $accountid;
}

function create_payable($supplierid, $description, $amount, $vat, $debit_accountid, $duedate, $pay = false)
{
	$suppliername = findValue("select name from supplier where supplierid=$supplierid");
	$narrative = tr("Invoice from supplier ") . "$supplierid - $suppliername";
	sql("insert into transaction (transtime, narrative, createdby, createdtime)
		 values (now(), '$narrative', '" . getUser() . "', now())");
	$transid = insert_id();
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $debit_accountid, $amount)");
	$accountid = findValue("select vat_recoverable from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, $vat)");
	if ($pay) {
		$creditAccount = findValue("select default_cash from accountconf");
	} else {
		$creditAccount = getCreditAccount($supplierid);
	}
	$sum = $amount + $vat;
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $creditAccount, (-1) * $sum)");
	sql("insert into payable (description, supplierid, amount, vat, transactionid, createdby, duedate)
		 values ('$description', $supplierid, $amount, $vat, $transid, '" . getUser() . "', from_unixtime($duedate))");
	$payableid = insert_id();
	if ($pay) {
		sql("insert into payment (supplierid, amount, transactionid, createdby)
			 values ($supplierid, $sum, $transid, '" . getUser() . "')");
		$paymentid = insert_id();
		sql("insert into payment_allocation (paymentid, payableid, amount)
			 values ($paymentid, $payableid, $sum)");
	}
	return $payableid;
}

function create_payable_from_purchaseorder($orderid)
{
	$supplierid = findValue("select supplierid from purchaseorder where orderid=$orderid");
	$narrative = "Payable for order $orderid";
	$duedate = addDay(time(), getCreditLength($supplierid));
	$vat = findValue("
	select sum(vat*si.quantity)
	from purchaseorder_item si
	join product p on p.productid=si.productid
	join category c on c.categoryid=p.categoryid and consignment=0
    where orderid=$orderid");
	$totalAmount = findValue("
	select sum(si.quantity*(unitprice+vat))
	from purchaseorder_item si
	join product p on p.productid=si.productid
	join category c on c.categoryid=p.categoryid and consignment=0
    where orderid=$orderid");
    if ($totalAmount == 0)
    	return;
	$totalAmountEx = $totalAmount - $vat;
	sql("insert into transaction (transtime, narrative, createdby, createdtime)
	     values (now(), '$narrative', '" . getUser() . "', now())");
	$transid = insert_id();
	$account_payable_accountid = getCreditAccount($supplierid);
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $account_payable_accountid, (-1) * $totalAmount)");
	$vat_recoverable_accountid = findValue("select vat_recoverable from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $vat_recoverable_accountid, $vat)");
	$goods_received_suspense = findValue("select goods_received_suspense from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $goods_received_suspense, $totalAmountEx)");
	sql("insert into payable (description, supplierid, amount, vat, transactionid, createdby,
	                          duedate)
		 values ('$narrative', $supplierid, $totalAmountEx, $vat, $transid, '" . getUser() . "',
		         from_unixtime($duedate))");
	$payableid = insert_id();
	sql("update purchaseorder set payableid=$payableid where orderid=$orderid");
}

function getSupplierBalance($supplierid, $overdue=false)
{
	$sql = "
	select
	  sum(p.amount+p.vat) as topay, sum(a.amount) as payed
	from payable p
	left outer join payment_allocation a on a.payableid=p.payableid
	where supplierid=$supplierid";
	if ($overdue)
		$sql .= " and p.duedate < now()";
	$row = find($sql);
	return $row->topay - $row->payed;
}

function create_payment($supplierid, $amount, $bankaccount)
{
	$suppliername = findValue("select name from supplier where supplierid=$supplierid");
	$narrative = tr("Payment to supplier") . " $supplierid - $suppliername";
	sql("insert into transaction (transtime, narrative, createdtime) values (now(), '$narrative', now())");
	$transid = insert_id();
	$accountid = findValue("select account_payable from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, (-1) * $amount)");
	$accountid = findValue("select glaccountid from bankaccount where number='$bankaccount'");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, $amount)");
	sql("insert into payment (supplierid, amount, transactionid)
	     values ($supplierid, $amount, $transid)");
	$receiptid = insert_id();
	return $receiptid;
}

function pay_payable($payableid)
{
	$supplierid = findValue("select supplierid from payable where payableid=$payableid");
	$sum = findValue("select amount + vat as amount
					  from payable
					  where payableid=$payableid");
	$narrative = tr("Payment payable ") . $payableid;
	sql("insert into transaction (transtime, narrative, createdby, createdtime)
		 values (now(), '$narrative', '" . getUser() . "', now())");
	$transid = insert_id();
	$orgTransid = findValue("
	select transactionid from payable where payableid=$payableid");
	$accountid = findValue("
	select tp.accountid
	from payable p
	join transaction_part tp on tp.transactionid=p.transactionid
	join account_group ag on ag.accountid=tp.accountid and ag.groupid=".GROUPID_LIABILITIES."
	where tp.amount=(-1)*$sum");
	//$accountid = getCreditAccount($supplierid);
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid,  $sum)");
	$accountid = findValue("select default_cash from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, (-1) * $sum)");
	sql("insert into payment (supplierid, amount, transactionid, createdby)
		 values ($supplierid, $sum, $transid, '" . getUser() . "')");
	$paymentid = insert_id();
	sql("insert into payment_allocation (paymentid, payableid, amount)
		 values ($paymentid, $payableid, $sum)");
}

function pay_purchaseorder($orderid)
{
	$payableid = findValue("select payableid from purchaseorder where orderid=$orderid");
	pay_payable($payableid);
}

function cancel_payable($payableid)
{
	$narrative = tr("Cancel payable ") . $payableid;
	$transid = findValue("select transactionid from payable where payableid=$payableid");
	$transid = cancel_transaction($transid, $narrative);
}


?>
