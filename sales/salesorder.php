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
		header("Location: salesorder.php?orderid=" . urlencode($orderid));
		die;
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
		where productid=" . sql_string($productid));
	}

	$customer = null;
	if (!isEmpty($customerid)) {
		$customer = find("select name from customer where customerid=$customerid");
	}

	$locations = rs2array(query("select locationid, name from location"));
	$productSuggestions = array();
	if ($addable) {
		$suggestionListId = isset($listid) ? (int)$listid : 0;
		$productSuggestionRows = query("select p.productid, p.model, p.description, p.barcode, sp.price from product p left join sales_price sp on sp.productid=p.productid and sp.listid=$suggestionListId where p.active=1 order by p.model");
		while ($suggestion = fetch($productSuggestionRows)) {
			$productSuggestions[] = array(
				'id' => (string)$suggestion->productid,
				'model' => (string)$suggestion->model,
				'description' => (string)$suggestion->description,
				'barcode' => (string)$suggestion->barcode,
				'price' => $suggestion->price === null ? '' : (string)$suggestion->price
			);
		}
	}
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
	var focusTarget = <?php
	if (getParam("method_changed") && $method == METHOD_CARD) 
		echo "document.postform.creditcardno";
	else
		echo "document.getElementById('product-search-new')";
	?>;
	if (focusTarget)
		focusTarget.focus();
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

var salesOrderProducts = <?php echo json_encode($productSuggestions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

function initProductPicker()
{
	var input = document.getElementById('product-search-new');
	var value = document.getElementById('productid-new');
	var results = document.getElementById('product-search-results');
	if (!input || !value || !results)
		return;
	document.body.appendChild(results);

	var activeIndex = -1;
	var matches = [];

	function closeResults() {
		results.hidden = true;
		results.innerHTML = '';
		activeIndex = -1;
		input.setAttribute('aria-expanded', 'false');
	}

	function positionResults() {
		var rect = input.getBoundingClientRect();
		results.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - Math.min(480, window.innerWidth - 16) - 8)) + 'px';
		results.style.top = (rect.bottom + 6) + 'px';
		results.style.width = Math.min(480, window.innerWidth - 16) + 'px';
	}

	function choose(product) {
		value.value = product.id;
		input.value = product.model;
		input.dataset.selectedLabel = product.model;
		if (document.postform.unitprice_new && product.price !== '')
			document.postform.unitprice_new.value = product.price;
		closeResults();
		document.postform.quantity_new.focus();
		document.postform.quantity_new.select();
	}

	function setActive(index) {
		var options = results.querySelectorAll('[role=option]');
		if (!options.length)
			return;
		activeIndex = (index + options.length) % options.length;
		options.forEach(function(option, optionIndex) {
			option.classList.toggle('is-active', optionIndex === activeIndex);
			option.setAttribute('aria-selected', optionIndex === activeIndex ? 'true' : 'false');
		});
		options[activeIndex].scrollIntoView({block: 'nearest'});
	}

	function render() {
		var term = input.value.trim().toLocaleLowerCase();
		value.value = '';
		delete input.dataset.selectedLabel;
		if (!term) {
			closeResults();
			return;
		}
		matches = salesOrderProducts.filter(function(product) {
			return [product.model, product.id, product.barcode, product.description].join(' ').toLocaleLowerCase().indexOf(term) !== -1;
		}).slice(0, 8);
		results.innerHTML = '';
		activeIndex = -1;
		if (!matches.length) {
			var empty = document.createElement('div');
			empty.className = 'sales-order-product-empty';
			empty.textContent = <?php echo json_encode(tr('No products found')) ?>;
			results.appendChild(empty);
		} else {
			matches.forEach(function(product, index) {
				var option = document.createElement('button');
				option.type = 'button';
				option.setAttribute('role', 'option');
				option.innerHTML = '<span><strong></strong><small></small></span><em></em>';
				option.querySelector('strong').textContent = product.model;
				option.querySelector('small').textContent = product.description || (product.barcode ? 'Barcode: ' + product.barcode : '');
				option.querySelector('em').textContent = '#' + product.id;
				option.addEventListener('mousedown', function(event) { event.preventDefault(); choose(matches[index]); });
				results.appendChild(option);
			});
		}
		results.hidden = false;
		positionResults();
		input.setAttribute('aria-expanded', 'true');
	}

	input.addEventListener('input', render);
	input.addEventListener('focus', function() { if (input.value && !input.dataset.selectedLabel) render(); });
	input.addEventListener('blur', function() { setTimeout(closeResults, 120); });
	window.addEventListener('resize', closeResults);
	window.addEventListener('scroll', closeResults, true);
	input.addEventListener('keydown', function(event) {
		if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
			event.preventDefault();
			if (results.hidden) render();
			setActive(activeIndex + (event.key === 'ArrowDown' ? 1 : -1));
		} else if (event.key === 'Enter' && !results.hidden && matches.length) {
			event.preventDefault();
			choose(matches[activeIndex < 0 ? 0 : activeIndex]);
		} else if (event.key === 'Escape') {
			closeResults();
		}
	});
}
</script>
</head>

