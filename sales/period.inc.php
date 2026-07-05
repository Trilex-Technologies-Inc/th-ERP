<?php
function createReceivables($cycleid, $periodid)
{
	$rs = query("select orderid from recur_salesorder where active=1");
	$user = getUser();
	while ($row = fetch($rs)) {
		sql("insert into salesorder (orderdate, customerid, createdby)
		     select now(), customerid, '$user'
		     from salesorder where orderid=$row->orderid");
		$newOrderid = insert_id();
		sql("insert into salesorder_item (orderid, productid, quantity, unitprice, no, comment, vat)
		     select $newOrderid, productid, quantity, unitprice, no, comment, vat
		     from salesorder_item
		     where orderid=$row->orderid");
		invoice_salesorder($newOrderid);
	}
	sql("update period set state_receivables=" . STATE_RECEIVABLES_CREATED . "
	     where cycleid=$cycleid and periodid=$periodid");
}

function sendReceivables($cycleid, $periodid)
{
	sql("update period set state_receivables=" . STATE_RECEIVABLES_SENT . "
	     where cycleid=$cycleid and periodid=$periodid");
}

function createTimeDebitInvoices()
{
	$today = time();
	$rs = query("
	select
		p.customerid,
		t.employeeid,
		ta.productid,
		t.description,
		t.taskid,
		t.projectid,
		sp.price,
		vc.percent,
		sum(t.minutes) as minutes
	from timedebit t
	join project p on p.projectid=t.projectid 
	join customer cu on cu.customerid=p.customerid
	join task ta on ta.projectid=t.projectid and ta.taskid=t.taskid
	join product pr on pr.productid=ta.productid
	join category c on c.categoryid=pr.categoryid
	join vat_category vc on vc.vatcatid=c.vatcatid
	join sales_price sp on sp.productid=ta.productid and sp.listid=cu.pricelistid
	where t.orderid is null and p.customerid is not null
	group by customerid, employeeid, productid 
	");
	$lastCustomerid = null;
	while ($row = fetch($rs)) {	
		if ($row->customerid != $lastCustomerid) {
			$duedate = addDay($today, getCustomerCreditLength($row->customerid));
			$user = getUser();
			sql("
			insert into salesorder (orderdate, customerid, duedate, createdby)
			values (from_unixtime($today), $row->customerid, from_unixtime($duedate), '$user')");
			$orderid = insert_id();
			$no = 1;	
		}
		$hours = $row->minutes / 60;		
		$vat = $row->percent;
		sql("
		insert into salesorder_item (orderid, no, productid, quantity, unitprice, vat)
		values ($orderid, $no, $row->productid, $hours, $row->price, $vat)");
		$no++;
		$lastCustomerid = $row->customerid;
		sql("
		update timedebit set orderid=$orderid
		where orderid is null and projectid=$row->projectid and taskid=$row->taskid and employeeid=$row->employeeid");
	}
}
?>
