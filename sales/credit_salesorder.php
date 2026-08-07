<?php
	include('include.php');
	include('salesorder.inc.php');
	include('invoice_pdf.inc.php');

	$orderid = getParam('orderid');
	$new = true;
	$customerid = getParam('customerid');
	$credit_orgid = prepNull(getParam('credit_orgid'));
	$orderdate = time();
	$invoice_transid = null;
	$addable = true;
	$cancelled = false;
	$createdby = null;
	$mess = null;
	if (getParam("action") == "create") {
		$sql = "insert into salesorder (orderdate, customerid, createdby, credit_orgid)
		        values (now(), $customerid, '" . getUser() . "', $credit_orgid)";
		sql($sql);
		$orderid = insert_id();
	}

	if (!isEmpty($orderid)) {
		$customerid = findValue("select customerid from salesorder where orderid=$orderid");
		$credit_orgid = findValue("select credit_orgid from salesorder where orderid=$orderid");
	}

	if (isSave()) {
		$count = getParam('count');
		$i = 0;
		while ($i < $count) {
			$no = getParam("no_$i");
			$quantity = (-1) * getParam("quantity_$i");
			$orgitem = find("select productid, unitprice, vat 
			                  from salesorder_item 
							  where orderid=$credit_orgid
							  and no=$no");
			sql("update salesorder_item set quantity=$quantity
			     where orderid=$orderid and no=$no");
			if (affected_rows() == 0) {
				sql("insert into salesorder_item 
				     (orderid, no, productid, unitprice, vat, quantity)
					 values
					 ($orderid, $no, $orgitem->productid, 
					  $orgitem->unitprice, $orgitem->vat, $quantity)
					");
			}
			$i++;
		}
	}

	if (array_key_exists('invoice', $_POST)) {
		tx("invoice_salesorder", array($orderid));
	}
	if (getParam("action") == "email") {
		$mess = email_invoice($orderid);
	}
	if (array_key_exists('pay', $_POST)) {
		tx("pay_salesorder", array($orderid));
	}

	$items = null;
	$receiptCount = 0;
	$receipt_transid = null;
	if (!isEmpty($orderid)) {
	    $sql =
  		"select so.orderid,
  		       unix_timestamp(orderdate) as orderdate,
		       customerid,
		       invoice_transid,
			   cancelled,
			   so.createdby,
			   credit_orgid
		from salesorder so
		left outer join transaction t on t.transactionid=so.invoice_transid
		where so.orderid=$orderid
		";
		$rec = find($sql);
		if ($rec != null) {
			$orderid = $rec->orderid;
			$customerid = $rec->customerid;
			$orderdate = $rec->orderdate;
			$invoice_transid = $rec->invoice_transid;
			$cancelled = $rec->cancelled;
			$createdby = $rec->createdby;
			$new = false;

			$sql = "
			select
			  osi.productid,
			  model,
			  osi.quantity as org_quantity,
			  si.quantity,
			  osi.unitprice,
			  osi.no,
              u.description as unittype,
			  osi.vat
			from salesorder_item osi
			join product p on p.productid=osi.productid         
			join salesorder so on osi.orderid=so.credit_orgid
			left outer join salesorder_item si on si.orderid=so.orderid and si.no=osi.no
            left outer join unittype u on u.unittype=p.unittype
			where so. orderid=$orderid";
			$items = query($sql);
		}		
		$toPay = getSalesOrderTotalIncVat($orderid);
		$payed = findValue("select sum(amount) from receipt_allocation where orderid=$orderid");
		$receipt_transid = findValue("select transactionid
		                              from receipt_allocation ra
									  join receipt r on r.receiptid=ra.receiptid
									  where orderid=$orderid");

	}

	$customer = null;
	if (!isEmpty($customerid)) {
		$customer = find("select name from customer where customerid=$customerid");
	}

?>

<head>
<title>thERP - <?php etr("Credit sales order") ?></title>
<?php
styleSheet();
include_common();
?>
<script>
function submitForm()
{
	document.postform.submit();
}
</script>
</head>

<body onLoad="onLoad()">
<?php
menubar('index.php');
title("<a href='sales.php'>" . tr("Sales orders") . "</a> > $orderid") ;
?>

<?php
if ($mess != null) {
	echo "<center class=error>$mess</center>";
}
?>

<form name=postform action="credit_salesorder.php" method="POST">
<input type=hidden name=customerid value='<?php echo $customerid ?>'/>
<div class="border p-3 mb-4">
	<div class="row g-3">
		<div class="col-md-4">
			<label class="form-label"><?php echo tr("Order id") ?></label>
			<div class="form-control-plaintext"><?php echo $orderid ?></div>
			<input type='hidden' name='orderid' value='<?php echo $orderid ?>'/>
		</div>
		<div class="col-md-4">
			<label class="form-label"><?php etr("Customer") ?></label>
			<div class="form-control-plaintext"><?php echo $customer->name ?></div>
		</div>
		<div class="col-md-4">
			<label class="form-label"><?php etr("Order date") ?></label>
			<div class="form-control-plaintext"><?php echo date(DATE_PATTERN, $orderdate) ?></div>
		</div>
	</div>
	<?php
	if (!isEmpty($invoice_transid)) {
		echo "<div class='row g-3 mt-3'><div class='col-md-12'><strong>" . tr("Invoice") . ":</strong> ";
		echo "<a href='invoice_pdf.php?orderid=$orderid&type=credit'>" . tr("Print") . "</a>";
		echo "&nbsp;&nbsp;";
		echo "<a href='salesorder.php?orderid=$orderid&action=email'>" . tr("E-mail customer") . "</a>";
		echo "&nbsp;&nbsp;";
		echo "<a href='../accounting/transaction.php?transactionid=$invoice_transid&salesorderid=$orderid'>" . tr("Show transaction") . "</a>";
		echo "</div></div>";
	}
	if ($payed != 0) {
		echo "<div class='row g-3 mt-3'><div class='col-md-12'><strong>" . tr("Receipt") . ":</strong> ";
		if ($payed == $toPay)
			echo tr("Fully paid");
		else
			echo formatMoney($payed) . " / " . formatMoney($toPay);
		$href = "../accounting/transaction.php?";
		$href .= "transactionid=$receipt_transid&salesorderid=$orderid";
		echo "&nbsp;&nbsp;<a href='$href'>" . tr("Show transaction") . "</a>";
		echo "</div></div>";
	}
	if ($cancelled) {
		echo "<div class='row g-3 mt-3'><div class='col-md-12'><span class='text-danger'>" . tr("This order is cancelled") . "</span></div></div>";
	}
	?>
	<div class="row g-3 mt-3">
		<div class="col-md-6">
			<label class="form-label"><?php etr("Created by") ?></label>
			<div class="form-control-plaintext"><?php echo $createdby ?></div>
		</div>
		<div class="col-md-6">
			<label class="form-label"><?php echo tr("Credit order") ?></label>
			<div class="form-control-plaintext"><?php echo tr("This is a credit order for") ?> <a href='salesorder.php?orderid=<?php echo $credit_orgid ?>'><?php echo tr("order") ?> <?php echo $credit_orgid ?></a></div>
		</div>
	</div>
</div>
<br/>
<br/>
<?php if ($items != null) { ?>
<div class='border'>
<div class='table-responsive'>
<table class='table table-sm table-striped table-hover align-middle w-100'>
<thead>
<tr>
<th><?php etr("Product") ?></th>
<th class='text-end'><?php etr("Original quantity") ?></th>
<th class='text-end'><?php etr("Diff") ?></th>
<th class='text-end'><?php etr("Unit price") ?></th>
<th class='text-end'><?php etr("Amount") ?></th>
<?php
    echo "<th class='text-end'>" . tr("VAT") . "</th>";
    if (isEmpty($invoice_transid))
        echo "<th class='text-center'>" . tr("Save") . "</th>";
?>
</tr>
</thead>
<tbody>
<?php
    $class = 'odd';
    $i = 0;
    $sum = 0;
    $vatSum = 0;
    while ($row = fetch($items)) {
        echo "<input type=hidden name=no_$i value='$row->no'/>";
        echo "<tr class='$class'>";
        echo "<td><a href='product.php?productid=$row->productid'>";
        echo "$row->productid - $row->model</a></td>";
        echo "<td class='text-end'>";
        echo $row->org_quantity . ' ' . $row->unittype;
        echo "</td>";
        echo "<td class='text-end'>";
        $quantity = (-1) * $row->quantity;
        if (isEmpty($invoice_transid))
            echo numberbox("quantity_$i", $quantity);
        else
            echo $quantity;
        echo "</td>";
        echo "<td class='text-end'>";
        $unitprice = $row->unitprice;
        echo formatMoney($unitprice);
        echo "</td>";
        $amount = $quantity * $unitprice;
        echo "<td class='text-end'>" . formatMoney($amount) . "</td>";
        echo "<td class='text-end'>" . formatMoney($row->vat) . "</td>";
        echo "<td class='text-center'>";
        if (isEmpty($invoice_transid))
            echo "<input type='image' name='save' value='Save' src='../images/disk.gif'>";
        echo "</td>";
        echo "</tr>
";
        $sum += $amount;
        $vatSum += $row->vat * $quantity;
        $class = ($class == "odd" ? "even" : "odd");
        $i++;
    }
?>
<input type=hidden name=count value='<?php echo $i ?>'/>
<tr>
<td/>
<td/>
<td/>
<?php
    echo "<td class='text-end'>" . tr("VAT") . ":</td>";
?>
<td class='text-end'><?php echo formatMoney($vatSum) ?></td>
</tr>
<tr>
<?php $colspan = $addable ? 3 : 2 ?>
<td colspan='<?php echo $colspan ?>'/>
<td class='text-end label'><?php etr("To refund") ?>:</td>
<td class='text-end'><?php echo formatMoney((-1) * $toPay) ?></td>
</tr>
</tbody>
</table>
</div>
</div>
<br/>
<?php } ?>

<?php
if (isEmpty($invoice_transid) && !$cancelled) {
	button('doInvoice', 'invoice');
	echo "&nbsp;";
} else if ($payed < $toPay && !$cancelled) {
	button('Receipt', 'pay');
	echo "&nbsp;";
}

if (!$cancelled) {
	button("Cancel order", 'cancel');
	echo "&nbsp;";
}
button("Show stock moves", "moves", "stockmoves.php?salesorderid=$orderid");
?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>
</body>
