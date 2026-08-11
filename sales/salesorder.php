<?php
	include('include.php');
	include('salesorder.inc.php');
	include('invoice_pdf.inc.php');

	checkPermission(PERMISSIONID_SELL);

	$orderid = getParam('orderid');
	
	$method = getParam('method', METHOD_CASH);
	$new = true;
	$customerid = getParam('customerid');
	$recur = getParam('recur');
	$orderdate = time();
	$invoice_transid = null;
	$addable = true;
	$cancelled = false;
	$createdby = null;
	$mess = null;
	if (getParam("action") == "create") {
		$locationid = findValue("select locationid from user where username='" . getUser() . "'", 1);
		$sql = "insert into salesorder (orderdate, customerid, createdby, locationid)
		        values (now(), $customerid, '" . getUser() . "', $locationid)";
		sql($sql);
		$orderid = insert_id();
		if ($recur)
			sql("insert into recur_salesorder (orderid, active) values ($orderid, 1)");
	}

	$incVAT = false;
	$useVAT = true;
	if (!isEmpty($orderid)) {
		$listid = findValue("
		select pricelistid
		from customer c
		join salesorder so on so.customerid=c.customerid
		where so.orderid=$orderid");
		$customerid = findValue("select customerid from salesorder where orderid=$orderid");
		$incVAT = findValue("select vat_included from pricelist where listid=$listid");
		$useVAT = findValue("
		select use_vat
		from customer c
		join salesorder so on so.customerid=c.customerid
		where orderid=$orderid");
	}

	if (isSave()) {
		$recur = getParam('recur', false);
		if ($recur) {
			$recur_active = getParam('recur_active', 0);
			sql("update recur_salesorder set active=$recur_active where orderid=$orderid");
		}
		$comment = getParam('comment');
		$orderedby = getParam('orderedby');
		$locationid = getParam("locationid");
		sql("
		update salesorder
		set comment='$comment',
			locationid=$locationid,
			orderedby='$orderedby'
		where orderid=$orderid");
		sql("update user set locationid=$locationid where username='" . getUser() . "'");
		$count = getParam('count');
		$i = 0;
		while ($i < $count) {
			$no = getParam("no_$i");
			$unitprice = getParam("unitprice_$i");
			$percent = findValue("select percent
								  from vat_category vc
								  join category c on c.vatcatid=vc.vatcatid
								  join product p on p.categoryid=c.categoryid
								  join salesorder_item soi on soi.productid=p.productid
								  where orderid=$orderid and no=$no");
			$description = getParam("description_$i");
			if ($useVAT) {
				if ($incVAT) {
					$unitpriceInc = $unitprice;
					$unitprice = $unitprice / (1 + $percent/100);
					$vat = $unitpriceInc - $unitprice;
				} else
					$vat = $unitprice * $percent/100;
			} else
				$vat = 0;
			$quantity = getParam("quantity_$i");
			$comment = getParam("comment_$i");
			sql("
			update salesorder_item set 
				quantity=$quantity, 
				unitprice=$unitprice, 
				vat=$percent,
				comment='$comment'
			where orderid=$orderid and no=$no");
			$i++;
		}
	}

	$productid_new = getParam('productid_new');
	if (array_key_exists('add', $_POST) || !isEmpty($productid_new)) {
		if (!isEmpty($productid_new)) {
			$quantity = getParam('quantity_new');
			$unitprice = getParam('unitprice_new');
			$comment = getParam('comment_new');
			$mess = add_orderitem($orderid, $productid_new, $quantity, $unitprice, $comment);
			$mess = getError($mess);
		}
	}
	if (array_key_exists('cancel', $_POST)) {
		tx("cancel_order", array($orderid));
	}

	$del_no = getParam("del_no");
	if (!isEmpty($del_no)) {
		sql("delete from salesorder_item where orderid=$orderid and no=$del_no");
		$productid = null;
	}
	if (array_key_exists('invoice', $_POST)) {
		tx("invoice_salesorder", array($orderid));
	}
	if (getParam("action") == "email") {
		$mess = email_invoice($orderid);
	}
	if (array_key_exists('pay', $_POST)) {
		$payedGross = getParam("payedGross");
		tx("pay_salesorder", array($orderid, $payedGross));
	}
	if (array_key_exists('finish', $_POST)) {
		$payedGross = getParam("payedGross");
		tx("finish_cashorder", array($orderid, $payedGross));
	}

	$items = null;
	$receiptCount = 0;
	$receipt_transid = null;
	$recur = null;
	$credited = false;
	$toPay = 0;
	$comment = '';
	$locationid = null;
	if (!isEmpty($orderid)) {
	    $sql =
  		"select 
  			so.orderid,
  			so.no,
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
			orderedby
		from salesorder so
		left outer join transaction t on t.transactionid=so.invoice_transid
		left outer join recur_salesorder rso on rso.orderid=so.orderid
		where so.orderid=$orderid
		";
		$rec = find($sql);
		if ($rec != null) {
			if ($rec->credit_orgid != null) {
					header("Location: credit_salesorder.php?orderid=$orderid");
					die;
			}

			$orderid = $rec->orderid;
			$customerid = $rec->customerid;
			$orderdate = $rec->orderdate;
			$invoice_transid = $rec->invoice_transid;
			if ($invoice_transid != null)
				$addable = false;
			$cancelled = $rec->cancelled;
			if ($cancelled)
				$addable = false;
			$createdby = $rec->createdby;
			$recur = $rec->recur_orderid != null;
			$recur_active = $rec->active;
			$comment = $rec->comment;
			$locationid= $rec->locationid;
			$salesorderno = $rec->no;
			$new = false;

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
              purchase_price,
              comment
			from salesorder_item si
			join product p on p.productid=si.productid
			join category c on c.categoryid=p.categoryid
			join vat_category vc on vc.vatcatid=c.vatcatid
            left outer join unittype u on u.unittype=p.unittype
			where orderid=$orderid
			and si.productid != " . PRODUCTID_ROUNDING;
			$items = query($sql);
		}
		$toPay = getSalesOrderTotalIncVat($orderid);
		$rounding = findValue("select unitprice from salesorder_item
		                       where orderid=$orderid and productid=" . PRODUCTID_ROUNDING);
		$payed = findValue("select sum(amount) from receipt_allocation where orderid=$orderid");
		$payed = round($payed, 2);
		$fullyPayed = (round($payed, 2) >= round($toPay, 2) && $toPay != 0);
		$payedGross = findValue("select sum(amount) from receipt_allocation
		                                           where orderid=$orderid and amount > 0");
		$receiptCount = findValue("select count(*) from receipt_allocation
		                           where orderid=$orderid");
		if ($receiptCount == 1) {
			$receipt_transid = findValue("select transactionid
			                              from receipt_allocation ra
										  join receipt r on r.receiptid=ra.receiptid
										  where orderid=$orderid");
		}

		$credited = findValue("select count(orderid)
		                       from salesorder
							   where credit_orgid=$orderid", 0) > 0;
	}

	$productid = getParam('productid');
	$unitprice_new = '';
	$purchaseprice_new = '';
	if (!isEmpty($productid)) {
		$unitprice_new = findValue("
		select price
		from sales_price
		where productid='$productid' and listid=$listid");
		$purchaseprice_new = findValue("
		select purchase_price
		from product
		where productid=$productid");
	}

	$customer = null;
	if (!isEmpty($customerid)) {
		$customer = find("select name from customer where customerid=$customerid");
	}

	$locations = rs2array(query("select locationid, name from location"));
	$methods = array();
	$methods[] = array(METHOD_CASH, tr("Cash"));
	$methods[] = array(METHOD_CARD, tr("Card"));
?>

<head>
<title>thERP - <?php etr("Sales order") ?></title>
<?php
styleSheet();
include_common();
?>
<script>
function onLoad()
{
	<?php
	if (getParam("method_changed") && $method == METHOD_CARD) 
		echo "document.postform.creditcardno.focus();";
	else
		echo "document.postform.productid_new.focus();";
	?>
}

function submitForm()
{
	document.postform.submit();
}

function methodChanged()
{
	document.postform.method_changed.value = 1;
	submitForm();
}
</script>
</head>

<body onLoad="onLoad()">
<?php
menubar('index.php', 'salesorder_help.php');
$title = $new ? tr("Register") : $salesorderno;
title("<a href='sales.php'>" . tr("Sales orders") . "</a> > $title") ;
?>

<?php
if ($mess != null) {
	echo "<center class=error>$mess</center>";
}
if (array_key_exists('finish', $_POST)) {
	$printUrl = "invoice_pdf.php?orderid=" . urlencode($orderid) . "&type=receipt";
	echo "<div class='alert alert-success d-flex align-items-center justify-content-between gap-3' role='status'>";
	echo "<span>" . tr("The sale is complete. The receipt is ready to print.") . "</span>";
	echo "<a class='btn btn-primary btn-sm text-nowrap' href='$printUrl' onclick='return thERPPrintDocument(this.href)'>" . tr("Print receipt") . "</a>";
	echo "</div>";
}
?>

<form name=postform action="salesorder.php" method="POST">
<input type=hidden name=customerid value='<?php echo $customerid ?>'/>
<div class="border p-3 mb-4">
	<div class="row g-3 align-items-end">
		<?php if (!$new) { ?>
		<div class="col-md-3">
			<label class="form-label"><?php echo tr("Order no") ?></label>
			<div class="form-control-plaintext"><?php echo $rec->no != null ? $rec->no : $orderid ?></div>
			<input type='hidden' name='orderid' value='<?php echo $orderid ?>'/>
		</div>
		<?php } ?>
		<div class="col-md-3">
			<label class="form-label"><?php etr("Customer") ?></label>
			<div class="form-control-plaintext"><?php echo $customer->name ?></div>
		</div>
		<?php if ($customerid != CUSTOMERID_CASH) { ?>
		<div class="col-md-4">
			<label class="form-label"><?php echo tr("Ordered by") ?></label>
			<?php if (isEmpty($invoice_transid)) { textbox('orderedby', $rec->orderedby); } else { echo "<div class='form-control-plaintext'>" . $rec->orderedby . "</div>"; } ?>
		</div>
		<div class="col-auto align-self-end">
			<?php if (isEmpty($invoice_transid)) { ?><input type='image' name='save' value='Save' src='../images/disk.gif'><?php } ?>
		</div>
		<?php } ?>
	</div>
	<div class="row g-3 align-items-end mt-3">
		<div class="col-md-3">
			<label class="form-label"><?php etr("Order date") ?></label>
			<div class="form-control-plaintext"><?php echo date(DATE_PATTERN, $orderdate) ?></div>
		</div>
		<div class="col-md-4">
			<label class="form-label"><?php etr("Location") ?></label>
			<?php
			if (isEmpty($invoice_transid))
				combobox('locationid', $locations, $locationid, false, 'saveForm()');
			else {
				$location = findValue("select name from location where locationid=$locationid");
				echo $location;
			}
			?>
		</div>
	</div>
	<?php
	if ($recur) {
		hidden('recur', 1);
		?>
		<div class="row g-3 align-items-center mt-3">
			<div class="col-auto"><label class="form-label"><?php echo tr("Recur active") ?></label></div>
			<div class="col-auto"><?php checkBox('recur_active', $recur_active); ?></div>
		</div>
		<?php
	} else {
		if ($customerid != CUSTOMERID_CASH) {
			if (!isEmpty($invoice_transid)) {
				?>
				<div class="row g-3 mt-3">
					<div class="col-md-12">
						<a href='invoice_pdf.php?orderid=<?php echo $orderid ?>' onclick="return thERPPrintDocument(this.href)"><?php echo tr("Print") ?></a>
						&nbsp;&nbsp;
						<a href='email_invoice.php?orderid=<?php echo $orderid ?>'><?php echo tr("E-mail customer") ?></a>
						&nbsp;&nbsp;
						<a href='../accounting/transaction.php?transactionid=<?php echo $invoice_transid ?>&salesorderid=<?php echo $orderid ?>'><?php echo tr("Show transaction") ?></a>
					</div>
				</div>
				<?php
			}
			if (!$new) {
				?>
				<div class="row g-3 mt-3">
					<div class="col-md-12">
						<strong><?php echo tr("Receipt") ?></strong>: <?php if ($fullyPayed) { etr("Fully paid"); } else { echo formatMoney($payed) . " / " . formatMoney($toPay); } ?>
						&nbsp;&nbsp;
						<?php if ($payed != 0) {
							if ($receiptCount > 1) { ?>
								<a href='salesorder_receipts.php?orderid=<?php echo $orderid ?>'><?php echo tr("Show receipts") ?></a>
							<?php } else {
								$href = "../accounting/transaction.php?transactionid=$receipt_transid&salesorderid=$orderid"; ?>
								<a href='<?php echo $href ?>'><?php echo tr("Show transaction") ?></a>
							<?php }
						} ?>
					</div>
				</div>
				<?php
			}
		}
		else {
			?>
			<div class="row g-3 mt-3">
				<div class="col-md-12">
					<strong><?php echo tr("Receipt") ?></strong>: <?php if ($fullyPayed) { etr("Fully paid"); } else { etr("Not paid"); } ?>
					&nbsp;&nbsp;
					<?php if ($payed != 0) { ?>
						<a href='invoice_pdf.php?orderid=<?php echo $orderid ?>&type=receipt' onclick="return thERPPrintDocument(this.href)"><?php echo tr("Print") ?></a>
						&nbsp;&nbsp;
						<a href='../accounting/transaction.php?transactionid=<?php echo $receipt_transid ?>&salesorderid=<?php echo $orderid ?>'><?php echo tr("Show transaction") ?></a>
					<?php } ?>
				</div>
			</div>
			<?php
		}
		if ($credited) {
			?>
			<div class="row g-3 mt-3">
				<div class="col-md-12">
					<?php echo tr("This order is credited") ?> &nbsp;&nbsp;
					<a href='sales.php?credit_orgid=<?php echo $orderid ?>'><?php echo tr("Show credit orders") ?></a>
				</div>
			</div>
			<?php
		}
		if ($cancelled) {
			?>
			<div class="row g-3 mt-3">
				<div class="col-md-12 text-danger"><?php echo tr("This order is cancelled") ?></div>
			</div>
			<?php
		}
	}
	?>
	<div class="row g-3 mt-3">
		<div class="col-md-3">
			<label class="form-label"><?php etr("Created by") ?></label>
			<div class="form-control-plaintext"><?php echo $createdby ?></div>
		</div>
	</div>
</div>
<br/>
<?php
if ($recur) {
	saveButton();
	echo "<br>";
}
?>
<?php if ($items != null) { ?>
<div class='border'>
<div class='table-responsive'>
<table class='table table-sm table-striped table-hover align-middle w-100'>
<thead>
<tr>
<?php
if ($addable)
    echo "<th>" . tr("Delete") . "</th>";
?>
<th><?php etr("Product") ?></th>
<th><?php etr("Comment") ?></th>
<th class='text-end'><?php etr("Quantity") ?></th>
<th class='text-end'><?php etr("Unit price") ?></th>
<!--  <th><?php etr("Purchase price") ?></th> -->
<th class='text-end'><?php etr("Amount") ?></th>
<?php
if (!$incVAT)
    echo "<th class='text-end'>" . tr("VAT") . "</th>";
if ($addable)
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
    if ($addable)
        echo "<input type=hidden name=no_$i value='$row->no'/>";
    echo "<tr class='$class'>";
    $href = "salesorder.php?orderid=$orderid&del_no=$row->no";
    if ($addable)
        deleteColumn($href);
    echo "<td><a href='../erp/product.php?productid=$row->productid'>";
    echo "$row->productid - $row->model</a></td>";
    echo "<td>";
    if ($addable)
        textbox("comment_$i", $row->comment, 20);
    else
        echo $row->comment;
    echo "</td>";
    echo "<td class='text-end'>";
    if ($addable)
        numberbox("quantity_$i", $row->quantity, 5, false, true);
    else
        echo $row->quantity . ' ' . $row->unittype;
    echo "</td>";
    echo "<td class='text-end'>";
    $unitprice = $row->unitprice;
    if ($incVAT)
        $unitprice = $unitprice + $row->vat;
    if ($addable)
        moneybox("unitprice_$i", $unitprice);
    else
        echo formatMoney($unitprice);
    echo "</td>";
    $amount = $row->quantity * $unitprice;
    echo "<td class='text-end'>" . formatMoney($amount) . "</td>";
    if (!$incVAT) {
        $vat = $row->vat/100 * $row->unitprice * $row->quantity;
        echo "<td class='text-end'>" . formatMoney($vat) . "</td>";
    }
    if ($addable) {
        echo "<td class='text-center'>";
        echo "<input type='image' name='save' value='Save' src='../images/disk.gif'>";
        echo "</td>";
    }
    echo "</tr>
";
    $sum += $amount;
    $vatSum += $row->vat/100 * $row->quantity * $row->unitprice;
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}

if ($addable) {
    hidden('count', $i);
    echo "<tr class='$class'>";
    echo "<td/>";
    echo "<td>";
    textbox('productid_new', $productid, 10);
    button("Search", "search", "../erp/products.php?mode=selectproduct&orderid=$orderid");
    echo "</td>";
    echo "<td>";
    textbox('comment_new', '', 20);
    echo "</td>";
    echo "<td class='text-end'>";
    numberbox('quantity_new', 1, 5, false, true);
    echo "</td>";
    echo "<td class='text-end'>";
    moneybox('unitprice_new', $unitprice_new);
    echo "</td>";
    echo "<td class='text-end'>";
    if (!isEmpty($purchaseprice_new))
        echo formatMoney($purchaseprice_new);
    echo "</td>";
    echo "<td><input type=submit name=add value='Add'/></td>";
    echo "</tr>";
}
?>
<tr>
<?php
if ($addable) echo "<td/>";
?>
<td/>
<td/>
<td/>
<?php
if ($incVAT)
    echo "<td class='text-end'>" . tr("VAT") . ":</td>";
else {
    echo "<td/>";
    echo "<td class='text-end'>" . formatMoney($sum) . "</td>";
}
?>
<td class='text-end'><?php echo formatMoney($vatSum) ?></td>
</tr>
<?php $colspan = $addable ? 4 : 3 ?>
<tr>
<td colspan='<?php echo $colspan ?>'/> 
<tr>
<td colspan='<?php echo $colspan ?>'/> 
<td class='text-end label'><?php etr("To pay") ?>:</td>
<td class='text-end'><?php echo formatMoney($toPay) ?></td>
</tr>
</tbody>
</table>
</div>
</div>
<br/>
<?php } ?>

<?php
if ($new) {
	button("Create", "save");
} else {
	if (!$recur) {
		if ($customerid == CUSTOMERID_CASH) {
			if (!$fullyPayed && !$cancelled) {
				button('Receipt', 'finish', null, 'F');
				echo "&nbsp;";
			}
			if ($fullyPayed) {
				button('New order', 'new',
				       "salesorder.php?customerid=$customerid&action=create", 'N');
				echo "<script>document.postform.new.focus();</script>";
				echo "&nbsp;";
			}
		}
		else {
			if (isEmpty($invoice_transid) && !$cancelled) {
				button('doInvoice', 'invoice');
				echo "&nbsp;";
			} else if (!$fullyPayed && !$cancelled) {
				button('Receipt', 'pay');
				echo "&nbsp;";
			}
		}
		if (!isEmpty($invoice_transid)) {
			button("Credit order", 'credit',
			       "credit_salesorder.php?action=create&credit_orgid=$orderid&customerid=$customerid");
			echo "&nbsp;";
		}
	}
	if (!$cancelled) {
		button("Cancel order", 'cancel');
		echo "&nbsp;";
	}
	button("Show stock moves", "moves", "../erp/stockmoves.php?salesorderid=$orderid");
}
?>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
<?php bottom() ?>

</body>