<body onLoad="onLoad(); initProductPicker()">
<?php
menubar('index.php', 'salesorder_help.php');
title(tr("Sales order")) ;
?>

<main class="sales-order-page">
	<header class="sales-order-intro">
		<a class="sales-order-back" href="sales.php" aria-label="<?php etr("Sales orders") ?>">&#8592;</a>
		<div class="sales-order-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 4h14v16H5V4Zm3 3h8v4H8V7Zm0 8h2m2 0h2m2 0h0M8 18h2m2 0h2m2 0h0"/></svg></div>
		<div class="sales-order-heading">
			<span><?php echo $customerid == CUSTOMERID_CASH ? tr("Point of sale") : tr("Sales") ?></span>
			<h1><?php echo $customerid == CUSTOMERID_CASH ? tr("Cash sale") : tr("Sales order") ?> <em>#<?php echo htmlspecialchars($orderid) ?></em></h1>
			<p><?php etr("Add products, review totals, take payment, and print the receipt.") ?></p>
		</div>
		<?php if ($cancelled) { ?><span class="stock-move-status is-cancelled"><?php etr("Cancelled") ?></span><?php } else if ($fullyPayed) { ?><span class="stock-move-status is-received"><?php etr("Paid") ?></span><?php } else { ?><span class="stock-move-status is-draft"><?php etr("In progress") ?></span><?php } ?>
	</header>

<?php
if ($mess != null) {
	echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($mess) . "</div>";
}
if (array_key_exists('finish', $_POST)) {
	$printUrl = "invoice_pdf.php?orderid=" . urlencode($orderid) . "&type=receipt";
	echo "<div class='alert alert-success d-flex align-items-center justify-content-between gap-3' role='status'>";
	echo "<span>" . tr("The sale is complete. The receipt is ready to print.") . "</span>";
	echo "<a class='btn btn-primary btn-sm text-nowrap' href='$printUrl' onclick='return thERPPrintDocument(this.href)'>" . tr("Print receipt") . "</a>";
	echo "</div>";
}
?>

<?php if (!$new) { ?>
<section class="sales-order-summary" aria-label="<?php etr("Order summary") ?>">
	<div><span><?php etr("Customer") ?></span><strong><?php echo htmlspecialchars($customer->name) ?></strong></div>
	<div><span><?php etr("Order date") ?></span><strong><?php echo htmlspecialchars(date(DATE_PATTERN, $orderdate)) ?></strong></div>
	<div><span><?php etr("Order total") ?></span><strong><?php echo formatMoney($toPay) ?></strong></div>
	<div class="<?php echo $fullyPayed ? 'is-paid' : 'is-due' ?>"><span><?php echo $fullyPayed ? tr("Payment status") : tr("Amount paid") ?></span><strong><?php echo $fullyPayed ? tr("Fully paid") : formatMoney($payed) ?></strong></div>
</section>
<?php } ?>

