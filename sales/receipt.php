<?php
	include('include.php');
	include('salesorder.inc.php');

	$receiptid = getParam('receiptid');
	$new = true;
	$customerid = getParam('customerid');
	$amount = getParam('amount');
	$receiptdate = time();
	$transid = null;
	$accountid = getParam('accountid');
	if (isSave()) {
		if (isNew()) {
			$receiptid = tx("create_receipt", array($customerid, $amount, $accountid));
		} else {
			$count = getParam('count');
			$i = 0;
			while ($i < $count) {
				$orderid = getParam("orderid_$i");
				$allocation = getParam("allocation_$i");
				$old_allocation = getParam("old_allocation_$i");
				if ($allocation != $old_allocation) {
					sql("update receipt_allocation set amount=$allocation
					     where orderid=$orderid and receiptid=$receiptid");
					if (affected_rows() == 0) {
						sql("insert into receipt_allocation (orderid, receiptid, amount) 
						     values ($orderid, $receiptid, $allocation)");
					}
				}
				if (getParam("allocate_all_$i") == 1) {
					sql("delete from receipt_allocation where receiptid=$receiptid and orderid=$orderid");
					sql("insert into receipt_allocation (receiptid, orderid, amount) 
					     select $receiptid, $orderid, amount
						 from receipt where receiptid=$receiptid");
				}
				$i++;
			}
		}
	}

	if (!isEmpty($receiptid)) {
	    $sql =
  		"select receiptid,
  		       unix_timestamp(transtime) as receiptdate,
		       customerid,
		       p.transactionid,
			   p.amount,
			   accountid as bankaccount
		from receipt p
		join transaction t on t.transactionid=p.transactionid
		join transaction_part tp on tp.transactionid=t.transactionid 
		where receiptid=$receiptid
		";
		$rec = find($sql);
		if ($rec != null) {
			$receiptid = $rec->receiptid;
			$customerid = $rec->customerid;
			$receiptdate = $rec->receiptdate;
			$transid = $rec->transactionid;
			$amount = $rec->amount;
			$bankaccount = $rec->bankaccount;
			$new = false;
		}
	}

	$customer = null;
	if (!isEmpty($customerid)) {
		$customer = find("select name from customer where customerid=$customerid");
	 }
	
	$orders = null;
	$leftToAllocate = null;
	if (!$new) {
		$orders = query("
		select so.orderid,
		sum(si.quantity*unitprice+vat) as total,
		sum(pa.amount) as allocated,
		pa2.amount allocation
		from salesorder so
		join salesorder_item si on si.orderid=so.orderid
		join product p on p.productid=si.productid
		left outer join receipt_allocation pa on pa.orderid=so.orderid and pa.receiptid!=$receiptid
		left outer join receipt_allocation pa2 on pa2.orderid=so.orderid and pa2.receiptid=$receiptid
		where so.customerid=$customerid
		and so.cancelled != 1
		group by orderid
		having allocated != total or allocated is null
		");
		$allocated = findValue("select sum(amount) from receipt_allocation
		                        where receiptid=$receiptid");
		$leftToAllocate = $amount - $allocated;
		
	}

	$bankaccounts = rs2array(query("
	select a.accountid, name
	from account a 
	join account_group ag on ag.dimid=a.dimid and ag.accountid=a.accountid
	where a.dimid=1 and ag.groupid = " . GROUPID_BANK_ACCOUNTS));
?>

<head>
<title>thERP - <?php etr("Receipt") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php menubar("index.php") ?>
<?php 
$title = $receiptid;
if ($new) $title = tr("New");
title("<a href='receipts.php'>" . tr("Receipts") . "</a> > $title") 
?>

<form action="receipt.php" method="POST">
<input type=hidden name=customerid value='<?php echo $customerid ?>'/>
<table>
<?php
	if (!$new) {
		echo "<tr><td class=label>" . tr("Receipt id") . ":</td>";
		echo "<td>";
		echo $receiptid;
		echo "<input type='hidden' name='receiptid' value='$receiptid'/>";
		echo "</td>";
	}
?>
<tr><td class=label><?php etr("Customer") ?>:</td><td><?php echo $customer->name ?></td>
<tr><td class=label><?php etr("Date") ?>:</td><td><?php echo date(DATE_PATTERN, $receiptdate) ?></td></tr>
<tr>
	<td class=label><?php etr("Amount") ?>:</td>
	<td>
	<?php 
	if ($new)
		echo "<input type=text name=amount value='$amount'/>";
	else	
		echo $amount;
	?>
	</td>
</tr>
<tr>
	<td class=label><?php etr("Bank account") ?>:</td>
	<td><?php comboBox('accountid', $bankaccounts, $accountid, false, !$new) ?></td>
	</td>
</tr>
	
</tr>
<?php
if (!$new) {
	echo "<tr>";
	echo "<td colspan=2><a href='../accounting/transaction.php?transactionid=$transid'>General ledger transaction</a></td>";
	echo "</tr>";
}

?>
</table>
<br/>
<?php 
if ($orders != null) { 
	echo "<p>Left to allocate: $leftToAllocate</p>";
	echo "<table>";
	echo "<th>" . tr("Sales order") . "</th>";
	echo "<th>" . tr("Amount") . "</th>";
	echo "<th>" . tr("Previous allocations") . "</th>";
	echo "<th>" . tr("Allocated all") . "</th>";
	echo "<th>" . tr("Allocation") . "</th>";
	$class = 'odd';
	$i = 0;
	while ($row = fetch($orders)) {
		echo "<input type=hidden name=orderid_$i value='$row->orderid'/>";
		echo "<tr class='$class'>";
		echo "<td><a href='salesorder.php?orderid=$row->orderid'>$row->orderid</a></td>";
		echo "<td align=right>$row->total</td>";
		echo "<td align=right>$row->allocated</td>";
		echo "<td align=center><input type=checkbox name='allocate_all_$i' value='1'/></td>";
		echo "<td><input type=text name=allocation_$i value='$row->allocation'/></td>";
		echo "<input type=hidden name=old_allocation_$i value='$row->allocation'/>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
		$i++;
	}
	echo "</table>";
	echo "<input type=hidden name=count value='$i' />";
}
?>
<br/>
<?php button("Save", "save") ?>

<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
