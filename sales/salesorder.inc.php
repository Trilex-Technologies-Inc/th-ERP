<?php
function add_orderitem($orderid, $productid, $quantity, $unitprice, $description)
{
	$count = findValue("
	select count(*)
	from product
	where productid='$productid'", 0);
	if ($count == 0) {
		$count = findValue("
		select count(*)
		from product
		where model='$productid'", 0);
		if ($count == 0)
			return "ERROR:" . tr("Product $productid doesn't exists!");
		else {
			$productid = findValue("select productid from product where model='$productid'");
		}
	}
	$no = findValue("select max(no) from salesorder_item where orderid=$orderid", 0);
	$no++;
	$listid = findValue("
	select pricelistid
	from customer c
	join salesorder so on so.customerid=c.customerid
	where so.orderid=$orderid");
	$vatIncluded = findValue("select vat_included from pricelist where listid=$listid");
	if (isEmpty($unitprice))
		$unitprice = findValue("
		select price from sales_price
		where productid='$productid' and listid=$listid");
	if (isEmpty($unitprice))
		return "ERROR:" . tr("No unit price supplied!");
	$useVAT = findValue("
	select use_vat
	from customer c
	join salesorder so on so.customerid=c.customerid
	where orderid=$orderid");
	if ($useVAT) {
		$vatPercent = findValue("
		select percent
		from vat_category v
		join category c on c.vatcatid=v.vatcatid
		join product p on p.categoryid=c.categoryid
		where productid=$productid", 0);
	} else
		$vatPercent = 0;
	if ($vatIncluded) {
		$unitpriceVAT = $unitprice;
		$unitprice = $unitprice / (1+$vatPercent/100);
		$vat = $unitpriceVAT - $unitprice;
	} else {
		$vat = $unitprice * ($vatPercent/100);
	}
	$sql = "
	insert into salesorder_item (
		orderid, 
		no, 
		productid, 
		quantity, 
		unitprice, 
		vat, 
		comment)
	values (
		$orderid, 
		$no, 
		$productid, 
		$quantity, 
		$unitprice, 
		$vatPercent, 
		'$description')";
	sql($sql);
	$toPay = getSalesOrderTotalIncVat($orderid);
	$toPayRounded = roundAmount($toPay);
	$diff = $toPayRounded - $toPay;
	$productid = PRODUCTID_ROUNDING;
	sql("update salesorder_item set unitprice=unitprice+$diff
	     where orderid=$orderid and productid=$productid");
	if (affected_rows() == 0) {
		$no = findValue("select max(no) from salesorder_item where orderid=$orderid", 0);
		$no++;
		sql("insert into salesorder_item (orderid, no, productid, quantity, unitprice, vat)
		     values ($orderid, $no, $productid, 1, $diff, 0)");
	}
	return $no;
}

function invoice_salesorder($orderid)
{
	$customerid = findValue("select customerid from salesorder where orderid=$orderid");
	$accountid = getCustomerCreditAccount($customerid);
	invoice_salesorder0($orderid, $accountid);
}

function invoice_salesorder0($orderid, $debitaccount)
{
	$rs = query("select si.productid,
				   si.quantity,
				   unitprice,
				   vat,
				   revenue_accountid as accountid,
				   purchase_price,
				   no,
				   stock
				 from salesorder_item si
				 join product p on p.productid=si.productid
				 join category c on c.categoryid=p.categoryid
				 where orderid=$orderid");
	$customerid = findValue("select customerid from salesorder where orderid=$orderid");
	$narrative = "Invoice order $orderid";
	$cost_of_sales_accountid = findValue("select cost_of_sales from accountconf");
	$finished_goods_accountid = findValue("select finished_goods from accountconf");
	$no = findValue("select no from salesorder where orderid=$orderid");
	$narrative = "Invoice order $no";
	sql("insert into transaction (transtime, narrative, createdby, createdtime)
		 values (now(), '$narrative', '" . getUser() . "', now())");
	$transid = insert_id();
	$locationid = findValue("select locationid from salesorder where orderid=$orderid",1);
	$sum = 0;
	$vat = 0;
	$standardCost = 0;
	$accountMap = array();
	while ($row = fetch($rs)) {
		sql("
		update product set quantity=quantity-$row->quantity
		where productid=$row->productid");
		$amount = $row->quantity * $row->unitprice;
		if (array_key_exists($row->accountid, $accountMap)) {
			$accountMap[$row->accountid] += $amount;
		} else {
			$accountMap[$row->accountid] = $amount;
		}
		$vat += $row->vat/100 * $row->unitprice * $row->quantity;
		$sum += $amount;
		$diff = (-1) * $row->quantity;
		if ($row->stock) {
			$standardCost += $row->quantity * $row->purchase_price;
			sql("insert into stockmove (productid, diff, narrative,
			     transactionid, salesorderid, no, createdby, locationid)
				 values ($row->productid, $diff, '$narrative',
				 $transid, $orderid, $row->no, '" . getUser() . "', $locationid)");
		}
	}
	if ($standardCost != 0) {
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $finished_goods_accountid, (-1) * $standardCost)");
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $cost_of_sales_accountid, $standardCost)");
	}
	foreach (array_keys($accountMap) as $accountid) {
		$amount = $accountMap[$accountid];
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $accountid, (-1) * $amount)");
	}
	$accountid = findValue("select vat_payable from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, (-1) * $vat)");
	$sumvat = $sum + $vat;
	//$accountid = findValue("select account_receivable from accountconf");
	$accountid = $debitaccount;
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, $sumvat)");
	$duedate = addDay(time(), getCustomerCreditLength($customerid));
	$no = findValue("
	select max(no) from salesorder", 0);
	$no++;
	sql("update salesorder
		 set no=$no, invoice_transid=$transid, duedate=from_unixtime($duedate)
		 where orderid=$orderid");
	handleConsignment($orderid, $transid);
	return $transid;
}

function handleConsignment($orderid, $transid)
{
	$count = findValue("
	select count(*)
	from salesorder_item si
	join product p on p.productid=si.productid
	join category c on c.categoryid=p.categoryid
	where orderid=$orderid and consignment=1");
	if ($count == 0)
		return;

	// Modify sales order
	$rs = query("select si.productid,
				   si.quantity,
				   unitprice,
				   vat,
				   revenue_accountid as accountid,
				   no,
				   sp.price as purchase_price
				 from salesorder_item si
				 join product p on p.productid=si.productid
				 join category c on c.categoryid=p.categoryid
				 join supplier_price sp on sp.productid=si.productid
				 where orderid=$orderid and consignment=1");
	$sum = 0;
	while ($row = fetch($rs)) {
		$diff = $row->quantity * ($row->purchase_price);
		$sum += $diff;
		sql("
		update transaction_part
		set amount = amount + $diff
		where transactionid=$transid and accountid=$row->accountid");
	}
	$consignment_payable = findValue("select consignment_payable from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $consignment_payable, (-1) * $sum)");

	// Create purchase order
	$locationid = findValue("
	select locationid from salesorder where orderid=$orderid");
	$user = getUser();

	$rs = query("
	select distinct(supplierid)
	from salesorder_item si
	join product p on p.productid=si.productid
	join category c on c.categoryid=p.categoryid and consignment=1
	join supplier_price sp on sp.productid=p.productid
	where orderid=$orderid");
	while ($row = fetch($rs)) {
		$supplierid = $row->supplierid;
		sql("
		insert into purchaseorder (supplierid, orderdate, createdby, locationid)
		values ($row->supplierid, now(), '$user', $locationid)");
		$purchaseOrderId = insert_id();
		$rs2 = query("
		select
			si.productid,
			si.quantity,
			sp.price as purchase_price,
			vc.percent
		from salesorder_item si
		join product p on p.productid=si.productid
  	 	join category c on c.categoryid=p.categoryid and consignment=1
  	 	join vat_category vc on vc.vatcatid=c.vatcatid
	 	join supplier_price sp 
	 	on sp.productid=si.productid and supplierid=$supplierid
		where orderid=$orderid");
		$no = 1;
		$amount = 0;
		$vatSum = 0;
		while ($row2 = fetch($rs2)) {
			$vat = $row2->purchase_price * $row2->percent/100;
			$vatSum += $vat;
			$amount += $row2->quantity * ($row2->purchase_price);
			sql("
			insert into purchaseorder_item (orderid, no, productid, 
			                                quantity, received_quantity, unitprice, vat)
			values ($purchaseOrderId, $no, $row2->productid, 
			                                $row2->quantity, $row2->quantity, $row2->purchase_price, $vat)");
			$no++;
		}
		$narrative = "Payable purchase order $purchaseOrderId";
		$duedate = addDay(time(), getCreditLength($supplierid));
		sql("insert into payable (description, supplierid, amount, 
		                          vat, transactionid, createdby, duedate)
			 values ('$narrative', $supplierid, $amount, 
			          $vatSum, $transid, '" . getUser() . "', from_unixtime($duedate))");
		$payableid = insert_id();
		sql("update purchaseorder set payableid=$payableid where orderid=$purchaseOrderId");
		$accountid = findValue("select vat_recoverable from accountconf");
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $accountid, $vatSum)");
		sql("
		update transaction_part set amount=amount-$vatSum
		where transactionid=$transid and accountid=$consignment_payable");
	}
}

function finish_cashorder($orderid, $payedGross)
{
	$debitaccount = findValue("select default_cash from accountconf");
	$transid = invoice_salesorder0($orderid, $debitaccount);
	$customerid = findValue("select customerid from salesorder where orderid=$orderid");
	$sum = findValue("select sum(quantity*unitprice*(100+vat)/100)
					  from salesorder_item
					  where orderid=$orderid");
	createReceipt($orderid, $customerid, $transid, $payedGross, $sum);
}

function createReceipt($orderid, $customerid, $transid, $payedGross, $sum)
{
	$narrative = tr("Receipt order") . ' ' . $orderid;
	if (isEmpty($payedGross))
		$payedGross = $sum;
	sql("insert into receipt (customerid, amount, transactionid, createdby)
		 values ($customerid, $payedGross, $transid, '". getUser() . "')");
	$receiptid = insert_id();
	sql("insert into receipt_allocation (receiptid, orderid, amount)
		 values ($receiptid, $orderid, $payedGross)");
	$exchange = $payedGross - $sum;
	if ($exchange > 0) {
		$narrative = tr("Exchange order") . ' ' . $orderid;
		$exchange = (-1) * $exchange;
		sql("insert into receipt (customerid, amount, transactionid, createdby)
			 values ($customerid, $exchange, $transid, '". getUser() . "')");
		$receiptid = insert_id();
		sql("insert into receipt_allocation (receiptid, orderid, amount)
			 values ($receiptid, $orderid, $exchange)");
	}
}

function finish_creditorder($orderid)
{
	$debitaccount = findValue("select default_cash from accountconf");
	invoice_salesorder0($orderid, $debitaccount);
	$customerid = findValue("select customerid from salesorder where orderid=$orderid");
	$sum = findValue("select sum(quantity*unitprice*(100+vat)/100))
					  from salesorder_item
					  where orderid=$orderid");
	$narrative = tr("Receipt order") . $orderid;
	sql("insert into receipt (customerid, amount, transactionid, createdby)
		 values ($customerid, $sum, null, '". getUser() . "')");
	$receiptid = insert_id();
	sql("insert into receipt_allocation (receiptid, orderid, amount)
		 values ($receiptid, $orderid, $sum)");
}

function email_invoice($orderid,
	$to = null,
	$cc = null,
	$from = null,
	$subject = null,
	$body = null)
{
	include('../include/sendmail.class.php');
	$filename = "../tmp/invoice$orderid.pdf";
	createInvoicePDF($orderid, $filename);
	if ($to == null) {
		$to = findValue("
		select email
		from customer c
		join salesorder o on o.customerid=c.customerid
		where orderid=$orderid");
	}
	if ($from == null) {
		$from = findValue("select email from companyinfo");
	}
	if ($cc == null)
		$cc = $from;
	if ($subject == null) {
		$company = findValue("select companyname from companyinfo");
		$subject = "Invoice from $company";
	}
	if ($body == null) {
		$body = "See the attached PDF-file";
	}
    $mail = new sendmail();
    $mail->SetCharSet(CHARSET);
    $mail->from($company, $from);
    $mail->to($to);
    $mail->cc($cc);
    $mail->subject($subject);
    $mail->text($body);
    $mail->attachment($filename);
    $mail->send();
	return tr("E-mail invoice sent to $to.");
}

function pay_salesorder($orderid, $payedGross)
{
	$customerid = findValue("select customerid from salesorder where orderid=$orderid");
	$sum = findValue("select sum(quantity*unitprice*(100+vat)/100)
					  from salesorder_item
					  where orderid=$orderid");
	$payed = findValue("select sum(amount)
	                                  from receipt_allocation
	                                  where orderid=$orderid");
	$sum = $sum - $payed;
	$no = findValue("select no from salesorder where orderid=$orderid");
	$narrative = tr("Receipt order") . $no;
	sql("insert into transaction (transtime, narrative, createdby)
		 values (now(), '$narrative', '" . getUser() . "')");
	$transid = insert_id();
	$accountid = findValue("select account_receivable from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, (-1) * $sum)");
	$accountid = findValue("select default_cash from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, $sum)");
	createReceipt($orderid, $customerid, $transid, $payedGross, $sum);
}

function create_receipt($customerid, $amount, $bankaccountid)
{
	$custname = findValue("select name from customer where customerid=$customerid");
	$narrative = "receipt customer $customerid - $custname";
	sql("insert into transaction (transtime, narrative) values (now(), '$narrative')");
	$transid = insert_id();
	$accountid = findValue("select account_receivable from accountconf");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $accountid, (-1) * $amount)");
	sql("insert into transaction_part (transactionid, accountid, amount)
		 values ($transid, $bankaccountid, $amount)");
	sql("insert into receipt (customerid, amount, transactionid, createdby)
	     values ($customerid, $amount, $transid, '" . getUser() . "')");
	$receiptid = insert_id();
	return $receiptid;
}

function getSalesOrderTotalEx($orderid)
{
	$value = findValue("
	select sum(unitprice*si.quantity)
	from salesorder_item si
    where orderid=$orderid");
    return round($value, 2);
}

function getSalesOrderTotalVat($orderid)
{
	$value = findValue("
	select sum(vat*quantity*unitprice/100)
	from salesorder_item si
    where orderid=$orderid");
    return round($value, 2);
}

function getSalesOrderTotalIncVat($orderid)
{
	$value = findValue("
	select sum(si.quantity*unitprice*(100+vat)/100)
	from salesorder_item si
    where orderid=$orderid");
    return round($value, 2);
}

function getCustomerBalance($customerid, $overdue = false)
{
	$sql = "
	select sum(topay) as topay, sum(payed) as payed
	from (
	select
	  (select sum(si.quantity*si.unitprice*(100+si.vat)/100) from salesorder_item si where si.orderid=so.orderid) as topay,
	  (select sum(a.amount) from receipt_allocation a where a.orderid=so.orderid) as payed
	from salesorder so
	where customerid=$customerid
	and so.invoice_transid is not null and so.cancelled != 1";
	if ($overdue)
		$sql .= " and so.duedate < now()";
	$sql .= ") as balance";
	$row = find($sql);
	return $row->topay - $row->payed;
}

function cancel_order($orderid)
{
	$narrative = tr("Cancel order ") . $orderid;
	$transid = findValue("select invoice_transid from salesorder where orderid=$orderid");
	if ($transid != null)
		cancel_transaction($transid, $narrative);
	$receiptid = findValue("select receiptid from receipt_allocation where orderid=$orderid");
	if ($receiptid != null) {
		$count = findValue("select count(*) from receipt_allocation where receiptid=$receiptid");
		if ($count == 1) {
			$transid = findValue("select transactionid from receipt where receiptid=$receiptid");
			if ($transid != null)
				cancel_transaction($transid);
			sql("update receipt set amount=0 where receiptid=$receiptid");
		}
		sql("update receipt_allocation set amount=0 where receiptid=$receiptid and orderid=$orderid");
	}
	$moves = query("update stockmove set diff=0 where salesorderid=$orderid");
	sql("update salesorder set cancelled=1 where orderid=$orderid");
}

function getCustomerCreditAccount($customerid)
{
	$accountid = findValue("select credit_account from customer where customerid=$customerid");
	if (isEmpty($accountid))
		$accountid = findValue("select account_receivable from accountconf");
	return $accountid;
}

function getCustomerCreditLength($customerid)
{
	$creditLength = findValue("select credit_length from customer where customerid=$customerid");
	if (isEmpty($creditLength))
		$creditLength = findValue("select credit_length from settings");
	return $creditLength;
}

function roundAmount($amount)
{
	$rounding = findValue("select rounding from settings");
	if ($rounding == null)
		return $amount;
	$amount = round($amount * 100 / $rounding);
	$amount = $amount * $rounding / 100;
	return $amount;
}


?>