<form name="postform" action="salesorder.php" method="POST">
<input type="hidden" name="customerid" value="<?php echo htmlspecialchars($customerid) ?>" />
<section class="sales-order-overview card border-0 shadow-sm mb-3">
	<div class="card-header bg-white sales-order-card-header"><div><span><?php etr("Sale details") ?></span><h2><?php etr("Order information") ?></h2></div></div>
	<div class="card-body">
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
			<div class="form-control-plaintext fw-semibold"><?php echo htmlspecialchars($customer->name) ?></div>
		</div>
		<?php if ($customerid != CUSTOMERID_CASH) { ?>
		<div class="col-md-4">
			<label class="form-label"><?php echo tr("Ordered by") ?></label>
			<?php if (isEmpty($invoice_transid)) { textbox('orderedby', $rec->orderedby); } else { echo "<div class='form-control-plaintext'>" . htmlspecialchars($rec->orderedby) . "</div>"; } ?>
		</div>
		<div class="col-auto align-self-end">
			<?php if (isEmpty($invoice_transid)) { ?><input type="submit" name="save" value="<?php etr("Save") ?>" /><?php } ?>
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
				combobox('locationid', $locations, $locationid, false, 'submitForm()');
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
					<div class="col-md-12 sales-order-links">
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
					<div class="col-md-12 sales-order-receipt-status">
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
				<div class="col-md-12 sales-order-receipt-status">
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
			<div class="form-control-plaintext"><?php echo htmlspecialchars($createdby) ?></div>
		</div>
	</div>
</div></section>
<?php
if ($recur) {
	saveButton();
	echo "<br>";
}
?>
<?php if ($items != null) { ?>
<section class="sales-order-lines card border-0 shadow-sm overflow-hidden mb-3">
<div class="card-header bg-white sales-order-card-header"><div><span><?php etr("Cart") ?></span><h2><?php etr("Products and totals") ?></h2></div></div>
<div class='erp-table-responsive'>
<table class='erp-data-table sales-order-table'>
<thead>
<tr>
<?php
if ($addable)
    echo "<th>" . tr("Delete") . "</th>";
if ($addable)
    echo "<th class='text-center'>" . tr("Save") . "</th>";
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
    echo "<tr class='$class'>";
    $href = "salesorder.php?orderid=$orderid&del_no=$row->no";
    if ($addable)
        deleteColumn($href);
    if ($addable) {
        echo "<td class='text-center'>";
        echo "<input type='submit' class='sales-line-save' name='save' value='" . tr("Save") . "'>";
        echo "</td>";
    }
    echo "<td>";
    if ($addable)
        echo "<input type='hidden' name='no_$i' value='" . htmlspecialchars($row->no) . "'/>";
    echo "<a href='../erp/product.php?productid=" . urlencode($row->productid) . "'>";
    echo htmlspecialchars($row->productid . ' - ' . $row->model) . "</a></td>";
    echo "<td>";
    if ($addable)
        textbox("comment_$i", $row->comment, 20);
    else
        echo htmlspecialchars($row->comment);
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
    echo "</tr>
";
    $sum += $amount;
    $vatSum += $row->vat/100 * $row->quantity * $row->unitprice;
    $class = ($class == "odd" ? "even" : "odd");
    $i++;
}

if ($addable) {
    echo "<tr class='sales-order-add-row'>";
    echo "<td/>";
    echo "<td class='sales-order-add-action'><input type=submit name=add value='" . tr("Add") . "'/></td>";
    echo "<td>";
    $selectedProductLabel = isEmpty($productid) ? '' : findValue("select model from product where productid=" . sql_string($productid), $productid);
    echo "<div class='sales-order-product-picker'>";
    echo "<div class='sales-order-product-combobox'>";
    echo "<span class='sales-order-product-search-icon' aria-hidden='true'>&#9906;</span>";
    echo "<input id='product-search-new' type='search' value='" . htmlspecialchars($selectedProductLabel, ENT_QUOTES, 'UTF-8') . "'" . (!isEmpty($productid) ? " data-selected-label='" . htmlspecialchars($selectedProductLabel, ENT_QUOTES, 'UTF-8') . "'" : "") . " placeholder='" . htmlspecialchars(tr("Search products or scan barcode"), ENT_QUOTES, 'UTF-8') . "' autocomplete='off' role='combobox' aria-autocomplete='list' aria-expanded='false' aria-controls='product-search-results'>";
    echo "<input id='productid-new' type='hidden' name='productid_new' value='" . htmlspecialchars($productid, ENT_QUOTES, 'UTF-8') . "'>";
    echo "<div id='product-search-results' class='sales-order-product-results' role='listbox' hidden></div>";
    echo "</div>";
    button("Browse all", "search", "../erp/products.php?mode=selectproduct&orderid=$orderid");
    echo "</div><small>" . tr("Search by product name, ID, barcode, or description. Use the arrow keys and Enter to select.") . "</small>";
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
	if (!$incVAT)
		echo "<td></td>";
    echo "</tr>";
}
?>
<?php $totalColumns = 5 + (!$incVAT ? 1 : 0) + ($addable ? 2 : 0); ?>
<tr class="sales-order-total-row"><td colspan="<?php echo $totalColumns - 1 ?>" class="text-end"><?php etr("Subtotal") ?>:</td><td class="text-end fw-semibold"><?php echo formatMoney($sum) ?></td></tr>
<?php if (!$incVAT) { ?><tr class="sales-order-total-row"><td colspan="<?php echo $totalColumns - 1 ?>" class="text-end"><?php etr("VAT") ?>:</td><td class="text-end fw-semibold"><?php echo formatMoney($vatSum) ?></td></tr><?php } ?>
<tr class="sales-order-pay-row"><td colspan="<?php echo $totalColumns - 1 ?>" class="text-end"><?php etr("To pay") ?>:</td><td class="text-end"><?php echo formatMoney($toPay) ?></td></tr>
</tbody>
</table>
<?php hidden('count', $i); ?>
</div></section>
<?php } ?>

<div class="sales-order-actions">
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
</div>
<input type="hidden" name="new" value="<?php echo $new ?>"/>
</form>
</main>
<?php bottom() ?>

</body>
