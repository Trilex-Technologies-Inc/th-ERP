<?php
	include('include.php');
	include('payable.inc.php');

	$paymentid = getParam('paymentid');
	$new = true;
	$supplierid = getParam('supplierid');
	$amount = getParam('amount');
	$paymentdate = time();
	$transid = null;
	$bankaccount = null;
	if (isSave()) {
		if (isNew()) {
			$bankaccount = getParam('bankaccount');
			$paymentid = tx("create_payment", array($supplierid, $amount, $bankaccount));
		} else {
			$count = getParam('count');
			$i = 0;
			while ($i < $count) {
				$payableid = getParam("payableid_$i");
				$allocation = getParam("allocation_$i");
				$old_allocation = getParam("old_allocation_$i");
				if ($allocation != $old_allocation) {
					sql("update payment_allocation set amount=$allocation
					     where payableid=$payableid and paymentid=$paymentid");
					if (affected_rows() == 0) {
						sql("insert into payment_allocation (payableid, paymentid, amount)
						     values ($payableid, $paymentid, $allocation)");
					}
				}
				if (getParam("allocate_all_$i") == 1) {
					sql("delete from payment_allocation where paymentid=$paymentid and payableid=$payableid");
					sql("insert into payment_allocation (paymentid, payableid, amount)
					     select $paymentid, $payableid, amount
						 from payment where paymentid=$paymentid");
				}
				$i++;
			}
		}
	}

	if (!isEmpty($paymentid)) {
	    $sql =
  		"select paymentid,
  		       unix_timestamp(transtime) as paymentdate,
		       supplierid,
		       p.transactionid,
			   p.amount,
			   b.number as bankaccount
		from payment p
		join transaction t on t.transactionid=p.transactionid
		join transaction_part tp on tp.transactionid=t.transactionid
		join bankaccount b on b.glaccountid=tp.accountid
		where paymentid=$paymentid
		";
		$rec = find($sql);
		if ($rec != null) {
			$paymentid = $rec->paymentid;
			$supplierid = $rec->supplierid;
			$paymentdate = $rec->paymentdate;
			$transid = $rec->transactionid;
			$amount = $rec->amount;
			$bankaccount = $rec->bankaccount;
			$new = false;
		}
	}

	$supplier = null;
	$balance = 0;
	if (!isEmpty($supplierid)) {
		$supplier = find("select name from supplier where supplierid=$supplierid");
		$balance = getSupplierBalance($supplierid);
	 }

	$orders = null;
	$leftToAllocate = null;
	if (!$new) {
		$orders = query("
		select pay.payableid,
		sum(pay.amount) as total,
		sum(pa.amount) as allocated,
		pa2.amount allocation
		from payable pay
		left outer join payment_allocation pa on pa.payableid=pay.payableid and pa.paymentid!=$paymentid
		left outer join payment_allocation pa2 on pa2.payableid=pay.payableid and pa2.paymentid=$paymentid
		where pay.supplierid=$supplierid
		group by payableid
		having allocated != total or allocated is null
		");
		$allocated = findValue("select sum(amount) from payment_allocation
		                        where paymentid=$paymentid");
		$leftToAllocate = $amount - $allocated;
	}

	$bankaccounts = rs2array(query("select number, name from bankaccount"));

?>

<head>
<title>thERP - <?php etr("Payment") ?></title>
<?php styleSheet() ?>
</head>

<body>
<?php include("menubar.php") ?>
<?php
$title = $paymentid;
if ($new) $title = tr("New");
title("<a href='payments.php'>" . tr("Payments") . "</a> > $title")
?>

<form action="payment.php" method="POST">
<input type=hidden name=supplierid value='<?php echo $supplierid ?>'/>
<div class="container-fluid px-0 erp-form-layout">
<?php
	if (!$new) {
		echo "<div class='row g-3 align-items-center mb-2'><div class='col-12 col-md-auto'><b>" . tr("Payment id") . ":</b></div>";
		echo "<div class='col-12 col-md-auto'>";
		echo $paymentid;
		echo "<input type='hidden' name='paymentid' value='$paymentid'/>";
		echo "</div>";
	}
?>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><b><?php etr("Supplier") ?>:</b></div><div class="col-12 col-md-auto"><?php echo $supplier->name ?></div>
</div><div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><b><?php etr("Date") ?>:</b></div><div class="col-12 col-md-auto"><?php echo date(DATE_PATTERN, $paymentdate) ?></div></div>
<div class="row g-3 align-items-center mb-2"><div class="col-12 col-md-auto"><b><?php etr("Balance") ?>:</b></div><div class="col-12 col-md-auto"><?php echo formatMoney($balance) ?></div></div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><?php etr("Bank account") ?>:</div>
	<div class="col-12 col-md-auto"><?php comboBox('bankaccount', $bankaccounts, $bankaccount, false, !$new) ?></div>

</div>
<div class="row g-3 align-items-center mb-2">
	<div class="col-12 col-md-auto"><b><?php etr("Amount") ?>:</b></div>
	<div class="col-12 col-md-auto">
	<?php
	if ($new)
		echo "<input type=text name=amount value='$amount'/>";
	else
		echo $amount;
	?>
	</div>
</div>
<?php
if (!$new) {
	echo "<div class='row g-3 align-items-center mb-2'>";
	echo "<div class='col-12 col-md-auto'><a href='transaction.php?transactionid=$transid'>General ledger transaction</a></div>";
	echo "</div>";
}

?>
</div>
<br/>
<?php
if ($orders != null) {
	echo "<p>Left to allocate: $leftToAllocate</p>";
	echo "<div class='card border-0 shadow-sm overflow-hidden'><div class='table-responsive'><table class='table table-hover align-middle mb-0'>";
	echo "<th>" . tr("Payable") . "</th>";
	echo "<th>" . tr("Amount") . "</th>";
	echo "<th>" . tr("Previous allocations") . "</th>";
	echo "<th>" . tr("Allocated all") . "</th>";
	echo "<th>" . tr("Allocation") . "</th>";
	$class = 'odd';
	$i = 0;
	while ($row = fetch($orders)) {
		echo "<input type=hidden name=payableid_$i value='$row->payableid'/>";
		echo "<tr class='$class'>";
		echo "<td><a href='payable.php?payableid=$row->payableid'>$row->payableid</a></td>";
		echo "<td align=right>$row->total</td>";
		echo "<td align=right>$row->allocated</td>";
		echo "<td align=center><input type=checkbox name='allocate_all_$i' value='1'/></td>";
		echo "<td><input type=text name=allocation_$i value='$row->allocation'/></td>";
		echo "<input type=hidden name=old_allocation_$i value='$row->allocation'/>";
		echo "</tr>";
        $class = ($class == "odd" ? "even" : "odd");
		$i++;
	}
	echo "</table></div></div>";
	echo "<input type=hidden name=count value='$i' />";
}
?>
<br/>
<?php button("Save", "save") ?>

<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>

</body>
