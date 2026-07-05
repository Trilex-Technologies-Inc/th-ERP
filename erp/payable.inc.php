<?php

function create_payable($supplierid, $description, $amount, $vat, $expense_accountid)
{
	try {
		begin();
		$suppliername = findValue("select name from supplier where supplierid=$supplierid");
		$narrative = tr("Invoice from supplier ") . "$supplierid - $suppliername";
		sql("insert into transaction (transtime, narrative, createdtime) values (now(), '$narrative', now())");
		$transid = insert_id();
		$accountid = findValue("select account_payable from accountconf");
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $accountid, $amount)");
		$amountExVat = (-1) * ($amount - $vat);
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $expense_accountid, $amountExVat)");
		$accountid = findValue("select vat_recoverable from accountconf");
		$vatMinus = (-1) * $vat;
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $accountid, $vatMinus)");		
		sql("insert into payable (description, supplierid, amount, vat, transactionid) 
		     values ('$description', $supplierid, $amount, $vat, $transid)");
		$receiptid = insert_id();
		 commit();
		 return $receiptid;
	} catch (Exception $e) {		
		rollback();
		throw $e;
	}
}

function getSupplierBalance($supplierid)
{
	$payed = findValue("
	select
		sum(pay.amount) paid
	from payment pay 
	join transaction t on t.transactionid=pay.transactionid
	where pay.supplierid=$supplierid and
	cancel_transid is null
	");
	$bought = findValue("
	select
		sum(amount) as bought
	from payable p
	where p.supplierid=$supplierid
	");
	return $payed - $bought;
}

function create_payment($supplierid, $amount, $bankaccount)
{
	try {
		begin();
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
		 commit();
		 return $receiptid;
	} catch (Exception $e) {
		rollback();
	}
}

function pay_payable($payableid)
{
	try {
		begin();
		$supplierid = findValue("select supplierid from payable where payableid=$payableid");
		$sum = findValue("select amount 
		                  from payable
					      where payableid=$payableid");
		$narrative = tr("Payment payable ") . $payableid;
		sql("insert into transaction (transtime, narrative, createdtime) values (now(), '$narrative', now())");
		$transid = insert_id();
		$accountid = findValue("select account_payable from accountconf");
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, $accountid, (-1) * $sum)");
		sql("insert into transaction_part (transactionid, accountid, amount)
			 values ($transid, " . ACCOUNTID_CASH . ", $sum)");
		sql("insert into payment (supplierid, amount, transactionid) 
		     values ($supplierid, $sum, $transid)");
		$paymentid = insert_id();
		sql("insert into payment_allocation (paymentid, payableid, amount)
		     values ($paymentid, $payableid, $sum)");
		commit();
	} catch (Exception $e) {
		rollback();
		throw $e;
	}
}

function cancel_payable($payableid)
{
	try {
		$narrative = tr("Cancel payable ") . $payableid;
		$transid = findValue("select transactionid from payable where payableid=$payableid");
		$transid = cancel_transaction($transid, $narrative);
		commit();
	} catch (Exception $e) {
		rollback();
		throw $e;
	}	
}

?>